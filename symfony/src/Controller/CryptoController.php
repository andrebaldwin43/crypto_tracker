<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Document\Crypto;
use App\Form\CryptoType;
use Doctrine\ODM\MongoDB\DocumentManager;

#[Route('/crypto')]
class CryptoController extends AbstractController
{
    #[Route('/add', name: 'add_crypto')]
    public function addCrypto(
        Request $request,
        DocumentManager $documentManager,
        #[Autowire('%app.crypto%')] array $cryptoChoices,
    ): Response {
        $crypto = new Crypto();
        $form = $this->createForm(CryptoType::class, $crypto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $key = $form->get('crypto')->getData();
            $coin = $cryptoChoices[$key];
            $crypto->setCoin($coin['name'], $coin['crypto']);

            $documentManager->persist($crypto);
            $documentManager->flush();

            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('crypto/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}