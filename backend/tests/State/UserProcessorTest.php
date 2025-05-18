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
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
        $user->setName('Test');
        $user->setEmail('test@test.com');
        $user->setEmployeeCode('1234567');

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
        $this->expectExceptionMessage('Password cannot be empty.');

        $user = new User();
        $user->setName('Test');
        $user->setEmail('test@test.com');
        $user->setEmployeeCode('1234567');

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
        $user->setName('Test');
        $user->setEmail('test@test.com');
        $user->setEmployeeCode('1234567');

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

    public function testProcessPostThrowsExceptionIfEmailIsMissing(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Email cannot be empty.');

        $user = new User();
        $user->setName('test');
        $user->setPassword('plain');
        $user->setEmployeeCode('1234567');

        $processor = new UserProcessor(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(UserPasswordHasherInterface::class),
            $this->createMock(ValidatorInterface::class),
        );

        $processor->process($user, new Post());
    }

    public function testProcessPostThrowsExceptionIfEmailIsInvalid(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Invalid email address.');

        $user = new User();
        $user->setName('test');
        $user->setEmail(' ');
        $user->setPassword('plain');
        $user->setEmployeeCode('1234567');

        $processor = new UserProcessor(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(UserPasswordHasherInterface::class),
            $this->createMock(ValidatorInterface::class),
        );

        $processor->process($user, new Post());
    }

    public function testProcessPostThrowsExceptionIfNameIsMissing(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Name cannot be empty.');

        $user = new User();
        $user->setEmail('testing@example.com');
        $user->setPassword('plain');
        $user->setEmployeeCode('1234567');

        $processor = new UserProcessor(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(UserPasswordHasherInterface::class),
            $this->createMock(ValidatorInterface::class),
        );

        $processor->process($user, new Post());
    }

    public function testProcessPostThrowsExceptionIfNameIsInvalid(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Name must be provided when creating a user.');

        $user = new User();
        $user->setName(' ');
        $user->setEmail('testing@example.com');
        $user->setPassword('plain');
        $user->setEmployeeCode('1234567');

        $processor = new UserProcessor(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(UserPasswordHasherInterface::class),
            $this->createMock(ValidatorInterface::class),
        );

        $processor->process($user, new Post());
    }

    public function testProcessPostThrowsExceptionIfEmployeeCodeIsMissing(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Employee code cannot be empty.');

        $user = new User();
        $user->setName('test');
        $user->setEmail('testing@example.com');
        $user->setPassword('plain');

        $processor = new UserProcessor(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(UserPasswordHasherInterface::class),
            $this->createMock(ValidatorInterface::class),
        );

        $processor->process($user, new Post());
    }

    public function testProcessPostThrowsExceptionIfEmployeeCodeIsInvalid(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Employee code must be a 7-digit number.');

        $user = new User();
        $user->setName('test');
        $user->setEmail('testing@example.com');
        $user->setPassword('plain');
        $user->setEmployeeCode('123');

        $processor = new UserProcessor(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(UserPasswordHasherInterface::class),
            $this->createMock(ValidatorInterface::class),
        );

        $processor->process($user, new Post());
    }

    public function testProcessPatchThrowsExceptionIfNameIsInvalid(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Name cannot be empty.');

        $user = new User();
        $this->setUserId($user, 1);
        $user->setName('');
        $user->setEmail('testing@example.com');
        $user->setPassword('plain');
        $user->setEmployeeCode('1234567');

        $existingUser = new User();
        $this->setUserId($existingUser, 1);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->with(1)->willReturn($existingUser);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repo);

        $processor = new UserProcessor(
            $entityManager,
            $this->createMock(UserPasswordHasherInterface::class),
            $this->createMock(ValidatorInterface::class),
        );

        $processor->process($user, new Patch());
    }

    public function testProcessPostThrowsExceptionIfEmailIsAlreadyInUse(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Email is already in use.');

        $user = new User();
        $user->setPassword('password');
        $user->setName('Duplicate');
        $user->setEmail('duplicate@example.com');
        $user->setEmployeeCode('1234567');

        $existingUser = new User(); // Simulate existing user with the same email

        $mockRepo = $this->createMock(EntityRepository::class);
        $mockRepo->method('findOneBy')->with(['email' => 'duplicate@example.com'])->willReturn($existingUser);

        $mockEntityManager = $this->createMock(EntityManagerInterface::class);
        $mockEntityManager->method('getRepository')->with(User::class)->willReturn($mockRepo);

        $processor = new UserProcessor(
            $mockEntityManager,
            $this->createMock(UserPasswordHasherInterface::class),
            $this->createMock(ValidatorInterface::class)
        );

        $processor->process($user, new Post());
    }

    public function testProcessPatchThrowsExceptionIfEmailIsInvalid(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Invalid email address.');

        $user = new User();
        $this->setUserId($user, 1);
        $user->setName('test');
        $user->setEmail('test');
        $user->setPassword('plain');
        $user->setEmployeeCode('1234567');

        $existingUser = new User();
        $this->setUserId($existingUser, 1);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->with(1)->willReturn($existingUser);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repo);

        $processor = new UserProcessor(
            $entityManager,
            $this->createMock(UserPasswordHasherInterface::class),
            $this->createMock(ValidatorInterface::class),
        );

        $processor->process($user, new Patch());
    }

    public function testProcessPatchThrowsExceptionIfUserNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('User not found.');
        $user = new User();
        $this->setUserId($user, 999);
        $user->setName('Updated Name');
        // Simulate missing user
        $mockRepo = $this->createMock(EntityRepository::class);
        $mockRepo->method('find')->with(999)->willReturn(null);
        $mockEntityManager = $this->createMock(EntityManagerInterface::class);
        $mockEntityManager->method('getRepository')->willReturn($mockRepo);
        $processor = new UserProcessor(
            $mockEntityManager,
            $this->createMock(UserPasswordHasherInterface::class),
            $this->createMock(ValidatorInterface::class)
        );
        $processor->process($user, new Patch());
    }

    private function setUserId(User $user, int $id): void
    {
        $ref = new \ReflectionClass($user);
        $prop = $ref->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($user, $id);
    }
}
