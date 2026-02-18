# Haka

> Built at **Cursor AI Hackathon** in Vilnius.

A customer support system with AI-powered ticket processing.

## What it does

- **Ticket management** — create, view, and manage customer support requests
- **AI analysis** — automatically assigns priority, summary, and labels based on customer messages
- **Customer chat** — interface for correspondence with AI reply suggestions
- **Customizable rules** — priorities and labels with prompts that define classification rules
- **Data import** — load tickets from a JSON file

## Tech stack

| Category  | Stack |
|-----------|-------|
| Backend   | PHP 8.2+, Symfony 7.3 |
| Database  | PostgreSQL, Doctrine ORM, migrations |
| AI        | DeepSeek API (deepseek-php/deepseek-php-client) |
| Frontend  | Twig, Bootstrap, Webpack Encore |
| HTTP      | Guzzle, Nyholm PSR-7, Symfony HTTP Client |

## Installation

```bash
make install    # Install dependencies (composer, npm, build assets)
make setup      # Create DB, run migrations, load fixtures
make start      # Start Symfony server + Docker (PostgreSQL)
```

## Commands

| Command | Description |
|---------|-------------|
| `php bin/console app:import` | Import tickets from test.json |
| `php bin/console app:process` | Process all tickets via AI (priority, summary, labels) |

## Main entities

- **Issue** — support ticket with title, summary, priority, and labels
- **Message** — chat message (from customer or support)
- **Priority** — priority level with AI classification rule (prompt)
- **Label** — label with AI rule and color

## Hackathon

This project was developed at **Cursor AI Hackathon** in Vilnius.
