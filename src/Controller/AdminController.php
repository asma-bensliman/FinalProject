<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\TournamentRepository;
use App\Repository\RegistrationRepository;
use App\Repository\SportMatchRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    // USERS
    #[Route('/users', name: 'users_list', methods: ['GET'])]
    public function listUsers(UserRepository $userRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $userRepository->findAll();
        $data = array_map(fn($u) => [
            'id' => $u->getId(),
            'firstName' => $u->getFirstName(),
            'lastName' => $u->getLastName(),
            'username' => $u->getUsername(),
            'emailAddress' => $u->getEmailAddress(),
            'status' => $u->getStatus(),
            'roles' => $u->getRoles(),
        ], $users);

        return $this->json($data);
    }

    #[Route('/users/{id}', name: 'user_update', methods: ['PUT'])]
    public function updateUser(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $userRepository->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['status'])) $user->setStatus($data['status']);
        if (isset($data['roles'])) $user->setRoles($data['roles']);

        $em->flush();

        return $this->json([
            'id' => $user->getId(),
            'status' => $user->getStatus(),
            'roles' => $user->getRoles(),
        ]);
    }

    #[Route('/users/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function deleteUser(int $id, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $userRepository->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $em->remove($user);
        $em->flush();

        return $this->json(['message' => 'User deleted successfully']);
    }

    // TOURNAMENTS
    #[Route('/tournaments', name: 'tournaments_list', methods: ['GET'])]
    public function listTournaments(TournamentRepository $tournamentRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $tournaments = $tournamentRepository->findAll();
        $data = array_map(fn($t) => [
            'id' => $t->getId(),
            'tournamentName' => $t->getTournamentName(),
            'sport' => $t->getSport(),
            'status' => $t->getStatus(),
            'maxParticipants' => $t->getMaxParticipants(),
        ], $tournaments);

        return $this->json($data);
    }

    // REGISTRATIONS
    #[Route('/registrations', name: 'registrations_list', methods: ['GET'])]
    public function listRegistrations(RegistrationRepository $registrationRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $registrations = $registrationRepository->findAll();
        $data = array_map(fn($r) => [
            'id' => $r->getId(),
            'player' => $r->getPlayer()->getId(),
            'tournament' => $r->getTournament()->getId(),
            'status' => $r->getStatus(),
            'registrationDate' => $r->getRegistrationDate()->format('Y-m-d'),
        ], $registrations);

        return $this->json($data);
    }

    #[Route('/registrations/{id}', name: 'registration_update', methods: ['PUT'])]
    public function updateRegistration(int $id, Request $request, RegistrationRepository $registrationRepository, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $registration = $registrationRepository->find($id);
        if (!$registration) {
            return $this->json(['error' => 'Registration not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['status'])) $registration->setStatus($data['status']);

        $em->flush();

        return $this->json([
            'id' => $registration->getId(),
            'status' => $registration->getStatus(),
        ]);
    }
}