<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Document\Crypto;
use App\Document\User;
use App\Form\CryptoType;
use Doctrine\ODM\MongoDB\DocumentManager;

#[Route('/crypto')]

class CryptoController extends AbstractController
{
    #[Route('/add', name: 'add_crypto')]
    #[IsGranted('ROLE_USER')]
    public function addCrypto(
        Request $request,
        DocumentManager $documentManager,
        #[Autowire('%app.crypto%')] array $cryptoChoices,
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('A user account is required to add cryptocurrency.');
        }

        $crypto = new Crypto();

        $form = $this->createForm(CryptoType::class, $crypto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $key = $form->get('crypto')->getData();
            $coin = $cryptoChoices[$key];
            $crypto->setCoin($coin['name'], $coin['cryptoCurrency']);

            $documentManager->persist($crypto);
            $documentManager->flush();

            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('crypto/add.html.twig', [
            'form' => $form->createView(),
        ], new Response(status: $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }
}