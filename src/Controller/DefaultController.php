<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;


class DefaultController
{
    #[Route('/', name: 'root_path')]
    public function index(): JsonResponse
    {
        return new JsonResponse(['name' => 'Hello World']);
    }
}
