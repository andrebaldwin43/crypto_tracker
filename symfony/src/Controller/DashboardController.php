<?php

namespace App\Controller;

use App\Document\Crypto;
use App\Repository\CryptoRepository;
use App\Service\Crypto\CryptoDataParserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Psr\Log\LoggerInterface;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
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

            if ($statusCode === 401 || $statusCode === 403) {
                $error = 'Unable to authenticate with the crypto service. Please contact support.';
                $logger->error('API authentication error', ['status' => $statusCode]);
            } elseif ($statusCode === 429) {
                $error = 'The crypto service is currently busy. Please try again in a few minutes.';
                $logger->warning('API rate limited');
            } elseif ($statusCode >= 500) {
                $error = 'The crypto service is temporarily unavailable. Please try again later.';
                $logger->error('API server error', ['status' => $statusCode]);
            } else {
                $error = 'Failed to fetch crypto data. Please try again.';
                $logger->error('API HTTP error', ['status' => $statusCode]);
            }
        } catch (TransportExceptionInterface $e) {
            $error = 'Network error connecting to crypto API. Please check your connection and try again.';
            $logger->error('API transport error: ' . $e->getMessage());
        } catch (\Exception $e) {
            $error = 'An unexpected error occurred while fetching crypto data.';
            $logger->error('Unexpected error in dashboard: ' . $e->getMessage());
        }

        return $this->render('dashboard.html.twig', [
            'user' => $user,
            'cryptoData' => $parsedData,
            'error' => $error,
        ]);
    }
}
