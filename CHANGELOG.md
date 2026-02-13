# Changelog

Toutes les modifications notables de ce projet seront documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet adhère au [Semantic Versioning](https://semver.org/lang/fr/).

## [Non publié]

### En cours
- Phase 3: Interface Admin - Gestion des Contacts

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
