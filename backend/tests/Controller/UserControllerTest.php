<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserControllerTest extends TestCase
{
    public function testListUsersReturnsUserData(): void
    {
        // Mock users
        $user1 = $this->createMock(User::class);
        $user1->method('getId')->willReturn(1);
        $user1->method('getName')->willReturn('Alice');
        $user1->method('getEmail')->willReturn('alice@example.com');
        $user1->method('getEmployeeCode')->willReturn('EMP001');
        $user1->method('getRoles')->willReturn(['ROLE_EMPLOYEE']);

        $user2 = $this->createMock(User::class);
        $user2->method('getId')->willReturn(2);
        $user2->method('getName')->willReturn('Bob');
        $user2->method('getEmail')->willReturn('bob@example.com');
        $user2->method('getEmployeeCode')->willReturn('EMP002');
        $user2->method('getRoles')->willReturn(['ROLE_MANAGER']);

        $mockRepo = $this->createMock(UserRepository::class);
        $mockRepo->method('findAll')->willReturn([$user1, $user2]);

        // Expected response data
        $expectedData = [
            [
                'id' => 1,
                'name' => 'Alice',
                'email' => 'alice@example.com',
                'employeeCode' => 'EMP001',
                'roles' => ['ROLE_EMPLOYEE'],
            ],
            [
                'id' => 2,
                'name' => 'Bob',
                'email' => 'bob@example.com',
                'employeeCode' => 'EMP002',
                'roles' => ['ROLE_MANAGER'],
            ]
        ];

        // Partial mock of UserController
        $controller = $this->getMockBuilder(\App\Controller\UserController::class)
            ->onlyMethods(['json'])
            ->getMock();

        $controller->expects($this->once())
            ->method('json')
            ->with($expectedData)
            ->willReturn(new JsonResponse($expectedData));

        $response = $controller->listUsers($mockRepo);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            (string) json_encode($expectedData),
            (string) $response->getContent()
        );
    }
}
