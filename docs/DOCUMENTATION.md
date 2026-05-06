# Documentation Complète — Tournament Platform

## Table des matières

1. [Présentation du projet](#1-présentation-du-projet)
2. [Architecture technique](#2-architecture-technique)
3. [Installation et configuration](#3-installation-et-configuration)
4. [Modèle de données (Entités)](#4-modèle-de-données-entités)
5. [API REST — Référence complète](#5-api-rest--référence-complète)
6. [Authentification et autorisation](#6-authentification-et-autorisation)
7. [Diagramme de base de données](#7-diagramme-de-base-de-données)
8. [Tests](#8-tests)
9. [Déploiement](#9-déploiement)
10. [Conventions de développement](#10-conventions-de-développement)

---

## 1. Présentation du projet

**Tournament Platform** est une application web de gestion de tournois sportifs développée avec le framework Symfony 5.4. Elle permet aux utilisateurs de créer des tournois, s'inscrire à des compétitions, gérer les matchs et suivre les résultats.

### Fonctionnalités principales

- **Gestion des utilisateurs** : Inscription, connexion, profils joueurs
- **Gestion des tournois** : Création, modification, suppression, consultation
- **Inscriptions** : Inscription des joueurs aux tournois avec validation
- **Matchs** : Suivi des rencontres entre joueurs avec scores
- **Administration** : Gestion complète des utilisateurs, tournois et inscriptions par les administrateurs
- **Authentification JWT** : Sécurisation des endpoints API via tokens JSON Web Token

---

## 2. Architecture technique

### Stack technique

| Composant | Version | Description |
|-----------|---------|-------------|
| PHP | 8.2 | Langage de programmation |
| Symfony | 5.4 | Framework PHP |
| MySQL | 8.0 | Base de données relationnelle |
| Doctrine ORM | 2.20 | ORM pour la persistance des données |
| Lexik JWT Bundle | 2.20 | Authentification par tokens JWT |
| PHPUnit | 9.5 | Framework de tests unitaires |

### Structure du projet

```
tournament-platform/
├── bin/                    # Exécutables (console, phpunit)
├── config/                 # Configuration de l'application
│   ├── bundles.php         # Liste des bundles activés
│   ├── jwt/                # Clés JWT (private.pem, public.pem)
│   ├── packages/           # Configuration des packages
│   │   ├── cache.yaml
│   │   ├── doctrine.yaml
│   │   ├── doctrine_migrations.yaml
│   │   ├── framework.yaml
│   │   ├── lexik_jwt_authentication.yaml
│   │   ├── routing.yaml
│   │   ├── security.yaml
│   │   └── validator.yaml
│   ├── routes/             # Configuration des routes
│   │   ├── annotations.yaml
│   │   └── framework.yaml
│   ├── routes.yaml         # Routes principales
│   ├── services.yaml       # Services personnalisés
│   └── preload.php         # Préchargement PHP
├── migrations/             # Migrations de base de données
├── public/                 # Point d'entrée web
│   └── index.php           # Front controller
├── src/                    # Code source de l'application
│   ├── Controller/         # Contrôleurs de l'API
│   │   ├── AdminController.php
│   │   ├── PlayerController.php
│   │   ├── RegistrationController.php
│   │   └── TournamentController.php
│   ├── Entity/             # Entités Doctrine
│   │   ├── Registration.php
│   │   ├── SportMatch.php
│   │   ├── Tournament.php
│   │   └── User.php
│   ├── Repository/         # Repositories Doctrine
│   │   ├── RegistrationRepository.php
│   │   ├── SportMatchRepository.php
│   │   ├── TournamentRepository.php
│   │   └── UserRepository.php
│   ├── DataFixtures/       # Fixtures pour les tests
│   │   └── AppFixtures.php
│   └── Kernel.php          # Kernel Symfony
├── tests/                  # Tests unitaires
│   ├── Entity/
│   │   ├── TournamentTest.php
│   │   └── UserTest.php
│   └── bootstrap.php       # Bootstrap des tests
├── .env                    # Variables d'environnement
├── .env.dev                # Variables d'environnement dev
├── .env.test               # Variables d'environnement test
├── .gitignore              # Fichiers ignorés par Git
├── compose.yaml            # Configuration Docker
├── compose.override.yaml   # Configuration Docker locale
├── composer.json           # Dépendances PHP
├── generate_keys.php       # Script de génération des clés JWT
└── phpunit.xml.dist        # Configuration PHPUnit
```

### Fichiers de configuration clés

**config/packages/security.yaml** — Configuration de la sécurité et des firewalls :
```yaml
security:
    enable_authenticator_manager: true
    password_hashers:
        App\Entity\User:
            algorithm: bcrypt

    providers:
        app_user_provider:
            entity:
                class: App\Entity\User
                property: username

    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false

        login:
            pattern: ^/api/login
            stateless: true
            json_login:
                check_path: /api/login
                username_path: username
                password_path: password
                success_handler: lexik_jwt_authentication.handler.authentication_success
                failure_handler: lexik_jwt_authentication.handler.authentication_failure

        register:
            pattern: ^/register
            stateless: true
            security: false

        api:
            pattern: ^/api
            stateless: true
            jwt: ~

    access_control:
        - { path: ^/register, roles: PUBLIC_ACCESS }
        - { path: ^/api/login, roles: PUBLIC_ACCESS }
        - { path: ^/api/admin, roles: ROLE_ADMIN }
        - { path: ^/api, roles: ROLE_USER }
```

**config/packages/doctrine.yaml** — Configuration Doctrine :
```yaml
doctrine:
    dbal:
        url: '%env(resolve:DATABASE_URL)%'
        server_version: '8.0'
        use_savepoints: true
    orm:
        auto_generate_proxy_classes: true
        naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
        auto_mapping: true
        mappings:
            App:
                is_bundle: false
                dir: '%kernel.project_dir%/src/Entity'
                prefix: 'App\Entity'
                alias: App
```

**config/packages/lexik_jwt_authentication.yaml** — Configuration JWT :
```yaml
lexik_jwt_authentication:
    secret_key: '%env(resolve:JWT_SECRET_KEY)%'
    public_key: '%env(resolve:JWT_PUBLIC_KEY)%'
    pass_phrase: '%env(JWT_PASSPHRASE)%'
    user_identity_field: username
```

**config/packages/framework.yaml** — Configuration du framework :
```yaml
framework:
    secret: '%env(APP_SECRET)%'
    serializer:
        enabled: true
    cache:
        app: cache.adapter.filesystem
```

---

## 3. Installation et configuration

### Prérequis

- **WAMP** avec PHP 8.2 installé (chemin par défaut : `C:\wamp64\bin\php\php8.2.29`)
- **Composer** (gestionnaire de dépendances PHP)
- **Symfony CLI** (optionnel mais recommandé)
- **MySQL** via WAMP (server version 8.0)

### Étapes d'installation

#### 1. Cloner le projet

```bash
git clone https://github.com/TON_USERNAME/tournament-platform.git
cd tournament-platform
```

#### 2. Installer les dépendances

```bash
composer install
```

Cette commande installe tous les packages listés dans `composer.json`, incluant :
- Symfony 5.4 (framework-bundle, security-bundle, serializer, validator, etc.)
- Doctrine ORM et bundles associés (doctrine-bundle, doctrine-migrations-bundle)
- Lexik JWT Bundle (version 2.20)
- Outils de développement (PHPUnit, MakerBundle, DoctrineFixturesBundle, Faker)

#### 3. Configurer la base de données

Le fichier `.env` contient la configuration de la base de données :

```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/tournament_db?serverVersion=8.0"
```

Créer la base de données dans WAMP :
- Ouvrir phpMyAdmin (`http://localhost/phpmyadmin`)
- Créer une base de données nommée `tournament_db`
- Ou utiliser la console Symfony :

```bash
php bin/console doctrine:database:create
```

#### 4. Exécuter les migrations

```bash
php bin/console doctrine:migrations:migrate
```

Les migrations créent les tables suivantes :
- `user` — Utilisateurs/joueurs
- `tournament` — Tournois
- `registration` — Inscriptions aux tournois
- `sport_match` — Matchs entre joueurs

#### 5. Générer les clés JWT

```powershell
$env:OPENSSL_CONF = "C:\wamp64\bin\php\php8.2.29\extras\ssl\openssl.cnf"
php generate_keys.php
```

Cette commande génère deux fichiers :
- `config/jwt/private.pem` — Clé privée pour signer les tokens
- `config/jwt/public.pem` — Clé publique pour vérifier les tokens

La passphrase utilisée est définie dans `.env` :
```env
JWT_PASSPHRASE=44d130f74618ccc8b637a468812800e7fe3032d4052920260083eeaea1300825
```

#### 6. Lancer le serveur de développement

```powershell
$env:PATH = "C:\wamp64\bin\php\php8.2.29;" + $env:PATH
$env:OPENSSL_CONF = "C:\wamp64\bin\php\php8.2.29\extras\ssl\openssl.cnf"
symfony serve
```

Le serveur est accessible à l'adresse : `http://localhost:8000`

---

## 4. Modèle de données (Entités)

### Entity User (`src/Entity/User.php`)

Représente un utilisateur de la plateforme (joueur ou administrateur).

| Attribut | Type | Nullable | Description |
|----------|------|----------|-------------|
| id | int | Non | Identifiant unique (auto-incrémenté) |
| lastName | string(255) | Non | Nom de famille |
| firstName | string(255) | Non | Prénom |
| username | string(255) | Non | Nom d'utilisateur (identifiant de connexion) |
| emailAddress | string(255) | Non | Adresse email |
| password | string(255) | Non | Mot de passe hashé (bcrypt) |
| status | string(255) | Non | Statut du compte (ex: 'actif') |
| roles | array | Non | Rôles de sécurité (ex: ['ROLE_USER'], ['ROLE_ADMIN']) |

**Interfaces implémentées** :
- `UserInterface` — Interface standard Symfony pour l'authentification
- `PasswordAuthenticatedUserInterface` — Gestion des mots de passe

**Méthodes notables** :
```php
public function getUserIdentifier(): string // Retourne emailAddress
public function eraseCredentials(): void     // Nettoyage des credentials
public function getSalt(): ?string          // Retourne null (bcrypt n'utilise pas de salt externe)
public function getRoles(): array           // Retourne les rôles de l'utilisateur
```

### Entity Tournament (`src/Entity/Tournament.php`)

Représente un tournoi sportif.

| Attribut | Type | Nullable | Description |
|----------|------|----------|-------------|
| id | int | Non | Identifiant unique (auto-incrémenté) |
| tournamentName | string(255) | Non | Nom du tournoi |
| startDate | Date | Non | Date de début |
| endDate | Date | Non | Date de fin |
| location | string(255) | Oui | Lieu du tournoi |
| description | Text | Non | Description détaillée |
| maxParticipants | int | Non | Nombre maximum de participants |
| sport | string(255) | Non | Type de sport |
| organizer | User | Non | Utilisateur organisateur (relation ManyToOne) |
| winner | User | Oui | Gagnant du tournoi (relation ManyToOne) |
| games | Collection<SportMatch> | Non | Collection des matchs (relation OneToMany) |

**Méthode `getStatus()`** — Calcule dynamiquement le statut :
```php
public function getStatus(): string
{
    $now = new \DateTime();
    if ($now < $this->startDate) { return 'upcoming'; }
    if ($now > $this->endDate) { return 'finished'; }
    return 'ongoing';
}
```

**Statuts possibles** :
- `upcoming` — Tournoi à venir (date de début future)
- `ongoing` — Tournoi en cours (entre startDate et endDate)
- `finished` — Tournoi terminé (date de fin passée)

### Entity SportMatch (`src/Entity/SportMatch.php`)

Représente un match entre deux joueurs dans un tournoi.

| Attribut | Type | Nullable | Description |
|----------|------|----------|-------------|
| id | int | Non | Identifiant unique (auto-incrémenté) |
| tournament | Tournament | Non | Tournoi associé (relation ManyToOne) |
| player1 | User | Non | Premier joueur (relation ManyToOne) |
| player2 | User | Non | Second joueur (relation ManyToOne) |
| matchDate | Date | Non | Date du match |
| scorePlayer1 | int | Oui | Score du premier joueur |
| scorePlayer2 | int | Oui | Score du second joueur |
| status | string(255) | Non | Statut du match (ex: 'scheduled', 'completed') |

### Entity Registration (`src/Entity/Registration.php`)

Représente l'inscription d'un joueur à un tournoi.

| Attribut | Type | Nullable | Description |
|----------|------|----------|-------------|
| id | int | Non | Identifiant unique (auto-incrémenté) |
| player | User | Non | Joueur inscrit (relation ManyToOne) |
| tournament | Tournament | Non | Tournoi concerné (relation ManyToOne) |
| registrationDate | Date | Non | Date d'inscription |
| status | string(255) | Non | Statut de l'inscription (ex: 'en attente', 'validée') |

---

## 5. API REST — Référence complète

### Authentification

#### POST `/register` — Créer un compte

**Public** — Aucune authentification requise.

**Requête** :
```json
POST /register
Content-Type: application/json

{
    "firstName": "John",
    "lastName": "Doe",
    "username": "johndoe",
    "emailAddress": "john@example.com",
    "password": "mon_mot_de_passe"
}
```

**Réponse 201 (succès)** :
```json
{
    "id": 1,
    "firstName": "John",
    "lastName": "Doe",
    "username": "johndoe",
    "emailAddress": "john@example.com",
    "status": "actif"
}
```

**Réponse 400 (erreur)** :
```json
{
    "error": "Missing required fields"
}
```

**Notes** :
- Le mot de passe est automatiquement hashé avec bcrypt
- L'utilisateur reçoit le rôle `ROLE_USER` par défaut
- Le statut est initialisé à `'actif'`

#### POST `/api/login` — Se connecter

**Public** — Aucune authentification requise.

**Requête** :
```json
POST /api/login
Content-Type: application/json

{
    "username": "johndoe",
    "password": "mon_mot_de_passe"
}
```

**Réponse 200 (succès)** :
```json
{
    "token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

**Réponse 401 (échec d'authentification)** :
```json
{
    "code": 401,
    "message": "Invalid credentials."
}
```

**Notes** :
- L'authentification utilise le champ `username` (et non `emailAddress`)
- Le token JWT doit être inclus dans les requêtes suivantes via le header `Authorization: Bearer <token>`

---

### Joueurs (Players)

**Protection** : JWT requis (`ROLE_USER`)

#### GET `/api/players` — Liste des joueurs

**Réponse 200** :
```json
[
    {
        "id": 1,
        "firstName": "John",
        "lastName": "Doe",
        "username": "johndoe",
        "emailAddress": "john@example.com",
        "status": "actif"
    }
]
```

#### GET `/api/players/{id}` — Détails d'un joueur

**Réponse 200** :
```json
{
    "id": 1,
    "firstName": "John",
    "lastName": "Doe",
    "username": "johndoe",
    "emailAddress": "john@example.com",
    "status": "actif"
}
```

**Réponse 404** :
```json
{
    "error": "Player not found"
}
```

#### PUT `/api/players/{id}` — Modifier un joueur

**Requête** :
```json
PUT /api/players/1
Content-Type: application/json

{
    "firstName": "Jane",
    "lastName": "Smith",
    "password": "nouveau_mot_de_passe"
}
```

**Réponse 200** :
```json
{
    "id": 1,
    "firstName": "Jane",
    "lastName": "Smith",
    "username": "johndoe",
    "emailAddress": "john@example.com",
    "status": "actif"
}
```

**Notes** :
- Tous les champs sont optionnels dans la requête
- Si `password` est fourni, il est automatiquement hashé
- Seuls les champs fournis sont modifiés

#### DELETE `/api/players/{id}` — Supprimer un joueur

**Réponse 200** :
```json
{
    "message": "Player deleted successfully"
}
```

**Réponse 404** :
```json
{
    "error": "Player not found"
}
```

---

### Tournois (Tournaments)

**Protection** : JWT requis (`ROLE_USER`)

#### GET `/api/tournaments` — Liste des tournois

**Réponse 200** :
```json
[
    {
        "id": 1,
        "tournamentName": "Tournoi de Football 2026",
        "startDate": "2026-06-01",
        "endDate": "2026-06-10",
        "location": "Paris",
        "description": "Tournoi annuel de football",
        "maxParticipants": 16,
        "sport": "Football",
        "status": "upcoming",
        "organizer": 1,
        "winner": null
    }
]
```

**Notes** :
- Le `status` est calculé dynamiquement (`upcoming`, `ongoing`, `finished`)
- `winner` est `null` si le tournoi n'est pas terminé

#### POST `/api/tournaments` — Créer un tournoi

**Requête** :
```json
POST /api/tournaments
Content-Type: application/json

{
    "tournamentName": "Tournoi de Football 2026",
    "startDate": "2026-06-01",
    "endDate": "2026-06-10",
    "description": "Tournoi annuel de football",
    "sport": "Football",
    "maxParticipants": 16,
    "location": "Paris"
}
```

**Champs obligatoires** : `tournamentName`, `startDate`, `endDate`, `description`

**Champs optionnels** :
- `sport` (valeur par défaut: `'Unknown'`)
- `maxParticipants` (valeur par défaut: `0`)
- `location` (valeur par défaut: `null`)

**Réponse 201** :
```json
{
    "id": 1,
    "tournamentName": "Tournoi de Football 2026",
    "status": "upcoming"
}
```

**Réponse 400** :
```json
{
    "error": "Missing required fields"
}
```

**Notes** :
- L'organisateur est automatiquement défini comme l'utilisateur authentifié (`$this->getUser()`)

#### GET `/api/tournaments/{id}` — Détails d'un tournoi

**Réponse 200** :
```json
{
    "id": 1,
    "tournamentName": "Tournoi de Football 2026",
    "startDate": "2026-06-01",
    "endDate": "2026-06-10",
    "location": "Paris",
    "description": "Tournoi annuel de football",
    "maxParticipants": 16,
    "sport": "Football",
    "status": "upcoming",
    "organizer": 1,
    "winner": null
}
```

**Réponse 404** : Retourné si le tournoi n'existe pas (injection automatique de l'entité par Doctrine)

#### PUT `/api/tournaments/{id}` — Modifier un tournoi

**Requête** :
```json
PUT /api/tournaments/1
Content-Type: application/json

{
    "tournamentName": "Nouveau nom du tournoi",
    "location": "Lyon"
}
```

**Réponse 200** :
```json
{
    "id": 1,
    "tournamentName": "Nouveau nom du tournoi",
    "status": "upcoming"
}
```

**Notes** :
- Tous les champs sont optionnels
- Seuls les champs fournis sont modifiés

#### DELETE `/api/tournaments/{id}` — Supprimer un tournoi

**Réponse 200** :
```json
{
    "message": "Tournament deleted successfully"
}
```

**Notes** :
- Attention : la suppression d'un tournoi peut entraîner la suppression en cascade des matchs associés (relation OneToMany)

---

### Inscriptions (Registrations)

**Protection** : JWT requis (`ROLE_USER`)

#### GET `/api/tournaments/{id}/registrations` — Liste des inscriptions d'un tournoi

**Réponse 200** :
```json
[
    {
        "id": 1,
        "player": 2,
        "tournament": 1,
        "registrationDate": "2026-05-01",
        "status": "en attente"
    }
]
```

**Réponse 404** :
```json
{
    "error": "Tournament not found"
}
```

#### POST `/api/tournaments/{id}/registrations` — Inscrire un joueur à un tournoi

**Requête** :
```json
POST /api/tournaments/1/registrations
Content-Type: application/json

{
    "playerId": 2
}
```

**Réponse 201** :
```json
{
    "id": 1,
    "player": 2,
    "tournament": 1,
    "registrationDate": "2026-05-06",
    "status": "en attente"
}
```

**Réponse 400** :
```json
{
    "error": "Missing playerId"
}
```

**Réponse 404** :
```json
{
    "error": "Tournament not found"
}
```
ou
```json
{
    "error": "Player not found"
}
```

**Notes** :
- Le statut est automatiquement initialisé à `'en attente'`
- La date d'inscription est la date actuelle

#### DELETE `/api/tournaments/{idTournament}/registrations/{idRegistration}` — Annuler une inscription

**Réponse 200** :
```json
{
    "message": "Registration deleted successfully"
}
```

**Réponse 404** :
```json
{
    "error": "Tournament not found"
}
```
ou
```json
{
    "error": "Registration not found"
}
```

**Notes** :
- Vérifie que l'inscription appartient bien au tournoi spécifié

---

### Administration (ROLE_ADMIN uniquement)

**Protection** : JWT requis + `ROLE_ADMIN`

#### GET `/api/admin/users` — Liste de tous les utilisateurs

**Réponse 200** :
```json
[
    {
        "id": 1,
        "firstName": "John",
        "lastName": "Doe",
        "username": "johndoe",
        "emailAddress": "john@example.com",
        "status": "actif",
        "roles": ["ROLE_USER"]
    }
]
```

#### PUT `/api/admin/users/{id}` — Modifier un utilisateur (admin)

**Requête** :
```json
PUT /api/admin/users/1
Content-Type: application/json

{
    "status": "suspendu",
    "roles": ["ROLE_USER", "ROLE_MODERATOR"]
}
```

**Réponse 200** :
```json
{
    "id": 1,
    "status": "suspendu",
    "roles": ["ROLE_USER", "ROLE_MODERATOR"]
}
```

**Réponse 404** :
```json
{
    "error": "User not found"
}
```

**Réponse 403** : Retourné si l'utilisateur n'a pas le rôle `ROLE_ADMIN`

#### DELETE `/api/admin/users/{id}` — Supprimer un utilisateur (admin)

**Réponse 200** :
```json
{
    "message": "User deleted successfully"
}
```

**Réponse 404** :
```json
{
    "error": "User not found"
}
```

#### GET `/api/admin/tournaments` — Liste de tous les tournois (admin)

**Réponse 200** :
```json
[
    {
        "id": 1,
        "tournamentName": "Tournoi de Football 2026",
        "sport": "Football",
        "status": "upcoming",
        "maxParticipants": 16
    }
]
```

#### GET `/api/admin/registrations` — Liste de toutes les inscriptions (admin)

**Réponse 200** :
```json
[
    {
        "id": 1,
        "player": 2,
        "tournament": 1,
        "status": "en attente",
        "registrationDate": "2026-05-01"
    }
]
```

#### PUT `/api/admin/registrations/{id}` — Modifier le statut d'une inscription (admin)

**Requête** :
```json
PUT /api/admin/registrations/1
Content-Type: application/json

{
    "status": "validée"
}
```

**Réponse 200** :
```json
{
    "id": 1,
    "status": "validée"
}
```

**Réponse 404** :
```json
{
    "error": "Registration not found"
}
```

---

### Récapitulatif des routes

| Méthode | Route | Description | Auth requise | Rôle requis |
|---------|-------|-------------|--------------|-------------|
| POST | `/register` | Créer un compte | Non | — |
| POST | `/api/login` | Se connecter | Non | — |
| GET | `/api/players` | Liste des joueurs | JWT | ROLE_USER |
| GET | `/api/players/{id}` | Détails d'un joueur | JWT | ROLE_USER |
| PUT | `/api/players/{id}` | Modifier un joueur | JWT | ROLE_USER |
| DELETE | `/api/players/{id}` | Supprimer un joueur | JWT | ROLE_USER |
| GET | `/api/tournaments` | Liste des tournois | JWT | ROLE_USER |
| POST | `/api/tournaments` | Créer un tournoi | JWT | ROLE_USER |
| GET | `/api/tournaments/{id}` | Détails d'un tournoi | JWT | ROLE_USER |
| PUT | `/api/tournaments/{id}` | Modifier un tournoi | JWT | ROLE_USER |
| DELETE | `/api/tournaments/{id}` | Supprimer un tournoi | JWT | ROLE_USER |
| GET | `/api/tournaments/{id}/registrations` | Liste des inscriptions | JWT | ROLE_USER |
| POST | `/api/tournaments/{id}/registrations` | Inscrire un joueur | JWT | ROLE_USER |
| DELETE | `/api/tournaments/{idTournament}/registrations/{idRegistration}` | Annuler inscription | JWT | ROLE_USER |
| GET | `/api/admin/users` | Liste des utilisateurs | JWT | ROLE_ADMIN |
| PUT | `/api/admin/users/{id}` | Modifier un utilisateur | JWT | ROLE_ADMIN |
| DELETE | `/api/admin/users/{id}` | Supprimer un utilisateur | JWT | ROLE_ADMIN |
| GET | `/api/admin/tournaments` | Liste des tournois | JWT | ROLE_ADMIN |
| GET | `/api/admin/registrations` | Liste des inscriptions | JWT | ROLE_ADMIN |
| PUT | `/api/admin/registrations/{id}` | Modifier inscription | JWT | ROLE_ADMIN |

---

## 6. Authentification et autorisation

### Flux d'authentification JWT

1. **Inscription** (`POST /register`) — Création d'un compte utilisateur
2. **Connexion** (`POST /api/login`) — Échange credentials contre un token JWT
3. **Utilisation de l'API** — Inclusion du token dans le header `Authorization: Bearer <token>`

### Configuration des firewalls

L'application Symfony utilise plusieurs firewalls pour sécuriser les différentes parties de l'API :

| Firewall | Pattern | Sécurité | Description |
|----------|---------|----------|-------------|
| `dev` | `^/(_(profiler\|wdt)\|css\|images\|js)/` | Désactivée | Accès libre aux assets et outils de dev |
| `login` | `^/api/login` | JSON Login | Authentification par credentials |
| `register` | `^/register` | Désactivée | Inscription publique |
| `api` | `^/api` | JWT | Authentification par token pour toutes les autres routes API |

### Contrôle d'accès

Les règles `access_control` définissent les autorisations minimales :

```yaml
access_control:
    - { path: ^/register, roles: PUBLIC_ACCESS }          # Inscription publique
    - { path: ^/api/login, roles: PUBLIC_ACCESS }         # Login public
    - { path: ^/api/admin, roles: ROLE_ADMIN }            # Admin uniquement
    - { path: ^/api, roles: ROLE_USER }                   # Utilisateur authentifié
```

**Important** : Les routes admin (`^/api/admin`) doivent matcher avant les routes générales (`^/api`), car Symfony évalue les règles dans l'ordre.

### Création d'un administrateur

Pour créer un utilisateur avec le rôle administrateur, il faut modifier directement la base de données ou créer un script dédié :

```sql
-- Exemple de requête SQL pour promouvoir un utilisateur en admin
UPDATE user SET roles = '["ROLE_ADMIN"]' WHERE username = 'admin_username';
```

Ou via la console Symfony (si un service personnalisé est créé) :
```bash
php bin/console app:promote-admin admin_username
```

### Gestion des rôles

Les rôles sont stockés dans la colonne `roles` de la table `user` au format JSON array :

| Rôle | Description | Accès |
|------|-------------|-------|
| `ROLE_USER` | Utilisateur standard | Accès aux endpoints joueurs, tournois, inscriptions |
| `ROLE_ADMIN` | Administrateur | Accès complet + gestion des utilisateurs et inscriptions |

### Provider d'utilisateurs

L'application utilise `app_user_provider` qui charge les utilisateurs depuis la base de données via Doctrine :

```yaml
providers:
    app_user_provider:
        entity:
            class: App\Entity\User
            property: username
```

**Note** : La propriété utilisée pour l'authentification est `username`, et non `emailAddress`.

---

## 7. Diagramme de base de données

### Relations entre les entités

```
┌─────────────────┐
│      User       │
├─────────────────┤
│ id (PK)         │
│ lastName        │
│ firstName       │
│ username        │
│ emailAddress    │
│ password        │
│ status          │
│ roles (JSON)    │
└────────┬────────┘
         │
         │ 1
         │
         ├───────────────────────────────┐
         │                               │
         │ N                             │ N
         ▼                               ▼
┌─────────────────┐             ┌─────────────────┐
│   Tournament    │             │   SportMatch    │
├─────────────────┤             ├─────────────────┤
│ id (PK)         │             │ id (PK)         │
│ tournamentName  │             │ tournament_id   │
│ startDate       │             │ player1_id      │
│ endDate         │             │ player2_id      │
│ location        │             │ matchDate       │
│ description     │             │ scorePlayer1    │
│ maxParticipants │             │ scorePlayer2    │
│ sport           │             │ status          │
│ organizer_id    │             └────────┬────────┘
│ winner_id       │                      │
└────────┬────────┘                      │
         │                               │
         │ 1                             │ N
         ▼                               │
┌─────────────────┐                      │
│  Registration   │                      │
├─────────────────┤                      │
│ id (PK)         │                      │
│ player_id       │                      │
│ tournament_id   │                      │
│ registrationDate│                      │
│ status          │                      │
└─────────────────┘                      │
         ▲                               │
         │                               │
         └───────────────────────────────┘
```

### Description des relations

| Relation | Type | Description |
|----------|------|-------------|
| User → Tournament (organizer) | OneToMany (1:N) | Un utilisateur peut organiser plusieurs tournois |
| User → Tournament (winner) | OneToMany (1:N) | Un utilisateur peut gagner plusieurs tournois |
| User → Registration | OneToMany (1:N) | Un utilisateur peut s'inscrire à plusieurs tournois |
| User → SportMatch (player1/player2) | OneToMany (1:N) | Un utilisateur peut participer à plusieurs matchs |
| Tournament → Registration | OneToMany (1:N) | Un tournoi peut avoir plusieurs inscriptions |
| Tournament → SportMatch | OneToMany (1:N) | Un tournoi contient plusieurs matchs |

### Contraintes

| Table | Contrainte | Description |
|-------|------------|-------------|
| Tournament | `organizer_id` NOT NULL | Tout tournoi doit avoir un organisateur |
| SportMatch | `tournament_id` NOT NULL | Tout match doit appartenir à un tournoi |
| SportMatch | `player1_id` NOT NULL | Le joueur 1 est obligatoire |
| SportMatch | `player2_id` NOT NULL | Le joueur 2 est obligatoire |
| Registration | `player_id` NOT NULL | L'inscription nécessite un joueur |
| Registration | `tournament_id` NOT NULL | L'inscription nécessite un tournoi |

---

## 8. Tests

### Exécuter les tests

```bash
php bin/phpunit
```

### Tests existants

#### TournamentTest (`tests/Entity/TournamentTest.php`)

| Test | Description | Assertions |
|------|-------------|------------|
| `testTournamentCreation` | Vérifie la création d'un tournoi avec toutes ses propriétés | 5 assertions |
| `testTournamentStatusUpcoming` | Vérifie que le statut est `upcoming` quand la date de début est future | 1 assertion |
| `testTournamentStatusOngoing` | Vérifie que le statut est `ongoing` quand la date actuelle est entre startDate et endDate | 1 assertion |
| `testTournamentStatusFinished` | Vérifie que le statut est `finished` quand la date de fin est passée | 1 assertion |

#### UserTest (`tests/Entity/UserTest.php`)

| Test | Description | Assertions |
|------|-------------|------------|
| `testUserCreation` | Vérifie la création d'un utilisateur avec toutes ses propriétés | 6 assertions |
| `testUserIdentifier` | Vérifie que `getUserIdentifier()` retourne l'adresse email | 1 assertion |
| `testEraseCredentials` | Vérifie que `eraseCredentials()` ne supprime pas le mot de passe | 1 assertion |
| `testDefaultRoles` | Vérifie que les rôles par défaut sont vides | 1 assertion |

### Résultat des tests

```
OK (8 tests, 17 assertions)
```

### Couverture actuelle

Les tests couvrent actuellement :
- ✅ Création et manipulation des entités `User` et `Tournament`
- ✅ Calcul dynamique du statut des tournois
- ✅ Interface UserInterface (`getUserIdentifier`, `eraseCredentials`)

### Tests manquants (recommandations)

Pour une couverture complète, il serait pertinent d'ajouter :

1. **Tests des contrôleurs** — Tests fonctionnels des endpoints API
2. **Tests des entités** `SportMatch` et `Registration`
3. **Tests d'intégration** — Tests avec la base de données
4. **Tests de sécurité** — Vérification des accès (ROLE_USER vs ROLE_ADMIN)
5. **Tests de validation** — Vérification des données invalides

---

## 9. Déploiement

### Environnement de production

Pour déployer en production :

1. **Définir l'environnement** :
   ```env
   APP_ENV=prod
   APP_DEBUG=0
   ```

2. **Compiler les variables d'environnement** :
   ```bash
   composer dump-env prod
   ```

3. **Optimiser l'autoloader** :
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

4. **Vider le cache** :
   ```bash
   php bin/console cache:clear --no-warmup
   php bin/console cache:warmup
   ```

5. **Exécuter les migrations** :
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

### Variables d'environnement critiques

| Variable | Description | Exemple |
|----------|-------------|---------|
| `APP_SECRET` | Clé secrète pour la sécurité Symfony | Chaîne aléatoire de 32+ caractères |
| `DATABASE_URL` | URL de connexion à la base de données | `mysql://user:password@host:port/dbname` |
| `JWT_SECRET_KEY` | Chemin vers la clé privée JWT | `%kernel.project_dir%/config/jwt/private.pem` |
| `JWT_PUBLIC_KEY` | Chemin vers la clé publique JWT | `%kernel.project_dir%/config/jwt/public.pem` |
| `JWT_PASSPHRASE` | Passphrase des clés JWT | Chaîne secrète |

### Sécurité en production

- **NE PAS** commiter les fichiers `.env.local` ou secrets dans Git
- **NE PAS** utiliser les clés JWT de développement en production
- **Générer** une nouvelle `APP_SECRET` pour chaque environnement
- **Utiliser** HTTPS pour toutes les communications API
- **Configurer** correctement les CORS si l'API est appelée depuis un frontend

---

## 10. Conventions de développement

### Architecture

L'application suit l'architecture MVC de Symfony :

- **Controllers** : Gèrent les requêtes HTTP et retournent des réponses JSON
- **Entities** : Représentent les modèles de données avec les annotations Doctrine
- **Repositories** : Fournissent les méthodes d'accès aux données (utilisent les méthodes par défaut de Doctrine)

### Conventions de nommage

| Élément | Convention | Exemple |
|---------|------------|---------|
| Entités | PascalCase | `User`, `Tournament` |
| Contrôleurs | PascalCase + suffixe `Controller` | `PlayerController` |
| Repositories | PascalCase + suffixe `Repository` | `UserRepository` |
| Routes | snake_case | `players_list`, `tournament_create` |
| Attributs | camelCase | `tournamentName`, `maxParticipants` |

### Réponses API

Toutes les réponses sont au format JSON via `JsonResponse`.

**Format standard** :
- Succès : `200`, `201` avec les données demandées
- Erreur client : `400`, `404` avec `{"error": "message"}`
- Erreur serveur : `500` (géré par Symfony)

### Gestion des dates

Les dates sont retournées au format `Y-m-d` (ex: `2026-05-06`).

### Statuts

| Entité | Statuts possibles |
|--------|-------------------|
| User | `actif`, `suspendu`, etc. |
| Tournament | `upcoming`, `ongoing`, `finished` (calculé dynamiquement) |
| Registration | `en attente`, `validée`, `refusée`, etc. |
| SportMatch | `scheduled`, `completed`, etc. |

---

## Annexes

### A. Commandes Symfony utiles

```bash
# Afficher toutes les routes
php bin/console debug:router

# Afficher les routes d'un contrôleur spécifique
php bin/console debug:router PlayerController

# Créer une nouvelle entité
php bin/console make:entity

# Créer une nouvelle migration
php bin/console make:migration

# Créer un nouveau contrôleur
php bin/console make:controller

# Vider le cache
php bin/console cache:clear

# Afficher les informations de la base de données
php bin/console doctrine:query:sql "SELECT * FROM user"
```

### B. Exemples de requêtes cURL

#### Inscription
```bash
curl -X POST http://localhost:8000/register \
  -H "Content-Type: application/json" \
  -d '{"firstName":"John","lastName":"Doe","username":"johndoe","emailAddress":"john@example.com","password":"password123"}'
```

#### Connexion
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"johndoe","password":"password123"}'
```

#### Créer un tournoi (avec token)
```bash
curl -X POST http://localhost:8000/api/tournaments \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <votre_token_jwt>" \
  -d '{"tournamentName":"Mon Tournoi","startDate":"2026-06-01","endDate":"2026-06-10","description":"Description","sport":"Football","maxParticipants":16}'
```

#### Liste des tournois
```bash
curl -X GET http://localhost:8000/api/tournaments \
  -H "Authorization: Bearer <votre_token_jwt>"
```

### C. Dépendances principales (composer.json)

| Package | Version | Utilisation |
|---------|---------|-------------|
| symfony/framework-bundle | 5.4.* | Framework principal |
| symfony/security-bundle | 5.4.* | Authentification et autorisation |
| doctrine/orm | ^2.20 | ORM pour la persistance |
| doctrine/doctrine-bundle | ^2.13 | Intégration Doctrine |
| lexik/jwt-authentication-bundle | ^2.20 | Authentification JWT |
| symfony/serializer | 5.4.* | Sérialisation/désérialisation JSON |
| symfony/validator | 5.4.* | Validation des données |

**Dev dependencies** :
| Package | Version | Utilisation |
|---------|---------|-------------|
| phpunit/phpunit | ^9.5 | Tests unitaires |
| symfony/maker-bundle | ^1.50 | Générateurs de code |
| doctrine/doctrine-fixtures-bundle | ^3.7 | Fixtures pour tests |
| fakerphp/faker | ^1.24 | Génération de données fictives |

---