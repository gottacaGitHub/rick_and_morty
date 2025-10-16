<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ReviewTestController extends AbstractController
{
    private string $baseUrl = 'http://localhost';
    private array $clientOptions = [
        'verify_peer' => false,
        'verify_host' => false,
        'timeout' => 30,
    ];

    #[Route('/review-test', name: 'app_review_test')]
    public function testReviewApi(): JsonResponse
    {
        $client = HttpClient::create($this->clientOptions);
        $results = [];

        try {
            // Тест 1: Валидный отзыв
            $results['test_1_valid_review'] = $this->testValidReview($client);

            // Тест 2: Короткий текст
            $results['test_2_short_text'] = $this->testShortText($client);

            // Тест 3: Пустой автор
            $results['test_3_empty_author'] = $this->testEmptyAuthor($client);

            // Тест 4: Короткое имя автора
            $results['test_4_short_author'] = $this->testShortAuthor($client);

            // Тест 5: Очень длинный автор
            $results['test_5_long_author'] = $this->testLongAuthor($client);

            // Тест 6: Очень длинный текст
            $results['test_6_long_text'] = $this->testLongText($client);

            // Тест 7: Отсутствует автор
            $results['test_7_missing_author'] = $this->testMissingAuthor($client);

            // Тест 8: Отсутствует текст
            $results['test_8_missing_text'] = $this->testMissingText($client);

            // Тест 9: Проверка автоматического расчета рейтинга
            $results['test_9_rating_calculation'] = $this->testRatingCalculation($client);

        } catch (\Exception $e) {
            $results['error'] = [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }

        return $this->json([
            'review_api_tests' => $results,
            'summary' => $this->generateSummary($results),
            'test_endpoint' => 'POST /api/episodes/1/reviews',
            'base_url' => $this->baseUrl
        ]);
    }

    private function testValidReview($client): array
    {
        $response = $client->request('POST', $this->baseUrl . '/api/episodes/1/reviews', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode([
                'author' => 'Test User',
                'text' => 'This is a valid review text that is definitely long enough to pass validation. The episode was amazing!'
            ]),
            'timeout' => 30,
        ]);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);
        $data = json_decode($content, true) ?? ['error' => 'Invalid JSON response: ' . $content];

        return [
            'description' => 'Valid review creation',
            'request' => [
                'author' => 'Test User',
                'text' => 'This is a valid review text...'
            ],
            'expected_status' => 201,
            'actual_status' => $statusCode,
            'success' => $statusCode === 201,
            'response' => $data,
            'rating_calculated' => isset($data['data']['rating']) && $data['data']['rating'] !== null
        ];
    }

    private function testShortText($client): array
    {
        $response = $client->request('POST', $this->baseUrl . '/api/episodes/1/reviews', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode([
                'author' => 'Test User',
                'text' => 'short'
            ]),
            'timeout' => 30,
        ]);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);
        $data = json_decode($content, true) ?? ['error' => 'Invalid JSON response: ' . $content];

        return [
            'description' => 'Short text validation',
            'request' => [
                'author' => 'Test User',
                'text' => 'short'
            ],
            'expected_status' => 400,
            'actual_status' => $statusCode,
            'success' => $statusCode === 400,
            'response' => $data,
            'has_validation_errors' => isset($data['messages']) && count($data['messages']) > 0
        ];
    }

    private function testEmptyAuthor($client): array
    {
        $response = $client->request('POST', $this->baseUrl . '/api/episodes/1/reviews', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode([
                'author' => '',
                'text' => 'This is a valid review text that is long enough for validation'
            ]),
            'timeout' => 30,
        ]);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);
        $data = json_decode($content, true) ?? ['error' => 'Invalid JSON response: ' . $content];

        return [
            'description' => 'Empty author validation',
            'request' => [
                'author' => '',
                'text' => 'This is a valid review text...'
            ],
            'expected_status' => 400,
            'actual_status' => $statusCode,
            'success' => $statusCode === 400,
            'response' => $data,
            'has_validation_errors' => isset($data['messages']) && count($data['messages']) > 0
        ];
    }

    private function testShortAuthor($client): array
    {
        $response = $client->request('POST', $this->baseUrl . '/api/episodes/1/reviews', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode([
                'author' => 'A',
                'text' => 'This is a valid review text that is long enough for validation'
            ]),
            'timeout' => 30,
        ]);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);
        $data = json_decode($content, true) ?? ['error' => 'Invalid JSON response: ' . $content];

        return [
            'description' => 'Short author name validation',
            'request' => [
                'author' => 'A',
                'text' => 'This is a valid review text...'
            ],
            'expected_status' => 400,
            'actual_status' => $statusCode,
            'success' => $statusCode === 400,
            'response' => $data,
            'has_validation_errors' => isset($data['messages']) && count($data['messages']) > 0
        ];
    }

    private function testLongAuthor($client): array
    {
        $longAuthor = str_repeat('A', 256); // 256 символов - больше лимита в 255

        $response = $client->request('POST', $this->baseUrl . '/api/episodes/1/reviews', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode([
                'author' => $longAuthor,
                'text' => 'This is a valid review text that is long enough for validation'
            ]),
            'timeout' => 30,
        ]);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);
        $data = json_decode($content, true) ?? ['error' => 'Invalid JSON response: ' . $content];

        return [
            'description' => 'Long author name validation',
            'request' => [
                'author' => 'A*256 (too long)',
                'text' => 'This is a valid review text...'
            ],
            'expected_status' => 400,
            'actual_status' => $statusCode,
            'success' => $statusCode === 400,
            'response' => $data,
            'has_validation_errors' => isset($data['messages']) && count($data['messages']) > 0
        ];
    }

    private function testLongText($client): array
    {
        $longText = str_repeat('This is a very long text. ', 200); // ~5000+ символов

        $response = $client->request('POST', $this->baseUrl . '/api/episodes/1/reviews', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode([
                'author' => 'Test User',
                'text' => $longText
            ]),
            'timeout' => 30,
        ]);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);
        $data = json_decode($content, true) ?? ['error' => 'Invalid JSON response: ' . $content];

        return [
            'description' => 'Long text validation',
            'request' => [
                'author' => 'Test User',
                'text' => 'Very long text (5000+ chars)'
            ],
            'expected_status' => 400,
            'actual_status' => $statusCode,
            'success' => $statusCode === 400,
            'response' => $data,
            'has_validation_errors' => isset($data['messages']) && count($data['messages']) > 0
        ];
    }

    private function testMissingAuthor($client): array
    {
        $response = $client->request('POST', $this->baseUrl . '/api/episodes/1/reviews', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode([
                'text' => 'This is a valid review text that is long enough for validation'
            ]),
            'timeout' => 30,
        ]);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);
        $data = json_decode($content, true) ?? ['error' => 'Invalid JSON response: ' . $content];

        return [
            'description' => 'Missing author field',
            'request' => [
                'text' => 'This is a valid review text...'
            ],
            'expected_status' => 400,
            'actual_status' => $statusCode,
            'success' => $statusCode === 400,
            'response' => $data,
            'has_validation_errors' => isset($data['messages']) && count($data['messages']) > 0
        ];
    }

    private function testMissingText($client): array
    {
        $response = $client->request('POST', $this->baseUrl . '/api/episodes/1/reviews', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode([
                'author' => 'Test User'
            ]),
            'timeout' => 30,
        ]);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);
        $data = json_decode($content, true) ?? ['error' => 'Invalid JSON response: ' . $content];

        return [
            'description' => 'Missing text field',
            'request' => [
                'author' => 'Test User'
            ],
            'expected_status' => 400,
            'actual_status' => $statusCode,
            'success' => $statusCode === 400,
            'response' => $data,
            'has_validation_errors' => isset($data['messages']) && count($data['messages']) > 0
        ];
    }

    private function testRatingCalculation($client): array
    {
        $response = $client->request('POST', $this->baseUrl . '/api/episodes/1/reviews', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode([
                'author' => 'Rating Test User',
                'text' => 'This episode was absolutely fantastic! Great character development and amazing plot twists. I loved every moment of it!'
            ]),
            'timeout' => 30,
        ]);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);
        $data = json_decode($content, true) ?? ['error' => 'Invalid JSON response: ' . $content];

        $ratingCalculated = isset($data['data']['rating']) && $data['data']['rating'] !== null;
        $ratingInRange = $ratingCalculated && $data['data']['rating'] >= 1 && $data['data']['rating'] <= 5;

        return [
            'description' => 'Automatic rating calculation',
            'request' => [
                'author' => 'Rating Test User',
                'text' => 'Positive review text...'
            ],
            'expected_status' => 201,
            'actual_status' => $statusCode,
            'success' => $statusCode === 201 && $ratingCalculated,
            'response' => $data,
            'rating_calculated' => $ratingCalculated,
            'rating_value' => $ratingCalculated ? $data['data']['rating'] : null,
            'rating_in_range' => $ratingInRange
        ];
    }

    private function generateSummary(array $results): array
    {
        $totalTests = count($results);
        $passedTests = 0;
        $failedTests = 0;

        foreach ($results as $test) {
            if (isset($test['success']) && $test['success']) {
                $passedTests++;
            } else {
                $failedTests++;
            }
        }

        return [
            'total_tests' => $totalTests,
            'passed_tests' => $passedTests,
            'failed_tests' => $failedTests,
            'success_rate' => $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 2) . '%' : '0%'
        ];
    }
}
