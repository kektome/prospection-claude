<?php
/**
 * Gestion des contacts dans l'admin.
 *
 * @package ProspectionClaude
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Contact_Manager pour la gestion des contacts.
 *
 * Cette classe gère l'affichage, la création, la modification
 * et la suppression des contacts.
 */
class Prospection_Claude_Contact_Manager {

	/**
	 * Repository des contacts.
	 *
	 * @var Prospection_Claude_Contact_Repository
	 */
	private $repository;

	/**
	 * Constructeur.
	 */
	public function __construct() {
		$this->repository = new Prospection_Claude_Contact_Repository();
	}

	/**
	 * Point d'entrée principal pour afficher la page.
	 */
	public function render() {
		// Gérer les actions POST
		$this->handle_actions();

		// Déterminer quelle vue afficher
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'list';

		switch ( $action ) {
			case 'new':
				$this->render_form();
				break;
			case 'edit':
				$this->render_form();
				break;
			case 'delete':
				$this->handle_delete();
				break;
			default:
				$this->render_list();
				break;
		}
	}

	/**
	 * Gère les actions POST (création, modification, suppression).
	 */
	private function handle_actions() {
		// Vérifier si c'est une soumission de formulaire
		if ( ! isset( $_POST['prospection_claude_contact_nonce'] ) ) {
			return;
		}

		// Vérifier le nonce
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['prospection_claude_contact_nonce'] ) ), 'prospection_claude_contact_action' ) ) {
			wp_die( esc_html__( 'Action non autorisée.', 'prospection-claude' ) );
		}

		// Vérifier les permissions
		if ( ! current_user_can( 'manage_prospection_contacts' ) ) {
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
	 * Gère la création d'un contact.
	 */
	private function handle_create() {
		$contact_data = $this->sanitize_contact_data( $_POST );
		$contact      = new Prospection_Claude_Contact( $contact_data );

		// Valider
		$errors = $contact->validate();
		if ( ! empty( $errors ) ) {
			$this->add_admin_notice( implode( '<br>', $errors ), 'error' );
			return;
		}

		// Vérifier si l'email existe déjà
		if ( $this->repository->find_by_email( $contact->email ) ) {
			$this->add_admin_notice( __( 'Un contact avec cet email existe déjà.', 'prospection-claude' ), 'error' );
			return;
		}

		// Créer
		$id = $this->repository->create( $contact );

		if ( $id ) {
			$redirect_url = add_query_arg(
				array(
					'page'    => 'prospection-claude-contacts',
					'message' => 'created',
				),
				admin_url( 'admin.php' )
			);
			wp_safe_redirect( $redirect_url );
			exit;
		} else {
			$this->add_admin_notice( __( 'Erreur lors de la création du contact.', 'prospection-claude' ), 'error' );
		}
	}

	/**
	 * Gère la mise à jour d'un contact.
	 */
	private function handle_update() {
		$contact_id = isset( $_POST['contact_id'] ) ? (int) $_POST['contact_id'] : 0;

		if ( ! $contact_id ) {
			$this->add_admin_notice( __( 'ID de contact invalide.', 'prospection-claude' ), 'error' );
			return;
		}

		$contact_data       = $this->sanitize_contact_data( $_POST );
		$contact_data['id'] = $contact_id;
		$contact            = new Prospection_Claude_Contact( $contact_data );

		// Valider
		$errors = $contact->validate();
		if ( ! empty( $errors ) ) {
			$this->add_admin_notice( implode( '<br>', $errors ), 'error' );
			return;
		}

		// Vérifier si l'email existe déjà (sauf pour ce contact)
		$existing = $this->repository->find_by_email( $contact->email );
		if ( $existing && $existing->id !== $contact_id ) {
			$this->add_admin_notice( __( 'Un autre contact avec cet email existe déjà.', 'prospection-claude' ), 'error' );
			return;
		}

		// Mettre à jour
		$success = $this->repository->update( $contact );

		if ( $success ) {
			$redirect_url = add_query_arg(
				array(
					'page'    => 'prospection-claude-contacts',
					'message' => 'updated',
				),
				admin_url( 'admin.php' )
			);
			wp_safe_redirect( $redirect_url );
			exit;
		} else {
			$this->add_admin_notice( __( 'Erreur lors de la mise à jour du contact.', 'prospection-claude' ), 'error' );
		}
	}

	/**
	 * Gère la suppression d'un contact.
	 */
	private function handle_delete() {
		// Vérifier le nonce
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'delete_contact' ) ) {
			wp_die( esc_html__( 'Action non autorisée.', 'prospection-claude' ) );
		}

		$contact_id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;

		if ( ! $contact_id ) {
			wp_die( esc_html__( 'ID de contact invalide.', 'prospection-claude' ) );
		}

		$success = $this->repository->delete( $contact_id );

		$redirect_url = add_query_arg(
			array(
				'page'    => 'prospection-claude-contacts',
				'message' => $success ? 'deleted' : 'delete_error',
			),
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Sanitize les données du formulaire de contact.
	 *
	 * @param array $data Les données POST.
	 * @return array Les données sanitizées.
	 */
	private function sanitize_contact_data( $data ) {
		return array(
			'first_name'       => isset( $data['first_name'] ) ? sanitize_text_field( wp_unslash( $data['first_name'] ) ) : '',
			'last_name'        => isset( $data['last_name'] ) ? sanitize_text_field( wp_unslash( $data['last_name'] ) ) : '',
			'company'          => isset( $data['company'] ) ? sanitize_text_field( wp_unslash( $data['company'] ) ) : '',
			'email'            => isset( $data['email'] ) ? sanitize_email( wp_unslash( $data['email'] ) ) : '',
			'phone'            => isset( $data['phone'] ) ? sanitize_text_field( wp_unslash( $data['phone'] ) ) : '',
			'category'         => isset( $data['category'] ) ? sanitize_text_field( wp_unslash( $data['category'] ) ) : '',
			'context'          => isset( $data['context'] ) ? wp_kses_post( wp_unslash( $data['context'] ) ) : '',
			'meeting_location' => isset( $data['meeting_location'] ) ? sanitize_text_field( wp_unslash( $data['meeting_location'] ) ) : '',
			'meeting_date'     => isset( $data['meeting_date'] ) ? sanitize_text_field( wp_unslash( $data['meeting_date'] ) ) : '',
			'is_subscribed'    => isset( $data['is_subscribed'] ) ? 1 : 0,
		);
	}

	/**
	 * Affiche la liste des contacts.
	 */
	private function render_list() {
		// Afficher les messages
		$this->display_admin_notices();

		// Pagination
		$page     = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
		$per_page = 20;
		$offset   = ( $page - 1 ) * $per_page;

		// Filtres
		$search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$category = isset( $_GET['category'] ) ? sanitize_text_field( wp_unslash( $_GET['category'] ) ) : '';

		// Récupérer les contacts
		if ( ! empty( $search ) ) {
			$contacts     = $this->repository->search( $search, $per_page );
			$total_items  = count( $contacts );
			$total_pages = 1;
		} elseif ( ! empty( $category ) && Prospection_Claude_Validator::is_valid_category( $category ) ) {
			$contacts     = $this->repository->find_by_category( $category );
			$total_items  = count( $contacts );
			$contacts     = array_slice( $contacts, $offset, $per_page );
			$total_pages = ceil( $total_items / $per_page );
		} else {
			$contacts     = $this->repository->find_all( $per_page, $offset );
			$total_items  = $this->repository->count();
			$total_pages = ceil( $total_items / $per_page );
		}

		// Inclure le template
		include PROSPECTION_CLAUDE_PLUGIN_DIR . 'templates/admin/contact-list.php';
	}

	/**
	 * Affiche le formulaire d'ajout/édition.
	 */
	private function render_form() {
		$contact_id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
		$contact    = null;
		$is_edit    = false;

		if ( $contact_id ) {
			$contact = $this->repository->find_by_id( $contact_id );
			if ( ! $contact ) {
				wp_die( esc_html__( 'Contact non trouvé.', 'prospection-claude' ) );
			}
			$is_edit = true;
		}

		// Inclure le template
		include PROSPECTION_CLAUDE_PLUGIN_DIR . 'templates/admin/contact-form.php';
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
					'text' => __( 'Contact créé avec succès.', 'prospection-claude' ),
					'type' => 'success',
				),
				'updated'      => array(
					'text' => __( 'Contact mis à jour avec succès.', 'prospection-claude' ),
					'type' => 'success',
				),
				'deleted'      => array(
					'text' => __( 'Contact supprimé avec succès.', 'prospection-claude' ),
					'type' => 'success',
				),
				'delete_error' => array(
					'text' => __( 'Erreur lors de la suppression du contact.', 'prospection-claude' ),
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
