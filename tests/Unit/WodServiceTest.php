<?php


namespace Tests\Unit;


use App\Models\Participant;
use App\Models\Wod;
use App\Services\WodService;
use Tests\Creator;
use Tests\TestCase;

class WodServiceTest extends TestCase
{
    /** @var WodService */
    private $service;
    /** @var Creator $creator */
    private $creator;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new WodService();
        $this->creator = new Creator();
    }

    public function testGetParticipantsListSucceeds()
    {
        $quantity = $this->randoms->quantity();
        $expectedList = $this->creator->createParticipantsArray($quantity);
        $filepath = $this->creator->createParticipantsCSVFile($expectedList);

        $list = $this->service->getParticipantsListFromCSV($filepath);

        $this->assertCount($quantity, $list);
        array_shift($expectedList);
        for ($x = 0; $x < $quantity; $x++) {
            $this->assertEquals($expectedList[$x][0], $list[$x]->getName());
            $this->assertEquals($expectedList[$x][1], $list[$x]->getIsBeginner());
        }
        unlink($filepath);
    }

    public function testGetExercisesListSucceeds()
    {
        $quantity = $this->randoms->quantity();
        $expectedList = $this->creator->createExercisesArray($quantity);
        $filepath = $this->creator->createExercisesCSVFile($expectedList);

        $list = $this->service->getExercisesListFromCSV($filepath);

        $this->assertCount($quantity, $list);
        array_shift($expectedList);
        for ($x = 0; $x < $quantity; $x++) {
            $this->assertEquals($expectedList[$x][0], $list[$x]->getName());
            $this->assertEquals($expectedList[$x][1], $list[$x]->getPracticeLimit());
            $this->assertEquals($expectedList[$x][2], $list[$x]->getIsCardio());
            $this->assertEquals($expectedList[$x][3], $list[$x]->getSimultaneousUsage());
        }
        unlink($filepath);
    }

    public function testDontAddTwoCardioExercisesInSequence()
    {
        $list = [];
        $list[] = $this->createCardioRound();

        $cardioExercise = $this->creator->createCardioExercise();
        $this->assertTrue($this->service->cardioIsNotAllowed($cardioExercise, $list));
    }

    public function testDontAssignMoreThanPracticeLimit()
    {
        $participant = $this->creator->createParticipantBeginner();
        $list = [];
        $list[] = $this->createPracticeLimitRound($participant);

        $exerciseWithPraticeLimit = $this->creator->createPracticeLimitExercise();
        $this->assertTrue($this->service->reachedPracticeLimit($exerciseWithPraticeLimit, $participant, $list));
    }

    private function createCardioRound(): Wod
    {
        return new Wod(
            $this->randoms->round(),
            $this->creator->createCardioExercise(),
            $this->creator->createParticipant()
        );
    }

    private function createPracticeLimitRound(Participant $participant)
    {
        return new Wod(
            $this->randoms->round(),
            $this->creator->createPracticeLimitExercise(),
            $participant
        );
    }
}