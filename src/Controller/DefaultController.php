<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Student;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController
{
    private const NAMES = [
        'Ali',
        'Beatriz',
        'Charles',
        'Diya',
        'Eric',
        'Fatima',
        'Gabriel',
        'Hanna',
    ];

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/', name: 'root_path')]
    public function index(): JsonResponse
    {
        return new JsonResponse(['name' => 'Hello World']);
    }

    /**
     * @throws RandomException
     */
    #[Route('/students/generate', name: 'generateStudent', methods: 'POST')]
    public function generateStudentData(): Response
    {
        foreach (self::NAMES as $name)
        {
            $student = new Student($name, random_int(100000,999999));

            $this->entityManager->persist($student);
        }

        $this->entityManager->flush();

        return new Response(null, 201);
    }

    #[Route('/students', name: 'getStudents', methods: 'GET')]
    public function getStudents(): JsonResponse
    {
        $students = $this->entityManager->getRepository(Student::class)->findAll();
        $data = [];

        foreach ($students as $student) {
            $data[] = [
                'id' => $student->getId(),
                'name' => $student->getName(),
                'code' => $student->getCode(),
                'isActive' => $student->isIsActive(),
            ];
        }

        return new JsonResponse(['students' => $data], 200);
    }
}
