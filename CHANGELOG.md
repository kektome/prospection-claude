# Changelog

Toutes les modifications notables de ce projet seront documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet adhère au [Semantic Versioning](https://semver.org/lang/fr/).

## [Non publié]

### En cours
- Phase 6: Service Layer - Envoi d'Emails

---

## [0.5.0] - 2026-02-13

### Ajouté
- **Phase 5 complétée: Interface Admin - Gestion des Campagnes**
  - Interface complète de gestion des campagnes d'emails (CRUD)
  - Sélection de template avec liste déroulante
  - Gestion des catégories cibles (checkboxes multiples)
  - Configuration du scheduling (quotidien, hebdomadaire, mensuel, personnalisé)
  - Activation/désactivation des campagnes
  - Affichage de la prochaine exécution
  - Filtrage par statut (actives/inactives)

### Interface Admin
- **Classe Campaign_Manager**: Gestion CRUD des campagnes
  - render(): Point d'entrée principal
  - handle_create/update/delete/toggle(): Actions avec validation
  - calculate_next_run(): Calcul automatique selon scheduling
  - render_list(): Liste avec pagination/filtres
  - render_form(): Formulaire avec template selection
  - Messages d'erreur/succès avec transients

### Templates
- **campaign-list.php**: Table responsive des campagnes
  - Colonnes: Nom, Template, Catégories, Scheduling, Prochaine exécution, Statut
  - Badges colorés pour scheduling (bleu/vert/orange/violet)
  - Actions: Modifier, Activer/Désactiver, Supprimer
  - Filtre par statut avec dropdown

- **campaign-form.php**: Formulaire complet
  - Sélection de template (dropdown avec catégorie)
  - Catégories multiples (checkboxes)
  - Types scheduling (radio): quotidien, hebdomadaire, mensuel, personnalisé
  - Champ date/heure conditionnel pour personnalisé
  - Checkbox activation

### Styles et JavaScript
- **admin.css**: Badges scheduling colorés, champ date conditionnel stylé
- **admin.js**:
  * initCampaignsPage(): Initialisation
  * filterByStatus(): Filtre actives/inactives
  * toggleCustomDateField(): Show/hide date selon scheduling
  * Confirmation suppression
  * Animation slideDown/slideUp

### Logique Métier
- calculate_next_run(): Calcul selon type (daily: +1j, weekly: +1w, monthly: +1m, custom: date fournie)
- Sanitization catégories (array checkboxes) et config (JSON)
- Toggle active rapide avec nonce

### Notes
- Prêt pour Phase 6 (Envoi d'Emails)
- Compatible WordPress 5.8+

---

## [0.4.0] - 2026-02-13

### Ajouté
- **Phase 4 complétée: Interface Admin - Templates d'Emails**
  - Interface complète de gestion des templates d'emails (CRUD)
  - Liste des templates avec pagination et filtres
  - Formulaire d'ajout/édition avec éditeur riche (TinyMCE)
  - Système de variables dynamiques pour personnalisation
  - Prévisualisation et insertion facile des variables
  - Suppression avec confirmation
  - Filtrage par catégorie (micrologiciel, scientifique, informatique, tous)

### Interface Admin
- **Classe Template_Manager**: Gestion CRUD des templates d'emails
  - render(): Point d'entrée principal
  - handle_create(): Création avec validation
  - handle_update(): Mise à jour avec validation
  - handle_delete(): Suppression avec nonce
  - render_list(): Liste avec pagination/filtres
  - render_form(): Formulaire avec éditeur WYSIWYG
  - Messages d'erreur/succès avec transients

### Templates
- **template-list.php**: Table responsive des templates
  - Pagination avec navigation
  - Filtre par catégorie (dropdown)
  - Actions rapides (Modifier, Supprimer)
  - Badges catégories colorés (incluant "Tous")
  - Affichage du sujet tronqué
  - Message si aucun template

- **template-form.php**: Formulaire complet d'édition
  - Champs: Nom, Sujet, Catégorie, Contenu
  - Éditeur TinyMCE intégré pour le contenu HTML
  - Variables disponibles: {first_name}, {last_name}, {full_name}, {company}, {email}, {unsubscribe_link}
  - Boutons cliquables pour copier les variables
  - Toast de confirmation de copie
  - Validation HTML5 (required)
  - Boutons Sauvegarder/Annuler

### Styles et JavaScript
- **admin.css**: Styles pour l'interface des templates
  - Variables box avec boutons cliquables
  - Boutons de variables avec hover effects et descriptions
  - Toast de confirmation animé (slideInUp/slideOutDown)
  - Badge coloré pour catégorie "Tous" (violet)
  - Layout responsive pour la liste et le formulaire
  - Intégration propre avec TinyMCE

- **admin.js**: Fonctionnalités interactives des templates
  - initTemplatesPage(): Initialisation de la page
  - copyToClipboard(): Copie des variables dans le presse-papiers
  - fallbackCopyToClipboard(): Fallback pour anciens navigateurs
  - showCopiedToast(): Toast de confirmation de copie
  - Filtre par catégorie avec navigation
  - Confirmation avant suppression
  - Support des API modernes (Navigator.clipboard)

### Intégration
- **Admin_Menu**: Template Manager initialisé dans le constructeur
  - render_templates_page() utilise Template_Manager->render()
  - Intégration avec le système de permissions existant

- **Plugin_Core**: Chargement automatique du Template_Manager
  - Template_Manager chargé avec les autres classes Admin
  - Commentaires mis à jour (Phase 3 & 4)

### Sécurité
- Nonces pour tous les formulaires
- Vérification des capabilities (manage_prospection_templates)
- Sanitization du HTML avec wp_kses_post()
- Validation côté serveur ET client
- Protection CSRF
- Échappement des outputs dans les templates

### UX/UI
- Interface moderne et intuitive
- Éditeur WYSIWYG professionnel
- Variables cliquables avec descriptions
- Toast de confirmation élégant
- Badges colorés par catégorie
- Messages de feedback clairs
- Design responsive (mobile, tablette, desktop)

### Notes
- Interface de gestion des templates complète et opérationnelle
- Intégration parfaite avec le Model EmailTemplate et Template Repository existants
- Système de variables prêt pour l'envoi personnalisé
- Prêt pour Phase 5 (Campagnes)
- Aucune erreur PHP
- Compatible WordPress 5.8+

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
