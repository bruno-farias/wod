<?php

namespace Tests\Unit;

use App\Models\Exercise;
use Tests\TestCase;


class ExercisesTest extends TestCase
{
    private $name;
    private $practiceLimit;
    private $isCardio;
    private $simultaneousUsage;
    private $exercise;

    public function setUp(): void
    {
        parent::setUp();
        $this->name = $this->randoms->exerciseName();
        $this->practiceLimit = $this->randoms->practiceLimit();
        $this->isCardio = $this->randoms->isCardio();
        $this->simultaneousUsage = $this->randoms->simultaneousUsage();
        $this->exercise = new Exercise($this->name, $this->practiceLimit, $this->isCardio, $this->simultaneousUsage);
    }

    public function testCreateExerciseModelSucceeds()
    {
        $this->assertEquals($this->name, $this->exercise->getName());
        $this->assertEquals($this->practiceLimit, $this->exercise->getPracticeLimit());
        $this->assertEquals($this->isCardio, $this->exercise->getIsCardio());
        $this->assertEquals($this->simultaneousUsage, $this->exercise->getSimultaneousUsage());
    }

    public function testGetExerciseAsArraySucceeds()
    {
        $expected = [
            $this->name,
            $this->practiceLimit,
            $this->isCardio,
            $this->simultaneousUsage
        ];

        $this->assertEquals($expected, $this->exercise->toArray());
    }
}