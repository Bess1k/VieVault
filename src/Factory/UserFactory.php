<?php

namespace App\Factory;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * Factory pour créer des utilisateurs de test
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    // Mot de passe par défaut utilisé pour les tests
    public const DEFAULT_PASSWORD = 'P@ssw0rd';

    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher
    ) {}

    #[\Override]
    public static function class(): string
    {
        return User::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'email' => self::faker()->email(),
            'firstname' => self::faker()->firstName(),
            'lastname' => self::faker()->lastName(),
            'password' => $this->userPasswordHasher->hashPassword(new User(), self::DEFAULT_PASSWORD),
            'birthDate' => self::faker()->dateTime(),
            'birthPlace' => self::faker()->city(),
            'isPaused' => false,
            'isVerified' => true,
            'status' => 'ACTIVE',
            'roles' => [],
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }
}