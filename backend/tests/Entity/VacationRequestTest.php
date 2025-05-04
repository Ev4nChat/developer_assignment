<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\VacationRequest;
use App\Enum\VacationStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

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
}
