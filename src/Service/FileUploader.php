<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Service qui gère l'upload et la suppression de fichiers
 * Utilisé pour les documents du coffre-fort
 */
class FileUploader
{
    public function __construct(
        private string $uploadDirectory,
        private Filesystem $fileSystem,
    ) {}

    /**
     * Upload un fichier : génère un nom unique et le déplace dans le répertoire configuré
     */
    public function upload(UploadedFile $file): string
    {
        // Récupérer le nom du fichier sans l'extension
        $strBaseFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        // Générer un nom unique : nom original + uniqid + extension
        $strNewFilename = $strBaseFileName . uniqid() . '.' . $file->guessExtension();

        // Déplacer le fichier dans le répertoire /public/uploads/vault
        $file->move($this->uploadDirectory, $strNewFilename);

        return $strNewFilename;
    }

    /**
     * Supprimer un fichier du disque
     */
    public function remove(string $filename): void
    {
        $this->fileSystem->remove($this->uploadDirectory . '/' . $filename);
    }
}