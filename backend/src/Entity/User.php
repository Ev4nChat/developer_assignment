<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\State\UserProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use LogicException;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(
    fields: ['employeeCode'],
    message: 'This employee code is already in use.',
    groups: ['user:write:post']
)]
#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_MANAGER')",
            securityMessage: "Only managers can view users."
        ),
        new Get(
            security: "is_granted('ROLE_MANAGER') or object == user",
            securityMessage: "Only managers or the user themselves can view this user."
        ),
        new Post(
            denormalizationContext: ['groups' => ['user:write:post']],
            security: "is_granted('ROLE_MANAGER')",
            securityMessage: "Only managers can create users.",
            validationContext: ['groups' => ['user:write:post']],
            processor: UserProcessor::class
        ),
        new Patch(
            denormalizationContext: ['groups' => ['user:write']],
            security: "is_granted('ROLE_MANAGER') or object == user",
            securityMessage: "You can only update your own account or be a manager.",
            validationContext: ['groups' => ['user:write']],
            processor: UserProcessor::class
        ),
        new Delete(
            security: "is_granted('ROLE_MANAGER')",
            securityMessage: "Only managers can delete users."
        ),
    ],
    normalizationContext: ['groups' => ['user:read']]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /** @var int|null */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'vacation_request:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:read', 'user:write', 'user:write:post', 'vacation_request:read'])]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write', 'user:write:post', 'vacation_request:read'])]
    private ?string $name = null;

    /** @var array<int, string> */
    #[ORM\Column]
    #[Groups(['user:write', 'user:write:post'])]
    private array $roles = [];

    #[ORM\Column]
    #[Groups(['user:write', 'user:write:post'])]
    private ?string $password = null;

    #[ORM\Column(length: 100, unique: true, nullable: true)]
    #[Groups(['user:read', 'user:write:post'])]
    #[Assert\NotBlank(groups: ['user:write:post'])]
    private ?string $employeeCode = null;

    /** @var Collection<int, VacationRequest> */
    #[ORM\OneToMany(targetEntity: VacationRequest::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $vacationRequests;

    public function __construct()
    {
        $this->vacationRequests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array<int, string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array<int, string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getEmployeeCode(): ?string
    {
        return $this->employeeCode;
    }

    public function setEmployeeCode(?string $employeeCode): self
    {
        $this->employeeCode = $employeeCode;
        return $this;
    }

    /**
     * @return non-empty-string
     */
    public function getUserIdentifier(): string
    {
        if ($this->email === null) {
            throw new LogicException('User email should not be null when calling getUserIdentifier().');
        }

        \assert($this->email !== ''); // Prevents empty string

        return $this->email;
    }

    /**
     * @return Collection<int, VacationRequest>
     */
    public function getVacationRequests(): Collection
    {
        return $this->vacationRequests;
    }

    public function eraseCredentials(): void
    {
        // Nothing needed here yet, but this method is required by Symfony
        // when using the UserInterface
    }
}
