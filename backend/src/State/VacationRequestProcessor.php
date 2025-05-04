<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Entity\VacationRequest;
use App\Enum\VacationStatus;
use DateTimeImmutable;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @implements ProcessorInterface<VacationRequest, VacationRequest>
 */
class VacationRequestProcessor implements ProcessorInterface
{
    /**
     * @param ProcessorInterface<VacationRequest, VacationRequest> $persistProcessor
     */
    public function __construct(
        private ProcessorInterface $persistProcessor,
        private Security $security
    ) {
    }

    /**
     * @return VacationRequest
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($operation instanceof Post) {
            $now = new DateTimeImmutable('today');

            if ($data->getStartDate() < $now || $data->getEndDate() < $now) {
                throw new BadRequestHttpException('Start date and end date must not be in the past.');
            }

            $user = $this->security->getUser();
            if ($user instanceof User) {
                $data->setUser($user);
                $data->setSubmittedAt(new DateTimeImmutable());
                $data->setStatus(VacationStatus::Pending);
            }
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
