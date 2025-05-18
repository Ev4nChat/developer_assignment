<?php

namespace App\Controller;

use App\Entity\VacationRequest;
use App\Enum\VacationStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class VacationRequestActionController extends AbstractController
{
    #[Route('/api/vacation_requests/{id}/approve', name: 'vacation_request_approve', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function approve(VacationRequest $vacationRequest, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($vacationRequest->getStatus() !== VacationStatus::Pending) {
            return $this->json(['message' => 'Only pending requests can be approved.'], 400);
        }

        $vacationRequest->setStatus(VacationStatus::Approved);
        $entityManager->flush();

        return $this->json(['message' => 'Vacation request approved successfully.']);
    }

    #[Route('/api/vacation_requests/{id}/reject', name: 'vacation_request_reject', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function reject(VacationRequest $vacationRequest, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($vacationRequest->getStatus() !== VacationStatus::Pending) {
            return $this->json(['message' => 'Only pending requests can be rejected.'], 400);
        }

        $vacationRequest->setStatus(VacationStatus::Rejected);
        $entityManager->flush();

        return $this->json(['message' => 'Vacation request rejected successfully.']);
    }
}
