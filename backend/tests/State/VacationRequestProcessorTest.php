<?php

namespace App\Tests\State;

use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Entity\VacationRequest;
use App\Enum\VacationStatus;
use App\State\VacationRequestProcessor;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class VacationRequestProcessorTest extends TestCase
{
    public function testProcessSetsFieldsOnPost(): void
    {
        $user = $this->createMock(User::class);

        $request = new VacationRequest();
        $request->setStartDate(new DateTimeImmutable('+1 day'));
        $request->setEndDate(new DateTimeImmutable('+2 days'));
        $request->setReason('Testing');

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);

        $mockPersistProcessor = $this->createMock(ProcessorInterface::class);
        $mockPersistProcessor->expects($this->once())
            ->method('process')
            ->with($request, $this->isInstanceOf(Post::class), [], [])
            ->willReturn($request);

        $processor = new VacationRequestProcessor($mockPersistProcessor, $security);

        $result = $processor->process($request, new Post());

        $this->assertInstanceOf(VacationRequest::class, $result);
        $this->assertSame($user, $result->getUser());
        $this->assertSame(VacationStatus::Pending, $result->getStatus());
    }

    public function testProcessPostWithoutUser(): void
    {
        $request = new VacationRequest();
        $request->setStartDate(new DateTimeImmutable('+1 day'));
        $request->setEndDate(new DateTimeImmutable('+2 days'));
        $request->setReason('Test reason');

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn(null); // No user

        $mockPersistProcessor = $this->createMock(ProcessorInterface::class);
        $mockPersistProcessor->expects($this->once())
            ->method('process')
            ->willReturn($request);

        $processor = new VacationRequestProcessor($mockPersistProcessor, $security);

        $result = $processor->process($request, new Post());

        $this->assertInstanceOf(VacationRequest::class, $result);
        $this->assertNull($result->getUser());
        $this->assertSame(VacationStatus::Pending, $result->getStatus());
        $this->assertInstanceOf(DateTimeInterface::class, $result->getStartDate());
        $this->assertInstanceOf(DateTimeInterface::class, $result->getEndDate());
    }
}
