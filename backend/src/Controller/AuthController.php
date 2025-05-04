<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AuthController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): JsonResponse {
        /** @var array{email: string, name: string, password: string, role: string, employeeCode: string} $data */
        $data = json_decode($request->getContent(), true);

        if (
            empty($data['email'])
            || empty($data['name'])
            || empty($data['password'])
            || empty($data['role'])
            || empty($data['employeeCode'])
        ) {
            return new JsonResponse(['error' => 'Missing required fields.'], 400);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setName($data['name']);
        $user->setEmployeeCode($data['employeeCode']);
        $user->setRoles([$data['role']]);

        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $em->persist($user);
        $em->flush();

        return new JsonResponse(['status' => 'User registered']);
    }
}
