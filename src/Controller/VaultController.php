<?php

namespace App\Controller;

use App\Entity\VaultElement;
use App\Entity\VaultFile;
use App\Form\VaultElementType;
use App\Service\AuditLogger;
use App\Service\FileUploader;
use App\Service\VaultEncryptor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/vault')]
final class VaultController extends AbstractController
{
    // Liste des éléments du coffre de l'utilisateur connecté
    #[Route('', name: 'app_vault')]
    public function index(RequestStack $requestStack, VaultEncryptor $encryptor): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($requestStack->getSession()->get('panic_mode', false)) {
            $fakeElements = [
                ['title' => 'Liste de courses', 'type' => 'NOTE', 'isHeritage' => false, 'createdAt' => new \DateTime('-5 days')],
                ['title' => 'Idées cadeaux Noël', 'type' => 'NOTE', 'isHeritage' => false, 'createdAt' => new \DateTime('-12 days')],
                ['title' => 'Recette tiramisu', 'type' => 'DOCUMENT', 'isHeritage' => false, 'createdAt' => new \DateTime('-30 days')],
            ];

            return $this->render('vault/leurre.html.twig', [
                'fakeElements' => $fakeElements,
            ]);
        }

        // Déchiffrer le contenu pour l'affichage
        $elements = $user->getVaultElements();
        foreach ($elements as $el) {
            $el->setContent($encryptor->decrypt($el->getContent()));
        }

        return $this->render('vault/index.html.twig', [
            'vaultElements' => $elements,
        ]);
    }

    // Ajouter un nouvel élément au coffre
    #[Route('/new', name: 'app_vault_new')]
    public function new(Request $request, EntityManagerInterface $em, AuditLogger $auditLogger, 
    FileUploader $fileUploader, VaultEncryptor $encryptor): Response
    {
        $element = new VaultElement();
        $form = $this->createForm(VaultElementType::class, $element, [
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Associer l'élément à l'utilisateur connecté
            $element->setCreatedBy($this->getUser());
            // Date de création automatique (DateTime pour VaultElement)
            $element->setCreatedAt(new \DateTime());

            // Chiffrer le contenu avant stockage
            $element->setContent($encryptor->encrypt($element->getContent()));

            $em->persist($element);

            // Gérer l'upload de fichier(s)
            $uploadedFiles = $form->get('uploadedFile')->getData();
            if ($uploadedFiles) {
                foreach ($uploadedFiles as $uploadedFile) {
                    $newFilename = $fileUploader->upload($uploadedFile);

                    $vaultFile = new VaultFile();
                    $vaultFile->setFilename($newFilename);
                    $vaultFile->setOriginalName($uploadedFile->getClientOriginalName());
                    $vaultFile->setMimeType($uploadedFile->getClientMimeType());
                    // DateTimeImmutable pour VaultFile
                    $vaultFile->setUploadedAt(new \DateTimeImmutable());
                    $vaultFile->setVaultElement($element);

                    $em->persist($vaultFile);
                }
            }

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
    public function edit(VaultElement $element, Request $request, EntityManagerInterface $em, AuditLogger $auditLogger, 
        FileUploader $fileUploader, VaultEncryptor $encryptor): Response
    {
        // Vérifier que l'élément appartient à l'utilisateur connecté
        if ($element->getCreatedBy() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Déchiffrer le contenu pour l'affichage dans le formulaire
        $element->setContent($encryptor->decrypt($element->getContent()));
        
        $form = $this->createForm(VaultElementType::class, $element, [
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mise à jour de la date de modification (DateTime pour VaultElement)
            $element->setUpdatedAt(new \DateTime());

            // Gérer l'upload de nouveaux fichiers (ajout, pas remplacement)
            $uploadedFiles = $form->get('uploadedFile')->getData();
            if ($uploadedFiles) {
                foreach ($uploadedFiles as $uploadedFile) {
                    $newFilename = $fileUploader->upload($uploadedFile);

                    $vaultFile = new VaultFile();
                    $vaultFile->setFilename($newFilename);
                    $vaultFile->setOriginalName($uploadedFile->getClientOriginalName());
                    $vaultFile->setMimeType($uploadedFile->getClientMimeType());
                    // DateTimeImmutable pour VaultFile
                    $vaultFile->setUploadedAt(new \DateTimeImmutable());
                    $vaultFile->setVaultElement($element);

                    $em->persist($vaultFile);
                }
            }

            // Chiffrer le contenu avant stockage
            $element->setContent($encryptor->encrypt($element->getContent()));
            
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
    public function delete(VaultElement $element, Request $request, EntityManagerInterface $em, AuditLogger $auditLogger, FileUploader $fileUploader): Response
    {
        // Vérifier que l'élément appartient à l'utilisateur connecté
        if ($element->getCreatedBy() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Vérification du token CSRF pour la sécurité
        if ($this->isCsrfTokenValid('delete' . $element->getId(), $request->request->get('_token'))) {
            // Supprimer tous les fichiers associés du disque
            foreach ($element->getFiles() as $file) {
                $fileUploader->remove($file->getFilename());
            }

            $em->remove($element);
            $em->flush();
            $auditLogger->log($this->getUser(), 'DELETE');
            $this->addFlash('success', 'Élément supprimé.');
        }

        return $this->redirectToRoute('app_vault');
    }

    // Supprimer un fichier individuel d'un élément
    #[Route('/file/{id}/delete', name: 'app_vault_file_delete', methods: ['POST'])]
    public function deleteFile(\App\Entity\VaultFile $vaultFile, Request $request, EntityManagerInterface $em, FileUploader $fileUploader): Response
    {
        // Vérifier que le fichier appartient à l'utilisateur connecté
        if ($vaultFile->getVaultElement()->getCreatedBy() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Vérification du token CSRF
        if ($this->isCsrfTokenValid('delete_file' . $vaultFile->getId(), $request->request->get('_token'))) {
            // Supprimer le fichier du disque
            $fileUploader->remove($vaultFile->getFilename());

            // Supprimer l'entrée de la base
            $em->remove($vaultFile);
            $em->flush();

            $this->addFlash('success', 'Fichier supprimé.');
        }

        return $this->redirectToRoute('app_vault_edit', ['id' => $vaultFile->getVaultElement()->getId()]);
    }
}