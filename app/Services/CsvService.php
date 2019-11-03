<?php


namespace App\Services;


class CsvService
{
    public function readCsv(string $filepath): array
    {
        $list = [];
        $handle = fopen($filepath, 'r');
        $row = 0;

        while (($line = fgetcsv($handle, 1000)) !== false) {
            if ($row === 0) {
                $row++;
                continue;
            }
            array_push($list, $line);
        }

        fclose($handle);

        return $list;
    }
}