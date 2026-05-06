<?php

namespace App\Controller;

use App\Entity\Registration;
use App\Entity\SportMatch;
use App\Entity\Tournament;
use App\Repository\TournamentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TournamentController extends AbstractController
{
    #[Route('/api/tournaments', name: 'tournaments_list', methods: ['GET'])]
    public function list(TournamentRepository $tournamentRepository): JsonResponse
    {
        $tournaments = $tournamentRepository->findAll();
        $data = array_map(fn($t) => [
            'id' => $t->getId(),
            'tournamentName' => $t->getTournamentName(),
            'startDate' => $t->getStartDate()->format('Y-m-d'),
            'endDate' => $t->getEndDate()->format('Y-m-d'),
            'location' => $t->getLocation(),
            'description' => $t->getDescription(),
            'maxParticipants' => $t->getMaxParticipants(),
            'sport' => $t->getSport(),
            'status' => $t->getStatus(),
            'organizer' => $t->getOrganizer()->getId(),
            'winner' => $t->getWinner()?->getId(),
        ], $tournaments);

        return $this->json($data);
    }

    #[Route('/api/tournaments', name: 'tournament_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['tournamentName'], $data['startDate'], $data['endDate'], $data['description'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        $tournament = new Tournament();
        $tournament->setTournamentName($data['tournamentName']);
        $tournament->setStartDate(new \DateTime($data['startDate']));
        $tournament->setEndDate(new \DateTime($data['endDate']));
        $tournament->setDescription($data['description']);
        $tournament->setSport($data['sport'] ?? 'Unknown');
        $tournament->setMaxParticipants($data['maxParticipants'] ?? 0);
        $tournament->setLocation($data['location'] ?? null);
        $tournament->setOrganizer($this->getUser());

        $em->persist($tournament);
        $em->flush();

        return $this->json([
            'id' => $tournament->getId(),
            'tournamentName' => $tournament->getTournamentName(),
            'status' => $tournament->getStatus(),
        ], 201);
    }

    #[Route('/api/tournaments/{id}', name: 'tournament_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $em): JsonResponse
    {
        $tournament = $em->getRepository(Tournament::class)->find($id);
        if (!$tournament) {
            return $this->json(['error' => 'Tournament not found'], 404);
        }

        return $this->json([
            'id' => $tournament->getId(),
            'tournamentName' => $tournament->getTournamentName(),
            'startDate' => $tournament->getStartDate()->format('Y-m-d'),
            'endDate' => $tournament->getEndDate()->format('Y-m-d'),
            'location' => $tournament->getLocation(),
            'description' => $tournament->getDescription(),
            'maxParticipants' => $tournament->getMaxParticipants(),
            'sport' => $tournament->getSport(),
            'status' => $tournament->getStatus(),
            'organizer' => $tournament->getOrganizer()->getId(),
            'winner' => $tournament->getWinner()?->getId(),
        ]);
    }

    #[Route('/api/tournaments/{id}', name: 'tournament_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $tournament = $em->getRepository(Tournament::class)->find($id);
        if (!$tournament) {
            return $this->json(['error' => 'Tournament not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['tournamentName'])) $tournament->setTournamentName($data['tournamentName']);
        if (isset($data['startDate'])) $tournament->setStartDate(new \DateTime($data['startDate']));
        if (isset($data['endDate'])) $tournament->setEndDate(new \DateTime($data['endDate']));
        if (isset($data['description'])) $tournament->setDescription($data['description']);
        if (isset($data['sport'])) $tournament->setSport($data['sport']);
        if (isset($data['maxParticipants'])) $tournament->setMaxParticipants($data['maxParticipants']);
        if (isset($data['location'])) $tournament->setLocation($data['location']);

        $em->flush();

        return $this->json([
            'id' => $tournament->getId(),
            'tournamentName' => $tournament->getTournamentName(),
            'status' => $tournament->getStatus(),
        ]);
    }

    #[Route('/api/tournaments/{id}', name: 'tournament_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        try {
            $em->getConnection()->executeStatement('DELETE FROM tournament WHERE id = ?', [$id]);
            return $this->json(['message' => 'Tournament deleted successfully']);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}