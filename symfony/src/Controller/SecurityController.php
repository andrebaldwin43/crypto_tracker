<?php

namespace App\Controller;

use App\Document\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Security\Core\User\UserInterface;

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

    #[Route(path: '/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, DocumentManager $documentManager): Response
    {
        $user = new User();
        $registrationForm = $this->createForm(UserType::class, $user);
        $registrationForm->handleRequest($request);

        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            $plainPassword = $registrationForm->get('password')->getData();
            
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $documentManager->persist($user);
            $documentManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $registrationForm,
        ]);
    }

    #[Route(path: '/account', name: 'app_account')]
    #[IsGranted('ROLE_USER')]
    public function account(Request $request, UserPasswordHasherInterface $passwordHasher, DocumentManager $documentManager)
    {
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            throw $this->createAccessDeniedException();
        }

        $changePasswordForm = $this->createForm(UserType::class, $user);
        $changePasswordForm->handleRequest($request);

        return $this->render('account/account.html.twig', [
            'user' => $user,
            'changePasswordForm' => $changePasswordForm,
        ]);
    }

    #[Route(path: '/account/change-password', name: 'app_change_password')]
    #[IsGranted('ROLE_USER')]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, DocumentManager $documentManager)
    {
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            throw $this->createAccessDeniedException();
        }
        
        $changePasswordForm = $this->createForm(UserType::class, $user);
        $changePasswordForm->handleRequest($request);

        if ($changePasswordForm->isSubmitted() && $changePasswordForm->isValid()) {
            $plainPassword = $changePasswordForm->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $documentManager->persist($user);
            $documentManager->flush();
            
            $this->addFlash('success', 'Password changed successfully.');

            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('account/change_password.html.twig', [
            'changePasswordForm' => $changePasswordForm,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}