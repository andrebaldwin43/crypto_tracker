<?php

namespace App\Controller;

use App\Document\Crypto;
use App\Repository\CryptoRepository;
use App\Service\CyrptoDataParserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        CryptoRepository $cryptoRepository,
        CyrptoDataParserService $cryptoDataParserService
    ): Response
    {
        $user = $this->getUser();
        $cryptoData = $cryptoRepository->findAllByUser($user)->toArray();

        return $this->render('dashboard.html.twig', [
            'user' => $user,
            'cryptoData' => $cryptoDataParserService->parse($cryptoData),
        ]);
    }
}
