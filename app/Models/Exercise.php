<?php

namespace App\Models;

class Exercise
{
    private $name;
    private $practiceLimit;
    private $isCardio;
    private $simultaneousUsage;

    /**
     * Exercise constructor.
     * @param string $name
     * @param int $practiceLimit
     * @param bool $cardio
     * @param int $simultaneousUsage
     */
    public function __construct(string $name, int $practiceLimit, bool $cardio, int $simultaneousUsage)
    {
        $this->name = $name;
        $this->practiceLimit = $practiceLimit;
        $this->isCardio = $cardio;
        $this->simultaneousUsage = $simultaneousUsage;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getPracticeLimit(): int
    {
        return $this->practiceLimit;
    }

    /**
     * @return bool
     */
    public function getIsCardio(): bool
    {
        return $this->isCardio;
    }

    /**
     * @return int
     */
    public function getSimultaneousUsage(): int
    {
        return $this->simultaneousUsage;
    }

    public function toArray(): array
    {
        return [
            $this->getName(),
            $this->getPracticeLimit(),
            $this->getIsCardio(),
            $this->getSimultaneousUsage()
        ];
    }


}