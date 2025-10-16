<?php

namespace App\Controller\Api;

use App\Repository\CharacterRepository;
use App\Service\DtoTransformer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/characters')]
class CharacterController extends AbstractController
{
    public function __construct(
        private DtoTransformer $dtoTransformer
    ) {
    }

    #[Route('', name: 'api_characters_list', methods: ['GET'])]
    public function list(
        Request $request,
        CharacterRepository $characterRepository
    ): JsonResponse {
        $offset = $request->query->getInt('offset', 0);
        $limit = $request->query->getInt('limit', 10);
        $status = $request->query->get('status');
        $gender = $request->query->get('gender');
        $search = $request->query->get('search');

        $limit = min($limit, 100);

        $characters = $characterRepository->findWithPaginationAndFilters(
            $offset,
            $limit,
            $status,
            $gender,
            $search
        );

        $total = $characterRepository->countWithFilters($status, $gender, $search);

        $characterDtos = $this->dtoTransformer->transformCharacters($characters);

        return $this->json([
            'data' => $characterDtos,
            'pagination' => [
                'offset' => $offset,
                'limit' => $limit,
                'total' => $total,
            ]
        ]);
    }

    #[Route('/{id}', name: 'api_characters_show', methods: ['GET'])]
    public function show(int $id, CharacterRepository $characterRepository): JsonResponse
    {
        $character = $characterRepository->find($id);

        if (!$character) {
            return $this->json([
                'error' => 'Персонажи не найдены'
            ], 404);
        }

        $characterDto = $this->dtoTransformer->transformCharacter($character);

        return $this->json([
            'data' => $characterDto
        ]);
    }
}
