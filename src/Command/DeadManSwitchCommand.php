<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\AuditLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

// Commande exécutée quotidiennement par un Cron Job
// Vérifie l'inactivité des utilisateurs (90 jours sans connexion)
#[AsCommand(
    name: 'app:dead-man-switch',
    description: 'Vérifie l\'inactivité des utilisateurs et déclenche le protocole d\'héritage',
)]
class DeadManSwitchCommand extends Command
{
    public function __construct(
        private UserRepository $userRepo,
        private EntityManagerInterface $em,
        private AuditLogger $auditLogger,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Date limite : 90 jours avant aujourd'hui
        $limitDate = new \DateTime('-90 days');

        // Récupérer tous les utilisateurs actifs
        $users = $this->userRepo->findBy(['status' => 'ACTIVE']);

        $triggeredCount = 0;

       foreach ($users as $user) {
            // Ignorer les utilisateurs en mode vacances
            if ($user->isPaused()) {
                $io->note('Utilisateur ' . $user->getEmail() . ' en mode vacances — ignoré.');
                continue;
            }

            // Vérifier si la dernière connexion dépasse 90 jours
            $lastLogin = $user->getLastLoginAt();

            // Ignorer les utilisateurs qui ne se sont jamais connectés
            if ($lastLogin === null) {
                continue;
            }

            // Ignorer les utilisateurs sans éléments d'héritage
            $heritageElements = $user->getVaultElements()->filter(function ($el) {
                return $el->isHeritage();
            });

            if ($heritageElements->isEmpty()) {
                $io->note('Utilisateur ' . $user->getEmail() . ' — aucun élément d\'héritage, ignoré.');
                continue;
            }

            if ($lastLogin < $limitDate) {
            // Déclencher le protocole d'héritage
            $user->setStatus('INACTIVE');
            $this->auditLogger->log($user, 'HERITAGE_TRIGGER');

            // Envoyer un email à chaque bénéficiaire pour les informer
            foreach ($user->getBeneficiaries() as $beneficiary) {
                // Ne notifier que les bénéficiaires avec au moins un élément d'héritage
                $hasHeritage = false;
                foreach ($user->getVaultElements() as $el) {
                    if ($el->isHeritage() && $el->getBeneficiary() === $beneficiary) {
                        $hasHeritage = true;
                        break;
                    }
                }

                if (!$hasHeritage) {
                    continue;
                }

                try {
                    // URL pour que le bénéficiaire soumette le justificatif
                    $submitUrl = $this->urlGenerator->generate(
                        'app_heritage_submit',
                        ['id' => $beneficiary->getId()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );

                    $heritageEmail = (new Email())
                        ->from('noreply@vievault.fr')
                        ->to($beneficiary->getEmail())
                        ->subject('VieVault — Activation du protocole d\'héritage')
                        ->html(
                            '<h2>Bonjour ' . $beneficiary->getFirstname() . ',</h2>' .
                            '<p>Vous avez été désigné(e) comme bénéficiaire par <strong>' . $user->getFirstname() . ' ' . $user->getLastname() . '</strong> ' .
                            'sur la plateforme VieVault (coffre-fort numérique et gestion d\'héritage numérique).</p>' .
                            '<p>Suite à une inactivité prolongée (plus de 90 jours) du titulaire, le protocole d\'héritage a été activé automatiquement.</p>' .
                            '<h3>Que faire ?</h3>' .
                            '<ol>' .
                            '<li>Cliquez sur le lien ci-dessous pour accéder à votre espace personnel.</li>' .
                            '<li>Téléversez un justificatif de décès (acte de décès, certificat médical).</li>' .
                            '<li>Un Notaire validera votre demande et votre identité.</li>' .
                            '<li>Après validation, vous recevrez un second email avec un lien d\'accès aux données léguées.</li>' .
                            '</ol>' .
                            '<p><a href="' . $submitUrl . '" style="background:#0d6efd;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;">Accéder à mon espace</a></p>' .
                            '<p><em>Cet email est automatique. Si vous pensez qu\'il s\'agit d\'une erreur, vous pouvez l\'ignorer.</em></p>' .
                            '<p><strong>L\'équipe VieVault</strong></p>'
                        );

                    $this->mailer->send($heritageEmail);
                } catch (\Exception $e) {
                    $io->warning('Email non envoyé à ' . $beneficiary->getEmail() . ' : ' . $e->getMessage());
                }
            }

            $io->warning('Utilisateur ' . $user->getEmail() . ' inactif depuis plus de 90 jours — protocole déclenché.');
            $triggeredCount++;
        }
        }

        $this->em->flush();

        if ($triggeredCount === 0) {
            $io->success('Aucun utilisateur inactif détecté.');
        } else {
            $io->success($triggeredCount . ' utilisateur(s) marqué(s) comme inactif(s).');
        }

        return Command::SUCCESS;
    }
}