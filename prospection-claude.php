<?php
/**
 * Plugin Name:       Prospection Claude
 * Plugin URI:        https://github.com/kektome/prospection-claude
 * Description:       Plugin de gestion de contacts et d'envoi automatisé de newsletters pour les prospects rencontrés en congrès.
 * Version:           0.1.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Développé avec Claude Code
 * Author URI:        https://claude.ai
 * License:           Proprietary
 * Text Domain:       prospection-claude
 * Domain Path:       /languages
 *
 * @package ProspectionClaude
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Version actuelle du plugin.
 */
define( 'PROSPECTION_CLAUDE_VERSION', '0.1.0' );

/**
 * Chemin du plugin.
 */
define( 'PROSPECTION_CLAUDE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * URL du plugin.
 */
define( 'PROSPECTION_CLAUDE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Nom de base du plugin.
 */
define( 'PROSPECTION_CLAUDE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Préfixe des tables de la base de données.
 */
define( 'PROSPECTION_CLAUDE_TABLE_PREFIX', 'prospection_' );

/**
 * Code exécuté lors de l'activation du plugin.
 */
function activate_prospection_claude() {
	require_once PROSPECTION_CLAUDE_PLUGIN_DIR . 'includes/class-activator.php';
	Prospection_Claude_Activator::activate();
}

/**
 * Code exécuté lors de la désactivation du plugin.
 */
function deactivate_prospection_claude() {
	require_once PROSPECTION_CLAUDE_PLUGIN_DIR . 'includes/class-deactivator.php';
	Prospection_Claude_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_prospection_claude' );
register_deactivation_hook( __FILE__, 'deactivate_prospection_claude' );

/**
 * La classe principale du plugin.
 */
require_once PROSPECTION_CLAUDE_PLUGIN_DIR . 'includes/class-plugin-core.php';

/**
 * Commence l'exécution du plugin.
 */
function run_prospection_claude() {
	$plugin = new Prospection_Claude_Plugin_Core();
	$plugin->run();
}

run_prospection_claude();
