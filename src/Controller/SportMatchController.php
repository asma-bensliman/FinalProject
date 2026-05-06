<?php

namespace App\Controller;

use App\Entity\SportMatch;
use App\Repository\SportMatchRepository;
use App\Repository\TournamentRepository;
use App\Repository\UserRepository;
use App\Repository\RegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SportMatchController extends AbstractController
{
    // GET - Liste des matchs d'un tournoi
    #[Route('/api/tournaments/{id}/sport-matchs', name: 'get_sport_matchs', methods: ['GET'])]
    public function index(int $id, TournamentRepository $tournamentRepository, SportMatchRepository $sportMatchRepository): JsonResponse
    {
        $tournament = $tournamentRepository->find($id);

        if (!$tournament) {
            return $this->json(['message' => 'Tournoi non trouvé'], 404);
        }

        $matches = $sportMatchRepository->findBy(['tournament' => $tournament]);

        $data = array_map(function (SportMatch $match) {
            return [
                'id' => $match->getId(),
                'player1' => $match->getPlayer1()->getUsername(),
                'player2' => $match->getPlayer2()->getUsername(),
                'matchDate' => $match->getMatchDate()->format('Y-m-d'),
                'scorePlayer1' => $match->getScorePlayer1(),
                'scorePlayer2' => $match->getScorePlayer2(),
                'status' => $match->getStatus(),
            ];
        }, $matches);

        return $this->json($data);
    }

        // POST - Créer un match
    #[Route('/api/tournaments/{id}/sport-matchs', name: 'create_sport_match', methods: ['POST'])]
    public function create(
        int $id,
        Request $request,
        TournamentRepository $tournamentRepository,
        UserRepository $userRepository,
        RegistrationRepository $registrationRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $tournament = $tournamentRepository->find($id);

        if (!$tournament) {
            return $this->json(['message' => 'Tournoi non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $player1 = $userRepository->find($data['player1Id']);
        $player2 = $userRepository->find($data['player2Id']);

        if (!$player1 || !$player2) {
            return $this->json(['message' => 'Joueur non trouvé'], 404);
        }

        // Vérifier que les 2 joueurs ont une inscription confirmée
        $reg1 = $registrationRepository->findOneBy([
            'player' => $player1,
            'tournament' => $tournament,
            'status' => 'confirmed'
        ]);

        $reg2 = $registrationRepository->findOneBy([
            'player' => $player2,
            'tournament' => $tournament,
            'status' => 'confirmed'
        ]);

        if (!$reg1 || !$reg2) {
            return $this->json(['message' => 'Les deux joueurs doivent avoir une inscription confirmée'], 403);
        }

        $match = new SportMatch();
        $match->setTournament($tournament);
        $match->setPlayer1($player1);
        $match->setPlayer2($player2);
        $match->setMatchDate(new \DateTime($data['matchDate'] ?? 'now'));
        $match->setStatus('en attente');

        $em->persist($match);
        $em->flush();

        return $this->json([
            'id' => $match->getId(),
            'player1' => $match->getPlayer1()->getUsername(),
            'player2' => $match->getPlayer2()->getUsername(),
            'matchDate' => $match->getMatchDate()->format('Y-m-d'),
            'status' => $match->getStatus(),
        ], 201);
    }

        // GET - Détails d'un match
    #[Route('/api/tournaments/{idTournament}/sport-matchs/{idSportMatch}', name: 'get_sport_match', methods: ['GET'])]
    public function show(
        string $idTournament,
        string $idSportMatch,

        TournamentRepository $tournamentRepository,
        SportMatchRepository $sportMatchRepository
    ): JsonResponse {
        $tournament = $tournamentRepository->find($idTournament);

        if (!$tournament) {
            return $this->json(['message' => 'Tournoi non trouvé'], 404);
        }

        $match = $sportMatchRepository->findOneBy([
            'id' => $idSportMatch,
            'tournament' => $tournament
        ]);

        if (!$match) {
            return $this->json(['message' => 'Match non trouvé'], 404);
        }

        return $this->json([
            'id' => $match->getId(),
            'player1' => $match->getPlayer1()->getUsername(),
            'player2' => $match->getPlayer2()->getUsername(),
            'matchDate' => $match->getMatchDate()->format('Y-m-d'),
            'scorePlayer1' => $match->getScorePlayer1(),
            'scorePlayer2' => $match->getScorePlayer2(),
            'status' => $match->getStatus(),
        ]);
    }

    // PUT - Modifier les scores
    #[Route('/api/tournaments/{idTournament}/sport-matchs/{idSportMatch}', name: 'update_sport_match', methods: ['PUT'])]
    public function update(
        string $idTournament,
        string $idSportMatch,
        Request $request,
        TournamentRepository $tournamentRepository,
        SportMatchRepository $sportMatchRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $tournament = $tournamentRepository->find($idTournament);

        if (!$tournament) {
            return $this->json(['message' => 'Tournoi non trouvé'], 404);
        }

        $match = $sportMatchRepository->findOneBy([
            'id' => $idSportMatch,
            'tournament' => $tournament
        ]);

        if (!$match) {
            return $this->json(['message' => 'Match non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $currentUser = $this->getUser();
        $isAdmin = in_array('ROLE_ADMIN', $currentUser->getRoles());

        // Vérifier que le joueur ne peut modifier que son propre score
        if (!$isAdmin) {
            if ($currentUser->getId() === $match->getPlayer1()->getId()) {
                if (isset($data['scorePlayer1'])) {
                    $match->setScorePlayer1($data['scorePlayer1']);
                }
            } elseif ($currentUser->getId() === $match->getPlayer2()->getId()) {
                if (isset($data['scorePlayer2'])) {
                    $match->setScorePlayer2($data['scorePlayer2']);
                }
            } else {
                return $this->json(['message' => 'Vous ne pouvez pas modifier ce match'], 403);
            }
        } else {
            // Admin peut tout modifier
            if (isset($data['scorePlayer1'])) $match->setScorePlayer1($data['scorePlayer1']);
            if (isset($data['scorePlayer2'])) $match->setScorePlayer2($data['scorePlayer2']);
        }

        // Si les 2 scores sont remplis → statut "terminé"
        if ($match->getScorePlayer1() !== null && $match->getScorePlayer2() !== null) {
            $match->setStatus('terminé');
        }

        $em->flush();

        return $this->json([
            'id' => $match->getId(),
            'player1' => $match->getPlayer1()->getUsername(),
            'player2' => $match->getPlayer2()->getUsername(),
            'scorePlayer1' => $match->getScorePlayer1(),
            'scorePlayer2' => $match->getScorePlayer2(),
            'status' => $match->getStatus(),
        ]);
    }

    // DELETE - Supprimer un match
    #[Route('/api/tournaments/{idTournament}/sport-matchs/{idSportMatch}', name: 'delete_sport_match', methods: ['DELETE'])]
    public function delete(
        string $idTournament,
        string $idSportMatch,

        TournamentRepository $tournamentRepository,
        SportMatchRepository $sportMatchRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $tournament = $tournamentRepository->find($idTournament);

        if (!$tournament) {
            return $this->json(['message' => 'Tournoi non trouvé'], 404);
        }

        $match = $sportMatchRepository->findOneBy([
            'id' => $idSportMatch,
            'tournament' => $tournament
        ]);

        if (!$match) {
            return $this->json(['message' => 'Match non trouvé'], 404);
        }

        $em->remove($match);
        $em->flush();

        return $this->json(['message' => 'Match supprimé'], 200);
    }
}