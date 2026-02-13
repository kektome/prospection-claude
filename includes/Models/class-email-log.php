<?php
/**
 * Classe Model EmailLog.
 *
 * @package ProspectionClaude
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe représentant un Log d'Email.
 *
 * Cette classe représente une entité EmailLog avec ses propriétés
 * et méthodes de validation.
 */
class Prospection_Claude_Email_Log {

	/**
	 * ID du log.
	 *
	 * @var int|null
	 */
	public $id;

	/**
	 * ID du contact.
	 *
	 * @var int
	 */
	public $contact_id;

	/**
	 * ID de la campagne.
	 *
	 * @var int|null
	 */
	public $campaign_id;

	/**
	 * ID du template.
	 *
	 * @var int|null
	 */
	public $template_id;

	/**
	 * Sujet de l'email.
	 *
	 * @var string|null
	 */
	public $subject;

	/**
	 * Statut de l'email (pending, sent, failed, bounced).
	 *
	 * @var string
	 */
	public $status;

	/**
	 * Message d'erreur (si échec).
	 *
	 * @var string|null
	 */
	public $error_message;

	/**
	 * Date d'envoi.
	 *
	 * @var string|null
	 */
	public $sent_at;

	/**
	 * Date de création.
	 *
	 * @var string|null
	 */
	public $created_at;

	/**
	 * Constructeur.
	 *
	 * @param array $data Données du log.
	 */
	public function __construct( $data = array() ) {
		$this->id            = isset( $data['id'] ) ? (int) $data['id'] : null;
		$this->contact_id    = isset( $data['contact_id'] ) ? (int) $data['contact_id'] : 0;
		$this->campaign_id   = isset( $data['campaign_id'] ) ? (int) $data['campaign_id'] : null;
		$this->template_id   = isset( $data['template_id'] ) ? (int) $data['template_id'] : null;
		$this->subject       = isset( $data['subject'] ) ? $data['subject'] : null;
		$this->status        = isset( $data['status'] ) ? $data['status'] : 'pending';
		$this->error_message = isset( $data['error_message'] ) ? $data['error_message'] : null;
		$this->sent_at       = isset( $data['sent_at'] ) ? $data['sent_at'] : null;
		$this->created_at    = isset( $data['created_at'] ) ? $data['created_at'] : null;
	}

	/**
	 * Valide les données du log.
	 *
	 * @return array Tableau d'erreurs (vide si valide).
	 */
	public function validate() {
		$errors = array();

		// Validation du contact_id
		if ( empty( $this->contact_id ) || $this->contact_id <= 0 ) {
			$errors['contact_id'] = __( 'Un contact doit être associé au log.', 'prospection-claude' );
		}

		// Validation du statut
		if ( ! Prospection_Claude_Validator::is_valid_email_status( $this->status ) ) {
			$errors['status'] = __( 'Le statut n\'est pas valide.', 'prospection-claude' );
		}

		return $errors;
	}

	/**
	 * Vérifie si le log est valide.
	 *
	 * @return bool True si valide, false sinon.
	 */
	public function is_valid() {
		return empty( $this->validate() );
	}

	/**
	 * Convertit le log en tableau.
	 *
	 * @return array Le log sous forme de tableau.
	 */
	public function to_array() {
		return array(
			'id'            => $this->id,
			'contact_id'    => $this->contact_id,
			'campaign_id'   => $this->campaign_id,
			'template_id'   => $this->template_id,
			'subject'       => $this->subject,
			'status'        => $this->status,
			'error_message' => $this->error_message,
			'sent_at'       => $this->sent_at,
			'created_at'    => $this->created_at,
		);
	}

	/**
	 * Retourne le nom du statut traduit.
	 *
	 * @return string Le nom du statut.
	 */
	public function get_status_label() {
		$labels = array(
			'pending' => __( 'En attente', 'prospection-claude' ),
			'sent'    => __( 'Envoyé', 'prospection-claude' ),
			'failed'  => __( 'Échec', 'prospection-claude' ),
			'bounced' => __( 'Rebond', 'prospection-claude' ),
		);

		return isset( $labels[ $this->status ] ) ? $labels[ $this->status ] : $this->status;
	}

	/**
	 * Retourne la classe CSS pour le statut.
	 *
	 * @return string La classe CSS.
	 */
	public function get_status_class() {
		$classes = array(
			'pending' => 'status-pending',
			'sent'    => 'status-success',
			'failed'  => 'status-error',
			'bounced' => 'status-warning',
		);

		return isset( $classes[ $this->status ] ) ? $classes[ $this->status ] : 'status-default';
	}

	/**
	 * Marque le log comme envoyé.
	 */
	public function mark_as_sent() {
		$this->status  = 'sent';
		$this->sent_at = current_time( 'mysql' );
	}

	/**
	 * Marque le log comme échoué.
	 *
	 * @param string $error_message Le message d'erreur.
	 */
	public function mark_as_failed( $error_message ) {
		$this->status        = 'failed';
		$this->error_message = $error_message;
		$this->sent_at       = current_time( 'mysql' );
	}
}
