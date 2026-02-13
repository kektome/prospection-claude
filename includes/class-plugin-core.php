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
	 * Pour l'instant, elle est minimale et sera étendue dans les phases suivantes.
	 */
	private function load_dependencies() {
		// Les autres dépendances seront chargées dans les phases suivantes
		// (Repositories, Services, Admin, etc.)
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
	 * Cette méthode sera étendue dans les phases suivantes pour ajouter
	 * les menus, pages d'administration, etc.
	 */
	private function define_admin_hooks() {
		// Enregistrer les styles et scripts admin
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Les autres hooks admin seront ajoutés dans les phases suivantes
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
