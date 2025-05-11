<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\VacationRequest;
use App\Enum\VacationStatus;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class VacationRequestTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $request = new VacationRequest();

        $start = new DateTimeImmutable('2025-07-01');
        $end = new DateTimeImmutable('2025-07-10');
        $submitted = new DateTimeImmutable('2025-06-15');
        $reason = 'Summer vacation';
        $status = VacationStatus::Approved;

        $user = $this->createMock(User::class);

        $request->setStartDate($start);
        $request->setEndDate($end);
        $request->setSubmittedAt($submitted);
        $request->setReason($reason);
        $request->setStatus($status);
        $request->setUser($user);

        $this->assertSame($start, $request->getStartDate());
        $this->assertSame($end, $request->getEndDate());
        $this->assertSame($submitted, $request->getSubmittedAt());
        $this->assertSame($reason, $request->getReason());
        $this->assertSame($status, $request->getStatus());
        $this->assertSame($user, $request->getUser());
    }

    public function testDefaultStatusIsPending(): void
    {
        $request = new VacationRequest();
        $this->assertSame(VacationStatus::Pending, $request->getStatus());
    }

    public function testSetEndDateThrowsWhenBeforeStartDate(): void
    {
        $request = new VacationRequest();

        $start = new DateTime('2025-12-10');
        $end = new DateTime('2025-12-01');

        $request->setStartDate($start);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('End date cannot be before start date');

        $request->setEndDate($end);
    }

    public function testSetEndDateAcceptsValidRange(): void
    {
        $request = new VacationRequest();

        $start = new DateTime('2025-12-01');
        $end = new DateTime('2025-12-10');

        $request->setStartDate($start);
        $request->setEndDate($end);

        $this->assertSame($start, $request->getStartDate());
        $this->assertSame($end, $request->getEndDate());
    }
}
