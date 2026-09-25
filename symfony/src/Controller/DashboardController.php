<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        return new Response('<html><body>Welcome to your secure MongoDB-backed dashboard, ' . $this->getUser()->getUserIdentifier() . '!</body></html>');
    }
}
