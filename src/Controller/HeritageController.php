<?php

namespace App\Controller;

use App\Entity\Beneficiary;
use App\Service\FileUploader;
use App\Service\VaultEncryptor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Espace public pour les bénéficiaires qui doivent soumettre un justificatif
#[Route('/heritage')]
final class HeritageController extends AbstractController
{
    // Page où le bénéficiaire peut soumettre le justificatif de décès
    #[Route('/submit/{id}', name: 'app_heritage_submit')]
    public function submit(Beneficiary $beneficiary, Request $request, EntityManagerInterface $em, FileUploader $fileUploader): Response
    {
        // Vérifier que le protocole d'héritage est activé
        if ($beneficiary->getCreatedBy()->getStatus() !== 'INACTIVE') {
            return $this->render('heritage/not_available.html.twig');
        }

        if ($request->isMethod('POST')) {
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile|null $deathCert */
            $deathCert = $request->files->get('death_cert');
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile|null $idDoc */
            $idDoc = $request->files->get('id_doc');

            if ($deathCert && $idDoc) {
                // Upload acte de décès
                $deathCertFilename = $fileUploader->upload($deathCert);
                $beneficiary->setSubmittedDocPath($deathCertFilename);

                // Upload pièce d'identité
                $idDocFilename = $fileUploader->upload($idDoc);
                $beneficiary->setIdDocPath($idDocFilename);

                $beneficiary->setValidationStatus('EN_ATTENTE');
                $em->flush();

                return $this->render('heritage/submitted.html.twig');
            }

            $this->addFlash('danger', 'Veuillez sélectionner les deux documents.');
        }

        return $this->render('heritage/submit.html.twig', [
            'beneficiary' => $beneficiary,
        ]);
    }

    // Accès aux données léguées via token (envoyé par email après validation du Notaire)
    #[Route('/access/{token}', name: 'app_heritage_access')]
    public function access(string $token, \App\Repository\BeneficiaryRepository $repo, VaultEncryptor $encryptor): Response
    {
        $beneficiary = $repo->findOneBy(['accessToken' => $token]);

        $elements = [];
        $error = null;

        if (!$beneficiary) {
            $error = 'invalid';
        } elseif ($beneficiary->getTokenExpiresAt() < new \DateTime()) {
            $error = 'expired';
        } elseif ($beneficiary->getValidationStatus() !== 'APPROUVE') {
            $error = 'not_approved';
        } else {
            // Récupérer et déchiffrer les éléments légués
            foreach ($beneficiary->getCreatedBy()->getVaultElements() as $el) {
                if ($el->isHeritage() && $el->getBeneficiary() === $beneficiary) {
                    // Déchiffrer le contenu pour l'affichage
                    $el->setContent($encryptor->decrypt($el->getContent()));
                    $elements[] = $el;
                }
            }
        }

        return $this->render('heritage/access.html.twig', [
            'beneficiary' => $beneficiary,
            'elements' => $elements,
            'error' => $error,
        ]);
    }
}