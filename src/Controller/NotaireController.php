<?php

namespace App\Controller;

use App\Repository\BeneficiaryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\AuditLogger;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

// Espace réservé aux Notaires pour valider les demandes d'héritage
#[IsGranted('ROLE_NOTAIRE')]
#[Route('/notaire')]
final class NotaireController extends AbstractController
{
    // Liste des demandes d'héritage en attente
    #[Route('', name: 'app_notaire')]
    public function index(BeneficiaryRepository $repo): Response
    {
        // Récupérer tous les bénéficiaires ayant soumis un justificatif
        $demandes = $repo->findBy(
            ['validationStatus' => 'EN_ATTENTE'],
        );

        // Filtrer uniquement ceux qui ont un document soumis
        $demandesAvecDoc = array_filter($demandes, function ($b) {
            return $b->getSubmittedDocPath() !== null;
        });

        return $this->render('notaire/index.html.twig', [
            'demandes' => $demandesAvecDoc,
        ]);
    }

    // Approuver une demande d'héritage
    #[Route('/{id}/approve', name: 'app_notaire_approve', methods: ['POST'])]
    public function approve( \App\Entity\Beneficiary $beneficiary, Request $request, EntityManagerInterface $em, 
    AuditLogger $auditLogger, MailerInterface $mailer, UrlGeneratorInterface $urlGenerator): Response 
    {
        if ($this->isCsrfTokenValid('approve' . $beneficiary->getId(), $request->request->get('_token'))) {
            $beneficiary->setValidationStatus('APPROUVE');

            // Générer un token d'accès unique
            $token = bin2hex(random_bytes(32));
            $beneficiary->setAccessToken($token);
            $beneficiary->setTokenExpiresAt(new \DateTime('+30 days'));

            $em->flush();

            // Envoyer l'email avec le lien d'accès
            try {
                $accessUrl = $urlGenerator->generate(
                    'app_heritage_access',
                    ['token' => $token],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $accessEmail = (new Email())
                    ->from('noreply@vievault.fr')
                    ->to($beneficiary->getEmail())
                    ->subject('VieVault — Accès aux données léguées approuvé')
                    ->html(
                        '<h2>Bonjour ' . $beneficiary->getFirstname() . ',</h2>' .
                        '<p>Votre demande a été <strong>approuvée</strong> par le Notaire.</p>' .
                        '<p>Vous pouvez désormais accéder aux données qui vous ont été léguées par <strong>' . 
                        $beneficiary->getCreatedBy()->getFirstname() . ' ' . $beneficiary->getCreatedBy()->getLastname() . '</strong>.</p>' .
                        '<p><a href="' . $accessUrl . '" style="background:#198754;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;">Accéder aux données léguées</a></p>' .
                        '<p><em>Ce lien est valable 30 jours.</em></p>' .
                        '<p><strong>L\'équipe VieVault</strong></p>'
                    );
                $mailer->send($accessEmail);
            } catch (\Exception $e) {
                // Continue sans bloquer
            }

            $auditLogger->log($this->getUser(), 'HERITAGE_APPROVED');
            $this->addFlash('success', 'Demande approuvée. Email envoyé au bénéficiaire.');
        }

        return $this->redirectToRoute('app_notaire');
    }

    // Refuser une demande d'héritage
    #[Route('/{id}/reject', name: 'app_notaire_reject', methods: ['POST'])]
    public function reject(\App\Entity\Beneficiary $beneficiary, Request $request, EntityManagerInterface $em, 
    AuditLogger $auditLogger): Response 
    {
        // Vérification CSRF
        if ($this->isCsrfTokenValid('reject' . $beneficiary->getId(), $request->request->get('_token'))) {
            // Refuser la demande
            $beneficiary->setValidationStatus('REFUSE');

            $em->flush();

            // Enregistrer dans les logs
            $auditLogger->log($this->getUser(), 'HERITAGE_REJECTED');

            $this->addFlash('success', 'Demande refusée.');
        }

        return $this->redirectToRoute('app_notaire');
    }
}