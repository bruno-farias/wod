<?php


namespace Tests;


use Faker\Factory;

class Randoms
{
    private $generator;

    public function __construct()
    {
        $this->generator = Factory::create();
    }

    public function exerciseName(): string
    {
        return $this->generator->firstName;
    }

    public function practiceLimit(): int
    {
        return $this->generator->numberBetween(1, 10);
    }

    public function isCardio(): bool
    {
        return $this->generator->boolean;
    }

    public function simultaneousUsage(): int
    {
        return $this->generator->randomNumber();
    }

    public function participantName(): string
    {
        return $this->generator->name;
    }

    public function isBeginner(): bool
    {
        return $this->generator->boolean;
    }

    public function quantity(): int
    {
        return $this->generator->numberBetween(1, 10);
    }

    public function round()
    {
        return $this->generator->numberBetween(1, 30);
    }

}