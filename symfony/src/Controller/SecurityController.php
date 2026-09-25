<?php

namespace App\Controller;

use App\Document\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ODM\MongoDB\DocumentManager;

class SecurityController extends AbstractController
{
   #[Route(path: '/login', name: 'app_login')]
    public function login(
        Request $request,
        AuthenticationUtils $authenticationUtils, 
        DocumentManager $documentManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {

        $user = new User();
        $user->setEmail('test1@gmail.com'); 
        $user->setRoles(['ROLE_USER']);

        $plaintextPassword = 'test1';

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );
        $user->setPassword($hashedPassword);

        $documentManager->persist($user);
        $documentManager->flush();
        

        
        // If user is already logged in, redirect them
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}