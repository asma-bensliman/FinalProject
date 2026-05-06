<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Repository\SportMatchRepository;
use App\Repository\TournamentRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:player:stats',
    description: 'Affiche les victoires et défaites d\'un joueur',
)]
class PlayerStatsCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private SportMatchRepository $sportMatchRepository,
        private TournamentRepository $tournamentRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('userId', InputArgument::REQUIRED, 'ID du joueur')
            ->addArgument('tournamentId', InputArgument::OPTIONAL, 'ID du tournoi (optionnel)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userId = $input->getArgument('userId');
        $tournamentId = $input->getArgument('tournamentId');

        $user = $this->userRepository->find($userId);

        if (!$user) {
            $output->writeln('<error>Joueur non trouvé</error>');
            return Command::FAILURE;
        }

        $output->writeln(sprintf('<info>Stats de %s %s :</info>', $user->getFirstName(), $user->getLastName()));

        // Récupérer tous les matchs terminés du joueur
        $allMatches = $this->sportMatchRepository->findBy(['status' => 'terminé']);

        if ($tournamentId) {
            $tournament = $this->tournamentRepository->find($tournamentId);
            if (!$tournament) {
                $output->writeln('<error>Tournoi non trouvé</error>');
                return Command::FAILURE;
            }
            $allMatches = $this->sportMatchRepository->findBy([
                'status' => 'terminé',
                'tournament' => $tournament
            ]);
            $output->writeln(sprintf('Tournoi : %s', $tournament->getTournamentName()));
        }

        $victories = 0;
        $defeats = 0;

        foreach ($allMatches as $match) {
            $isPlayer1 = $match->getPlayer1()->getId() === $user->getId();
            $isPlayer2 = $match->getPlayer2()->getId() === $user->getId();

            if (!$isPlayer1 && !$isPlayer2) {
                continue;
            }

            if ($isPlayer1) {
                if ($match->getScorePlayer1() > $match->getScorePlayer2()) {
                    $victories++;
                } else {
                    $defeats++;
                }
            } else {
                if ($match->getScorePlayer2() > $match->getScorePlayer1()) {
                    $victories++;
                } else {
                    $defeats++;
                }
            }
        }

        $output->writeln(sprintf('Victoires : <info>%d</info>', $victories));
        $output->writeln(sprintf('Défaites  : <info>%d</info>', $defeats));

        return Command::SUCCESS;
    }
}