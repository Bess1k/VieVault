<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

#[IsGranted('ROLE_USER')]
final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        return $this->render('dashboard/index.html.twig', [
            'user' => $user,
            'vaultElements' => $user->getVaultElements(),
            'beneficiaries' => $user->getBeneficiaries(),
        ]);
    }
    
    // Activer/Désactiver le mode vacances
    #[Route('/dashboard/vacation', name: 'app_vacation_toggle', methods: ['POST'])]
    public function toggleVacation(Request $request, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
    
        // Vérification du token CSRF
        if ($this->isCsrfTokenValid('vacation', $request->request->get('_token'))) {
            if ($user->isPaused()) {
                // Désactiver le mode vacances
                $user->setIsPaused(false);
                $user->setPauseUntil(null);
                $this->addFlash('success', 'Mode vacances désactivé.');
            } else {
                // Activer le mode vacances (180 jours maximum)
                $user->setIsPaused(true);
                $user->setPauseUntil(new \DateTime('+180 days'));
                $this->addFlash('success', 'Mode vacances activé pour 180 jours.');
            }
    
            $em->flush();
        }
    
        return $this->redirectToRoute('app_dashboard');
    }
}
