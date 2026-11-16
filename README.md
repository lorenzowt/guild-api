# Guild API

Guild API is a RESTful Laravel application for managing a fantasy guild.

Users can register, manage heroes, accept quests, create missions, and complete or cancel those missions. Administrators can manage users and the quest catalogue, and can access every hero, mission, and user record.

## Features

- User registration, login, and logout with Laravel Passport.
- Role-based access for users and administrators.
- Hero creation, updates, healing, and deactivation.
- Quest catalogue with admin creation, updates, and deactivation.
- Mission creation using available heroes.
- Mission outcome calculation based on hero levels, unique hero classes, and quest difficulty.
- Mission completion, cancellation, hero level updates, and hero status updates.
- User profile management.
- Administrator user management.

## Requirements

- PHP 8.3 or later
- Composer
- MySQL
- Node.js and npm, if you want to use Laravel frontend tooling

## Installation

Clone the repository:

```bash
git clone https://github.com/lorenzowt/guild-api.git
cd guild-api
```

Install PHP dependencies:

```bash
composer install
```

Create the environment file:

```bash
cp .env.example .env
```

Configure your database credentials in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=guild_api
DB_USERNAME=root
DB_PASSWORD=
```

Create the database, then generate the application key:

```bash
php artisan key:generate
```

Run migrations:

```bash
php artisan migrate
```

Install Laravel Passport encryption keys and personal access client:

```bash
php artisan passport:install
```

Seed demo data:

```bash
php artisan db:seed
```

Start the development server:

```bash
php artisan serve
```

The API is then available at:

```text
http://127.0.0.1:8000/api
```

## Demo Accounts

After running the seeders, use these accounts to test the API:

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@guild.test` | `password` |
| User | `arthas@guild.test` | `password` |
| User | `jaina@guild.test` | `password` |

## Authentication

The API uses Bearer token authentication through Laravel Passport.

Log in with a seeded account:

```http
POST /api/login
Content-Type: application/json
```

```json
{
  "email": "admin@guild.test",
  "password": "password"
}
```

The response contains an access token:

```json
{
  "access_token": "your-access-token",
  "token_type": "Bearer"
}
```

Send it in protected requests:

```http
Authorization: Bearer your-access-token
```

## Demo Data Reset

To delete all local data, rerun migrations, and reseed the application:

```bash
php artisan migrate:fresh
php artisan passport:install --force
php artisan db:seed
```

> Warning: this command permanently deletes all data in the configured database.

## Testing

Run the full Pest test suite:

```bash
php artisan test
```

Run a specific test file:

```bash
php artisan test tests/Feature/Mission/MissionCreateTest.php
```

## Core Domain Rules

### Heroes

- Heroes begin at level 1 with `available` status.
- A hero can only join a mission while available.
- Completing a successful mission increases each assigned hero's level by 1.
- Completing a failed mission decreases each assigned hero's level by 1, to a minimum of 1, and injures the hero.
- Cancelling a mission releases assigned heroes without changing their level.
- Deactivated heroes cannot be used in missions.

### Missions

- A mission requires between 1 and 4 available heroes.
- A mission can only use an active quest.
- A mission starts with `assigned` status.
- Only assigned missions can be completed or cancelled.
- A mission succeeds when the party level is greater than the quest difficulty.
- Party level equals the sum of hero levels plus one bonus point for every additional unique hero class.

### Quests

- Only administrators can create, update, or deactivate quests.
- Deactivated quests are hidden from the user-facing quest catalogue.
- Deleting a quest deactivates it rather than permanently deleting it.

### User Deletion

- Only administrators can delete user accounts.
- Deleting a user permanently deletes their heroes, missions, and related mission assignments.
- Quests created by a deleted user remain available, with their `created_by` value set to `null`.

## Main Endpoints

| Resource | Endpoints |
| --- | --- |
| Authentication | Register, login, logout |
| Heroes | Index, create, show, update, deactivate, heal |
| Quests | Index, show, create, update, deactivate |
| Missions | Index, create, show, complete, cancel |
| Profile | Show and update authenticated user profile |
| Users | Admin index, show, update, and delete |

## License

This project is licensed under the MIT License.