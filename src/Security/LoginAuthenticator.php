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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

// Authenticator personnalisé pour VieVault
// Gère deux mots de passe : le réel (accès normal) et le panique (mode leurre)
class LoginAuthenticator extends AbstractLoginFormAuthenticator
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepo,
        private AuditLogger $auditLogger,
        private MailerInterface $mailer,
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
            // Vérification personnalisée : accepte le mot de passe normal OU panique
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
                    // Enregistrer dans les logs d'audit
                    $this->auditLogger->log($user, 'PANIC_LOGIN');

                    // Envoyer un SOS au contact de confiance
                    if ($user->getEmergencyEmail()) {
                        try {
                            $sosEmail = (new Email())
                                ->from('noreply@vievault.fr')
                                ->to($user->getEmergencyEmail())
                                ->subject('ALERTE VieVault — SOS')
                                ->html(
                                    '<h2>Alerte de sécurité VieVault</h2>' .
                                    '<p>L\'utilisateur <strong>' . $user->getFirstname() . ' ' . $user->getLastname() . '</strong> ' .
                                    'a déclenché une alerte de sécurité sur son compte VieVault.</p>' .
                                    '<p>Cette alerte signifie que l\'utilisateur pourrait être en danger.</p>' .
                                    '<p><em>Message automatique — ne pas répondre.</em></p>'
                                );
                            $this->mailer->send($sosEmail);
                        } catch (\Exception $e) {
                            // En cas d'erreur d'envoi, on continue sans bloquer la connexion
                        }
                    }

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