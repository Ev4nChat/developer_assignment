<?php

namespace App\Tests\Controller;

use App\Controller\SecurityController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class SecurityControllerTest extends TestCase
{
    public function testLoginFallbackResponse(): void
    {
        $controller = new SecurityController();
        $response = $controller->login();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(400, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            (string) json_encode(['message' => 'This should never be reached!']),
            (string) $response->getContent()
        );
    }
}
