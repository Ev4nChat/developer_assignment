<?php

namespace App\Tests\Command;

use App\Command\SeedDatabaseCommand;
use App\Entity\User;
use App\Entity\VacationRequest;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SeedDatabaseCommandTest extends TestCase
{
    public function testExecuteSeedsDatabaseSuccessfully(): void
    {
        // Arrange
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $hasher = $this->createMock(UserPasswordHasherInterface::class);

        // Always return 'hashed_password' for simplicity
        $hasher->method('hashPassword')->willReturn('hashed_password');

        // Track persist calls
        $entityManager->expects($this->atLeast(6)) //2 managers + 2 employees + 2 requests minimum
        ->method('persist')
            ->with($this->callback(function ($entity) {
                return $entity instanceof User || $entity instanceof VacationRequest;
            }));

        $entityManager->expects($this->once())
            ->method('flush');

        $command = new SeedDatabaseCommand($entityManager, $hasher);
        $commandTester = new CommandTester($command);

        // Act
        $exitCode = $commandTester->execute([]);

        // Assert
        $output = $commandTester->getDisplay();

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Database seeded successfully', $output);
    }
}
