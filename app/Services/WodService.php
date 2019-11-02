<?php


namespace App\Services;


use App\Models\Participant;

class WodService
{
    public function getParticipantsListFromCSV(string $filepath): array
    {
        $list = [];
        $file = fopen($filepath, 'r');
        $row = 0;

        while (($line = fgetcsv($file, 1000)) !== false) {
            if ($row === 0) {
                $row++;
                continue;
            }
            array_push($list, new Participant($line[0], $line[1]));
        }

        return $list;
    }
}