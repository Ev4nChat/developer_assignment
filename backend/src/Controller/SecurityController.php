<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController
{
    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // This code is never executed - Lexik handles it
        return new JsonResponse([
            'message' => 'This should never be reached!'
        ], 400);
    }
}
