<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/** @implements ProcessorInterface<User, User> */
class UserProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($operation instanceof Post) {
            $data = $this->handlePost($data);
            $this->entityManager->persist($data);
        } elseif ($operation instanceof Patch) {
            $data = $this->handlePatch($data);
        } else {
            throw new RuntimeException('Unsupported operation type.');
        }

        $errors = $this->validator->validate($data);
        if (count($errors) > 0) {
            throw new ValidationFailedException($data, $errors);
        }

        $this->entityManager->flush();

        return $data;
    }

    private function handlePost(User $user): User
    {
        if (empty($user->getPassword())) {
            throw new RuntimeException('Password must be provided when creating a user.');
        }

        if (
            !in_array('ROLE_EMPLOYEE', $user->getRoles())
            && !in_array('ROLE_MANAGER', $user->getRoles())
        ) {
            $user->setRoles(['ROLE_EMPLOYEE']);
        }

        // Always hash the password on creation
        $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);

        return $user;
    }

    private function handlePatch(User $newData): User
    {
        $originalUser = $this->entityManager->getRepository(User::class)->find($newData->getId());

        if (!$originalUser) {
            throw new NotFoundHttpException('User not found.');
        }

        if ($newData->getName() !== null) {
            $originalUser->setName($newData->getName());
        }

        if ($newData->getEmail() !== null) {
            $originalUser->setEmail($newData->getEmail());
        }

        if ($newData->getPassword() !== null && $newData->getPassword() !== '') {
            if (!str_starts_with($newData->getPassword(), '$2y$')) {
                $hashedPassword = $this->passwordHasher->hashPassword($originalUser, $newData->getPassword());
                $originalUser->setPassword($hashedPassword);
            } else {
                $originalUser->setPassword($newData->getPassword());
            }
        }

        return $originalUser;
    }
}
