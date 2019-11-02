<?php


namespace Tests;


use App\Models\Exercise;

class Creator
{
    /** @var Randoms $randoms */
    private $randoms;

    public function __construct()
    {
        $this->randoms = new Randoms();
    }

    public function createParticipantsArray(int $quantity): array
    {
        $list = [];
        array_push($list, ['name', 'is beginner']);

        for ($x = 0; $x < $quantity; $x++) {
            array_push($list, [$this->randoms->participantName(), (int)$this->randoms->isBeginner()]);
        }

        return $list;
    }

    public function createParticipantsCSVFile(array $participants): string
    {
        $filename = './tests/data/test_participants.csv';
        $this->createCSVFile($participants, $filename);

        return $filename;
    }

    public function createExercisesArray(int $quantity): array
    {
        $list = [];
        array_push($list, ['Exercise', 'practice limit', 'is cardio', 'simultaneous usage']);

        for ($x = 0; $x < $quantity; $x++) {
            $exercise = new Exercise(
                $this->randoms->exerciseName(),
                $this->randoms->practiceLimit(),
                $this->randoms->isCardio(),
                $this->randoms->simultaneousUsage()
            );
            array_push($list, $exercise->toArray());
        }

        return $list;
    }

    public function createExercisesCSVFile(array $exercises): string
    {
        $filename = './tests/data/test_exercises.csv';
        $this->createCSVFile($exercises, $filename);

        return $filename;
    }

    private function createCSVFile(array $items, string $filename): void
    {
        $fp = fopen($filename, 'wb');
        foreach ($items as $item) {
            fputcsv($fp, $item);
        }
        fclose($fp);
    }
}