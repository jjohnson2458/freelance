# claude_freelance

Multi-platform freelance proposal optimizer powered by Claude API.

## Tech Stack
- PHP 8.1+ custom MVC framework
- MySQL database: `freelance`
- Bootstrap 5, vanilla JS, left sidebar nav
- Session-based auth
- Claude API (Anthropic) for proposal generation
- claude_messenger for email delivery

## Project Structure
- `core/` — Framework classes (Controller, Model, Router, Auth, Csrf, Env, Database, ErrorHandler)
- `config/` — App config, database config, routes
- `app/Controllers/` — Request handlers
- `app/Models/` — Data models extending Core\Model
- `app/Services/` — Business logic (ClaudeApiService, ProposalGenerator, parsers)
- `app/Views/` — PHP templates with Bootstrap 5
- `public/` — Web root (index.php, css/, js/, uploads/)
- `migrations/` — SQL migration files + runner
- `data/` — Fallback flat-file storage

## Key URLs
- Local: http://freelance.local
- Production: https://freelance.visionquest2020.net

## Database
Run `php migrations/migrate.php` to create/update tables.
Run `php migrations/seed.php` to seed admin user and default rules.

## Testing
- PHPUnit tests in `tests/`
- Run: `php vendor/bin/phpunit` or `php tests/run.php`

## Conventions
- All controllers extend `Core\Controller`, call `$this->requireAuth()` for protected routes
- CSRF protection on all POST forms via `Core\Csrf::field()` and `Csrf::verifyOrFail()`
- Flash messages via `$this->flash('success', 'message')` and `$this->getFlash('success')`
- Models use static methods: `::find()`, `::all()`, `::where()`, `::create()`, `::update()`, `::delete()`
