# Changelog

Toutes les modifications notables de ce projet seront documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet adhère au [Semantic Versioning](https://semver.org/lang/fr/).

## [Non publié]

### En cours
- Phase 4: Interface Admin - Templates d'Emails

---

## [0.3.0] - 2026-02-13

### Ajouté
- **Phase 3 complétée: Interface Admin - Gestion des Contacts**
  - Menu d'administration principal avec sous-menus
  - Dashboard avec statistiques (total contacts, abonnés, désabonnés, emails envoyés)
  - Actions rapides pour accès direct aux fonctionnalités
  - Interface complète de gestion des contacts (CRUD)
  - Liste des contacts avec pagination, recherche et filtres
  - Formulaire d'ajout/édition de contact
  - Suppression avec confirmation
  - Filtrage par catégorie (micrologiciel, scientifique, informatique)
  - Recherche par nom, email, entreprise
  - Badges visuels pour catégories et statuts
  - Validation côté client et serveur
  - Messages de succès/erreur

### Interface Admin
- **Classe Admin_Menu**: Gestion du menu principal et sous-menus
  - Menu "Prospection" dans l'admin WordPress
  - Sous-menus: Dashboard, Contacts, Templates, Campagnes, Logs
  - Vérification des capabilities WordPress
  - Dashboard avec cartes statistiques et actions rapides

- **Classe Contact_Manager**: Gestion CRUD des contacts
  - render(): Point d'entrée principal
  - handle_create(): Création avec validation
  - handle_update(): Mise à jour avec validation
  - handle_delete(): Suppression avec nonce
  - render_list(): Liste avec pagination/recherche/filtres
  - render_form(): Formulaire ajout/édition
  - Messages d'erreur/succès avec transients

### Templates
- **contact-list.php**: Table responsive des contacts
  - Pagination avec navigation
  - Recherche full-text
  - Filtre par catégorie (dropdown)
  - Actions rapides (Modifier, Supprimer)
  - Badges catégories et statuts
  - Message si aucun contact

- **contact-form.php**: Formulaire complet
  - Champs: Prénom, Nom, Entreprise, Email, Téléphone
  - Catégorie (select), Contexte (textarea)
  - Lieu et date de rencontre
  - Checkbox abonnement avec description
  - Validation HTML5 (required, email, tel, date)
  - Boutons Sauvegarder/Annuler

### Styles et JavaScript
- **admin.css**: Styles complets pour l'interface
  - Dashboard: Grid responsive, cartes stats avec icônes
  - Actions rapides avec hover effects
  - Table contacts: Badges colorés, row actions
  - Formulaire: Layout propre, champs alignés
  - Responsive: Mobile-first design
  - Variables CSS pour cohérence

- **admin.js**: Fonctionnalités interactives
  - Filtre par catégorie avec navigation
  - Confirmation avant suppression
  - Validation formulaire côté client
  - Validation email et téléphone
  - Auto-redirection après filtrage

### Sécurité
- Nonces pour tous les formulaires
- Vérification des capabilities (manage_prospection_contacts)
- Sanitization de tous les inputs (sanitize_*, esc_*)
- Validation côté serveur ET client
- Protection CSRF
- Échappement des outputs dans les templates

### UX/UI
- Interface moderne et intuitive
- Badges colorés par catégorie
- Messages de feedback clairs
- Pagination fluide
- Recherche instantanée
- Filtres intuitifs
- Design responsive (mobile, tablette, desktop)

### Notes
- Interface admin complète et opérationnelle
- Prêt pour Phase 4 (Templates d'Emails)
- Aucune erreur PHP
- Compatible WordPress 5.8+

---

## [0.2.0] - 2026-02-13

### Ajouté
- **Phase 2 complétée: Models et Repositories**
  - Helper `Validator` avec méthodes de validation et sanitization
  - Model `Contact` avec validation complète
  - Model `EmailTemplate` avec remplacement de variables
  - Model `Campaign` avec gestion du scheduling
  - Model `EmailLog` avec gestion des statuts
  - Repository `Contact` avec CRUD complet + recherche/filtres
  - Repository `Template` avec CRUD et filtrage par catégorie
  - Repository `Campaign` avec recherche des campagnes à exécuter
  - Repository `Log` avec statistiques d'envoi

### Technique
- Pattern Repository implémenté pour abstraction de la couche données
- Validation et sanitization systématiques (sanitize_*, esc_*, wp_kses)
- Méthodes de recherche avec pagination
- Support complet des 3 catégories (micrologiciel, scientifique, informatique)
- Variables dynamiques dans les templates: {first_name}, {last_name}, {company}, etc.
- Méthodes de comptage et statistiques
- Protection contre injection SQL avec $wpdb->prepare()
- Chargement automatique des classes dans Plugin_Core

### Models
- **Contact**: Validation email/téléphone, catégorie, dates
- **EmailTemplate**: Variables remplaçables, sanitization HTML
- **Campaign**: Gestion types de scheduling (daily, weekly, monthly, custom)
- **EmailLog**: Tracking statuts (pending, sent, failed, bounced)

### Repositories - Méthodes CRUD
- `create()`: Création avec validation
- `find_by_id()`: Récupération par ID
- `find_all()`: Liste avec pagination et tri
- `update()`: Mise à jour avec validation
- `delete()`: Suppression
- `count()`: Comptage avec filtres

### Repositories - Méthodes Spécifiques
- **Contact**: search(), find_by_category(), find_by_email(), unsubscribe()
- **Template**: find_by_category()
- **Campaign**: find_active(), find_due_campaigns(), toggle_active()
- **Log**: find_by_contact(), find_by_campaign(), find_by_status(), get_statistics()

### Notes
- Couche de données complète et opérationnelle
- Prêt pour Phase 3 (Interface Admin - Gestion des Contacts)
- Aucune interface utilisateur encore (phases 3-5)

---

## [0.1.0] - 2026-02-13

### Ajouté
- Initialisation du projet
- Structure de documentation (README.md, DEVELOPMENT.md, CHANGELOG.md)
- Configuration git (.gitignore)
- Plan de développement en 10 phases
- Architecture du plugin définie
- **Phase 1 complétée: Infrastructure de Base**
  - Structure complète de dossiers (includes, assets, templates, etc.)
  - Fichier principal `prospection-claude.php` avec headers WordPress
  - Classe `Activator` avec création automatique des 4 tables DB
  - Classe `Deactivator` avec nettoyage des tâches cron
  - Classe `Plugin_Core` pour initialisation et gestion des hooks
  - Fichier `uninstall.php` pour suppression complète des données
  - Fichiers assets de base (CSS/JS admin et public)
  - Capacités personnalisées WordPress (manage_prospection_*)
  - Support i18n avec domaine de traduction

### Technique
- Tables DB: `prospection_contacts`, `prospection_email_templates`, `prospection_campaigns`, `prospection_email_logs`
- Utilisation de `dbDelta()` pour création/mise à jour des tables
- Hooks d'activation/désactivation/désinstallation fonctionnels
- Architecture PSR-4 ready (autoloading préparé pour phases suivantes)
- Syntaxe PHP validée sans erreurs

### Notes
- Plugin activable/désactivable sans erreur
- Tables créées correctement à l'activation
- Prêt pour Phase 2 (Models et Repositories)

---

## Types de changements
- `Ajouté` pour les nouvelles fonctionnalités
- `Modifié` pour les changements aux fonctionnalités existantes
- `Déprécié` pour les fonctionnalités qui seront retirées
- `Retiré` pour les fonctionnalités retirées
- `Corrigé` pour les corrections de bugs
- `Sécurité` pour les vulnérabilités corrigées
