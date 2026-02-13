# Guide de Développement - Prospection Claude

## Version Actuelle: 0.1.0

Date de dernière mise à jour: 2026-02-13

---

## Architecture du Plugin

### Principes de Conception

Le plugin utilise une **architecture en couches** avec les patterns suivants:

- **Repository Pattern**: Abstraction de l'accès aux données
- **Service Layer**: Logique métier centralisée
- **Factory Pattern**: Création des templates d'emails
- **Strategy Pattern**: Gestion des différents types de scheduling
- **MVC adapté**: Séparation présentation/logique/données

### Structure des Dossiers

```
prospection-claude/
├── prospection-claude.php          # Point d'entrée principal
├── uninstall.php                   # Nettoyage lors de la désinstallation
├── includes/
│   ├── class-activator.php        # Logique d'activation (création tables)
│   ├── class-deactivator.php      # Logique de désactivation
│   ├── class-plugin-core.php      # Classe principale du plugin
│   ├── Admin/                     # Interfaces d'administration
│   │   ├── class-admin-menu.php
│   │   ├── class-contact-manager.php
│   │   ├── class-campaign-manager.php
│   │   └── class-template-manager.php
│   ├── Models/                    # Entités métier
│   │   ├── class-contact.php
│   │   ├── class-campaign.php
│   │   ├── class-email-template.php
│   │   └── class-email-log.php
│   ├── Repositories/              # Accès base de données
│   │   ├── class-contact-repository.php
│   │   ├── class-campaign-repository.php
│   │   ├── class-template-repository.php
│   │   └── class-log-repository.php
│   ├── Services/                  # Logique métier
│   │   ├── class-email-service.php
│   │   ├── class-campaign-service.php
│   │   └── class-unsubscribe-service.php
│   ├── Cron/                      # Tâches planifiées
│   │   ├── class-cron-manager.php
│   │   └── class-campaign-scheduler.php
│   └── Helpers/                   # Utilitaires
│       ├── class-validator.php
│       └── class-formatter.php
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── public.css
│   ├── js/
│   │   ├── admin.js
│   │   └── public.js
│   └── images/
├── templates/                     # Vues HTML
│   ├── admin/                     # Interfaces admin
│   │   ├── contact-form.php
│   │   ├── contact-list.php
│   │   ├── campaign-form.php
│   │   ├── campaign-list.php
│   │   ├── template-editor.php
│   │   └── template-list.php
│   ├── emails/                    # Templates emails
│   │   └── base-template.php
│   └── public/                    # Pages publiques
│       └── unsubscribe-page.php
├── languages/                     # Fichiers de traduction
└── docs/                         # Documentation
    ├── DEVELOPMENT.md
    ├── API.md
    └── USER-GUIDE.md
```

---

## Base de Données

### Tables

#### 1. `wp_prospection_contacts`

Stocke les informations des contacts.

```sql
CREATE TABLE wp_prospection_contacts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    company VARCHAR(255),
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(50),
    category ENUM('micrologiciel', 'scientifique', 'informatique') NOT NULL,
    context TEXT,
    meeting_location VARCHAR(255),
    meeting_date DATE,
    is_subscribed TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_category (category),
    INDEX idx_subscribed (is_subscribed)
);
```

#### 2. `wp_prospection_email_templates`

Stocke les templates d'emails réutilisables.

```sql
CREATE TABLE wp_prospection_email_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    subject VARCHAR(500) NOT NULL,
    content LONGTEXT NOT NULL,
    category ENUM('micrologiciel', 'scientifique', 'informatique', 'all') DEFAULT 'all',
    variables TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category)
);
```

#### 3. `wp_prospection_campaigns`

Configuration des campagnes d'envoi automatique.

```sql
CREATE TABLE wp_prospection_campaigns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    template_id BIGINT UNSIGNED NOT NULL,
    target_categories TEXT NOT NULL,
    schedule_type ENUM('daily', 'weekly', 'monthly', 'custom') NOT NULL,
    schedule_config TEXT,
    next_run DATETIME,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES wp_prospection_email_templates(id) ON DELETE CASCADE,
    INDEX idx_active (is_active),
    INDEX idx_next_run (next_run)
);
```

#### 4. `wp_prospection_email_logs`

Historique des envois d'emails.

```sql
CREATE TABLE wp_prospection_email_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contact_id BIGINT UNSIGNED NOT NULL,
    campaign_id BIGINT UNSIGNED,
    template_id BIGINT UNSIGNED,
    subject VARCHAR(500),
    status ENUM('pending', 'sent', 'failed', 'bounced') DEFAULT 'pending',
    error_message TEXT,
    sent_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES wp_prospection_contacts(id) ON DELETE CASCADE,
    FOREIGN KEY (campaign_id) REFERENCES wp_prospection_campaigns(id) ON DELETE SET NULL,
    FOREIGN KEY (template_id) REFERENCES wp_prospection_email_templates(id) ON DELETE SET NULL,
    INDEX idx_contact (contact_id),
    INDEX idx_campaign (campaign_id),
    INDEX idx_status (status),
    INDEX idx_sent_at (sent_at)
);
```

---

## Plan de Développement par Phases

### **Phase 1: Infrastructure de Base** 🔨
**Branche**: `feature/infrastructure`
**Version**: 0.1.0

**Objectifs**:
- Structure de fichiers et dossiers
- Fichier principal du plugin avec headers WordPress
- Classes Activator et Deactivator
- Création des tables de base de données
- Classe Plugin Core (chargement, initialisation)
- Configuration de base (constantes, autoloader)

**Fichiers à créer**:
- `prospection-claude.php`
- `uninstall.php`
- `includes/class-activator.php`
- `includes/class-deactivator.php`
- `includes/class-plugin-core.php`

**Critères de succès**:
- ✅ Plugin activable/désactivable sans erreur
- ✅ Tables créées correctement à l'activation
- ✅ Tables supprimées à la désinstallation
- ✅ Aucune erreur PHP

---

### **Phase 2: Models et Repositories** 📦
**Branche**: `feature/data-layer`
**Version**: 0.2.0

**Objectifs**:
- Créer les classes Model (Contact, EmailTemplate, Campaign, EmailLog)
- Implémenter les Repositories pour chaque entité
- Méthodes CRUD de base
- Validation des données

**Fichiers à créer**:
- `includes/Models/class-contact.php`
- `includes/Models/class-email-template.php`
- `includes/Models/class-campaign.php`
- `includes/Models/class-email-log.php`
- `includes/Repositories/class-contact-repository.php`
- `includes/Repositories/class-template-repository.php`
- `includes/Repositories/class-campaign-repository.php`
- `includes/Repositories/class-log-repository.php`
- `includes/Helpers/class-validator.php`

**Critères de succès**:
- ✅ CRUD fonctionnel pour chaque entité
- ✅ Validation des emails, téléphones, etc.
- ✅ Échappement et sanitization des données
- ✅ Gestion des erreurs

---

### **Phase 3: Interface Admin - Gestion des Contacts** 👥
**Branche**: `feature/contact-management`
**Version**: 0.3.0

**Objectifs**:
- Menu d'administration principal
- Interface de liste des contacts (avec pagination, recherche, filtres)
- Formulaire d'ajout/édition de contact
- Suppression de contact (avec confirmation)
- Import/Export CSV (bonus)

**Fichiers à créer**:
- `includes/Admin/class-admin-menu.php`
- `includes/Admin/class-contact-manager.php`
- `templates/admin/contact-list.php`
- `templates/admin/contact-form.php`
- `assets/css/admin.css`
- `assets/js/admin.js`

**Critères de succès**:
- ✅ Menu "Prospection" dans l'admin WordPress
- ✅ Liste des contacts avec recherche et filtres
- ✅ Ajout/édition/suppression fonctionnels
- ✅ Interface responsive et intuitive
- ✅ Messages de succès/erreur appropriés

---

### **Phase 4: Interface Admin - Templates d'Emails** ✉️
**Branche**: `feature/email-templates`
**Version**: 0.4.0

**Objectifs**:
- Interface de gestion des templates
- Éditeur de template avec variables dynamiques
- Prévisualisation du template
- Variables disponibles: {first_name}, {last_name}, {company}, {unsubscribe_link}

**Fichiers à créer**:
- `includes/Admin/class-template-manager.php`
- `templates/admin/template-list.php`
- `templates/admin/template-editor.php`
- `includes/Helpers/class-formatter.php`
- `templates/emails/base-template.php`

**Critères de succès**:
- ✅ Liste des templates
- ✅ Création/édition de templates avec éditeur WYSIWYG
- ✅ Variables dynamiques fonctionnelles
- ✅ Prévisualisation avec données de test
- ✅ Validation du sujet et contenu

---

### **Phase 5: Interface Admin - Campagnes** 📅
**Branche**: `feature/campaign-management`
**Version**: 0.5.0

**Objectifs**:
- Interface de gestion des campagnes
- Configuration du scheduling (quotidien, hebdomadaire, mensuel, personnalisé)
- Sélection des catégories cibles
- Activation/désactivation de campagnes
- Vue de la prochaine exécution

**Fichiers à créer**:
- `includes/Admin/class-campaign-manager.php`
- `templates/admin/campaign-list.php`
- `templates/admin/campaign-form.php`

**Critères de succès**:
- ✅ Création de campagnes avec sélection de template
- ✅ Configuration du schedule intuitive
- ✅ Sélection multiple de catégories
- ✅ Calcul correct de la prochaine exécution
- ✅ Activation/désactivation rapide

---

### **Phase 6: Service d'Envoi d'Emails** 📧
**Branche**: `feature/email-service`
**Version**: 0.6.0

**Objectifs**:
- Service d'envoi utilisant WP Mail SMTP
- Remplacement des variables dans les templates
- Ajout automatique du lien de désinscription
- Gestion des erreurs d'envoi
- Logging dans la table email_logs

**Fichiers à créer**:
- `includes/Services/class-email-service.php`
- `includes/Services/class-unsubscribe-service.php`

**Critères de succès**:
- ✅ Envoi d'email fonctionnel via WP Mail SMTP
- ✅ Variables remplacées correctement
- ✅ Lien de désinscription inclus
- ✅ Logs créés pour chaque envoi
- ✅ Gestion des échecs d'envoi

---

### **Phase 7: Système de Cron et Automation** ⏰
**Branche**: `feature/cron-automation`
**Version**: 0.7.0

**Objectifs**:
- Intégration avec WP-Cron
- Vérification périodique des campagnes à exécuter
- Exécution automatique des envois
- Service de gestion des campagnes
- Calcul de la prochaine exécution

**Fichiers à créer**:
- `includes/Cron/class-cron-manager.php`
- `includes/Cron/class-campaign-scheduler.php`
- `includes/Services/class-campaign-service.php`

**Critères de succès**:
- ✅ Tâche cron enregistrée correctement
- ✅ Campagnes exécutées selon le schedule
- ✅ Gestion des contacts abonnés uniquement
- ✅ Mise à jour de next_run après exécution
- ✅ Gestion des erreurs sans bloquer les autres campagnes

---

### **Phase 8: Page de Désinscription** 🚫
**Branche**: `feature/unsubscribe`
**Version**: 0.8.0

**Objectifs**:
- Page publique de désinscription
- Token sécurisé pour identifier le contact
- Mise à jour du flag is_subscribed
- Page de confirmation
- Protection contre CSRF

**Fichiers à créer**:
- `templates/public/unsubscribe-page.php`
- `assets/css/public.css`

**Critères de succès**:
- ✅ Lien de désinscription fonctionnel
- ✅ Token sécurisé et validé
- ✅ Contact marqué comme désinscrit
- ✅ Page de confirmation claire
- ✅ Pas de suppression de la fiche contact

---

### **Phase 9: Logs et Reporting** 📊
**Branche**: `feature/logs-reporting`
**Version**: 0.9.0

**Objectifs**:
- Interface de visualisation des logs
- Statistiques d'envoi (total, succès, échecs)
- Filtres par contact, campagne, date, statut
- Export des logs en CSV
- Dashboard avec métriques

**Fichiers à créer**:
- `includes/Admin/class-log-viewer.php`
- `templates/admin/log-list.php`
- `templates/admin/dashboard.php`

**Critères de succès**:
- ✅ Liste des logs avec filtres
- ✅ Statistiques visuelles
- ✅ Export CSV fonctionnel
- ✅ Dashboard informatif

---

### **Phase 10: Tests, Documentation et Polish** ✨
**Branche**: `feature/finalization`
**Version**: 1.0.0

**Objectifs**:
- Tests manuels complets de toutes les fonctionnalités
- Correction des bugs découverts
- Documentation utilisateur complète
- Documentation API
- Internationalisation (i18n)
- Optimisations de performance
- Sécurité: audit et corrections

**Fichiers à créer**:
- `docs/USER-GUIDE.md`
- `docs/API.md`
- `languages/prospection-claude.pot`

**Critères de succès**:
- ✅ Toutes les fonctionnalités testées
- ✅ Aucun bug critique
- ✅ Documentation complète
- ✅ Prêt pour la production
- ✅ Version 1.0.0 stable

---

## Workflow de Développement

### Branches

- **`main`**: Branche stable, releases uniquement
- **`feature/*`**: Branches de fonctionnalités
- **`bugfix/*`**: Corrections de bugs
- **`hotfix/*`**: Corrections urgentes en production

### Processus

1. **Créer une branche** pour la phase en cours
   ```bash
   git checkout -b feature/nom-fonctionnalite
   ```

2. **Développer** la fonctionnalité avec commits réguliers
   ```bash
   git add .
   git commit -m "feat: description de la fonctionnalité"
   git push origin feature/nom-fonctionnalite
   ```

3. **Créer une Pull Request** sur GitHub

4. **Revue et validation** par le propriétaire du projet

5. **Merge dans main** après validation

6. **Tag de version** si phase complétée
   ```bash
   git tag -a v0.x.0 -m "Version 0.x.0: Description"
   git push origin v0.x.0
   ```

### Conventions de Commits

Utilisation de [Conventional Commits](https://www.conventionalcommits.org/):

- `feat:` Nouvelle fonctionnalité
- `fix:` Correction de bug
- `docs:` Documentation
- `style:` Formatage, pas de changement de code
- `refactor:` Refactorisation
- `test:` Ajout ou modification de tests
- `chore:` Maintenance, tâches diverses

**Exemples**:
```
feat: ajouter formulaire de création de contact
fix: corriger validation email dans contact repository
docs: mettre à jour guide utilisateur
refactor: extraire logique de validation dans helper
```

---

## Standards de Code

### PHP

- **PSR-12**: Style de code
- **PSR-4**: Autoloading
- **WordPress Coding Standards**: Respect des conventions WordPress
- **Validation**: Toujours utiliser `sanitize_*` et `esc_*`
- **Sécurité**: Vérifier nonces, capabilities, échapper les sorties

### JavaScript

- **ESLint**: Linting
- **ES6+**: Syntaxe moderne
- **jQuery**: Utilisation minimale, préférer vanilla JS

### CSS

- **BEM**: Naming convention
- **Mobile-first**: Design responsive

---

## Sécurité

### Checklist

- [ ] Vérification des capabilities WordPress (`current_user_can()`)
- [ ] Nonces pour tous les formulaires
- [ ] Sanitization des inputs (`sanitize_text_field()`, etc.)
- [ ] Échappement des outputs (`esc_html()`, `esc_attr()`, etc.)
- [ ] Requêtes préparées pour la base de données
- [ ] Protection CSRF
- [ ] Validation des emails et URLs
- [ ] Tokens sécurisés pour désinscription

---

## Notes de Version

### v0.1.0 (En cours)
- Initialisation du projet
- Structure de base
- Configuration git

---

## Ressources

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WP Mail SMTP Documentation](https://wpmailsmtp.com/docs/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)

---

**Dernière mise à jour**: 2026-02-13
**Statut**: Phase 1 - Infrastructure de Base
