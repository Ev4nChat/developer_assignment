<?php

namespace App\Tests\Controller;

use App\Controller\AuthController;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthControllerTest extends TestCase
{
    public function testRegisterReturnsSuccessResponse(): void
    {
        $mockRequest = $this->createMock(Request::class);
        $mockHasher = $this->createMock(UserPasswordHasherInterface::class);
        $mockEm = $this->createMock(EntityManagerInterface::class);

        $userData = [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'employeeCode' => 'EMP123',
            'role' => 'ROLE_EMPLOYEE',
            'password' => 'plainPassword',
        ];

        $mockRequest->method('getContent')
            ->willReturn(json_encode($userData));

        $mockHasher->method('hashPassword')
            ->willReturn('hashedPassword');

        $mockEm->expects($this->once())->method('persist')->with($this->isInstanceOf(User::class));

        $mockEm->expects($this->once())->method('flush');

        $controller = new AuthController();
        $response = $controller->register($mockRequest, $mockHasher, $mockEm);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            (string) json_encode(['status' => 'User registered']),
            (string) $response->getContent()
        );
    }
}
