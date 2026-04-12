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

            if ($lastLogin === null || $lastLogin < $limitDate) {
                // Déclencher le protocole d'héritage
                $user->setStatus('INACTIVE');
                $this->auditLogger->log($user, 'HERITAGE_TRIGGER');

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