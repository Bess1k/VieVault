<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Service qui gère l'upload, la compression et la suppression de fichiers
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

        // Générer un nom unique
        $strNewFilename = $strBaseFileName . uniqid() . '.' . $file->guessExtension();

        // Déplacer le fichier dans le répertoire d'upload
        $file->move($this->uploadDirectory, $strNewFilename);

        // Compresser si c'est une image (JPEG ou PNG)
        $fullPath = $this->uploadDirectory . '/' . $strNewFilename;
        $this->compressImage($fullPath, $file->getClientMimeType());

        return $strNewFilename;
    }

    /**
     * Compresser une image avec GD (qualité réduite pour économiser l'espace)
     */
    private function compressImage(string $filePath, string $mimeType): void
    {
        // Ne compresser que les images JPEG et PNG
        if ($mimeType === 'image/jpeg' || $mimeType === 'image/jpg') {
            $image = imagecreatefromjpeg($filePath);
            if ($image) {
                // Qualité 75% (bon compromis taille/qualité)
                imagejpeg($image, $filePath, 75);
                imagedestroy($image);
            }
        } elseif ($mimeType === 'image/png') {
            $image = imagecreatefrompng($filePath);
            if ($image) {
                // Compression PNG niveau 6 (0=aucune, 9=max)
                imagepng($image, $filePath, 6);
                imagedestroy($image);
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