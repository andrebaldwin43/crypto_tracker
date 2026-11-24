<?php

namespace App\Controller;

use App\Document\Crypto;
use App\Repository\CryptoRepository;
use App\Service\Crypto\CryptoDataParserService;
use App\Service\Crypto\CryptoChartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Psr\Log\LoggerInterface;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function index(
        CryptoRepository $cryptoRepository,
        CryptoDataParserService $cryptoDataParserService,
        LoggerInterface $logger
    ): Response
    {
        $user       = $this->getUser();
        $cryptoData = $cryptoRepository->findAllByUser($user)->toArray();
        $parsedData = [];
        $error      = null;

        try {
            $parsedData = $cryptoDataParserService->parse($cryptoData);
        } catch (HttpExceptionInterface $e) {
            $statusCode = $e->getResponse()->getStatusCode();

            [$error, $logLevel, $logMessage, $logContext] = match (true) {
                $statusCode === 401 || $statusCode === 403 => [
                    'Unable to authenticate with the crypto service. Please contact support.',
                    'error',
                    'API authentication error',
                    ['status' => $statusCode],
                ],
                $statusCode === 429 => [
                    'The crypto service is currently busy. Please try again in a few minutes.',
                    'warning',
                    'API rate limited',
                    [],
                ],
                $statusCode >= 500 => [
                    'The crypto service is temporarily unavailable. Please try again later.',
                    'error',
                    'API server error',
                    ['status' => $statusCode],
                ],
                default => [
                    'Failed to fetch crypto data. Please try again.',
                    'error',
                    'API HTTP error',
                    ['status' => $statusCode],
                ],
            };
            $logger->log($logLevel, $logMessage, $logContext);
        } catch (TransportExceptionInterface $e) {
            $error = 'Network error connecting to crypto API. Please check your connection and try again.';
            $logger->error('API transport error: ' . $e->getMessage());
        } catch (\Throwable $e) {
            $error = 'An unexpected error occurred while fetching crypto data.';
            $logger->error('Unexpected error in dashboard: ' . $e->getMessage());
        }

        return $this->render('dashboard.html.twig', [
            'user'       => $user,
            'cryptoData' => $parsedData,
            'error'      => $error,
        ]);
    }

    #[Route('/dashboard/chart', name: 'app_dashboard_chart', methods: ['GET'])]
    public function chart(Request $request, CryptoChartService $chartService, LoggerInterface $logger): JsonResponse
    {
        try {
            $points = $chartService->getPriceHistory($request->query->getInt('id'), $request->query->get('range', '24h'));
            return $this->json(['points' => $points]);

        } catch (\InvalidArgumentException $exception) {
            return $this->json(['error' => 'Invalid cryptocurrency or chart range.'], Response::HTTP_BAD_REQUEST);
        } catch (HttpExceptionInterface $exception) {
            $statusCode = $exception->getResponse()->getStatusCode();
            $message = match ($statusCode) {
                400 => 'CoinMarketCap rejected this historical request. Your plan may limit the available date range.',
                401, 403 => 'CoinMarketCap denied access to historical data. Check your API key and plan.',
                429 => 'CoinMarketCap rate limit reached. Please try again later.',
                default => 'CoinMarketCap could not provide price history. Please try again later.',
            };
            $logger->warning('Chart history request rejected', ['status' => $statusCode]);
            return $this->json(['error' => $message], Response::HTTP_SERVICE_UNAVAILABLE);
        } catch (\Throwable $exception) {
            $logger->error('Unable to fetch chart data', ['exception' => $exception]);
            return $this->json(['error' => 'Unable to load price history. Check your connection and CoinMarketCap plan.'], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
}
