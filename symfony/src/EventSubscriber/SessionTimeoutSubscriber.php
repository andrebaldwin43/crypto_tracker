<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SessionTimeoutSubscriber implements EventSubscriberInterface
{
    private const MAX_IDLE_TIME = 1800; // 30 minutes in seconds

    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->hasSession()) {
            return;
        }

        $session = $request->getSession();
        $now = time();

        // Check if the user has been idle longer than MAX_IDLE_TIME
        if ($session->has('last_activity') && ($now - $session->get('last_activity')) > self::MAX_IDLE_TIME) {
            $session->invalidate();
            $this->tokenStorage->setToken(null);

            // Redirect to your login route
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_login')));
            return;
        }

        // Update timestamp on active requests
        $session->set('last_activity', $now);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }
}
