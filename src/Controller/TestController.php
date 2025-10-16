<?php

namespace App\Controller;

use App\Entity\Character;
use App\Entity\Episode;
use App\Entity\Review;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test', name: 'app_test')]
    public function test(EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $character = new Character();
            $character->setName('Rick Sanchez');
            $character->setStatus('alive');
            $character->setGender('male');
            $character->setUrl('https://rickandmortyapi.com/api/character/1');

            $episode = new Episode();
            $episode->setName('Pilot');
            $episode->setAirDate(new \DateTime('2013-12-02'));
            $episode->setSeason(1);
            $episode->setEpisode(1);
            $episode->setUrl('https://rickandmortyapi.com/api/episode/1');

            $review = new Review();
            $review->setAuthor('Test');
            $review->setText('Great episode!');
            $review->setRating(5);
            $review->setEpisode($episode);

            $entityManager->persist($character);
            $entityManager->persist($episode);
            $entityManager->persist($review);
            $entityManager->flush();

            return $this->json([
                'character_id' => $character->getId(),
                'episode_id' => $episode->getId(),
                'review_id' => $review->getId(),
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
