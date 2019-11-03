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

    public function testGroupExercisesOnListSucceeds()
    {
        $list = [];
        $exerciseToGroup = $this->creator->createCustomExercise();
        $exerciseNotGrouped = $this->creator->createCustomExercise();

        $list[] = $this->creator->createWod(null, $exerciseToGroup);
        $list[] = $this->creator->createWod(null, $exerciseToGroup);
        $list[] = $this->creator->createWod(null, $exerciseNotGrouped);

        $result = $this->service->groupExercise($list);

        $this->assertCount(2, $result);
        $this->assertCount(2, $result[$exerciseToGroup->getName()]);
        $this->assertCount(1, $result[$exerciseNotGrouped->getName()]);
    }

    public function testFilterRoundSucceeds()
    {
        $list = [];

        $filterRound = $this->randoms->round();
        $otherRound = $this->randoms->round($filterRound);

        $list[] = $this->creator->createWod($filterRound);
        $list[] = $this->creator->createWod($filterRound);
        $list[] = $this->creator->createWod($otherRound);

        $result = $this->service->filterRound($list, $filterRound);

        $this->assertCount(2, $result);
    }

    public function testFilterByParticipantSucceeds()
    {
        $list = [];
        $participantToFilter = $this->creator->createParticipant();

        $list[] = $this->creator->createWod(null, null, $participantToFilter);
        $list[] = $this->creator->createWod(null, null, $participantToFilter);
        $list[] = $this->creator->createWod(null, null, $this->creator->createParticipant());

        $result = $this->service->filterParticipant($list, $participantToFilter);

        $this->assertCount(2, $result);
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