# Tournament Platform
Plateforme de gestion de tournois de sport — Projet final.

## Stack technique
- PHP 8.3
- Symfony 5.4
- MySQL
- JWT Authentication (lexik/jwt-authentication-bundle)
- EasyAdmin 4

## Prérequis
- PHP 8.2+
- Composer
- Symfony CLI
- MySQL

## Installation

### 1. Cloner le projet
git clone https://github.com/asma-bensliman/FinalProject.git
cd FinalProject

### 2. Installer les dépendances
composer install

### 3. Configurer l'environnement
Crée un fichier `.env` à la racine :

APP_ENV=dev
APP_SECRET=6c67c19f35d75c8473b06f6b7e83b9ed
DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8"

Crée un fichier `.env.local` à la racine :

DATABASE_URL="mysql://root:TONMOTDEPASSE@127.0.0.1:3306/tournament_db?serverVersion=9.1.0&charset=utf8mb4"
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=

### 4. Créer la base de données
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

### 5. Générer les clés JWT
Windows :
$env:OPENSSL_CONF = "C:\wamp64\bin\php\php8.3.14\extras\ssl\openssl.cnf"
php bin/console lexik:jwt:generate-keypair

### 6. Charger les fixtures
php bin/console doctrine:fixtures:load

### 7. Lancer le serveur
symfony serve

## Comptes de test (après fixtures)

| Username | Password | Rôle |
|---|---|---|
| admin | admin123 | ROLE_ADMIN |
| alice | password123 | ROLE_USER |
| bob | password123 | ROLE_USER |
| charlie | password123 | ROLE_USER |
| diana | password123 | ROLE_USER |

## Interface d'administration
Accessible uniquement aux admins sur :
http://127.0.0.1:8000/admin

Login admin :
http://127.0.0.1:8000/admin/login

## Routes disponibles

### Authentification
| Méthode | Route | Description | Auth |
|---|---|---|---|
| POST | /register | Créer un compte | Non |
| POST | /api/login | Se connecter | Non |

### Joueurs
| Méthode | Route | Description | Auth |
|---|---|---|---|
| GET | /api/players | Liste des joueurs | JWT |
| GET | /api/players/{id} | Détails d'un joueur | JWT |
| PUT | /api/players/{id} | Modifier un joueur | JWT |
| DELETE | /api/players/{id} | Supprimer un joueur | JWT |

### Tournois
| Méthode | Route | Description | Auth |
|---|---|---|---|
| GET | /api/tournaments | Liste des tournois | JWT |
| POST | /api/tournaments | Créer un tournoi | JWT |
| GET | /api/tournaments/{id} | Détails d'un tournoi | JWT |
| PUT | /api/tournaments/{id} | Modifier un tournoi | JWT |
| DELETE | /api/tournaments/{id} | Supprimer un tournoi | JWT |

### Inscriptions
| Méthode | Route | Description | Auth |
|---|---|---|---|
| GET | /api/tournaments/{id}/registrations | Liste des inscriptions | JWT |
| POST | /api/tournaments/{id}/registrations | Inscrire un joueur | JWT |
| DELETE | /api/tournaments/{idTournament}/registrations/{idRegistration} | Annuler une inscription | JWT |

### Matchs
| Méthode | Route | Description | Auth |
|---|---|---|---|
| GET | /api/tournaments/{id}/sport-matchs | Liste des matchs | JWT |
| POST | /api/tournaments/{id}/sport-matchs | Créer un match | JWT |
| GET | /api/tournaments/{idTournament}/sport-matchs/{idSportMatch} | Détails d'un match | JWT |
| PUT | /api/tournaments/{idTournament}/sport-matchs/{idSportMatch} | Modifier les scores | JWT |
| DELETE | /api/tournaments/{idTournament}/sport-matchs/{idSportMatch} | Supprimer un match | JWT |

### Administration (ROLE_ADMIN uniquement)
| Méthode | Route | Description | Auth |
|---|---|---|---|
| GET | /api/admin/users | Liste des utilisateurs | JWT + ROLE_ADMIN |
| PUT | /api/admin/users/{id} | Modifier un utilisateur | JWT + ROLE_ADMIN |
| DELETE | /api/admin/users/{id} | Supprimer un utilisateur | JWT + ROLE_ADMIN |
| GET | /api/admin/tournaments | Liste des tournois | JWT + ROLE_ADMIN |
| GET | /api/admin/registrations | Liste des inscriptions | JWT + ROLE_ADMIN |
| PUT | /api/admin/registrations/{id} | Modifier une inscription | JWT + ROLE_ADMIN |

## Commande Symfony

Stats d'un joueur (victoires/défaites) :
php bin/console app:player:stats {userId}

Stats d'un joueur pour un tournoi spécifique :
php bin/console app:player:stats {userId} {tournamentId}

## Tests
php bin/phpunit

## Règles métier
- Seul le joueur concerné peut modifier son propre score (ou un admin)
- Les joueurs doivent avoir une inscription confirmée pour jouer un match
- Quand les 2 scores sont remplis le match passe automatiquement en terminé
- Quand un joueur met son score une notification est envoyée à l'adversaire
- Quand un tournoi est remporté une notification est envoyée à tous les participants
- Les admins ne déclenchent pas de notifications quand ils modifient les scores