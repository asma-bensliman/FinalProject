<?php

namespace App\EventListener;

use App\Event\ScoreUpdatedEvent;
use App\Event\TournamentWonEvent;
use App\Repository\RegistrationRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SportMatchListener implements EventSubscriberInterface
{
    public function __construct(
        private RegistrationRepository $registrationRepository,
        private LoggerInterface $logger
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ScoreUpdatedEvent::NAME => 'onScoreUpdated',
            TournamentWonEvent::NAME => 'onTournamentWon',
        ];
    }

    public function onScoreUpdated(ScoreUpdatedEvent $event): void
    {
        $match = $event->getMatch();
        $updatedBy = $event->getUpdatedBy();

        if ($updatedBy->getId() === $match->getPlayer1()->getId()) {
            $opponent = $match->getPlayer2();
        } else {
            $opponent = $match->getPlayer1();
        }

        $message = sprintf(
            'NOTIFICATION → %s : %s a mis à jour son score. Veuillez remplir le vôtre !',
            $opponent->getUsername(),
            $updatedBy->getUsername()
        );

        file_put_contents(__DIR__ . '/../../var/log/notifications.log', $message . PHP_EOL, FILE_APPEND);
    }

    public function onTournamentWon(TournamentWonEvent $event): void
    {
        $tournament = $event->getTournament();
        $winner = $event->getWinner();

        $registrations = $this->registrationRepository->findBy([
            'tournament' => $tournament,
            'status' => 'confirmed'
        ]);

        foreach ($registrations as $registration) {
            $player = $registration->getPlayer();
            $message = sprintf(
                'NOTIFICATION → %s : Le tournoi "%s" est terminé ! Le vainqueur est %s !',
                $player->getUsername(),
                $tournament->getTournamentName(),
                $winner->getUsername()
            );
            file_put_contents(__DIR__ . '/../../var/log/notifications.log', $message . PHP_EOL, FILE_APPEND);
        }
    }
}