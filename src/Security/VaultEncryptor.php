<?php

namespace App\Service;

/**
 * Service de chiffrement/déchiffrement du contenu du coffre
 * Utilise AES-256-CBC pour protéger les données
 */
class VaultEncryptor
{
    private string $key;
    private string $method = 'aes-256-cbc';

    public function __construct(string $appSecret)
    {
        // Dériver une clé de chiffrement à partir du secret de l'application
        $this->key = hash('sha256', $appSecret, true);
    }

    /**
     * Chiffrer le contenu avant stockage en base
     */
    public function encrypt(string $data): string
    {
        // Générer un vecteur d'initialisation aléatoire
        $iv = openssl_random_pseudo_bytes(16);
        // Chiffrer avec AES-256-CBC
        $encrypted = openssl_encrypt($data, $this->method, $this->key, 0, $iv);

        // Stocker le IV avec les données chiffrées (séparés par ::)
        return base64_encode($iv . '::' . $encrypted);
    }

    /**
     * Déchiffrer le contenu pour l'affichage
     */
    public function decrypt(string $data): string
    {
        $decoded = base64_decode($data);
        $parts = explode('::', $decoded, 2);

        // Si le format ne correspond pas, retourner tel quel (données non chiffrées)
        if (count($parts) !== 2) {
            return $data;
        }

        $iv = $parts[0];
        $encrypted = $parts[1];

        $decrypted = openssl_decrypt($encrypted, $this->method, $this->key, 0, $iv);

        return $decrypted ?: $data;
    }
}