<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Tournament;
use App\Entity\Registration;
use App\Entity\SportMatch;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // Créer des utilisateurs
        $users = [];
        $userData = [
            ['firstName' => 'Alice', 'lastName' => 'Martin', 'username' => 'alice', 'email' => 'alice@test.com'],
            ['firstName' => 'Bob', 'lastName' => 'Dupont', 'username' => 'bob', 'email' => 'bob@test.com'],
            ['firstName' => 'Charlie', 'lastName' => 'Bernard', 'username' => 'charlie', 'email' => 'charlie@test.com'],
            ['firstName' => 'Diana', 'lastName' => 'Leroy', 'username' => 'diana', 'email' => 'diana@test.com'],
        ];

        foreach ($userData as $data) {
            $user = new User();
            $user->setFirstName($data['firstName']);
            $user->setLastName($data['lastName']);
            $user->setUsername($data['username']);
            $user->setEmailAddress($data['email']);
            $user->setPassword($this->hasher->hashPassword($user, 'password123'));
            $user->setStatus('actif');
            $manager->persist($user);
            $users[] = $user;
        }

        // Créer un admin
        $admin = new User();
        $admin->setFirstName('Admin');
        $admin->setLastName('Admin');
        $admin->setUsername('admin');
        $admin->setEmailAddress('admin@test.com');
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $admin->setStatus('actif');
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        // Créer des tournois
        $tournament1 = new Tournament();
        $tournament1->setTournamentName('Tournoi de Tennis Paris');
        $tournament1->setStartDate(new \DateTime('2026-06-01'));
        $tournament1->setEndDate(new \DateTime('2026-06-30'));
        $tournament1->setLocation('Paris');
        $tournament1->setDescription('Un super tournoi de tennis');
        $tournament1->setMaxParticipants(16);
        $tournament1->setSport('Tennis');
        $tournament1->setOrganizer($admin);
        $manager->persist($tournament1);

        $tournament2 = new Tournament();
        $tournament2->setTournamentName('Championnat de Football');
        $tournament2->setStartDate(new \DateTime('2026-07-01'));
        $tournament2->setEndDate(new \DateTime('2026-07-31'));
        $tournament2->setLocation('Lyon');
        $tournament2->setDescription('Championnat de football régional');
        $tournament2->setMaxParticipants(8);
        $tournament2->setSport('Football');
        $tournament2->setOrganizer($admin);
        $manager->persist($tournament2);

        // Créer des inscriptions confirmées
        foreach ($users as $user) {
            $registration = new Registration();
            $registration->setPlayer($user);
            $registration->setTournament($tournament1);
            $registration->setRegistrationDate(new \DateTime());
            $registration->setStatus('confirmed');
            $manager->persist($registration);
        }

        // Créer des matchs
        $match1 = new SportMatch();
        $match1->setTournament($tournament1);
        $match1->setPlayer1($users[0]);
        $match1->setPlayer2($users[1]);
        $match1->setMatchDate(new \DateTime('2026-06-10'));
        $match1->setScorePlayer1(6);
        $match1->setScorePlayer2(3);
        $match1->setStatus('terminé');
        $manager->persist($match1);

        $match2 = new SportMatch();
        $match2->setTournament($tournament1);
        $match2->setPlayer1($users[2]);
        $match2->setPlayer2($users[3]);
        $match2->setMatchDate(new \DateTime('2026-06-11'));
        $match2->setScorePlayer1(4);
        $match2->setScorePlayer2(6);
        $match2->setStatus('terminé');
        $manager->persist($match2);

        $match3 = new SportMatch();
        $match3->setTournament($tournament1);
        $match3->setPlayer1($users[0]);
        $match3->setPlayer2($users[2]);
        $match3->setMatchDate(new \DateTime('2026-06-15'));
        $match3->setStatus('en attente');
        $manager->persist($match3);

        $manager->flush();
    }
}