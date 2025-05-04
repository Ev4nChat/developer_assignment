<?php

namespace App\Tests\State;

use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\User;
use App\State\UserProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserProcessorTest extends TestCase
{
    public function testProcessPostHashesPasswordAndPersistsUser(): void
    {
        $user = new User();
        $user->setPassword('plain');

        $hashedPassword = 'hashed_password';

        $mockHasher = $this->createMock(UserPasswordHasherInterface::class);
        $mockHasher->method('hashPassword')->with($user, 'plain')->willReturn($hashedPassword);

        $mockValidator = $this->createMock(ValidatorInterface::class);
        $mockValidator->method('validate')->with($user)->willReturn(new ConstraintViolationList());

        $mockEntityManager = $this->createMock(EntityManagerInterface::class);
        $mockEntityManager->expects($this->once())->method('persist')->with($user);
        $mockEntityManager->expects($this->once())->method('flush');

        $processor = new UserProcessor($mockEntityManager, $mockHasher, $mockValidator);

        $result = $processor->process($user, new Post());

        $this->assertSame($hashedPassword, $user->getPassword());
        $this->assertSame($user, $result);
    }

    public function testProcessPostThrowsIfNoPassword(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Password must be provided when creating a user.');

        $user = new User(); // No password set

        $processor = new UserProcessor(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(UserPasswordHasherInterface::class),
            $this->createMock(ValidatorInterface::class),
        );

        $processor->process($user, new Post());
    }

    public function testProcessPatchUpdatesUserFields(): void
    {
        $user = new User();
        $this->setUserId($user, 1);
        $user->setName('Updated')->setEmail('updated@example.com')->setPassword('newpass');

        $originalUser = new User();
        $this->setUserId($originalUser, 1);
        $originalUser->setName('Old')->setEmail('old@example.com')->setPassword('oldpass');

        // 👇 Fix: use EntityRepository (not just ObjectRepository)
        $mockRepo = $this->createMock(EntityRepository::class);
        $mockRepo->method('find')->with(1)->willReturn($originalUser);

        $mockEntityManager = $this->createMock(EntityManagerInterface::class);
        $mockEntityManager->method('getRepository')->willReturn($mockRepo);
        $mockEntityManager->expects($this->once())->method('flush');

        $mockHasher = $this->createMock(UserPasswordHasherInterface::class);
        $mockHasher->method('hashPassword')->willReturn('hashed_newpass');

        $mockValidator = $this->createMock(ValidatorInterface::class);
        $mockValidator->method('validate')->willReturn(new ConstraintViolationList());

        $processor = new UserProcessor($mockEntityManager, $mockHasher, $mockValidator);

        $result = $processor->process($user, new Patch());

        $this->assertSame('Updated', $result->getName());
        $this->assertSame('updated@example.com', $result->getEmail());
        $this->assertSame('hashed_newpass', $result->getPassword());
    }

    public function testProcessThrowsValidationErrors(): void
    {
        $this->expectException(ValidationFailedException::class);

        $user = new User();
        $user->setPassword('test');

        $violation = $this->createMock(ConstraintViolationInterface::class);
        $violations = new ConstraintViolationList([$violation]);

        $mockValidator = $this->createMock(ValidatorInterface::class);
        $mockValidator->method('validate')->willReturn($violations);

        $processor = new UserProcessor(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(UserPasswordHasherInterface::class),
            $mockValidator,
        );

        $processor->process($user, new Post());
    }

    private function setUserId(User $user, int $id): void
    {
        $ref = new \ReflectionClass($user);
        $prop = $ref->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($user, $id);
    }
}
