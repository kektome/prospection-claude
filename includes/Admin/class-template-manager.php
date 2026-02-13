<?php
/**
 * Gestion des templates d'emails dans l'admin.
 *
 * @package ProspectionClaude
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Template_Manager pour la gestion des templates d'emails.
 *
 * Cette classe gère l'affichage, la création, la modification
 * et la suppression des templates d'emails.
 */
class Prospection_Claude_Template_Manager {

	/**
	 * Repository des templates.
	 *
	 * @var Prospection_Claude_Template_Repository
	 */
	private $repository;

	/**
	 * Constructeur.
	 */
	public function __construct() {
		$this->repository = new Prospection_Claude_Template_Repository();

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
	 * Gère les actions POST (création, modification, suppression).
	 */
	public function handle_actions() {
		// Vérifier qu'on est sur la page des templates
		if ( ! isset( $_GET['page'] ) || 'prospection-claude-templates' !== $_GET['page'] ) {
			return;
		}

		// Gérer la suppression (GET avec nonce)
		if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && isset( $_GET['id'] ) ) {
			$this->handle_delete();
			return;
		}

		// Vérifier si c'est une soumission de formulaire POST
		if ( ! isset( $_POST['prospection_claude_template_nonce'] ) ) {
			return;
		}

		// Vérifier le nonce
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['prospection_claude_template_nonce'] ) ), 'prospection_claude_template_action' ) ) {
			wp_die( esc_html__( 'Action non autorisée.', 'prospection-claude' ) );
		}

		// Vérifier les permissions
		if ( ! current_user_can( 'manage_prospection_templates' ) ) {
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
	 * Gère la création d'un template.
	 */
	private function handle_create() {
		$template_data = $this->sanitize_template_data( $_POST );
		$template      = new Prospection_Claude_Email_Template( $template_data );

		// Valider
		$errors = $template->validate();
		if ( ! empty( $errors ) ) {
			$this->add_admin_notice( implode( '<br>', $errors ), 'error' );
			return;
		}

		// Créer
		$id = $this->repository->create( $template );

		if ( $id ) {
			$redirect_url = add_query_arg(
				array(
					'page'    => 'prospection-claude-templates',
					'message' => 'created',
				),
				admin_url( 'admin.php' )
			);
			wp_safe_redirect( $redirect_url );
			exit;
		} else {
			$this->add_admin_notice( __( 'Erreur lors de la création du template.', 'prospection-claude' ), 'error' );
		}
	}

	/**
	 * Gère la mise à jour d'un template.
	 */
	private function handle_update() {
		$template_id = isset( $_POST['template_id'] ) ? (int) $_POST['template_id'] : 0;

		if ( ! $template_id ) {
			$this->add_admin_notice( __( 'ID de template invalide.', 'prospection-claude' ), 'error' );
			return;
		}

		$template_data       = $this->sanitize_template_data( $_POST );
		$template_data['id'] = $template_id;
		$template            = new Prospection_Claude_Email_Template( $template_data );

		// Valider
		$errors = $template->validate();
		if ( ! empty( $errors ) ) {
			$this->add_admin_notice( implode( '<br>', $errors ), 'error' );
			return;
		}

		// Mettre à jour
		$success = $this->repository->update( $template );

		if ( $success ) {
			$redirect_url = add_query_arg(
				array(
					'page'    => 'prospection-claude-templates',
					'message' => 'updated',
				),
				admin_url( 'admin.php' )
			);
			wp_safe_redirect( $redirect_url );
			exit;
		} else {
			$this->add_admin_notice( __( 'Erreur lors de la mise à jour du template.', 'prospection-claude' ), 'error' );
		}
	}

	/**
	 * Gère la suppression d'un template.
	 */
	private function handle_delete() {
		// Vérifier le nonce
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'delete_template' ) ) {
			wp_die( esc_html__( 'Action non autorisée.', 'prospection-claude' ) );
		}

		$template_id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;

		if ( ! $template_id ) {
			wp_die( esc_html__( 'ID de template invalide.', 'prospection-claude' ) );
		}

		$success = $this->repository->delete( $template_id );

		$redirect_url = add_query_arg(
			array(
				'page'    => 'prospection-claude-templates',
				'message' => $success ? 'deleted' : 'delete_error',
			),
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Sanitize les données du formulaire de template.
	 *
	 * @param array $data Les données POST.
	 * @return array Les données sanitizées.
	 */
	private function sanitize_template_data( $data ) {
		return array(
			'name'     => isset( $data['name'] ) ? sanitize_text_field( wp_unslash( $data['name'] ) ) : '',
			'subject'  => isset( $data['subject'] ) ? sanitize_text_field( wp_unslash( $data['subject'] ) ) : '',
			'content'  => isset( $data['content'] ) ? wp_kses_post( wp_unslash( $data['content'] ) ) : '',
			'category' => isset( $data['category'] ) ? sanitize_text_field( wp_unslash( $data['category'] ) ) : 'all',
		);
	}

	/**
	 * Affiche la liste des templates.
	 */
	private function render_list() {
		// Afficher les messages
		$this->display_admin_notices();

		// Pagination
		$page     = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
		$per_page = 20;
		$offset   = ( $page - 1 ) * $per_page;

		// Filtre par catégorie
		$category = isset( $_GET['category'] ) ? sanitize_text_field( wp_unslash( $_GET['category'] ) ) : '';

		// Récupérer les templates
		if ( ! empty( $category ) && Prospection_Claude_Validator::is_valid_template_category( $category ) ) {
			$templates   = $this->repository->find_by_category( $category );
			$total_items = count( $templates );
			$templates   = array_slice( $templates, $offset, $per_page );
			$total_pages = ceil( $total_items / $per_page );
		} else {
			$templates   = $this->repository->find_all( $per_page, $offset );
			$total_items = $this->repository->count();
			$total_pages = ceil( $total_items / $per_page );
		}

		// Inclure le template
		include PROSPECTION_CLAUDE_PLUGIN_DIR . 'templates/admin/template-list.php';
	}

	/**
	 * Affiche le formulaire d'ajout/édition.
	 */
	private function render_form() {
		// Afficher les messages d'erreur
		$this->display_admin_notices();

		$template_id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
		$template    = null;
		$is_edit     = false;

		if ( $template_id ) {
			$template = $this->repository->find_by_id( $template_id );
			if ( ! $template ) {
				wp_die( esc_html__( 'Template non trouvé.', 'prospection-claude' ) );
			}
			$is_edit = true;
		}

		// Variables disponibles
		$available_variables = array(
			'{first_name}'        => __( 'Prénom du contact', 'prospection-claude' ),
			'{last_name}'         => __( 'Nom du contact', 'prospection-claude' ),
			'{full_name}'         => __( 'Nom complet du contact', 'prospection-claude' ),
			'{company}'           => __( 'Entreprise', 'prospection-claude' ),
			'{email}'             => __( 'Email', 'prospection-claude' ),
			'{unsubscribe_link}' => __( 'Lien de désinscription', 'prospection-claude' ),
		);

		// Inclure le template
		include PROSPECTION_CLAUDE_PLUGIN_DIR . 'templates/admin/template-form.php';
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
					'text' => __( 'Template créé avec succès.', 'prospection-claude' ),
					'type' => 'success',
				),
				'updated'      => array(
					'text' => __( 'Template mis à jour avec succès.', 'prospection-claude' ),
					'type' => 'success',
				),
				'deleted'      => array(
					'text' => __( 'Template supprimé avec succès.', 'prospection-claude' ),
					'type' => 'success',
				),
				'delete_error' => array(
					'text' => __( 'Erreur lors de la suppression du template.', 'prospection-claude' ),
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
