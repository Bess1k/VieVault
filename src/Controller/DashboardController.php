<?php

namespace App\Controller;

use App\Service\AuditLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(RequestStack $requestStack): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Vérifier si on est en mode panique
        $isPanicMode = $this->container->get('request_stack')->getSession()->get('panic_mode', false);

        if ($isPanicMode) {
            // Mode panique : afficher le dashboard leurre avec de fausses données
            return $this->render('dashboard/leurre.html.twig', [
                'user' => $user,
            ]);
        }

        // Mode normal : afficher le vrai dashboard
        return $this->render('dashboard/index.html.twig', [
            'user' => $user,
            'vaultElements' => $user->getVaultElements(),
            'beneficiaries' => $user->getBeneficiaries(),
        ]);
    }
    
    // Activer/Désactiver le mode vacances
    #[Route('/dashboard/vacation', name: 'app_vacation_toggle', methods: ['POST'])]
    public function toggleVacation(Request $request, EntityManagerInterface $em, AuditLogger $auditLogger): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Vérification du token CSRF
        if ($this->isCsrfTokenValid('vacation', $request->request->get('_token'))) {
            if ($user->isPaused()) {
                // Enregistrer l'action dans les logs avant le changement
                $auditLogger->log($user, 'VACATION_OFF');
                // Désactiver le mode vacances
                $user->setIsPaused(false);
                $user->setPauseUntil(null);
                $this->addFlash('success', 'Mode vacances désactivé.');
            } else {
                // Enregistrer l'action dans les logs avant le changement
                $auditLogger->log($user, 'VACATION_ON');
                // Activer le mode vacances (180 jours maximum)
                $user->setIsPaused(true);
                $user->setPauseUntil(new \DateTime('+180 days'));
                $this->addFlash('success', 'Mode vacances activé pour 180 jours.');
            }

            $em->flush();
        }

        return $this->redirectToRoute('app_dashboard');
    }

    // Configurer le mot de passe panique
    #[Route('/dashboard/panic-password', name: 'app_panic_password')]
    public function panicPassword(Request $request, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $panicPassword = $request->request->get('panic_password');
            $emergencyEmail = $request->request->get('emergency_email');

            if ($panicPassword && strlen($panicPassword) >= 6) {
                $user->setPanicPasswordHash(password_hash($panicPassword, PASSWORD_BCRYPT));
                $user->setEmergencyEmail($emergencyEmail);
                $em->flush();
                $this->addFlash('success', 'Mot de passe panique et contact de confiance configurés.');
            } else {
                $this->addFlash('danger', 'Le mot de passe panique doit contenir au moins 6 caractères.');
            }

            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('dashboard/panic_password.html.twig');
    }

   
}
