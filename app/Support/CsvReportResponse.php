<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvReportResponse
{
    public static function download(string $filename, array $headers, iterable $rows, callable $transform): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows, $transform): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, array_map(CsvCell::safe(...), $transform($row)));
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
