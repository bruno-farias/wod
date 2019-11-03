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
        $table->setHeaders(['Minute', 'Participant', 'Level', 'Exercise', 'Cardio']);
        $list = [];

        for ($round = 1; $round <= self::WOD_DURATION; $round++) {
            /** @var Participant $participant */
            foreach ($this->participants as $participant) {
                $exercise = $this->getExercise($list, $participant, $round);
                $list[] = new Wod($round, $exercise, $participant);
            }
        }

        foreach ($list as $wod) {
            $table->addRow([
                $wod->getRound(),
                $wod->getParticipant()->getName(),
                $wod->getParticipant()->getLevelDescription(),
                $wod->getExercise()->getName(),
                $wod->getExercise()->getIsCardio() ? 'Yes' : 'No'
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
            break;
        }

        /** Check if we can add a limited practice exercise to a participant */
        while ($this->reachedPracticeLimit($exercise, $participant, $participantList)) {
            $exercise = $this->getExercise($participantList, $participant, $round);
            break;
        }

        /** Check if we can add a exercise that has equipment usage limitation */
        while ($this->reachedSimultaneousLimit($exercise, $roundExercises)) {
            $exercise = $this->getExercise($participantList, $participant, $round);
            break;
        }

        return $this->shouldAddBreak($participant, $round) ? $this->break() : $exercise;
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
        return $previous->getExercise()->getIsCardio() == $exercise->getIsCardio();
    }

    public function reachedSimultaneousLimit(Exercise $currentExercise, array $roundList): bool
    {
        $noRestriction = $currentExercise->getSimultaneousUsage() === 0;
        $notAssigned = is_null($roundList[$currentExercise->getName()]);

        if ($noRestriction || $notAssigned) {
            return false;
        }

        return count($roundList[$currentExercise->getName()]) >= $currentExercise->getSimultaneousUsage();
    }

    public function filterParticipant(array $list, Participant $participant): array
    {
        return array_filter($list, function ($item) use ($participant) {
            return $item->getParticipant()->getName() == $participant->getName();
        });
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

    public function shouldAddBreak(Participant $participant, int $round, int $wodDuration = self::WOD_DURATION): bool
    {
        $percentage = floor(($round / $wodDuration) * 100);

        if ($participant->getIsBeginner() && in_array($percentage, [20, 40, 60, 80])) {
            return true;
        } elseif (!$participant->getIsBeginner() && in_array($percentage, [33, 66])) {
            return true;
        }

        return false;
    }

    private function break(): Exercise
    {
        return new Exercise('Break', 0, false, 0);
    }
}