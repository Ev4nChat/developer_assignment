<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\VacationRequestRepository;
use App\Enum\VacationStatus;
use App\State\VacationRequestProcessor;
use App\State\VacationRequestProvider;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\MaxDepth;

#[ORM\Entity(repositoryClass: VacationRequestRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['vacation_request:read']],
            security: "is_granted('ROLE_EMPLOYEE') or is_granted('ROLE_MANAGER')",
            securityMessage: "Only employees and managers can view vacation requests.",
            provider: VacationRequestProvider::class,
        ),
        new Post(
            denormalizationContext: ['groups' => ['vacation_request:write']],
            security: "is_granted('ROLE_EMPLOYEE')",
            securityMessage: "Only employees can create vacation requests.",
            processor: VacationRequestProcessor::class,
        ),
        new Get(
            normalizationContext: ['groups' => ['vacation_request:read']],
            security: "is_granted('ROLE_EMPLOYEE') or is_granted('ROLE_MANAGER')",
            securityMessage: "Only employees and managers can view a vacation request.",
        ),
        new Put(
            denormalizationContext: ['groups' => ['vacation_request:write']],
            security: "is_granted('ROLE_MANAGER')",
            securityMessage: "Only managers can update vacation requests.",
        )
    ],
    normalizationContext: ['groups' => ['vacation_request:read']],
    denormalizationContext: ['groups' => ['vacation_request:write']],
)]

class VacationRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['vacation_request:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['vacation_request:read', 'vacation_request:write'])]
    private DateTimeInterface $startDate;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['vacation_request:read', 'vacation_request:write'])]
    private DateTimeInterface $endDate;

    #[ORM\Column(length: 255)]
    #[Groups(['vacation_request:read', 'vacation_request:write'])]
    private string $reason;

    #[ORM\Column(length: 20, enumType: VacationStatus::class)]
    #[Groups(['vacation_request:read'])]
    private VacationStatus $status = VacationStatus::Pending;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['vacation_request:read'])]
    private DateTimeInterface $submittedAt;

    #[ORM\ManyToOne(inversedBy: 'vacationRequests')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['vacation_request:read'])]
    #[MaxDepth(1)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(DateTimeInterface $endDate): self
    {
        if (isset($this->startDate) && $endDate < $this->startDate) {
            throw new BadRequestException('End date cannot be before start date');
        }
        $this->endDate = $endDate;
        return $this;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;
        return $this;
    }

    public function getStatus(): VacationStatus
    {
        return $this->status;
    }

    public function setStatus(VacationStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getSubmittedAt(): DateTimeInterface
    {
        return $this->submittedAt;
    }

    public function setSubmittedAt(DateTimeInterface $submittedAt): self
    {
        $this->submittedAt = $submittedAt;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }
}
