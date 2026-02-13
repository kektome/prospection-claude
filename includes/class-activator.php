<?php
/**
 * Gestion de l'activation du plugin.
 *
 * @package ProspectionClaude
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe gérant l'activation du plugin.
 *
 * Cette classe définit tout le code nécessaire lors de l'activation du plugin,
 * notamment la création des tables de base de données.
 */
class Prospection_Claude_Activator {

	/**
	 * Méthode exécutée lors de l'activation du plugin.
	 *
	 * Crée les tables de base de données nécessaires au fonctionnement du plugin.
	 */
	public static function activate() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_prefix    = $wpdb->prefix . PROSPECTION_CLAUDE_TABLE_PREFIX;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Table des contacts
		$table_contacts = $table_prefix . 'contacts';
		$sql_contacts   = "CREATE TABLE $table_contacts (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			first_name varchar(100) NOT NULL,
			last_name varchar(100) NOT NULL,
			company varchar(255) DEFAULT NULL,
			email varchar(255) NOT NULL,
			phone varchar(50) DEFAULT NULL,
			category enum('micrologiciel','scientifique','informatique') NOT NULL,
			context text DEFAULT NULL,
			meeting_location varchar(255) DEFAULT NULL,
			meeting_date date DEFAULT NULL,
			is_subscribed tinyint(1) DEFAULT 1,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY email (email),
			KEY idx_email (email),
			KEY idx_category (category),
			KEY idx_subscribed (is_subscribed)
		) $charset_collate;";

		dbDelta( $sql_contacts );

		// Table des templates d'emails
		$table_templates = $table_prefix . 'email_templates';
		$sql_templates   = "CREATE TABLE $table_templates (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			subject varchar(500) NOT NULL,
			content longtext NOT NULL,
			category enum('micrologiciel','scientifique','informatique','all') DEFAULT 'all',
			variables text DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY idx_category (category)
		) $charset_collate;";

		dbDelta( $sql_templates );

		// Table des campagnes
		$table_campaigns = $table_prefix . 'campaigns';
		$sql_campaigns   = "CREATE TABLE $table_campaigns (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			template_id bigint(20) UNSIGNED NOT NULL,
			target_categories text NOT NULL,
			schedule_type enum('daily','weekly','monthly','custom') NOT NULL,
			schedule_config text DEFAULT NULL,
			next_run datetime DEFAULT NULL,
			is_active tinyint(1) DEFAULT 1,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY idx_template_id (template_id),
			KEY idx_active (is_active),
			KEY idx_next_run (next_run)
		) $charset_collate;";

		dbDelta( $sql_campaigns );

		// Table des logs d'emails
		$table_logs = $table_prefix . 'email_logs';
		$sql_logs   = "CREATE TABLE $table_logs (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			contact_id bigint(20) UNSIGNED NOT NULL,
			campaign_id bigint(20) UNSIGNED DEFAULT NULL,
			template_id bigint(20) UNSIGNED DEFAULT NULL,
			subject varchar(500) DEFAULT NULL,
			status enum('pending','sent','failed','bounced') DEFAULT 'pending',
			error_message text DEFAULT NULL,
			sent_at datetime DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY idx_contact_id (contact_id),
			KEY idx_campaign_id (campaign_id),
			KEY idx_template_id (template_id),
			KEY idx_status (status),
			KEY idx_sent_at (sent_at)
		) $charset_collate;";

		dbDelta( $sql_logs );

		// Enregistrer la version du plugin
		add_option( 'prospection_claude_version', PROSPECTION_CLAUDE_VERSION );

		// Enregistrer la date d'installation
		add_option( 'prospection_claude_installed_date', current_time( 'mysql' ) );

		// Créer les capacités personnalisées si nécessaire (pour phases futures)
		self::add_custom_capabilities();
	}

	/**
	 * Ajoute les capacités personnalisées pour la gestion du plugin.
	 *
	 * Permet de contrôler qui peut gérer les contacts, campagnes, etc.
	 */
	private static function add_custom_capabilities() {
		$role = get_role( 'administrator' );

		if ( $role ) {
			$role->add_cap( 'manage_prospection_contacts' );
			$role->add_cap( 'manage_prospection_campaigns' );
			$role->add_cap( 'manage_prospection_templates' );
			$role->add_cap( 'view_prospection_logs' );
		}
	}
}
