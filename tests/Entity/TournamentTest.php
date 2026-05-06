<?php

namespace App\Tests\Entity;

use App\Entity\Tournament;
use PHPUnit\Framework\TestCase;

class TournamentTest extends TestCase
{
    public function testTournamentCreation(): void
    {
        $tournament = new Tournament();
        $tournament->setTournamentName('Tournoi Test');
        $tournament->setDescription('Description test');
        $tournament->setSport('Football');
        $tournament->setMaxParticipants(16);
        $tournament->setLocation('Paris');

        $this->assertEquals('Tournoi Test', $tournament->getTournamentName());
        $this->assertEquals('Description test', $tournament->getDescription());
        $this->assertEquals('Football', $tournament->getSport());
        $this->assertEquals(16, $tournament->getMaxParticipants());
        $this->assertEquals('Paris', $tournament->getLocation());
    }

    public function testTournamentStatusUpcoming(): void
    {
        $tournament = new Tournament();
        $tournament->setStartDate(new \DateTime('+1 day'));
        $tournament->setEndDate(new \DateTime('+10 days'));

        $this->assertEquals('upcoming', $tournament->getStatus());
    }

    public function testTournamentStatusOngoing(): void
    {
        $tournament = new Tournament();
        $tournament->setStartDate(new \DateTime('-1 day'));
        $tournament->setEndDate(new \DateTime('+10 days'));

        $this->assertEquals('ongoing', $tournament->getStatus());
    }

    public function testTournamentStatusFinished(): void
    {
        $tournament = new Tournament();
        $tournament->setStartDate(new \DateTime('-10 days'));
        $tournament->setEndDate(new \DateTime('-1 day'));

        $this->assertEquals('finished', $tournament->getStatus());
    }
}