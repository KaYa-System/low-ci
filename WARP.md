# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Development Commands

### Setup and Installation
```bash
# Full project setup
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm run build

# Development environment with hot reload
composer run dev
```

### Testing
```bash
# Run all tests
php artisan test

# Run tests using Pest (preferred test runner)
./vendor/bin/pest

# Run specific test file
./vendor/bin/pest tests/Feature/LegalDocumentTest.php

# Run tests with coverage
./vendor/bin/pest --coverage
```

### Code Quality
```bash
# Format PHP code (Laravel Pint)
./vendor/bin/pint

# Lint and format JavaScript/Vue
npm run lint
npm run format

# Check formatting without changes
npm run format:check
```

### Data Management
```bash
# Seed database with mock legislation data
php artisan legislation:scrape --source=mock --limit=10

# Force refresh all documents (development)
php artisan legislation:scrape --source=mock --limit=50 --force

# Clear application cache
php artisan optimize:clear
```

### Asset Building
```bash
# Development build with hot reload
npm run dev

# Production build
npm run build

# SSR build (if needed)
npm run build:ssr
```

## Architecture Overview

### Technology Stack
- **Backend**: Laravel 12 with PHP 8.2+
- **Frontend**: Vue 3 + TypeScript + Inertia.js 
- **Database**: SQLite (development) / MySQL (production)
- **Search**: Laravel Scout (configured for full-text search)
- **Styling**: Tailwind CSS 4.x
- **Build**: Vite with Laravel integration

### Core Domain Models

**LegalDocument** is the central entity with hierarchical structure:
```
LegalCategory (hierarchical)
├── LegalDocument (title, type, content, metadata)
    ├── LegalSection (nested sections support)
    └── LegalArticle (individual articles)

AiChatSession
├── AiChatMessage (conversation history)
```

Document types: `loi`, `decret`, `arrete`, `ordonnance`, `constitution`, `code`, `autre`

### Key Features
1. **Full-text search** via Scout integration
2. **AI Chat Assistant** with Hugging Face API integration 
3. **Hierarchical document structure** with sections and articles
4. **Automated scraping system** via Artisan commands
5. **SPA experience** with Inertia.js (no separate API calls needed for UI)

### API Architecture
- **Public API** at `/api/legal/*` for legislation queries
- **AI Chat API** at `/api/ai/*` for chat functionality  
- **Web routes** use Inertia.js for SPA experience
- Scout handles search indexing automatically

### Frontend Structure
- **Layout**: `LegalLayout.vue` for main app structure
- **Pages**: Located in `resources/js/pages/` 
- **Components**: Reusable UI components in `resources/js/components/`
- **Styling**: Tailwind with custom design system

### Database Schema Key Points
- Legal documents support soft deletes
- Hierarchical categories with parent/child relationships
- Full metadata storage as JSON columns
- Optimized indexes for search and filtering
- View counting and featured document support

### AI Integration
- Hugging Face API with fallback to mock responses
- Session-based chat history
- Context-aware responses using legal document content
- Configurable models (DialoGPT-medium recommended)

## Important Development Notes

### Environment Configuration
- **Required**: `HUGGINGFACE_API_KEY` for AI features (falls back to mock data)
- **Database**: Uses SQLite by default, easily switchable to MySQL
- **Scout**: Currently configured for database search, can be switched to Meilisearch

### Model Relationships
- LegalDocument uses `slug` as route key (not ID)
- Categories support unlimited nesting depth
- Articles and sections have `sort_order` for proper display
- Scout searchable integration on LegalDocument model

### Frontend Patterns
- Use Inertia.js for page navigation (not traditional API calls)
- Components follow Composition API pattern
- TypeScript is enabled and should be used for new code
- Tailwind classes follow the project's design system

### Scraping System
- Command: `php artisan legislation:scrape`
- Currently supports mock data generation
- Designed for extension to real government sources
- Handles duplicate detection and updates

### Testing Strategy
- Uses Pest for PHP testing (not PHPUnit syntax)
- Test files in `tests/Feature/` and `tests/Unit/`
- Database uses in-memory SQLite for tests
- Vue components can be tested with frontend tools

## Project Context
This is **LégisCI**, a legal document consultation platform for Côte d'Ivoire legislation. The application provides:
- Searchable database of legal documents
- AI-powered legal assistant 
- Hierarchical document browsing
- Modern SPA interface

The codebase follows Laravel conventions with Vue 3 frontend, designed for easy extension and maintenance.