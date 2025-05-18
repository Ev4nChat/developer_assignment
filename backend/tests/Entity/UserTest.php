<?php

namespace App\Tests\Entity;

use App\Entity\User;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $user = new User();

        $user->setEmail('test@example.com');
        $this->assertSame('test@example.com', $user->getEmail());

        $user->setName('Test User');
        $this->assertSame('Test User', $user->getName());

        $user->setPassword('securepassword');
        $this->assertSame('securepassword', $user->getPassword());

        $user->setEmployeeCode('EMP001');
        $this->assertSame('EMP001', $user->getEmployeeCode());

        $user->setRoles(['ROLE_MANAGER']);
        $roles = $user->getRoles();
        $this->assertContains('ROLE_MANAGER', $roles);
        $this->assertContains('ROLE_USER', $roles); // Always included
        $this->assertCount(2, array_unique($roles));
    }

    public function testGetUserIdentifier(): void
    {
        $user = new User();
        $user->setEmail('identifier@example.com');

        $this->assertSame('identifier@example.com', $user->getUserIdentifier());
    }

    public function testSetRolesThrowsOnInvalidRole(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid role: ROLE_INVALID. Allowed roles are: ROLE_MANAGER, ROLE_EMPLOYEE');

        $user = new User();
        $user->setRoles(['ROLE_INVALID']);
    }
}
