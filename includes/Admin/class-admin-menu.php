<?php
/**
 * Gestion du menu d'administration.
 *
 * @package ProspectionClaude
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Admin_Menu pour la gestion du menu d'administration.
 *
 * Cette classe crée le menu principal et les sous-menus
 * dans l'interface d'administration WordPress.
 */
class Prospection_Claude_Admin_Menu {

	/**
	 * Slug du menu principal.
	 *
	 * @var string
	 */
	private $menu_slug;

	/**
	 * Constructeur.
	 */
	public function __construct() {
		$this->menu_slug = 'prospection-claude';
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
	}

	/**
	 * Enregistre le menu et les sous-menus.
	 */
	public function register_menu() {
		// Menu principal
		add_menu_page(
			__( 'Prospection', 'prospection-claude' ), // Page title
			__( 'Prospection', 'prospection-claude' ), // Menu title
			'manage_prospection_contacts', // Capability
			$this->menu_slug, // Menu slug
			array( $this, 'render_dashboard_page' ), // Callback
			'dashicons-email', // Icon
			30 // Position
		);

		// Sous-menu: Dashboard (remplace le menu principal)
		add_submenu_page(
			$this->menu_slug,
			__( 'Tableau de bord', 'prospection-claude' ),
			__( 'Tableau de bord', 'prospection-claude' ),
			'manage_prospection_contacts',
			$this->menu_slug,
			array( $this, 'render_dashboard_page' )
		);

		// Sous-menu: Contacts
		add_submenu_page(
			$this->menu_slug,
			__( 'Contacts', 'prospection-claude' ),
			__( 'Contacts', 'prospection-claude' ),
			'manage_prospection_contacts',
			$this->menu_slug . '-contacts',
			array( $this, 'render_contacts_page' )
		);

		// Sous-menu: Templates (Phase 4)
		add_submenu_page(
			$this->menu_slug,
			__( 'Templates d\'emails', 'prospection-claude' ),
			__( 'Templates', 'prospection-claude' ),
			'manage_prospection_templates',
			$this->menu_slug . '-templates',
			array( $this, 'render_templates_page' )
		);

		// Sous-menu: Campagnes (Phase 5)
		add_submenu_page(
			$this->menu_slug,
			__( 'Campagnes', 'prospection-claude' ),
			__( 'Campagnes', 'prospection-claude' ),
			'manage_prospection_campaigns',
			$this->menu_slug . '-campaigns',
			array( $this, 'render_campaigns_page' )
		);

		// Sous-menu: Logs (Phase 9)
		add_submenu_page(
			$this->menu_slug,
			__( 'Logs d\'envoi', 'prospection-claude' ),
			__( 'Logs', 'prospection-claude' ),
			'view_prospection_logs',
			$this->menu_slug . '-logs',
			array( $this, 'render_logs_page' )
		);
	}

	/**
	 * Affiche la page Dashboard.
	 */
	public function render_dashboard_page() {
		// Vérifier les permissions
		if ( ! current_user_can( 'manage_prospection_contacts' ) ) {
			wp_die( esc_html__( 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'prospection-claude' ) );
		}

		echo '<div class="wrap prospection-claude">';
		echo '<h1>' . esc_html__( 'Prospection - Tableau de bord', 'prospection-claude' ) . '</h1>';

		// Statistiques rapides
		$this->render_dashboard_stats();

		echo '</div>';
	}

	/**
	 * Affiche les statistiques du dashboard.
	 */
	private function render_dashboard_stats() {
		$contact_repo = new Prospection_Claude_Contact_Repository();
		$log_repo     = new Prospection_Claude_Log_Repository();

		$total_contacts     = $contact_repo->count();
		$subscribed_count   = $contact_repo->count( array( 'is_subscribed' => 1 ) );
		$unsubscribed_count = $contact_repo->count( array( 'is_subscribed' => 0 ) );
		$email_stats        = $log_repo->get_statistics();

		echo '<div class="prospection-stats-grid">';

		// Stat: Total contacts
		$this->render_stat_card(
			__( 'Total Contacts', 'prospection-claude' ),
			$total_contacts,
			'dashicons-groups',
			'primary'
		);

		// Stat: Abonnés
		$this->render_stat_card(
			__( 'Abonnés', 'prospection-claude' ),
			$subscribed_count,
			'dashicons-yes-alt',
			'success'
		);

		// Stat: Désabonnés
		$this->render_stat_card(
			__( 'Désabonnés', 'prospection-claude' ),
			$unsubscribed_count,
			'dashicons-dismiss',
			'warning'
		);

		// Stat: Emails envoyés
		$this->render_stat_card(
			__( 'Emails Envoyés', 'prospection-claude' ),
			$email_stats['sent'],
			'dashicons-email-alt',
			'info'
		);

		echo '</div>';

		// Actions rapides
		echo '<div class="prospection-quick-actions">';
		echo '<h2>' . esc_html__( 'Actions rapides', 'prospection-claude' ) . '</h2>';
		echo '<div class="quick-actions-grid">';

		$this->render_quick_action(
			__( 'Ajouter un contact', 'prospection-claude' ),
			admin_url( 'admin.php?page=prospection-claude-contacts&action=new' ),
			'dashicons-plus-alt'
		);

		$this->render_quick_action(
			__( 'Voir les contacts', 'prospection-claude' ),
			admin_url( 'admin.php?page=prospection-claude-contacts' ),
			'dashicons-list-view'
		);

		$this->render_quick_action(
			__( 'Créer un template', 'prospection-claude' ),
			admin_url( 'admin.php?page=prospection-claude-templates&action=new' ),
			'dashicons-media-document'
		);

		$this->render_quick_action(
			__( 'Créer une campagne', 'prospection-claude' ),
			admin_url( 'admin.php?page=prospection-claude-campaigns&action=new' ),
			'dashicons-megaphone'
		);

		echo '</div>';
		echo '</div>';
	}

	/**
	 * Affiche une carte de statistique.
	 *
	 * @param string $label Le label.
	 * @param int    $value La valeur.
	 * @param string $icon L'icône dashicons.
	 * @param string $color La couleur (primary, success, warning, info).
	 */
	private function render_stat_card( $label, $value, $icon, $color ) {
		?>
		<div class="stat-card stat-<?php echo esc_attr( $color ); ?>">
			<div class="stat-icon">
				<span class="dashicons <?php echo esc_attr( $icon ); ?>"></span>
			</div>
			<div class="stat-content">
				<div class="stat-value"><?php echo esc_html( number_format_i18n( $value ) ); ?></div>
				<div class="stat-label"><?php echo esc_html( $label ); ?></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Affiche un bouton d'action rapide.
	 *
	 * @param string $label Le label.
	 * @param string $url L'URL.
	 * @param string $icon L'icône dashicons.
	 */
	private function render_quick_action( $label, $url, $icon ) {
		?>
		<a href="<?php echo esc_url( $url ); ?>" class="quick-action-button">
			<span class="dashicons <?php echo esc_attr( $icon ); ?>"></span>
			<span><?php echo esc_html( $label ); ?></span>
		</a>
		<?php
	}

	/**
	 * Affiche la page Contacts.
	 */
	public function render_contacts_page() {
		if ( ! current_user_can( 'manage_prospection_contacts' ) ) {
			wp_die( esc_html__( 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'prospection-claude' ) );
		}

		// Cette page sera gérée par Contact_Manager
		$contact_manager = new Prospection_Claude_Contact_Manager();
		$contact_manager->render();
	}

	/**
	 * Affiche la page Templates (Phase 4).
	 */
	public function render_templates_page() {
		if ( ! current_user_can( 'manage_prospection_templates' ) ) {
			wp_die( esc_html__( 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'prospection-claude' ) );
		}

		echo '<div class="wrap prospection-claude">';
		echo '<h1>' . esc_html__( 'Templates d\'emails', 'prospection-claude' ) . '</h1>';
		echo '<p>' . esc_html__( 'Cette fonctionnalité sera disponible dans la Phase 4.', 'prospection-claude' ) . '</p>';
		echo '</div>';
	}

	/**
	 * Affiche la page Campagnes (Phase 5).
	 */
	public function render_campaigns_page() {
		if ( ! current_user_can( 'manage_prospection_campaigns' ) ) {
			wp_die( esc_html__( 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'prospection-claude' ) );
		}

		echo '<div class="wrap prospection-claude">';
		echo '<h1>' . esc_html__( 'Campagnes', 'prospection-claude' ) . '</h1>';
		echo '<p>' . esc_html__( 'Cette fonctionnalité sera disponible dans la Phase 5.', 'prospection-claude' ) . '</p>';
		echo '</div>';
	}

	/**
	 * Affiche la page Logs (Phase 9).
	 */
	public function render_logs_page() {
		if ( ! current_user_can( 'view_prospection_logs' ) ) {
			wp_die( esc_html__( 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'prospection-claude' ) );
		}

		echo '<div class="wrap prospection-claude">';
		echo '<h1>' . esc_html__( 'Logs d\'envoi', 'prospection-claude' ) . '</h1>';
		echo '<p>' . esc_html__( 'Cette fonctionnalité sera disponible dans la Phase 9.', 'prospection-claude' ) . '</p>';
		echo '</div>';
	}
}
