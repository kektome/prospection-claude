<?php
/**
 * Gestion des campagnes dans l'admin.
 *
 * @package ProspectionClaude
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Campaign_Manager pour la gestion des campagnes.
 *
 * Cette classe gère l'affichage, la création, la modification
 * et la suppression des campagnes d'emails.
 */
class Prospection_Claude_Campaign_Manager {

	/**
	 * Repository des campagnes.
	 *
	 * @var Prospection_Claude_Campaign_Repository
	 */
	private $repository;

	/**
	 * Repository des templates.
	 *
	 * @var Prospection_Claude_Template_Repository
	 */
	private $template_repository;

	/**
	 * Constructeur.
	 */
	public function __construct() {
		$this->repository          = new Prospection_Claude_Campaign_Repository();
		$this->template_repository = new Prospection_Claude_Template_Repository();

		// Traiter les actions POST tôt, avant tout output
		add_action( 'admin_init', array( $this, 'handle_actions' ) );
	}

	/**
	 * Point d'entrée principal pour afficher la page.
	 */
	public function render() {
		// Les actions POST et DELETE sont maintenant gérées via le hook admin_init

		// Déterminer quelle vue afficher
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'list';

		switch ( $action ) {
			case 'new':
				$this->render_form();
				break;
			case 'edit':
				$this->render_form();
				break;
			default:
				$this->render_list();
				break;
		}
	}

	/**
	 * Gère les actions POST (création, modification, suppression, toggle).
	 */
	public function handle_actions() {
		// Vérifier qu'on est sur la page des campagnes
		if ( ! isset( $_GET['page'] ) || 'prospection-claude-campaigns' !== $_GET['page'] ) {
			return;
		}

		// Gérer la suppression (GET avec nonce)
		if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && isset( $_GET['id'] ) ) {
			$this->handle_delete();
			return;
		}

		// Gérer l'activation/désactivation (GET avec nonce)
		if ( isset( $_GET['action'] ) && 'toggle' === $_GET['action'] && isset( $_GET['id'] ) ) {
			$this->handle_toggle();
			return;
		}

		// Vérifier si c'est une soumission de formulaire POST
		if ( ! isset( $_POST['prospection_claude_campaign_nonce'] ) ) {
			return;
		}

		// Vérifier le nonce
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['prospection_claude_campaign_nonce'] ) ), 'prospection_claude_campaign_action' ) ) {
			wp_die( esc_html__( 'Action non autorisée.', 'prospection-claude' ) );
		}

		// Vérifier les permissions
		if ( ! current_user_can( 'manage_prospection_campaigns' ) ) {
			wp_die( esc_html__( 'Permissions insuffisantes.', 'prospection-claude' ) );
		}

		$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';

		if ( 'create' === $action ) {
			$this->handle_create();
		} elseif ( 'update' === $action ) {
			$this->handle_update();
		}
	}

	/**
	 * Gère la création d'une campagne.
	 */
	private function handle_create() {
		$campaign_data = $this->sanitize_campaign_data( $_POST );
		$campaign      = new Prospection_Claude_Campaign( $campaign_data );

		// Calculer la prochaine exécution selon le type de scheduling
		$campaign->next_run = $this->calculate_next_run( $campaign->schedule_type, $campaign->get_schedule_config_array() );

		// Valider
		$errors = $campaign->validate();
		if ( ! empty( $errors ) ) {
			$this->add_admin_notice( implode( '<br>', $errors ), 'error' );
			return;
		}

		// Créer
		$id = $this->repository->create( $campaign );

		if ( $id ) {
			$redirect_url = add_query_arg(
				array(
					'page'    => 'prospection-claude-campaigns',
					'message' => 'created',
				),
				admin_url( 'admin.php' )
			);
			wp_safe_redirect( $redirect_url );
			exit;
		} else {
			$this->add_admin_notice( __( 'Erreur lors de la création de la campagne.', 'prospection-claude' ), 'error' );
		}
	}

	/**
	 * Gère la mise à jour d'une campagne.
	 */
	private function handle_update() {
		$campaign_id = isset( $_POST['campaign_id'] ) ? (int) $_POST['campaign_id'] : 0;

		if ( ! $campaign_id ) {
			$this->add_admin_notice( __( 'ID de campagne invalide.', 'prospection-claude' ), 'error' );
			return;
		}

		$campaign_data       = $this->sanitize_campaign_data( $_POST );
		$campaign_data['id'] = $campaign_id;
		$campaign            = new Prospection_Claude_Campaign( $campaign_data );

		// Calculer la prochaine exécution selon le type de scheduling
		$campaign->next_run = $this->calculate_next_run( $campaign->schedule_type, $campaign->get_schedule_config_array() );

		// Valider
		$errors = $campaign->validate();
		if ( ! empty( $errors ) ) {
			$this->add_admin_notice( implode( '<br>', $errors ), 'error' );
			return;
		}

		// Mettre à jour
		$success = $this->repository->update( $campaign );

		if ( $success ) {
			$redirect_url = add_query_arg(
				array(
					'page'    => 'prospection-claude-campaigns',
					'message' => 'updated',
				),
				admin_url( 'admin.php' )
			);
			wp_safe_redirect( $redirect_url );
			exit;
		} else {
			$this->add_admin_notice( __( 'Erreur lors de la mise à jour de la campagne.', 'prospection-claude' ), 'error' );
		}
	}

	/**
	 * Gère la suppression d'une campagne.
	 */
	private function handle_delete() {
		// Vérifier le nonce
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'delete_campaign' ) ) {
			wp_die( esc_html__( 'Action non autorisée.', 'prospection-claude' ) );
		}

		$campaign_id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;

		if ( ! $campaign_id ) {
			wp_die( esc_html__( 'ID de campagne invalide.', 'prospection-claude' ) );
		}

		$success = $this->repository->delete( $campaign_id );

		$redirect_url = add_query_arg(
			array(
				'page'    => 'prospection-claude-campaigns',
				'message' => $success ? 'deleted' : 'delete_error',
			),
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Gère l'activation/désactivation d'une campagne.
	 */
	private function handle_toggle() {
		// Vérifier le nonce
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'toggle_campaign' ) ) {
			wp_die( esc_html__( 'Action non autorisée.', 'prospection-claude' ) );
		}

		$campaign_id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
		$is_active   = isset( $_GET['is_active'] ) ? (int) $_GET['is_active'] : 0;

		if ( ! $campaign_id ) {
			wp_die( esc_html__( 'ID de campagne invalide.', 'prospection-claude' ) );
		}

		$success = $this->repository->toggle_active( $campaign_id, $is_active );

		$redirect_url = add_query_arg(
			array(
				'page'    => 'prospection-claude-campaigns',
				'message' => $success ? 'toggled' : 'toggle_error',
			),
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Calcule la prochaine date d'exécution selon le type de scheduling.
	 *
	 * @param string $schedule_type Type de scheduling.
	 * @param array  $schedule_config Configuration du scheduling.
	 * @return string Date au format MySQL.
	 */
	private function calculate_next_run( $schedule_type, $schedule_config = array() ) {
		// Récupérer la date de départ depuis la config
		$start_date = isset( $schedule_config['schedule_date'] ) ? $schedule_config['schedule_date'] : current_time( 'mysql' );
		$start_timestamp = strtotime( $start_date );
		$current_timestamp = strtotime( current_time( 'mysql' ) );

		// Si la date de départ est dans le futur, l'utiliser directement
		if ( $start_timestamp > $current_timestamp ) {
			return gmdate( 'Y-m-d H:i:s', $start_timestamp );
		}

		// Si la date est dans le passé, calculer la prochaine occurrence
		$next_run = $start_timestamp;

		switch ( $schedule_type ) {
			case 'daily':
				// Trouver le prochain jour après maintenant
				while ( $next_run <= $current_timestamp ) {
					$next_run = strtotime( '+1 day', $next_run );
				}
				break;
			case 'weekly':
				// Trouver la prochaine semaine après maintenant
				while ( $next_run <= $current_timestamp ) {
					$next_run = strtotime( '+1 week', $next_run );
				}
				break;
			case 'monthly':
				// Trouver le prochain mois après maintenant
				while ( $next_run <= $current_timestamp ) {
					$next_run = strtotime( '+1 month', $next_run );
				}
				break;
			case 'custom':
				// Pour custom, utiliser la date exacte (même si passée)
				$next_run = $start_timestamp;
				break;
		}

		return gmdate( 'Y-m-d H:i:s', $next_run );
	}

	/**
	 * Sanitize les données du formulaire de campagne.
	 *
	 * @param array $data Les données POST.
	 * @return array Les données sanitizées.
	 */
	private function sanitize_campaign_data( $data ) {
		// Sanitize les catégories cibles (array de checkboxes)
		$target_categories = array();
		if ( isset( $data['target_categories'] ) && is_array( $data['target_categories'] ) ) {
			$target_categories = array_map( 'sanitize_text_field', $data['target_categories'] );
		}

		// Sanitize la config du scheduling (date de début pour tous les types)
		$schedule_config = array();
		if ( isset( $data['schedule_date'] ) && ! empty( $data['schedule_date'] ) ) {
			$schedule_config['schedule_date'] = sanitize_text_field( wp_unslash( $data['schedule_date'] ) );
		}

		$campaign_data = array(
			'name'              => isset( $data['name'] ) ? sanitize_text_field( wp_unslash( $data['name'] ) ) : '',
			'template_id'       => isset( $data['template_id'] ) ? (int) $data['template_id'] : 0,
			'schedule_type'     => isset( $data['schedule_type'] ) ? sanitize_text_field( wp_unslash( $data['schedule_type'] ) ) : '',
			'is_active'         => isset( $data['is_active'] ) ? 1 : 0,
		);

		// Créer une instance temporaire pour utiliser les setters
		$temp_campaign = new Prospection_Claude_Campaign( $campaign_data );
		$temp_campaign->set_target_categories_array( $target_categories );
		$temp_campaign->set_schedule_config_array( $schedule_config );

		$campaign_data['target_categories'] = $temp_campaign->target_categories;
		$campaign_data['schedule_config']   = $temp_campaign->schedule_config;

		return $campaign_data;
	}

	/**
	 * Affiche la liste des campagnes.
	 */
	private function render_list() {
		// Afficher les messages
		$this->display_admin_notices();

		// Pagination
		$page     = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
		$per_page = 20;
		$offset   = ( $page - 1 ) * $per_page;

		// Filtre par statut
		$filter_active = isset( $_GET['filter_active'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_active'] ) ) : '';

		// Récupérer les campagnes
		if ( '' !== $filter_active ) {
			$campaigns   = $this->repository->find_all( 999, 0 ); // Get all for filtering
			$campaigns   = array_filter( $campaigns, function( $c ) use ( $filter_active ) {
				return (string) $c->is_active === $filter_active;
			});
			$total_items = count( $campaigns );
			$campaigns   = array_slice( $campaigns, $offset, $per_page );
			$total_pages = ceil( $total_items / $per_page );
		} else {
			$campaigns   = $this->repository->find_all( $per_page, $offset );
			$total_items = $this->repository->count();
			$total_pages = ceil( $total_items / $per_page );
		}

		// Inclure le template
		include PROSPECTION_CLAUDE_PLUGIN_DIR . 'templates/admin/campaign-list.php';
	}

	/**
	 * Affiche le formulaire d'ajout/édition.
	 */
	private function render_form() {
		// Afficher les messages d'erreur
		$this->display_admin_notices();

		$campaign_id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
		$campaign    = null;
		$is_edit     = false;

		if ( $campaign_id ) {
			$campaign = $this->repository->find_by_id( $campaign_id );
			if ( ! $campaign ) {
				wp_die( esc_html__( 'Campagne non trouvée.', 'prospection-claude' ) );
			}
			$is_edit = true;
		}

		// Récupérer tous les templates pour le select
		$templates = $this->template_repository->find_all( 999 );

		// Catégories disponibles
		$available_categories = array(
			'micrologiciel' => __( 'Micrologiciel', 'prospection-claude' ),
			'scientifique'  => __( 'Scientifique', 'prospection-claude' ),
			'informatique'  => __( 'Informatique', 'prospection-claude' ),
		);

		// Inclure le template
		include PROSPECTION_CLAUDE_PLUGIN_DIR . 'templates/admin/campaign-form.php';
	}

	/**
	 * Ajoute un message d'administration.
	 *
	 * @param string $message Le message.
	 * @param string $type Le type (success, error, warning, info).
	 */
	private function add_admin_notice( $message, $type = 'success' ) {
		set_transient( 'prospection_claude_admin_notice', array( 'message' => $message, 'type' => $type ), 30 );
	}

	/**
	 * Affiche les messages d'administration.
	 */
	private function display_admin_notices() {
		// Messages depuis $_GET
		if ( isset( $_GET['message'] ) ) {
			$message_type = sanitize_text_field( wp_unslash( $_GET['message'] ) );
			$messages     = array(
				'created'      => array(
					'text' => __( 'Campagne créée avec succès.', 'prospection-claude' ),
					'type' => 'success',
				),
				'updated'      => array(
					'text' => __( 'Campagne mise à jour avec succès.', 'prospection-claude' ),
					'type' => 'success',
				),
				'deleted'      => array(
					'text' => __( 'Campagne supprimée avec succès.', 'prospection-claude' ),
					'type' => 'success',
				),
				'toggled'      => array(
					'text' => __( 'Statut de la campagne modifié avec succès.', 'prospection-claude' ),
					'type' => 'success',
				),
				'delete_error' => array(
					'text' => __( 'Erreur lors de la suppression de la campagne.', 'prospection-claude' ),
					'type' => 'error',
				),
				'toggle_error' => array(
					'text' => __( 'Erreur lors de la modification du statut.', 'prospection-claude' ),
					'type' => 'error',
				),
			);

			if ( isset( $messages[ $message_type ] ) ) {
				echo '<div class="notice notice-' . esc_attr( $messages[ $message_type ]['type'] ) . ' is-dismissible"><p>' .
					esc_html( $messages[ $message_type ]['text'] ) . '</p></div>';
			}
		}

		// Messages depuis transient
		$notice = get_transient( 'prospection_claude_admin_notice' );
		if ( $notice ) {
			echo '<div class="notice notice-' . esc_attr( $notice['type'] ) . ' is-dismissible"><p>' .
				wp_kses_post( $notice['message'] ) . '</p></div>';
			delete_transient( 'prospection_claude_admin_notice' );
		}
	}
}
