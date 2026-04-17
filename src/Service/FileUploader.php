<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Service qui gère l'upload, la compression et la suppression de fichiers
 * Utilisé pour les documents et images du coffre-fort
 */
class FileUploader
{
    public function __construct(
        private string $uploadDirectory,
        private Filesystem $fileSystem,
    ) {}

    /**
     * Upload un fichier : génère un nom unique, compresse si c'est une image
     */
    public function upload(UploadedFile $file): string
    {
        // Récupérer le nom du fichier sans l'extension
        $strBaseFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        // Générer un nom unique : nom original + identifiant unique + extension
        $strNewFilename = $strBaseFileName . uniqid() . '.' . $file->guessExtension();

        // Déplacer le fichier dans le répertoire d'upload
        $file->move($this->uploadDirectory, $strNewFilename);

        // Compresser si c'est une image (JPEG ou PNG)
        $fullPath = $this->uploadDirectory . '/' . $strNewFilename;
        $this->compressImage($fullPath, $file->getClientMimeType());

        return $strNewFilename;
    }

    /**
     * Compresser une image avec la bibliothèque GD de PHP
     * Réduit la qualité pour économiser l'espace disque
     */
    private function compressImage(string $filePath, string $mimeType): void
    {
        // Compresser uniquement les images JPEG et PNG
        if ($mimeType === 'image/jpeg' || $mimeType === 'image/jpg') {
            $image = imagecreatefromjpeg($filePath);
            if ($image) {
                // Qualité 75% (bon compromis taille/qualité)
                imagejpeg($image, $filePath, 75);
            }
        } elseif ($mimeType === 'image/png') {
            $image = imagecreatefrompng($filePath);
            if ($image) {
                // Compression PNG niveau 6 (0=aucune, 9=maximum)
                imagepng($image, $filePath, 6);
            }
        }
    }

    /**
     * Supprimer un fichier du disque
     */
    public function remove(string $filename): void
    {
        $this->fileSystem->remove($this->uploadDirectory . '/' . $filename);
    }
}