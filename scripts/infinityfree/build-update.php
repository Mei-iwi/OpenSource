<?php

declare(strict_types=1);

/*
 * Prepare a conservative update package from a production Git tag.
 * This script never deletes remote files and never copies .env or uploads.
 */

$projectRoot = realpath(__DIR__.'/../..');
$tag = null;

for ($index = 1; $index < $argc; $index++) {
    if ($argv[$index] === '--from') {
        $tag = $argv[++$index] ?? null;
        break;
    }
}

if ($projectRoot === false || $tag === null || $tag === '') {
    fail('Usage: php scripts/infinityfree/build-update.php --from <production-tag>');
}

$reference = trim((string) shell_exec('git -C '.quote($projectRoot).' rev-parse --verify '.quote($tag).' 2>NUL'));
if ($reference === '') {
    fail('The production tag does not resolve: '.$tag);
}

$diffOutput = (string) shell_exec(
    'git -C '.quote($projectRoot).' diff --name-status --find-renames '
    .quote($tag).'..HEAD -- 2>NUL',
);
$changes = parseChanges($diffOutput);
$frontendChanged = false;
$composerChanged = false;
$migrationsChanged = false;

foreach ($changes as $change) {
    $path = $change['path'];
    $frontendChanged = $frontendChanged
        || str_starts_with($path, 'resources/')
        || in_array($path, ['package.json', 'package-lock.json', 'vite.config.js', 'tailwind.config.js'], true);
    $composerChanged = $composerChanged || in_array($path, ['composer.json', 'composer.lock'], true);
    $migrationsChanged = $migrationsChanged || str_starts_with($path, 'database/migrations/');
}

$distRoot = $projectRoot.'/dist/infinityfree';
$updateRoot = $distRoot.'/update';
ensureDirectory($distRoot);
removeDirectory($updateRoot);
ensureDirectory($updateRoot);

if ($frontendChanged || $composerChanged) {
    runCommand(quote(PHP_BINARY).' scripts/infinityfree/build-release.php', $projectRoot);
}

foreach ($changes as $change) {
    if ($change['deleted'] || isForbiddenUpdatePath($change['path'])) {
        continue;
    }

    $source = $projectRoot.'/'.$change['path'];
    if (! is_file($source)) {
        continue;
    }

    copyFile($source, $updateRoot.'/'.$change['path']);
}

if ($frontendChanged) {
    copyTree(
        $distRoot.'/htdocs/public/build',
        $updateRoot.'/public/build',
    );
}

if ($composerChanged) {
    copyTree(
        $distRoot.'/htdocs/vendor',
        $updateRoot.'/vendor',
    );
}

$deleted = array_values(array_filter(
    array_map(
        static fn (array $change): ?string => $change['deleted'] ? $change['path'] : null,
        $changes,
    ),
));

$plan = [
    'FROM_TAG='.$tag,
    'TO_COMMIT='.trim((string) shell_exec('git -C '.quote($projectRoot).' rev-parse HEAD')),
    'FRONTEND_CHANGED='.($frontendChanged ? 'YES' : 'NO'),
    'COMPOSER_CHANGED='.($composerChanged ? 'YES' : 'NO'),
    'MIGRATIONS_CHANGED='.($migrationsChanged ? 'YES' : 'NO'),
    'FULL_VENDOR_REQUIRED='.($composerChanged ? 'YES' : 'NO'),
    'DB_PATCH_REQUIRED='.($migrationsChanged ? 'YES' : 'NO'),
    '',
    'Changed files:',
    ...array_map(static fn (array $change): string => $change['status']."\t".$change['path'], $changes),
];
file_put_contents($updateRoot.'/update-plan.txt', implode(PHP_EOL, $plan).PHP_EOL);
file_put_contents(
    $updateRoot.'/delete-list.txt',
    $deleted === [] ? "No remote deletions identified.\n" : implode(PHP_EOL, $deleted).PHP_EOL,
);

echo 'Update package created at: '.$updateRoot.PHP_EOL;
echo 'FRONTEND_CHANGED='.($frontendChanged ? 'YES' : 'NO').PHP_EOL;
echo 'COMPOSER_CHANGED='.($composerChanged ? 'YES' : 'NO').PHP_EOL;
echo 'MIGRATIONS_CHANGED='.($migrationsChanged ? 'YES' : 'NO').PHP_EOL;
echo 'FULL_VENDOR_REQUIRED='.($composerChanged ? 'YES' : 'NO').PHP_EOL;
echo 'DB_PATCH_REQUIRED='.($migrationsChanged ? 'YES' : 'NO').PHP_EOL;

function fail(string $message): void
{
    fwrite(STDERR, "INFINITYFREE_UPDATE_BLOCKED\n".$message."\n");
    exit(1);
}

function quote(string $value): string
{
    return escapeshellarg($value);
}

function runCommand(string $command, string $cwd): void
{
    $previousDirectory = getcwd();
    if ($previousDirectory === false || ! chdir($cwd)) {
        fail('Unable to change directory before command: '.$command);
    }
    passthru($command, $exitCode);
    chdir($previousDirectory);
    if ($exitCode !== 0) {
        fail('Command failed with exit code '.$exitCode.': '.$command);
    }
}

/**
 * @return list<array{status:string,path:string,deleted:bool}>
 */
function parseChanges(string $output): array
{
    $changes = [];
    foreach (preg_split('/\R/', trim($output)) ?: [] as $line) {
        if ($line === '') {
            continue;
        }
        $parts = explode("\t", $line);
        $status = $parts[0] ?? '';
        $path = $parts[count($parts) - 1] ?? '';
        $changes[] = [
            'status' => $status,
            'path' => str_replace('\\', '/', $path),
            'deleted' => str_starts_with($status, 'D'),
        ];
    }
    return $changes;
}

function isForbiddenUpdatePath(string $path): bool
{
    return str_starts_with($path, '.env')
        || str_starts_with($path, 'storage/app/private/')
        || str_starts_with($path, 'storage/framework/')
        || str_starts_with($path, 'storage/logs/')
        || str_starts_with($path, 'tests/')
        || str_starts_with($path, 'node_modules/')
        || str_starts_with($path, 'vendor/')
        || str_starts_with($path, 'dist/');
}

function ensureDirectory(string $path): void
{
    if (! is_dir($path) && ! mkdir($path, 0775, true) && ! is_dir($path)) {
        fail('Unable to create directory: '.$path);
    }
}

function removeDirectory(string $path): void
{
    if (! is_dir($path)) {
        return;
    }
    foreach (scandir($path) ?: [] as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $child = $path.'/'.$item;
        if (is_dir($child) && ! is_link($child)) {
            removeDirectory($child);
            @rmdir($child);
        } else {
            @unlink($child);
        }
    }
    @rmdir($path);
}

function copyFile(string $source, string $destination): void
{
    ensureDirectory(dirname($destination));
    if (! copy($source, $destination)) {
        fail('Unable to copy update file: '.$source);
    }
}

function copyTree(string $source, string $destination): void
{
    if (! is_dir($source)) {
        fail('Required update source is missing: '.$source);
    }
    ensureDirectory($destination);
    foreach (scandir($source) ?: [] as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $sourcePath = $source.'/'.$item;
        $destinationPath = $destination.'/'.$item;
        if (is_link($sourcePath)) {
            continue;
        }
        if (is_dir($sourcePath)) {
            copyTree($sourcePath, $destinationPath);
        } else {
            copyFile($sourcePath, $destinationPath);
        }
    }
}
