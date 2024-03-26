<?php

declare(strict_types=1);

namespace App\Controller;

use Google\Cloud\Firestore\DocumentReference;
use Google\Cloud\Firestore\DocumentSnapshot;
use Google\Cloud\Firestore\QuerySnapshot;
use IteratorAggregate;
use Kreait\Firebase\Contract\Firestore;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class FirebaseController
{
    public function __construct(
        #[Autowire(service: 'kreait_firebase.symfony_firebase.firestore')]
        private Firestore $firestore,
        private string $collection
    ) {
    }

    #[Route('/documents', name: 'getDocuments', methods: ['GET'])]
    public function getDocuments(Request $request): JsonResponse
    {
        $limit = $request->query->get('limit');
        $collection = $this->firestore->database()->collection($this->collection);

        if (is_numeric($limit) && 0 <= $limit) {
            $collection = $collection->limit($limit);
        }

        /** @var QuerySnapshot $documents */
        $documents = $collection->documents();

        if ($documents->isEmpty()) {
            return new JsonResponse([], 200);
        }

        $result = array_map(static function (DocumentSnapshot $document) {
            return $document->data();
        }, $documents->rows());

        return new JsonResponse($result, 200);
    }

    #[Route('/documents', name: 'addDocument', methods: ['POST'])]
    public function addDocument(Request $request): Response
    {
        if ('application/json' !== $request->headers->get('Content-Type')) {
            throw new BadRequestHttpException('Content type mismatch, only allow application/json');
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['code']) || empty($data['city']) || empty($data['state'])) {
            throw new BadRequestHttpException('Fail to valid the request body');
        }

        /** @var DocumentReference $document */
        $document = $this->firestore->database()->collection($this->collection)->document($data['code']);

        if ($document->snapshot()->exists()) {
            throw new BadRequestHttpException('The provided city is already exist');
        }

        $document->set([
            'code' => strtolower($data['code']),
            'city' => strtolower($data['city']),
            'state' => strtolower($data['state'])
        ]);

        return new Response(null, 201);
    }

    #[Route('/documents/{code}', name: 'updateDocument', methods: ['PATCH'])]
    public function updateDocument(Request $request, string $code): Response
    {
        if ('application/json' !== $request->headers->get('Content-Type')) {
            throw new BadRequestHttpException('Content type mismatch, only allow application/json');
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['city']) || empty($data['state'])) {
            throw new BadRequestHttpException('Fail to valid the request body');
        }

        $code = strtolower($code);
        /** @var DocumentReference $document */
        $document = $this->firestore->database()->collection($this->collection)->document($code);

        if (!$document->snapshot()->exists()) {
            throw new BadRequestHttpException('No provided city exist');
        }

        $document->set([
            'code' => $code,
            'city' => strtolower($data['city']),
            'state' => strtolower($data['state'])
        ]);

        return new Response(null, 204);
    }

    #[Route('/documents', name: 'deleteDocuments', methods: ['DELETE'])]
    public function deleteDocuments(): Response
    {
        /** @var IteratorAggregate $documents */
        $documents = $this->firestore->database()->collection($this->collection)->documents();

        foreach ($documents as $document) {
            /** @var DocumentSnapshot $document */
            $document->reference()->delete();
        }

        return new Response(null, 204);
    }
}
