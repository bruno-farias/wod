<?php


namespace App\Models;


class Participant
{
    private $name;
    private $isBeginner;

    const AVAILABLE_LEVELS = [
        'PRO',
        'BEGINNER'
    ];

    public function __construct(string $name, bool $isBeginner)
    {
        $this->name = $name;
        $this->isBeginner = $isBeginner;
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function getIsBeginner(): bool
    {
        return $this->isBeginner;
    }

    public function getLevelDescription(): string
    {
        return self::AVAILABLE_LEVELS[$this->isBeginner];
    }
}