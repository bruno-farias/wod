<?php


namespace Tests\Unit;


use App\Models\Exercise;
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

    public function testGetParticipantsListSucceeds(): void
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

    public function testGetExercisesListSucceeds(): void
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

    public function testDontAddTwoCardioExercisesInSequence(): void
    {
        $list = [];
        $list[] = $this->creator->createCardioRound();

        $cardioExercise = $this->creator->createCardioExercise();
        $this->assertTrue($this->service->cardioIsNotAllowed($cardioExercise, $list));
    }

    public function testDontAssignMoreThanPracticeLimit(): void
    {
        $participant = $this->creator->createParticipantBeginner();
        $list = [];
        $list[] = $this->creator->createPracticeLimitRound($participant);

        $exerciseWithPraticeLimit = $this->creator->createPracticeLimitExercise();
        $this->assertTrue($this->service->reachedPracticeLimit($exerciseWithPraticeLimit, $participant, $list));
    }

    public function testGroupExercisesOnListSucceeds(): void
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

    public function testFilterRoundSucceeds(): void
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

    public function testFilterByParticipantSucceeds(): void
    {
        $list = [];
        $participantToFilter = $this->creator->createParticipant();

        $list[] = $this->creator->createWod(null, null, $participantToFilter);
        $list[] = $this->creator->createWod(null, null, $participantToFilter);
        $list[] = $this->creator->createWod(null, null, $this->creator->createParticipant());

        $result = $this->service->filterParticipant($list, $participantToFilter);

        $this->assertCount(2, $result);
    }

    public function testReturnsTrueWhenExerciseReachedSimultaneousLimitInRound(): void
    {
        $list = [];
        $exerciseWithLimit = $this->creator->createCustomExercise(null, null, null, 1);

        $list[] = $this->creator->createWod(1, $exerciseWithLimit);
        $list[] = $this->creator->createWod(1, $exerciseWithLimit);

        $roundList = $this->service->filterRound($list, 1);
        $roundExercises = $this->service->groupExercise($roundList);

        $result = $this->service->reachedSimultaneousLimit($exerciseWithLimit, $roundExercises);

        $this->assertTrue($result);
    }

    public function testReturnsFalseWhenExerciseNotReachedSimultaneousLimitInRound(): void
    {
        $list = [];
        $exerciseWithLimit = $this->creator->createCustomExercise(null, null, null, 3);

        $list = $this->createWodListForSimultaneousTest($exerciseWithLimit, $list);

        $roundList = $this->service->filterRound($list, 1);
        $roundExercises = $this->service->groupExercise($roundList);

        $result = $this->service->reachedSimultaneousLimit($exerciseWithLimit, $roundExercises);
        $this->assertFalse($result);
    }

    /**
     * @param Exercise $exerciseWithLimit
     * @param array $list
     * @return array
     */
    private function createWodListForSimultaneousTest(Exercise $exerciseWithLimit, array $list): array
    {
        $list[] = $this->creator->createWod(1, $exerciseWithLimit);
        $list[] = $this->creator->createWod(1, $exerciseWithLimit);
        return $list;
    }

}