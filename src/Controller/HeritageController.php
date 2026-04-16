<?php

namespace App\Controller;

use App\Entity\Beneficiary;
use App\Service\FileUploader;
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
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile|null $file */
            $file = $request->files->get('justificatif');

            if ($file) {
                $filename = $fileUploader->upload($file);
                $beneficiary->setSubmittedDocPath($filename);
                $beneficiary->setValidationStatus('EN_ATTENTE');
                $em->flush();

                return $this->render('heritage/submitted.html.twig');
            }

            $this->addFlash('danger', 'Veuillez sélectionner un fichier.');
        }

        return $this->render('heritage/submit.html.twig', [
            'beneficiary' => $beneficiary,
        ]);
    }
}