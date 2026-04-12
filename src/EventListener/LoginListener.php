<?php

namespace App\EventListener;

use App\Service\AuditLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

// Écouter chaque connexion réussie pour mettre à jour la date de dernière connexion
#[AsEventListener(event: LoginSuccessEvent::class)]
class LoginListener
{
    public function __construct(
        private EntityManagerInterface $em,
        private AuditLogger $auditLogger,
    ) {}

    public function __invoke(LoginSuccessEvent $event): void
    {
        /** @var \App\Entity\User $user */
        $user = $event->getUser();

        // Mettre à jour la date de dernière connexion
        $user->setLastLoginAt(new \DateTime());

        // Enregistrer la connexion dans les logs d'audit
        $this->auditLogger->log($user, 'LOGIN');

        $this->em->flush();
    }
}