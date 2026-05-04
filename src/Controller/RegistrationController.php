<?php

namespace App\Controller;

use App\Entity\Registration;
use App\Repository\RegistrationRepository;
use App\Repository\TournamentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/api/tournaments/{id}/registrations', name: 'registrations_list', methods: ['GET'])]
    public function list(int $id, TournamentRepository $tournamentRepository, RegistrationRepository $registrationRepository): JsonResponse
    {
        $tournament = $tournamentRepository->find($id);
        if (!$tournament) {
            return $this->json(['error' => 'Tournament not found'], 404);
        }

        $registrations = $registrationRepository->findBy(['tournament' => $tournament]);
        $data = array_map(fn($r) => [
            'id' => $r->getId(),
            'player' => $r->getPlayer()->getId(),
            'tournament' => $r->getTournament()->getId(),
            'registrationDate' => $r->getRegistrationDate()->format('Y-m-d'),
            'status' => $r->getStatus(),
        ], $registrations);

        return $this->json($data);
    }

    #[Route('/api/tournaments/{id}/registrations', name: 'registration_create', methods: ['POST'])]
    public function create(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        TournamentRepository $tournamentRepository,
        UserRepository $userRepository
    ): JsonResponse {
        $tournament = $tournamentRepository->find($id);
        if (!$tournament) {
            return $this->json(['error' => 'Tournament not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['playerId'])) {
            return $this->json(['error' => 'Missing playerId'], 400);
        }

        $player = $userRepository->find($data['playerId']);
        if (!$player) {
            return $this->json(['error' => 'Player not found'], 404);
        }

        $registration = new Registration();
        $registration->setPlayer($player);
        $registration->setTournament($tournament);
        $registration->setRegistrationDate(new \DateTime());
        $registration->setStatus('en attente');

        $em->persist($registration);
        $em->flush();

        return $this->json([
            'id' => $registration->getId(),
            'player' => $player->getId(),
            'tournament' => $tournament->getId(),
            'registrationDate' => $registration->getRegistrationDate()->format('Y-m-d'),
            'status' => $registration->getStatus(),
        ], 201);
    }

    #[Route('/api/tournaments/{idTournament}/registrations/{idRegistration}', name: 'registration_delete', methods: ['DELETE'])]
    public function delete(
        int $idTournament,
        int $idRegistration,
        TournamentRepository $tournamentRepository,
        RegistrationRepository $registrationRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $tournament = $tournamentRepository->find($idTournament);
        if (!$tournament) {
            return $this->json(['error' => 'Tournament not found'], 404);
        }

        $registration = $registrationRepository->find($idRegistration);
        if (!$registration || $registration->getTournament()->getId() !== $idTournament) {
            return $this->json(['error' => 'Registration not found'], 404);
        }

        $em->remove($registration);
        $em->flush();

        return $this->json(['message' => 'Registration deleted successfully']);
    }
}