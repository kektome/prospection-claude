# Changelog

Toutes les modifications notables de ce projet seront documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet adhère au [Semantic Versioning](https://semver.org/lang/fr/).

## [Non publié]

### En cours
- Phase 2: Models et Repositories

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
