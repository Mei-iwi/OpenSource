<?php

declare(strict_types=1);

/*
 * Build a complete, upload-ready InfinityFree package locally.
 *
 * This script intentionally never reads or copies a real .env and never
 * touches a production system. The only directory it removes is the exact
 * generated output directory: dist/infinityfree.
 */

$projectRoot = realpath(__DIR__.'/../..');

if ($projectRoot === false) {
    fail('Unable to resolve the project root.');
}

$distRoot = $projectRoot.'/dist/infinityfree';
$stagingRoot = $distRoot.'/htdocs';

if (! is_file($projectRoot.'/composer.json')
    || ! is_file($projectRoot.'/composer.lock')
    || ! is_file($projectRoot.'/package-lock.json')
    || ! is_file($projectRoot.'/artisan')
    || ! is_file($projectRoot.'/deploy/infinityfree/htdocs.htaccess')) {
    fail('Required project files are missing. Expected composer/package lockfiles, artisan, and the InfinityFree .htaccess.');
}

if (! is_dir($projectRoot.'/vendor')) {
    fail('The local vendor directory is missing. Run composer install before building.');
}

if (! is_dir($projectRoot.'/node_modules')) {
    fail('The local node_modules directory is missing. Run npm ci before building.');
}

ensureDirectory($distRoot);
removeDirectory($distRoot);
ensureDirectory($stagingRoot);

echo "Clearing local Laravel caches...\n";
runCommand(quote(PHP_BINARY).' artisan optimize:clear', $projectRoot);

echo "Installing frontend dependencies and building Vite assets...\n";
runCommand('npm ci', $projectRoot);
runCommand('npm run build', $projectRoot);

if (! is_file($projectRoot.'/public/build/manifest.json')) {
    fail('PUBLIC_BUILD_MISSING: public/build/manifest.json was not generated.');
}

echo "Copying application files into staging...\n";
copyTree($projectRoot, $stagingRoot);

ensureRuntimeDirectories($stagingRoot);
copy($projectRoot.'/deploy/infinityfree/htdocs.htaccess', $stagingRoot.'/.htaccess');

echo "Installing production Composer dependencies inside staging...\n";
// Keep dependency installation independent from Composer's classmap pass so
// the staged vendor can be audited before any fallback is applied.
runCommand('composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --no-autoloader', $stagingRoot);
runCommand('composer config optimize-autoloader true', $stagingRoot);
runCommand('composer dump-autoload --no-dev --no-scripts', $stagingRoot);
// Keep the package manifest aligned with the source project.
copy($projectRoot.'/composer.json', $stagingRoot.'/composer.json');

clearStagedMachineCaches($stagingRoot);

echo "Checking package contents and PHP file sizes...\n";
$forbidden = findForbiddenFiles($stagingRoot);
if ($forbidden !== []) {
    fail("FORBIDDEN_FILES_IN_PACKAGE\n".implode("\n", $forbidden));
}

$oversized = findOversizedPhpFiles($stagingRoot);
if ($oversized !== []) {
    echo "PHP files over 1 MB found after production Composer install; regenerating non-optimized autoload files...\n";
    runCommand('composer config optimize-autoloader false', $stagingRoot);
    runCommand('composer dump-autoload --no-dev --no-scripts', $stagingRoot);
    copy($projectRoot.'/composer.json', $stagingRoot.'/composer.json');
    clearStagedMachineCaches($stagingRoot);
    $oversized = findOversizedPhpFiles($stagingRoot);
}

if ($oversized !== []) {
    $lines = array_map(
        static fn (array $file): string => $file['path'].' ('.$file['size'].' bytes)',
        $oversized,
    );
    fail("BLOCKED_INFINITYFREE_PHP_FILE_SIZE\n".implode("\n", $lines));
}

verifyPackage($stagingRoot);

$manifest = buildManifest($projectRoot, $stagingRoot);
file_put_contents(
    $distRoot.'/release-manifest.json',
    json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL,
);

$summary = [
    'TOTAL_FILES' => (string) $manifest['file_count'],
    'TOTAL_SIZE' => (string) $manifest['total_size'],
    'PHP_FILES_OVER_1MB' => '0',
    'PUBLIC_BUILD_PRESENT' => is_file($stagingRoot.'/public/build/manifest.json') ? 'YES' : 'NO',
    'VENDOR_PRESENT' => is_file($stagingRoot.'/vendor/autoload.php') ? 'YES' : 'NO',
    'ROOT_HTACCESS_PRESENT' => is_file($stagingRoot.'/.htaccess') ? 'YES' : 'NO',
    'PUBLIC_HTACCESS_PRESENT' => is_file($stagingRoot.'/public/.htaccess') ? 'YES' : 'NO',
    'REAL_ENV_PRESENT' => hasRealEnvFile($stagingRoot) ? 'YES' : 'NO',
];

echo "\nRelease package created at: ".$distRoot."\n";
foreach ($summary as $key => $value) {
    echo $key.'='.$value."\n";
}

exit(0);

/**
 * @return never
 */
function fail(string $message): void
{
    fwrite(STDERR, "INFINITYFREE_RELEASE_BLOCKED\n".$message."\n");
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

    $items = scandir($path);
    if ($items === false) {
        fail('Unable to inspect generated directory: '.$path);
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $child = $path.DIRECTORY_SEPARATOR.$item;
        if (is_dir($child) && ! is_link($child)) {
            removeDirectory($child);
            removeDirectoryWithRetry($child);
        } elseif (! unlink($child)) {
            fail('Unable to remove generated file: '.$child);
        }
    }
}

function removeDirectoryWithRetry(string $path): void
{
    for ($attempt = 0; $attempt < 10 && is_dir($path); $attempt++) {
        if (@rmdir($path)) {
            return;
        }

        usleep(100_000);
        removeDirectory($path);
    }

    if (is_dir($path)) {
        fail('Unable to remove generated directory: '.$path);
    }
}

function copyTree(string $source, string $destination, string $relative = ''): void
{
    $items = scandir($source);
    if ($items === false) {
        fail('Unable to inspect source directory: '.$source);
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $sourcePath = $source.DIRECTORY_SEPARATOR.$item;
        $relativePath = trim($relative.'/'.$item, '/');
        $destinationPath = $destination.DIRECTORY_SEPARATOR.$item;

        // public/storage is a local storage:link junction/symlink. Private
        // media does not use it, and copying the link target would package
        // local uploads.
        if ($relativePath === 'public/storage') {
            continue;
        }

        if (isExcluded($relativePath)) {
            continue;
        }

        if (isDirectoryLink($sourcePath)) {
            continue;
        }

        if (is_dir($sourcePath)) {
            ensureDirectory($destinationPath);
            copyTree($sourcePath, $destinationPath, $relativePath);
            continue;
        }

        ensureDirectory(dirname($destinationPath));
        if (! copy($sourcePath, $destinationPath)) {
            fail('Unable to copy file: '.$relativePath);
        }
    }
}

function isExcluded(string $relativePath): bool
{
    $normalized = str_replace('\\', '/', $relativePath);
    $segments = explode('/', $normalized);
    $topLevel = $segments[0] ?? '';
    $baseName = basename($normalized);

    if (in_array($topLevel, [
        '.git', '.github', 'node_modules', 'vendor', 'tests', 'coverage',
        'docker', 'codex-prompts', 'dist', '.idea', '.vscode', '.fleet',
        '.nova', '.zed', 'docs', 'scripts', 'deploy',
    ], true)) {
        return true;
    }

    if (in_array($baseName, [
        'Dockerfile', 'docker-compose.yml', 'auth.json', '.phpunit.result.cache',
        '.dockerignore', '.styleci.yml', '.editorconfig', '.gitattributes',
        '.gitignore', 'README.md', 'CHANGELOG.md', 'phpunit.xml',
        'package.json', 'package-lock.json', 'postcss.config.js',
        'tailwind.config.js', 'vite.config.js',
    ], true) || str_starts_with($baseName, '.env') || preg_match('/\\.(?:sql|log)$/i', $baseName)) {
        return true;
    }

    if ($normalized === 'bootstrap/cache' && is_dir($normalized)) {
        return false;
    }

    if (str_starts_with($normalized, 'bootstrap/cache/') && str_ends_with($normalized, '.php')) {
        return true;
    }

    foreach ([
        'storage/app/private/',
        'storage/app/public/',
        'storage/framework/cache/data/',
        'storage/framework/sessions/',
        'storage/framework/testing/',
        'storage/framework/views/',
        'storage/logs/',
    ] as $runtimePath) {
        if (str_starts_with($normalized, $runtimePath) && $baseName !== '.gitignore') {
            return true;
        }
    }

    return false;
}

function ensureRuntimeDirectories(string $stagingRoot): void
{
    foreach ([
        'storage/app/private/uploads',
        'storage/framework/cache/data',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/logs',
        'bootstrap/cache',
    ] as $relativePath) {
        $directory = $stagingRoot.'/'.$relativePath;
        ensureDirectory($directory);
        $placeholder = $directory.'/.gitkeep';
        if (! is_file($placeholder)) {
            file_put_contents($placeholder, "Generated package runtime directory.\n");
        }
    }
}

function isDirectoryLink(string $path): bool
{
    if (is_link($path)) {
        return true;
    }

    // PHP reports Windows directory junctions as directories with a zero mode
    // rather than as symbolic links. They must not be copied as regular dirs.
    if (DIRECTORY_SEPARATOR === '\\' && is_dir($path)) {
        $stat = @lstat($path);

        return is_array($stat) && ($stat['mode'] ?? 1) === 0;
    }

    return false;
}

function clearStagedMachineCaches(string $stagingRoot): void
{
    foreach ([
        'bootstrap/cache',
        'storage/framework/views',
        'storage/framework/sessions',
        'storage/framework/cache/data',
        'storage/logs',
    ] as $relativePath) {
        $directory = $stagingRoot.'/'.$relativePath;
        if (! is_dir($directory)) {
            continue;
        }

        foreach (scandir($directory) ?: [] as $item) {
            if ($item === '.' || $item === '..' || $item === '.gitignore' || $item === '.gitkeep') {
                continue;
            }
            $path = $directory.'/'.$item;
            if (is_dir($path)) {
                removeDirectory($path);
                rmdir($path);
            } else {
                unlink($path);
            }
        }
    }
}

/**
 * @return list<string>
 */
function findForbiddenFiles(string $stagingRoot): array
{
    $forbidden = [];
    foreach (filesUnder($stagingRoot) as $file) {
        $relative = $file['path'];
        $baseName = basename($relative);
        if (str_starts_with($baseName, '.env')
            || in_array($baseName, ['auth.json', 'Dockerfile', 'docker-compose.yml'], true)
            || preg_match('/\\.(?:sql|log)$/i', $baseName)
            || preg_match('#^(?:\\.git|\\.github|node_modules|tests|coverage|docker|codex-prompts)(?:/|$)#', $relative)) {
            $forbidden[] = $relative;
        }
    }

    return $forbidden;
}

/**
 * @return list<array{path:string,size:int}>
 */
function findOversizedPhpFiles(string $stagingRoot): array
{
    $oversized = [];
    foreach (filesUnder($stagingRoot) as $file) {
        if (strtolower(pathinfo($file['path'], PATHINFO_EXTENSION)) === 'php'
            && $file['size'] > 1_000_000) {
            $oversized[] = ['path' => $file['path'], 'size' => $file['size']];
        }
    }

    return $oversized;
}

/**
 * @return list<array{path:string,absolute:string,size:int}>
 */
function filesUnder(string $root): array
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if (! $file->isFile()) {
            continue;
        }
        $absolute = $file->getPathname();
        $relative = str_replace('\\', '/', substr($absolute, strlen($root) + 1));
        $files[] = ['path' => $relative, 'absolute' => $absolute, 'size' => $file->getSize()];
    }

    usort($files, static fn (array $a, array $b): int => $a['path'] <=> $b['path']);

    return $files;
}

function verifyPackage(string $stagingRoot): void
{
    foreach ([
        'app',
        'bootstrap',
        'config',
        'database',
        'public/index.php',
        'public/.htaccess',
        'public/build/manifest.json',
        'resources',
        'routes',
        'storage/app/private/uploads',
        'storage/framework/cache/data',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/logs',
        'bootstrap/cache',
        'vendor/autoload.php',
        'artisan',
        'composer.json',
        'composer.lock',
    ] as $relativePath) {
        $path = $stagingRoot.'/'.$relativePath;
        if (! file_exists($path)) {
            fail('Required package path is missing: '.$relativePath);
        }
    }
}

/**
 * @return array<string,mixed>
 */
function buildManifest(string $projectRoot, string $stagingRoot): array
{
    $files = [];
    $totalSize = 0;
    foreach (filesUnder($stagingRoot) as $file) {
        $files[$file['path']] = [
            'size' => $file['size'],
            'sha256' => hash_file('sha256', $file['absolute']),
        ];
        $totalSize += $file['size'];
    }

    $composer = json_decode((string) file_get_contents($projectRoot.'/composer.json'), true);
    $lock = json_decode((string) file_get_contents($projectRoot.'/composer.lock'), true);
    $laravelVersion = null;
    foreach (($lock['packages'] ?? []) as $package) {
        if (($package['name'] ?? null) === 'laravel/framework') {
            $laravelVersion = $package['version'] ?? null;
            break;
        }
    }

    $gitCommit = trim((string) shell_exec('git -C '.quote($projectRoot).' rev-parse HEAD 2>NUL'));

    return [
        'git_commit' => $gitCommit !== '' ? $gitCommit : 'UNKNOWN',
        'build_timestamp' => gmdate('c'),
        'laravel_version' => $laravelVersion,
        'php_requirement' => $composer['require']['php'] ?? null,
        'file_count' => count($files),
        'total_size' => $totalSize,
        'files' => $files,
    ];
}

function hasRealEnvFile(string $stagingRoot): bool
{
    foreach (scandir($stagingRoot) ?: [] as $item) {
        if ($item === '.env' || $item === '.env.production') {
            return true;
        }
    }

    return false;
}
