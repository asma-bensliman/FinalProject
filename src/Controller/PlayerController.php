<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class PlayerController extends AbstractController
{
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['firstName'], $data['lastName'], $data['username'], $data['emailAddress'], $data['password'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        $user = new User();
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setUsername($data['username']);
        $user->setEmailAddress($data['emailAddress']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $user->setStatus('actif');
        $user->setRoles(['ROLE_USER']);

        $em->persist($user);
        $em->flush();

        return $this->json([
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'username' => $user->getUsername(),
            'emailAddress' => $user->getEmailAddress(),
            'status' => $user->getStatus(),
        ], 201);
    }

    #[Route('/api/players', name: 'players_list', methods: ['GET'])]
    public function list(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();
        $data = array_map(fn($u) => [
            'id' => $u->getId(),
            'firstName' => $u->getFirstName(),
            'lastName' => $u->getLastName(),
            'username' => $u->getUsername(),
            'emailAddress' => $u->getEmailAddress(),
            'status' => $u->getStatus(),
        ], $users);

        return $this->json($data);
    }

    #[Route('/api/players/{id}', name: 'player_show', methods: ['GET'])]
    public function show(int $id, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($id);
        if (!$user) {
            return $this->json(['error' => 'Player not found'], 404);
        }

        return $this->json([
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'username' => $user->getUsername(),
            'emailAddress' => $user->getEmailAddress(),
            'status' => $user->getStatus(),
        ]);
    }

    #[Route('/api/players/{id}', name: 'player_update', methods: ['PUT'])]
    public function update(
        int $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $user = $userRepository->find($id);
        if (!$user) {
            return $this->json(['error' => 'Player not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['firstName'])) $user->setFirstName($data['firstName']);
        if (isset($data['lastName'])) $user->setLastName($data['lastName']);
        if (isset($data['username'])) $user->setUsername($data['username']);
        if (isset($data['emailAddress'])) $user->setEmailAddress($data['emailAddress']);
        if (isset($data['status'])) $user->setStatus($data['status']);
        if (isset($data['password'])) {
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        }

        $em->flush();

        return $this->json([
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'username' => $user->getUsername(),
            'emailAddress' => $user->getEmailAddress(),
            'status' => $user->getStatus(),
        ]);
    }

    #[Route('/api/players/{id}', name: 'player_delete', methods: ['DELETE'])]
    public function delete(int $id, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
    {
        $user = $userRepository->find($id);
        if (!$user) {
            return $this->json(['error' => 'Player not found'], 404);
        }

        $em->remove($user);
        $em->flush();

        return $this->json(['message' => 'Player deleted successfully']);
    }
}