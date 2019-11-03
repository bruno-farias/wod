<?php


namespace App\Services;


use App\Models\Exercise;
use App\Models\Participant;
use App\Models\Wod;
use LucidFrame\Console\ConsoleTable;

class WodService
{
    /** @var CsvService $csvService */
    private $csvService;

    private const PARTICIPANTS_LIST = './data/participants.csv';
    private const EXERCISES_LIST = './data/exercises.csv';
    private const WOD_DURATION = 30;

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
                (string)$exercise[0],
                (int)$exercise[1],
                (bool)$exercise[2],
                (int)$exercise[3]
            ));
        }

        return $list;
    }

    public function createWod(): void
    {
        $participants = $this->getParticipantsListFromCSV(self::PARTICIPANTS_LIST);
        $exerciseList = $this->getExercisesListFromCSV(self::EXERCISES_LIST);

        $table = new ConsoleTable();
        $table->setHeaders(['Minute', 'Participant', 'Beginner', 'Exercise', 'Cardio']);

        $data = [];

        /** @var Participant $participant */
        foreach ($participants as $participant) {
            array_push($data, $this->buildWod($participant, $exerciseList));
        }


        foreach ($data as $wod) {
            /** @var Wod $item */
            foreach ($wod as $item) {
                $table->addRow([
                    $item->getRound(),
                    $item->getParticipant()->getName(),
                    $item->getParticipant()->getIsBeginner(),
                    $item->getExercise()->getName(),
                    $item->getExercise()->getIsCardio()
                ]);
            }
        }

        $table->display();
    }

    public function getExercise(array $exerciseList, array $currentList, Participant $participant): Exercise
    {
        $exerciseItem = array_rand($exerciseList);
        /** @var Exercise $exercise */
        $exercise = $exerciseList[$exerciseItem];

        /** Check to avoid sequence of cardio exercise */
        while ($this->cardioIsNotAllowed($exercise, $currentList)) {
            $exercise = $this->getExercise($exerciseList, $currentList, $participant);
        }

        while ($this->reachedPracticeLimit($exercise, $participant, $currentList)) {
            $exercise = $this->getExercise($exerciseList, $currentList, $participant);
        }

        return $exercise;
    }

    public function buildWod(Participant $participant, array $exerciseList)
    {
        $list = [];
        $breakCount = $participant->getIsBeginner() ? 4 : 2;

        for ($round = 1; $round <= self::WOD_DURATION; $round++) {
            /** @var Exercise $exercise */
            $exercise = $this->getExercise($exerciseList, $list, $participant);

            $list[] = new Wod($round, $exercise, $participant);
        }
        return $list;
    }

    public function reachedPracticeLimit(Exercise $currentExercise, Participant $participant, array $currentList): bool
    {
        if ($currentExercise->getPracticeLimit() === 0 || !$participant->getIsBeginner()) {
            return false;
        }

        $response = false;
        foreach ($currentList as $item) {
            /** @var Exercise $exercise */
            $exercise = $item->getExercise();
            if ($exercise->getPracticeLimit() >= 1) {
                $response = true;
                break;
            }
        }

        return $response;
    }

    public function cardioIsNotAllowed(Exercise $exercise, array $currentList): bool
    {
        if (count($currentList) === 0) {
            return false;
        }
        /** @var Wod $previous */
        $previous = end($currentList);
        return $previous->getExercise()->getIsCardio() === $exercise->getIsCardio();
    }
}