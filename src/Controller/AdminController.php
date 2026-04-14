<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Espace d'administration — gestion des rôles utilisateurs
#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
final class AdminController extends AbstractController
{
    // Liste de tous les utilisateurs
    #[Route('', name: 'app_admin')]
    public function index(UserRepository $repo): Response
    {
        $users = $repo->findAll();

        return $this->render('admin/index.html.twig', [
            'users' => $users,
        ]);
    }

    // Attribuer le rôle Notaire à un utilisateur
    #[Route('/{id}/set-notaire', name: 'app_admin_set_notaire', methods: ['POST'])]
    public function setNotaire(\App\Entity\User $user, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('role' . $user->getId(), $request->request->get('_token'))) {
            $roles = $user->getRoles();

            if (in_array('ROLE_NOTAIRE', $roles)) {
                // Retirer le rôle Notaire
                $roles = array_filter($roles, fn($r) => $r !== 'ROLE_NOTAIRE' && $r !== 'ROLE_USER');
                $user->setRoles(array_values($roles));
                $this->addFlash('success', 'Rôle Notaire retiré pour ' . $user->getEmail());
            } else {
                // Ajouter le rôle Notaire
                $roles[] = 'ROLE_NOTAIRE';
                $user->setRoles($roles);
                $this->addFlash('success', 'Rôle Notaire attribué à ' . $user->getEmail());
            }

            $em->flush();
        }

        return $this->redirectToRoute('app_admin');
    }
}