<?php


namespace App\Services;


use App\Models\Exercise;
use App\Models\Participant;

class WodService
{
    /** @var CsvService $csvService */
    private $csvService;

    public function __construct()
    {
        $this->csvService = new CsvService();
    }

    public function getParticipantsListFromCSV(string $filepath): array
    {
        $list = [];
        $participants = $this->csvService->readCsv($filepath);

        foreach ($participants as $participant) {
            array_push($list, new Participant($participant[0], $participant[1]));
        }

        return $list;
    }

    public function getExercisesListFromCSV(string $filepath): array
    {
        $list = [];
        $exercises = $this->csvService->readCsv($filepath);

        foreach ($exercises as $exercise) {
            array_push($list, new Exercise(
                $exercise[0],
                $exercise[1],
                $exercise[2],
                $exercise[3]
            ));
        }

        return $list;
    }
}