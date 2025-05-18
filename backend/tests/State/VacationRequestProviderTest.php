<?php

namespace App\Tests\State;

use ApiPlatform\Metadata\Operation;
use App\Entity\User;
use App\Entity\VacationRequest;
use App\Repository\VacationRequestRepository;
use App\State\VacationRequestProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class VacationRequestProviderTest extends TestCase
{
    public function testProvideReturnsAllForManager(): void
    {
        $manager = $this->createMock(User::class);
        $manager->method('getRoles')->willReturn(['ROLE_MANAGER']);

        $requests = [new VacationRequest(), new VacationRequest()];

        $repo = $this->createMock(VacationRequestRepository::class);
        $repo->expects($this->once())->method('findAll')->willReturn($requests);

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($manager);

        $provider = new VacationRequestProvider($repo, $security);
        $result = $provider->provide($this->createMock(Operation::class));

        $this->assertSame($requests, $result);
    }

    public function testProvideReturnsUserOnlyRequestsForEmployee(): void
    {
        $employee = $this->createMock(User::class);
        $employee->method('getRoles')->willReturn(['ROLE_EMPLOYEE']);

        $requests = [new VacationRequest()];

        $repo = $this->createMock(VacationRequestRepository::class);
        $repo->expects($this->once())
            ->method('findBy')
            ->with(['user' => $employee])
            ->willReturn($requests);

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($employee);

        $provider = new VacationRequestProvider($repo, $security);
        $result = $provider->provide($this->createMock(Operation::class));

        $this->assertSame($requests, $result);
    }

    public function testProvideReturnsEmptyForUnauthenticated(): void
    {
        $repo = $this->createMock(VacationRequestRepository::class);
        $repo->expects($this->never())->method($this->anything());

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn(null);

        $provider = new VacationRequestProvider($repo, $security);
        $result = $provider->provide($this->createMock(Operation::class));

        $this->assertSame([], $result);
    }
}
