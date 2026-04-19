<?php

namespace App\Service;

use App\Entity\AuditLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

// Service pour enregistrer les actions des utilisateurs dans les logs d'audit
class AuditLogger
{
    public function __construct(
        private EntityManagerInterface $em,
        private RequestStack $requestStack,
        private LoggerInterface $logger,
    ) {}

    // Enregistrer une action dans les logs
    public function log(User $user, string $action): void
    {
        try {
            $log = new AuditLog();
            $log->setCreatedBy($user);
            $log->setAction($action);
            $log->setCreatedAt(new \DateTime());

            // Récupérer l'adresse IP de l'utilisateur
            $request = $this->requestStack->getCurrentRequest();
            if ($request) {
                $log->setIpAddress($request->getClientIp());
            }

            $this->em->persist($log);
            $this->em->flush();
        } catch (\Exception $e) {
            // L'échec de l'audit ne doit pas bloquer l'action principale
            $this->logger->error('Échec de l\'écriture du log d\'audit : ' . $e->getMessage());
        }
    }
}