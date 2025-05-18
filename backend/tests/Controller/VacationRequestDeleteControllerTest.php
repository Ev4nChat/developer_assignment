<?php

namespace App\Tests\Controller;

use App\Controller\VacationRequestDeleteController;
use App\Entity\User;
use App\Entity\VacationRequest;
use App\Enum\VacationStatus;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

class VacationRequestDeleteControllerTest extends TestCase
{
    public function testDeleteSuccess(): void
    {
        $user = $this->createMock(User::class);

        $vacationRequest = $this->createMock(VacationRequest::class);
        $vacationRequest->method('getUser')->willReturn($user);
        $vacationRequest->method('getStatus')->willReturn(VacationStatus::Pending);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('remove')->with($vacationRequest);
        $entityManager->expects($this->once())->method('flush');

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);
        $security->method('isGranted')->with('ROLE_EMPLOYEE')->willReturn(true);

        $controller = new VacationRequestDeleteController($entityManager, $security);
        $response = $controller->__invoke($vacationRequest);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            (string) json_encode(['success' => 'Vacation request deleted.']),
            (string) $response->getContent()
        );
    }

    public function testDeleteUnauthenticated(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn(null);

        $controller = new VacationRequestDeleteController($entityManager, $security);
        $response = $controller->__invoke($this->createMock(VacationRequest::class));

        $this->assertSame(401, $response->getStatusCode());
    }

    public function testDeleteUnauthorizedRole(): void
    {
        $user = $this->createMock(User::class);

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);
        $security->method('isGranted')->with('ROLE_EMPLOYEE')->willReturn(false);

        $controller = new VacationRequestDeleteController($entityManager, $security);
        $response = $controller->__invoke($this->createMock(VacationRequest::class));

        $this->assertSame(403, $response->getStatusCode());
    }

    public function testDeleteNotOwner(): void
    {
        $user = $this->createMock(User::class);
        $anotherUser = $this->createMock(User::class);

        $vacationRequest = $this->createMock(VacationRequest::class);
        $vacationRequest->method('getUser')->willReturn($anotherUser);
        $vacationRequest->method('getStatus')->willReturn(VacationStatus::Pending);

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);
        $security->method('isGranted')->with('ROLE_EMPLOYEE')->willReturn(true);

        $controller = new VacationRequestDeleteController($entityManager, $security);
        $response = $controller->__invoke($vacationRequest);

        $this->assertSame(403, $response->getStatusCode());
    }

    public function testDeleteNotPending(): void
    {
        $user = $this->createMock(User::class);

        $vacationRequest = $this->createMock(VacationRequest::class);
        $vacationRequest->method('getUser')->willReturn($user);
        $vacationRequest->method('getStatus')->willReturn(VacationStatus::Approved);

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);
        $security->method('isGranted')->with('ROLE_EMPLOYEE')->willReturn(true);

        $controller = new VacationRequestDeleteController($entityManager, $security);
        $response = $controller->__invoke($vacationRequest);

        $this->assertSame(403, $response->getStatusCode());
    }
}
