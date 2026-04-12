<?php

namespace App\Security;

use App\Repository\UserRepository;
use App\Service\AuditLogger;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// Authenticator personnalisé pour VieVault
// Gère deux mots de passe : le réel (accès normal) et le panique (mode leurre)
class LoginAuthenticator extends AbstractLoginFormAuthenticator
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepo,
        private AuditLogger $auditLogger,
    ) {}

    // URL de la page de connexion
    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate('app_login');
    }

    // Vérification des identifiants : mot de passe normal OU panique
    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('_username', '');
        $password = $request->request->get('_password', '');
        $csrfToken = $request->request->get('_csrf_token', '');

        return new Passport(
            new UserBadge($email),
            // Vérification personnalisée des credentials
            new CustomCredentials(function ($password, $user) use ($request) {
                // 1. Vérifier le mot de passe normal
                if ($this->passwordHasher->isPasswordValid($user, $password)) {
                    // Connexion normale — supprimer le mode panique si existant
                    $request->getSession()->remove('panic_mode');
                    return true;
                }

                // 2. Vérifier le mot de passe panique
                if ($user->getPanicPasswordHash() && password_verify($password, $user->getPanicPasswordHash())) {
                    // Mode panique activé — marquer la session
                    $request->getSession()->set('panic_mode', true);
                    // Enregistrer l'alerte dans les logs
                    $this->auditLogger->log($user, 'PANIC_LOGIN');
                    return true;
                }

                // Aucun mot de passe valide
                return false;
            }, $password),
            [new CsrfTokenBadge('authenticate', $csrfToken)]
        );
    }

    // Redirection après connexion réussie
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate('app_dashboard'));
    }
}