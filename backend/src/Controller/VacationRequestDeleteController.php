<?php

namespace App\Controller;

use App\Entity\VacationRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VacationRequestDeleteController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {
    }

    #[Route('/api/vacation_requests/{id}/delete', name: 'vacation_request_delete', methods: ['DELETE'])]
    public function __invoke(VacationRequest $vacationRequest): Response
    {
        $user = $this->security->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->security->isGranted('ROLE_EMPLOYEE')) {
            return new JsonResponse(['error' => 'Only employees can delete requests.'], Response::HTTP_FORBIDDEN);
        }

        if ($vacationRequest->getUser() !== $user) {
            return new JsonResponse(['error' => 'You can only delete your own requests.'], Response::HTTP_FORBIDDEN);
        }

        if ($vacationRequest->getStatus()->value !== 'Pending') {
            return new JsonResponse(['error' => 'Only pending requests can be deleted.'], Response::HTTP_FORBIDDEN);
        }

        $this->entityManager->remove($vacationRequest);
        $this->entityManager->flush();

        return new JsonResponse(['success' => 'Vacation request deleted.']);
    }
}
