<?php
/**
 * Gestion de la désinstallation du plugin.
 *
 * Ce fichier est exécuté lorsque le plugin est désinstallé (supprimé) depuis WordPress.
 * Il supprime toutes les données du plugin: tables, options, capacités, etc.
 *
 * @package ProspectionClaude
 */

// Si la désinstallation n'est pas appelée depuis WordPress, quitter.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Supprime toutes les données du plugin.
 *
 * Cette fonction est responsable de nettoyer complètement toutes les traces
 * du plugin dans la base de données.
 */
function prospection_claude_uninstall() {
	global $wpdb;

	// Préfixe des tables
	$table_prefix = $wpdb->prefix . 'prospection_';

	// Supprimer toutes les tables du plugin
	$tables = array(
		$table_prefix . 'email_logs',
		$table_prefix . 'campaigns',
		$table_prefix . 'email_templates',
		$table_prefix . 'contacts',
	);

	foreach ( $tables as $table ) {
		$wpdb->query( "DROP TABLE IF EXISTS $table" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	// Supprimer toutes les options du plugin
	delete_option( 'prospection_claude_version' );
	delete_option( 'prospection_claude_installed_date' );

	// Supprimer toutes les options qui commencent par 'prospection_claude_'
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
			$wpdb->esc_like( 'prospection_claude_' ) . '%'
		)
	);

	// Supprimer les capacités personnalisées
	$role = get_role( 'administrator' );
	if ( $role ) {
		$role->remove_cap( 'manage_prospection_contacts' );
		$role->remove_cap( 'manage_prospection_campaigns' );
		$role->remove_cap( 'manage_prospection_templates' );
		$role->remove_cap( 'view_prospection_logs' );
	}

	// Supprimer les tâches cron (au cas où elles existeraient encore)
	wp_clear_scheduled_hook( 'prospection_claude_check_campaigns' );

	// Log de désinstallation (optionnel, pour debug)
	error_log( 'Prospection Claude: Plugin désinstallé - Toutes les données supprimées' );
}

// Exécuter la désinstallation
prospection_claude_uninstall();
