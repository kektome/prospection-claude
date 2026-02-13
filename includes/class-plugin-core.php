<?php
/**
 * Classe principale du plugin.
 *
 * @package ProspectionClaude
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe principale du plugin Prospection Claude.
 *
 * Cette classe est responsable de:
 * - Charger les dépendances
 * - Définir la locale pour l'internationalisation
 * - Enregistrer les hooks admin et publics
 * - Initialiser les composants du plugin
 */
class Prospection_Claude_Plugin_Core {

	/**
	 * Version du plugin.
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Slug du plugin.
	 *
	 * @var string
	 */
	protected $plugin_slug;

	/**
	 * Constructeur de la classe.
	 *
	 * Initialise les propriétés et charge les dépendances.
	 */
	public function __construct() {
		$this->version     = PROSPECTION_CLAUDE_VERSION;
		$this->plugin_slug = 'prospection-claude';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Charge toutes les dépendances du plugin.
	 *
	 * Cette méthode inclut les fichiers nécessaires au fonctionnement du plugin.
	 * Phase 2: Chargement des Helpers, Models et Repositories.
	 * Phase 3: Chargement des classes Admin.
	 */
	private function load_dependencies() {
		// Charger le Helper Validator
		require_once PROSPECTION_CLAUDE_PLUGIN_DIR . 'includes/Helpers/class-validator.php';

		// Charger les Models
		require_once PROSPECTION_CLAUDE_PLUGIN_DIR . 'includes/Models/class-contact.php';
		require_once PROSPECTION_CLAUDE_PLUGIN_DIR . 'includes/Models/class-email-template.php';
		require_once PROSPECTION_CLAUDE_PLUGIN_DIR . 'includes/Models/class-campaign.php';
		require_once PROSPECTION_CLAUDE_PLUGIN_DIR . 'includes/Models/class-email-log.php';

		// Charger les Repositories
		require_once PROSPECTION_CLAUDE_PLUGIN_DIR . 'includes/Repositories/class-contact-repository.php';
		require_once PROSPECTION_CLAUDE_PLUGIN_DIR . 'includes/Repositories/class-template-repository.php';
		require_once PROSPECTION_CLAUDE_PLUGIN_DIR . 'includes/Repositories/class-campaign-repository.php';
		require_once PROSPECTION_CLAUDE_PLUGIN_DIR . 'includes/Repositories/class-log-repository.php';

		// Charger les classes Admin (Phase 3, 4 & 5)
		if ( is_admin() ) {
			require_once PROSPECTION_CLAUDE_PLUGIN_DIR . 'includes/Admin/class-admin-menu.php';
			require_once PROSPECTION_CLAUDE_PLUGIN_DIR . 'includes/Admin/class-contact-manager.php';
			require_once PROSPECTION_CLAUDE_PLUGIN_DIR . 'includes/Admin/class-template-manager.php';
			require_once PROSPECTION_CLAUDE_PLUGIN_DIR . 'includes/Admin/class-campaign-manager.php';
		}

		// Les Services seront chargés dans les phases suivantes
	}

	/**
	 * Définit la locale pour l'internationalisation.
	 *
	 * Configure le domaine de traduction pour permettre la traduction du plugin.
	 */
	private function set_locale() {
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
	}

	/**
	 * Charge le domaine de traduction du plugin.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'prospection-claude',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}

	/**
	 * Enregistre tous les hooks liés à l'administration.
	 *
	 * Phase 3: Initialisation du menu et des pages d'administration.
	 */
	private function define_admin_hooks() {
		// Enregistrer les styles et scripts admin
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Initialiser le menu admin (Phase 3)
		if ( is_admin() ) {
			new Prospection_Claude_Admin_Menu();
		}

		// Les autres hooks admin (templates, campagnes) seront ajoutés dans les phases suivantes
	}

	/**
	 * Enregistre tous les hooks liés à la partie publique.
	 *
	 * Cette méthode sera étendue pour gérer la page de désinscription
	 * et autres fonctionnalités publiques.
	 */
	private function define_public_hooks() {
		// Enregistrer les styles et scripts publics
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );

		// Les autres hooks publics seront ajoutés dans les phases suivantes
	}

	/**
	 * Charge les assets (CSS/JS) de l'administration.
	 *
	 * @param string $hook La page admin actuelle.
	 */
	public function enqueue_admin_assets( $hook ) {
		// Charger uniquement sur les pages du plugin
		if ( strpos( $hook, 'prospection-claude' ) === false ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_slug . '-admin',
			PROSPECTION_CLAUDE_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			$this->version,
			'all'
		);

		wp_enqueue_script(
			$this->plugin_slug . '-admin',
			PROSPECTION_CLAUDE_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		// Passer des données à JavaScript
		wp_localize_script(
			$this->plugin_slug . '-admin',
			'prospectionClaude',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'prospection_claude_nonce' ),
			)
		);
	}

	/**
	 * Charge les assets (CSS/JS) de la partie publique.
	 */
	public function enqueue_public_assets() {
		wp_enqueue_style(
			$this->plugin_slug . '-public',
			PROSPECTION_CLAUDE_PLUGIN_URL . 'assets/css/public.css',
			array(),
			$this->version,
			'all'
		);

		wp_enqueue_script(
			$this->plugin_slug . '-public',
			PROSPECTION_CLAUDE_PLUGIN_URL . 'assets/js/public.js',
			array( 'jquery' ),
			$this->version,
			true
		);
	}

	/**
	 * Lance l'exécution du plugin.
	 *
	 * Cette méthode est appelée depuis le fichier principal du plugin.
	 */
	public function run() {
		// Le plugin est maintenant initialisé et les hooks sont enregistrés
		// Les fonctionnalités seront ajoutées dans les phases suivantes
	}

	/**
	 * Récupère la version du plugin.
	 *
	 * @return string La version du plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Récupère le slug du plugin.
	 *
	 * @return string Le slug du plugin.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}
}
