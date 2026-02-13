# Prospection Claude - Plugin WordPress

Plugin de gestion de contacts et d'envoi automatisé de newsletters pour les prospects rencontrés en congrès.

## Description

Ce plugin permet de:
- Gérer des fiches contacts de prospects rencontrés dans des congrès
- Catégoriser les contacts par équipe (Micrologiciel, Scientifique, Informatique)
- Créer des templates d'emails personnalisés
- Programmer des envois automatiques de newsletters par catégorie
- Gérer les désinscriptions (opt-out) tout en conservant les fiches contacts

## Prérequis

- WordPress 5.8 ou supérieur
- PHP 7.4 ou supérieur
- MySQL 5.6 ou supérieur
- Plugin **WP Mail SMTP** installé et configuré

## Installation

1. Télécharger le plugin
2. Placer le dossier dans `/wp-content/plugins/`
3. Activer le plugin dans l'interface d'administration WordPress
4. S'assurer que WP Mail SMTP est configuré

## Fonctionnalités

### Gestion des Contacts
- Création, modification, suppression de fiches contacts
- Informations: nom, entreprise, email, téléphone, contexte de discussion, lieu de rencontre
- Catégorisation par équipe
- Gestion du statut d'abonnement

### Templates d'Emails
- Création de templates personnalisés
- Variables dynamiques (nom, entreprise, etc.)
- Prévisualisation avant envoi

### Campagnes Automatisées
- Programmation d'envois récurrents (hebdomadaire, mensuel, personnalisé)
- Ciblage par catégorie
- Logs complets des envois

### Désinscription
- Lien de désinscription dans chaque email
- Conservation de la fiche contact avec flag de désinscription
- Page de confirmation de désinscription

## Documentation

- [Guide de développement](docs/DEVELOPMENT.md)
- [Guide utilisateur](docs/USER-GUIDE.md) _(à venir)_
- [Documentation API](docs/API.md) _(à venir)_

## Versioning

Ce projet suit les principes du [Semantic Versioning](https://semver.org/):
- **MAJOR**: Changements incompatibles avec les versions précédentes
- **MINOR**: Ajout de fonctionnalités compatibles
- **PATCH**: Corrections de bugs

Version actuelle: **0.1.0** (Développement initial)

## Auteur

Développé avec Claude Code (Anthropic)

## Licence

Propriétaire - Tous droits réservés
