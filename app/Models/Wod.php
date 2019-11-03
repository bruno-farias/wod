<?php


namespace App\Models;


class Wod
{
    private $round;
    private $exercise;
    private $participant;

    public function __construct(int $round, Exercise $exercise, Participant $participant)
    {
        $this->round = $round;
        $this->exercise = $exercise;
        $this->participant = $participant;
    }

    /**
     * @return int
     */
    public function getRound(): int
    {
        return $this->round;
    }

    /**
     * @return Exercise
     */
    public function getExercise(): Exercise
    {
        return $this->exercise;
    }

    /**
     * @return Participant
     */
    public function getParticipant(): Participant
    {
        return $this->participant;
    }




}