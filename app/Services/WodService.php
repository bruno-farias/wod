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
    private $participants;
    private $exerciseList;

    public function __construct()
    {
        $this->csvService = new CsvService();
        $this->participants = $this->getParticipantsListFromCSV(self::PARTICIPANTS_LIST);
        $this->exerciseList = $this->getExercisesListFromCSV(self::EXERCISES_LIST);
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
        $table = new ConsoleTable();
        $table->setHeaders(['Minute', 'Participant', 'Beginner', 'Exercise', 'Cardio']);
        $list = [];

        for ($round = 1; $round <= self::WOD_DURATION; $round++) {
            foreach ($this->participants as $participant) {
                $exercise = $this->getExercise($list, $participant, $round);
                $list[] = new Wod($round, $exercise, $participant);
            }
        }

        foreach ($list as $wod) {
            $table->addRow([
                $wod->getRound(),
                $wod->getParticipant()->getName(),
                $wod->getParticipant()->getIsBeginner(),
                $wod->getExercise()->getName(),
                $wod->getExercise()->getIsCardio()
            ]);
        }

        $table->display();
    }

    public function getExercise(array $currentList, Participant $participant, int $round): Exercise
    {
        $exerciseItem = array_rand($this->exerciseList);
        /** @var Exercise $exercise */
        $exercise = $this->exerciseList[$exerciseItem];
        $participantList = $this->filterParticipant($currentList, $participant);

        $roundList = $this->filterRound($currentList, $round);
        $roundExercises = $this->groupExercise($roundList);

        /** Check to avoid sequence of cardio exercise */
        while ($this->cardioIsNotAllowed($exercise, $currentList)) {
            $exercise = $this->getExercise($participantList, $participant, $round);
        }

        /** Check if we can add a limited practice exercise to a participant */
        while ($this->reachedPracticeLimit($exercise, $participant, $participantList)) {
            $exercise = $this->getExercise($participantList, $participant, $round);
        }

        /** Check if we can add a exercise that has equipment usage limitation */
        while ($this->reachedSimultaneousLimit($exercise, $roundExercises)) {
            $exercise = $this->getExercise($participantList, $participant, $round);
        }

        return $exercise;
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

    public function reachedSimultaneousLimit(Exercise $currentExercise, array $roundList): bool
    {
        if ($currentExercise->getSimultaneousUsage() === 0) {
            return false;
        }

        return count($roundList[$currentExercise->getName()]) >= $currentExercise->getSimultaneousUsage();
    }

    public function filterParticipant(array $list, Participant $participant): array
    {
        $filteredList = [];
        foreach ($list as $item) {
            if ($item->getParticipant()->getName() === $participant->getName()) {
                array_push($filteredList, $item);
            }
        }
        return $filteredList;
    }

    public function filterRound(array $list, int $round): array
    {
        return array_filter($list, function ($item) use ($round) {
            /** Wod $item */
            return ($item->getRound() === $round);
        });
    }

    public function groupExercise(array $list): array
    {
        $group = [];
        foreach ($list as $item) {
            /** Wod $item */
            $group[$item->getExercise()->getName()][] = $item;
        }

        return $group;
    }
}