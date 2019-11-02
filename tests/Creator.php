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

    public function createExercise(): Exercise
    {
        return new Exercise(
            $this->randoms->exerciseName(),
            $this->randoms->practiceLimit(),
            $this->randoms->isCardio(),
            $this->randoms->simultaneousUsage()
        );
    }

    public function createParticipantsArray($quantity)
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

        $fp = fopen($filename, 'wb');
        foreach ($participants as $item) {
            fputcsv($fp, $item);
        }
        fclose($fp);

        return $filename;
    }
}