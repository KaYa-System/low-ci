# 🏛️ LégisCI - Législation Ivoirienne

Une application web moderne pour consulter et comprendre la législation de la République de Côte d'Ivoire, avec assistant IA intégré.

![Vue d'ensemble](https://via.placeholder.com/800x400/4F46E5/FFFFFF?text=LégisCI+-+Législation+Ivoirienne)

## ✨ Fonctionnalités

### 🔍 **Consultation de la législation**
- Navigation intuitive par catégories juridiques
- Consultation détaillée des documents légaux
- Structure hiérarchique (sections, articles)
- Recherche full-text avancée

### 🤖 **Assistant IA Juridique**
- Chat interactif pour comprendre la législation
- Réponses contextualisées basées sur les textes officiels
- Références automatiques aux documents pertinents
- Interface conversationnelle moderne

### 📊 **Base de données complète**
- Constitution de 2016
- Codes (travail, pénal, civil, commercial)
- Lois, décrets, arrêtés, ordonnances
- Métadonnées complètes (dates, références JO)

### 🛠️ **Scraping automatisé**
- Commandes Artisan pour collecter la législation
- Support pour sources multiples (sites gouvernementaux, Journal Officiel)
- Mise à jour automatique du contenu

## 🏗️ Architecture technique

### Backend (Laravel 12)
- **API REST** complète avec routes resourceful
- **Modèles Eloquent** avec relations complexes
- **Scout** pour la recherche full-text
- **Migrations** pour structure de données hiérarchique
- **Commandes Artisan** pour le scraping

### Frontend (Vue 3 + TypeScript)
- **Inertia.js** pour SPA experience
- **Tailwind CSS** pour design moderne
- **Lucide Icons** pour iconographie
- **Composants réactifs** avec Composition API

### Base de données (SQLite/MySQL)
```
legal_categories (hiérarchique)
├── legal_documents
    ├── legal_sections (hiérarchique)
    └── legal_articles
    
ai_chat_sessions
├── ai_chat_messages
```

## 🚀 Installation et déploiement

### Prérequis
- PHP 8.2+
- Node.js 18+
- Composer
- SQLite ou MySQL

### Installation
```bash
# Cloner le repository
git clone https://github.com/your-org/low-ci.git
cd low-ci

# Installer les dépendances PHP
composer install

# Installer les dépendances Node.js
npm install

# Configuration
cp .env.example .env
php artisan key:generate

# Base de données
php artisan migrate
php artisan db:seed

# Compiler les assets
npm run build

# Démarrer le serveur
php artisan serve
```

### Scraping de la législation
```bash
# Données de démonstration
php artisan legislation:scrape --source=mock --limit=10

# Sources officielles (à implémenter)
php artisan legislation:scrape --source=gouv --limit=100
php artisan legislation:scrape --source=jo --limit=50
```

## 📡 API Documentation

### Endpoints principaux

#### Législation
```
GET /api/legal/categories           # Liste des catégories
GET /api/legal/categories/{slug}    # Détails d'une catégorie
GET /api/legal/documents           # Liste des documents
GET /api/legal/documents/{slug}    # Détails d'un document
GET /api/legal/documents/featured  # Documents vedettes
GET /api/legal/documents/recent    # Documents récents
```

#### Recherche
```
GET /api/legal/search              # Recherche générale
GET /api/legal/search/suggestions  # Suggestions de recherche
GET /api/legal/search/advanced     # Recherche avancée avec filtres
```

#### Chat IA
```
POST /api/ai/chat/sessions                        # Créer session
GET /api/ai/chat/sessions/{session_id}           # Détails session
POST /api/ai/chat/sessions/{session_id}/messages # Envoyer message
GET /api/ai/chat/sessions/{session_id}/messages  # Historique messages
```

## 🎨 Interface utilisateur

### Design System
- **Couleurs** : Palette indigo/purple avec accents
- **Typography** : Inter font system
- **Composants** : Design system cohérent
- **Responsive** : Mobile-first approach

### Pages principales
- **Accueil** : Vue d'ensemble avec recherche et catégories
- **Document** : Consultation détaillée avec table des matières
- **Chat IA** : Interface conversationnelle moderne
- **Recherche** : Résultats paginés avec filtres

## 🔧 Structure du code

```
app/
├── Console/Commands/
│   └── ScrapeIvoirianLegislation.php
├── Http/Controllers/Api/
│   ├── LegalCategoryController.php
│   ├── LegalDocumentController.php
│   ├── SearchController.php
│   └── AiChatController.php
├── Models/
│   ├── LegalCategory.php
│   ├── LegalDocument.php
│   ├── LegalSection.php
│   ├── LegalArticle.php
│   ├── AiChatSession.php
│   └── AiChatMessage.php

resources/js/
├── layouts/
│   └── LegalLayout.vue
├── pages/
│   ├── Home.vue
│   ├── AiChat.vue
│   └── legal/
│       └── DocumentView.vue

database/
├── migrations/
└── seeders/
    └── LegalDataSeeder.php
```

## 🤝 Contribution

### Développement local
```bash
# Démarrer en mode développement
npm run dev
php artisan serve

# Tests
php artisan test

# Linting
npm run lint
```

### Roadmap
- [ ] Intégration Scout avec Meilisearch
- [ ] Scraping sites gouvernementaux réels
- [ ] Authentification utilisateurs
- [ ] Favoris et annotations
- [ ] API publique documentée
- [ ] Application mobile (Vue/Capacitor)
- [ ] Notifications de nouveaux textes

## 📝 Licence

Ce projet est sous licence MIT. Voir [LICENSE](LICENSE) pour plus de détails.

## 🙏 Remerciements

- République de Côte d'Ivoire pour l'accès aux textes officiels
- Communauté Laravel et Vue.js
- Contributeurs open source

---

**LégisCI** - Démocratiser l'accès au droit ivoirien 🇨🇮

*Une initiative pour rendre la législation accessible à tous les citoyens.*