# Tournament Platform

Plateforme de gestion de tournois de sport — Projet final.

## Stack technique

- PHP 8.2
- Symfony 5.4
- MySQL (WAMP)
- JWT Authentication (lexik/jwt-authentication-bundle 2.20)

## Prérequis

- WAMP avec PHP 8.2
- Composer
- Symfony CLI

## Installation

### 1. Cloner le projet

```bash
git clone https://github.com/TON_USERNAME/tournament-platform.git
cd tournament-platform
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configurer l'environnement

Crée un fichier `.env` à la racine :

```env
APP_ENV=dev
APP_DEBUG=0
APP_SECRET=your_secret_here
DATABASE_URL="mysql://root:@127.0.0.1:3306/tournament_db?serverVersion=8.0"
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=
```

### 4. Créer la base de données

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Générer les clés JWT

```powershell
$env:OPENSSL_CONF = "C:\wamp64\bin\php\php8.2.29\extras\ssl\openssl.cnf"
php generate_keys.php
```

### 6. Lancer le serveur

```powershell
$env:PATH = "C:\wamp64\bin\php\php8.2.29;" + $env:PATH
$env:OPENSSL_CONF = "C:\wamp64\bin\php\php8.2.29\extras\ssl\openssl.cnf"
symfony serve
```

## Routes disponibles

### Authentification

| Méthode | Route | Description | Auth |
|---------|-------|-------------|------|
| POST | `/register` | Créer un compte | Non |
| POST | `/api/login` | Se connecter | Non |

### Joueurs

| Méthode | Route | Description | Auth |
|---------|-------|-------------|------|
| GET | `/api/players` | Liste des joueurs | JWT |
| GET | `/api/players/{id}` | Détails d'un joueur | JWT |
| PUT | `/api/players/{id}` | Modifier un joueur | JWT |
| DELETE | `/api/players/{id}` | Supprimer un joueur | JWT |

### Tournois

| Méthode | Route | Description | Auth |
|---------|-------|-------------|------|
| GET | `/api/tournaments` | Liste des tournois | JWT |
| POST | `/api/tournaments` | Créer un tournoi | JWT |
| GET | `/api/tournaments/{id}` | Détails d'un tournoi | JWT |
| PUT | `/api/tournaments/{id}` | Modifier un tournoi | JWT |
| DELETE | `/api/tournaments/{id}` | Supprimer un tournoi | JWT |

### Inscriptions

| Méthode | Route | Description | Auth |
|---------|-------|-------------|------|
| GET | `/api/tournaments/{id}/registrations` | Liste des inscriptions | JWT |
| POST | `/api/tournaments/{id}/registrations` | Inscrire un joueur | JWT |
| DELETE | `/api/tournaments/{idTournament}/registrations/{idRegistration}` | Annuler une inscription | JWT |

### Administration (ROLE_ADMIN uniquement)

| Méthode | Route | Description | Auth |
|---------|-------|-------------|------|
| GET | `/api/admin/users` | Liste des utilisateurs | JWT + ROLE_ADMIN |
| PUT | `/api/admin/users/{id}` | Modifier un utilisateur | JWT + ROLE_ADMIN |
| DELETE | `/api/admin/users/{id}` | Supprimer un utilisateur | JWT + ROLE_ADMIN |
| GET | `/api/admin/tournaments` | Liste des tournois | JWT + ROLE_ADMIN |
| GET | `/api/admin/registrations` | Liste des inscriptions | JWT + ROLE_ADMIN |
| PUT | `/api/admin/registrations/{id}` | Modifier une inscription | JWT + ROLE_ADMIN |

## Utilisation de l'API

### 1. Login

```json
POST /api/login
{
    "username": "votre_username",
    "password": "votre_password"
}
```

### 2. Utiliser le token
## Tests

```bash
php bin/phpunit
```

8 tests, 17 assertions — tous passés ✅

## Notes importantes

- Le login utilise le champ `username` (pas `emailAddress`)
- PHP 8.2 est requis
- Les clés JWT doivent être générées localement avec `generate_keys.php`
- L'admin doit avoir le rôle `ROLE_ADMIN` dans la base de données
