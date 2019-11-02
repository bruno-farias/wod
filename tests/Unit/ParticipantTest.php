<?php


namespace Tests\Unit;


use App\Models\Participant;
use Tests\TestCase;

class ParticipantTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testCreateParticipantSucceeds()
    {
        $name = $this->randoms->participantName();
        $isBeginner = $this->randoms->isBeginner();
        $expectedLevel = $isBeginner ? 'BEGINNER' : 'PRO';

        $participant = new Participant($name, $isBeginner);

        $this->assertEquals($name, $participant->getName());
        $this->assertEquals($isBeginner, $participant->getIsBeginner());
        $this->assertEquals($expectedLevel, $participant->getLevelDescription());
    }
}