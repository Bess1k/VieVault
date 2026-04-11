<?php

namespace App\Controller;

use App\Entity\VaultElement;
use App\Form\VaultElementType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\AuditLogger;

#[IsGranted('ROLE_USER')]
#[Route('/vault')]
final class VaultController extends AbstractController
{
    // Liste des éléments du coffre de l'utilisateur connecté
    #[Route('', name: 'app_vault')]
    public function index(): Response
    {
        $user = $this->getUser();

        return $this->render('vault/index.html.twig', [
            'vaultElements' => $user->getVaultElements(),
        ]);
    }

    // Ajouter un nouvel élément au coffre
    #[Route('/new', name: 'app_vault_new')]
    public function new(Request $request, EntityManagerInterface $em, AuditLogger $auditLogger): Response
    {
        $element = new VaultElement();
        $form = $this->createForm(VaultElementType::class, $element, [
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Associer l'élément à l'utilisateur connecté
            $element->setCreatedBy($this->getUser());
            // Date de création automatique
            $element->setCreatedAt(new \DateTime());

            $em->persist($element);
            $em->flush();
            $auditLogger->log($this->getUser(), 'CREATE');

            $this->addFlash('success', 'Élément ajouté au coffre.');
            return $this->redirectToRoute('app_vault');
        }

        return $this->render('vault/form.html.twig', [
            'form' => $form,
            'element' => $element,
        ]);
    }

    // Modifier un élément existant
    #[Route('/{id}/edit', name: 'app_vault_edit')]
    public function edit(VaultElement $element, Request $request, EntityManagerInterface $em, AuditLogger $auditLogger): Response
    {
        // Vérifier que l'élément appartient à l'utilisateur connecté
        if ($element->getCreatedBy() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(VaultElementType::class, $element, [
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mise à jour de la date de modification
            $element->setUpdatedAt(new \DateTime());

            $em->flush();
            $auditLogger->log($this->getUser(), 'UPDATE');

            $this->addFlash('success', 'Élément modifié.');
            return $this->redirectToRoute('app_vault');
        }

        return $this->render('vault/form.html.twig', [
            'form' => $form,
            'element' => $element,
        ]);
    }

    // Supprimer un élément
    #[Route('/{id}/delete', name: 'app_vault_delete', methods: ['POST'])]
    public function delete(VaultElement $element, Request $request, EntityManagerInterface $em, AuditLogger $auditLogger): Response
    {
        // Vérifier que l'élément appartient à l'utilisateur connecté
        if ($element->getCreatedBy() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Vérification du token CSRF pour la sécurité
        if ($this->isCsrfTokenValid('delete' . $element->getId(), $request->request->get('_token'))) {
            $em->remove($element);
            $em->flush();
            $auditLogger->log($this->getUser(), 'DELETE');
            $this->addFlash('success', 'Élément supprimé.');
        }

        return $this->redirectToRoute('app_vault');
    }
}