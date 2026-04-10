<?php

namespace App\Controller;

use App\Entity\Beneficiary;
use App\Form\BeneficiaryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/beneficiary')]
final class BeneficiaryController extends AbstractController
{
    // Liste des bénéficiaires de l'utilisateur connecté
    #[Route('', name: 'app_beneficiary')]
    public function index(): Response
    {
        $user = $this->getUser();

        return $this->render('beneficiary/index.html.twig', [
            'beneficiaries' => $user->getBeneficiaries(),
        ]);
    }

    // Ajouter un nouveau bénéficiaire
    #[Route('/new', name: 'app_beneficiary_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $beneficiary = new Beneficiary();
        $form = $this->createForm(BeneficiaryType::class, $beneficiary);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Associer le bénéficiaire à l'utilisateur connecté
            $beneficiary->setCreatedBy($this->getUser());
            // Statut par défaut : en attente de validation
            $beneficiary->setValidationStatus('EN_ATTENTE');

            $em->persist($beneficiary);
            $em->flush();

            $this->addFlash('success', 'Bénéficiaire ajouté.');
            return $this->redirectToRoute('app_beneficiary');
        }

        return $this->render('beneficiary/form.html.twig', [
            'form' => $form,
            'beneficiary' => $beneficiary,
        ]);
    }

    // Modifier un bénéficiaire existant
    #[Route('/{id}/edit', name: 'app_beneficiary_edit')]
    public function edit(Beneficiary $beneficiary, Request $request, EntityManagerInterface $em): Response
    {
        // Vérifier que le bénéficiaire appartient à l'utilisateur connecté
        if ($beneficiary->getCreatedBy() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(BeneficiaryType::class, $beneficiary);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Bénéficiaire modifié.');
            return $this->redirectToRoute('app_beneficiary');
        }

        return $this->render('beneficiary/edit.html.twig', [
            'form' => $form,
            'beneficiary' => $beneficiary,
        ]);
    }

    // Supprimer un bénéficiaire
    #[Route('/{id}/delete', name: 'app_beneficiary_delete', methods: ['POST'])]
    public function delete(Beneficiary $beneficiary, Request $request, EntityManagerInterface $em): Response
    {
        // Vérifier que le bénéficiaire appartient à l'utilisateur connecté
        if ($beneficiary->getCreatedBy() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Vérification du token CSRF
        if ($this->isCsrfTokenValid('delete' . $beneficiary->getId(), $request->request->get('_token'))) {
            $em->remove($beneficiary);
            $em->flush();
            $this->addFlash('success', 'Bénéficiaire supprimé.');
        }

        return $this->redirectToRoute('app_beneficiary');
    }
}