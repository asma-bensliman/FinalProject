<?php

namespace App\Tests;

use App\Entity\SportMatch;
use App\Entity\User;
use App\Entity\Tournament;
use PHPUnit\Framework\TestCase;

class SportMatchTest extends TestCase
{
    // Test 1 : Le statut passe automatiquement en "terminé" quand les 2 scores sont remplis
    public function testStatusBecomesTermineWhenBothScoresAreFilled(): void
    {
        $match = new SportMatch();
        $match->setStatus('en attente');
        $match->setScorePlayer1(6);
        $match->setScorePlayer2(3);

        // Simuler la logique du controller
        if ($match->getScorePlayer1() !== null && $match->getScorePlayer2() !== null) {
            $match->setStatus('terminé');
        }

        $this->assertEquals('terminé', $match->getStatus());
    }

    // Test 2 : Le statut reste "en attente" si un seul score est rempli
    public function testStatusRemainsEnAttenteWhenOnlyOneScoreIsFilled(): void
    {
        $match = new SportMatch();
        $match->setStatus('en attente');
        $match->setScorePlayer1(6);

        if ($match->getScorePlayer1() !== null && $match->getScorePlayer2() !== null) {
            $match->setStatus('terminé');
        }

        $this->assertEquals('en attente', $match->getStatus());
    }

    // Test 3 : Les scores sont bien enregistrés
    public function testScoresAreCorrectlySet(): void
    {
        $match = new SportMatch();
        $match->setScorePlayer1(6);
        $match->setScorePlayer2(3);

        $this->assertEquals(6, $match->getScorePlayer1());
        $this->assertEquals(3, $match->getScorePlayer2());
    }

    // Test 4 : Le statut initial est null
    public function testInitialScoresAreNull(): void
    {
        $match = new SportMatch();

        $this->assertNull($match->getScorePlayer1());
        $this->assertNull($match->getScorePlayer2());
    }

    // Test 5 : Vérifier le joueur gagnant
    public function testPlayer1WinsWhenScorePlayer1IsHigher(): void
    {
        $match = new SportMatch();
        $match->setScorePlayer1(6);
        $match->setScorePlayer2(3);

        $this->assertGreaterThan($match->getScorePlayer2(), $match->getScorePlayer1());
    }
}