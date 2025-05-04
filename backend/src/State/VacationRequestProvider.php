<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\VacationRequest;
use App\Repository\VacationRequestRepository;
use Symfony\Bundle\SecurityBundle\Security;

/** @implements ProviderInterface<VacationRequest> */
class VacationRequestProvider implements ProviderInterface
{
    public function __construct(
        private VacationRequestRepository $repository,
        private Security $security
    ) {}

    /**
     * @return iterable<VacationRequest>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $user = $this->security->getUser();

        if (!$user) {
            return [];
        }

        if (in_array('ROLE_MANAGER', $user->getRoles(), true)) {
            // Manager sees everything
            return $this->repository->findAll();
        }

        // Employees see only their own requests
        return $this->repository->findBy(['user' => $user]);
    }
}
