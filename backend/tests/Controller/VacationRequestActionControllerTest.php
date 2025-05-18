<?php

namespace App\Tests\Controller;

use App\Controller\VacationRequestActionController;
use App\Entity\VacationRequest;
use App\Enum\VacationStatus;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class VacationRequestActionControllerTest extends TestCase
{
    public function testApprovePendingRequest(): void
    {
        $vacationRequest = $this->createMock(VacationRequest::class);
        $vacationRequest->method('getStatus')->willReturn(VacationStatus::Pending);
        $vacationRequest->expects($this->once())->method('setStatus')->with(VacationStatus::Approved);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('flush');

        $controller = $this->getMockBuilder(VacationRequestActionController::class)
            ->onlyMethods(['json'])
            ->getMock();

        $controller->expects($this->once())
            ->method('json')
            ->with(['message' => 'Vacation request approved successfully.'])
            ->willReturn(new JsonResponse(['message' => 'Vacation request approved successfully.']));

        $response = $controller->approve($vacationRequest, $entityManager);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testApproveNonPendingRequest(): void
    {
        $vacationRequest = $this->createMock(VacationRequest::class);
        $vacationRequest->method('getStatus')->willReturn(VacationStatus::Approved);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->never())->method('flush');

        $controller = $this->getMockBuilder(VacationRequestActionController::class)
            ->onlyMethods(['json'])
            ->getMock();

        $controller->expects($this->once())
            ->method('json')
            ->with(['message' => 'Only pending requests can be approved.'], 400)
            ->willReturn(new JsonResponse(['message' => 'Only pending requests can be approved.'], 400));

        $response = $controller->approve($vacationRequest, $entityManager);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(400, $response->getStatusCode());
    }

    public function testRejectPendingRequest(): void
    {
        $vacationRequest = $this->createMock(VacationRequest::class);
        $vacationRequest->method('getStatus')->willReturn(VacationStatus::Pending);
        $vacationRequest->expects($this->once())->method('setStatus')->with(VacationStatus::Rejected);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('flush');

        $controller = $this->getMockBuilder(VacationRequestActionController::class)
            ->onlyMethods(['json'])
            ->getMock();

        $controller->expects($this->once())
            ->method('json')
            ->with(['message' => 'Vacation request rejected successfully.'])
            ->willReturn(new JsonResponse(['message' => 'Vacation request rejected successfully.']));

        $response = $controller->reject($vacationRequest, $entityManager);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testRejectNonPendingRequest(): void
    {
        $vacationRequest = $this->createMock(VacationRequest::class);
        $vacationRequest->method('getStatus')->willReturn(VacationStatus::Rejected);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->never())->method('flush');

        $controller = $this->getMockBuilder(VacationRequestActionController::class)
            ->onlyMethods(['json'])
            ->getMock();

        $controller->expects($this->once())
            ->method('json')
            ->with(['message' => 'Only pending requests can be rejected.'], 400)
            ->willReturn(new JsonResponse(['message' => 'Only pending requests can be rejected.'], 400));

        $response = $controller->reject($vacationRequest, $entityManager);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(400, $response->getStatusCode());
    }
}
