<?php
/**
 * Gestion de la désactivation du plugin.
 *
 * @package ProspectionClaude
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe gérant la désactivation du plugin.
 *
 * Cette classe définit tout le code nécessaire lors de la désactivation du plugin,
 * notamment la suppression des tâches cron programmées.
 */
class Prospection_Claude_Deactivator {

	/**
	 * Méthode exécutée lors de la désactivation du plugin.
	 *
	 * Nettoie les tâches cron programmées pour éviter qu'elles continuent
	 * à s'exécuter après la désactivation du plugin.
	 * Note: Les données (tables, options) sont conservées pour permettre
	 * une réactivation sans perte de données.
	 */
	public static function deactivate() {
		// Supprimer les tâches cron programmées
		$timestamp = wp_next_scheduled( 'prospection_claude_check_campaigns' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'prospection_claude_check_campaigns' );
		}

		// Supprimer le hook cron personnalisé
		wp_clear_scheduled_hook( 'prospection_claude_check_campaigns' );

		// Log de désactivation (optionnel, pour debug)
		error_log( 'Prospection Claude: Plugin désactivé - Tâches cron supprimées' );
	}
}
