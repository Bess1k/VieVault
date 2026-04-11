<?php

namespace App\Controller;

use App\Repository\AuditLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class AuditLogController extends AbstractController
{
    // Afficher les logs d'audit de l'utilisateur connecté
    #[Route('/audit-logs', name: 'app_audit_logs')]
    public function index(AuditLogRepository $repo): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Récupérer les logs de l'utilisateur, triés du plus récent au plus ancien
        $logs = $repo->findBy(
            ['createdBy' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('audit_log/index.html.twig', [
            'logs' => $logs,
        ]);
    }
}