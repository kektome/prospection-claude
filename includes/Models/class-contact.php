<?php
/**
 * Classe Model Contact.
 *
 * @package ProspectionClaude
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe représentant un Contact.
 *
 * Cette classe représente une entité Contact avec ses propriétés
 * et méthodes de validation.
 */
class Prospection_Claude_Contact {

	/**
	 * ID du contact.
	 *
	 * @var int|null
	 */
	public $id;

	/**
	 * Prénom du contact.
	 *
	 * @var string
	 */
	public $first_name;

	/**
	 * Nom de famille du contact.
	 *
	 * @var string
	 */
	public $last_name;

	/**
	 * Entreprise du contact.
	 *
	 * @var string|null
	 */
	public $company;

	/**
	 * Email du contact.
	 *
	 * @var string
	 */
	public $email;

	/**
	 * Téléphone du contact.
	 *
	 * @var string|null
	 */
	public $phone;

	/**
	 * Catégorie du contact (micrologiciel, scientifique, informatique).
	 *
	 * @var string
	 */
	public $category;

	/**
	 * Contexte de la discussion.
	 *
	 * @var string|null
	 */
	public $context;

	/**
	 * Lieu de rencontre.
	 *
	 * @var string|null
	 */
	public $meeting_location;

	/**
	 * Date de rencontre.
	 *
	 * @var string|null Format: Y-m-d
	 */
	public $meeting_date;

	/**
	 * Statut d'abonnement.
	 *
	 * @var bool
	 */
	public $is_subscribed;

	/**
	 * Date de création.
	 *
	 * @var string|null
	 */
	public $created_at;

	/**
	 * Date de mise à jour.
	 *
	 * @var string|null
	 */
	public $updated_at;

	/**
	 * Constructeur.
	 *
	 * @param array $data Données du contact.
	 */
	public function __construct( $data = array() ) {
		$this->id               = isset( $data['id'] ) ? (int) $data['id'] : null;
		$this->first_name       = isset( $data['first_name'] ) ? $data['first_name'] : '';
		$this->last_name        = isset( $data['last_name'] ) ? $data['last_name'] : '';
		$this->company          = isset( $data['company'] ) ? $data['company'] : null;
		$this->email            = isset( $data['email'] ) ? $data['email'] : '';
		$this->phone            = isset( $data['phone'] ) ? $data['phone'] : null;
		$this->category         = isset( $data['category'] ) ? $data['category'] : '';
		$this->context          = isset( $data['context'] ) ? $data['context'] : null;
		$this->meeting_location = isset( $data['meeting_location'] ) ? $data['meeting_location'] : null;
		$this->meeting_date     = isset( $data['meeting_date'] ) ? $data['meeting_date'] : null;
		$this->is_subscribed    = isset( $data['is_subscribed'] ) ? (bool) $data['is_subscribed'] : true;
		$this->created_at       = isset( $data['created_at'] ) ? $data['created_at'] : null;
		$this->updated_at       = isset( $data['updated_at'] ) ? $data['updated_at'] : null;
	}

	/**
	 * Valide les données du contact.
	 *
	 * @return array Tableau d'erreurs (vide si valide).
	 */
	public function validate() {
		$errors = array();

		// Validation du prénom
		if ( empty( $this->first_name ) ) {
			$errors['first_name'] = __( 'Le prénom est requis.', 'prospection-claude' );
		} elseif ( strlen( $this->first_name ) < 2 ) {
			$errors['first_name'] = __( 'Le prénom doit contenir au moins 2 caractères.', 'prospection-claude' );
		}

		// Validation du nom
		if ( empty( $this->last_name ) ) {
			$errors['last_name'] = __( 'Le nom est requis.', 'prospection-claude' );
		} elseif ( strlen( $this->last_name ) < 2 ) {
			$errors['last_name'] = __( 'Le nom doit contenir au moins 2 caractères.', 'prospection-claude' );
		}

		// Validation de l'email
		if ( empty( $this->email ) ) {
			$errors['email'] = __( 'L\'email est requis.', 'prospection-claude' );
		} elseif ( ! Prospection_Claude_Validator::is_valid_email( $this->email ) ) {
			$errors['email'] = __( 'L\'email n\'est pas valide.', 'prospection-claude' );
		}

		// Validation du téléphone (optionnel)
		if ( ! empty( $this->phone ) && ! Prospection_Claude_Validator::is_valid_phone( $this->phone ) ) {
			$errors['phone'] = __( 'Le numéro de téléphone n\'est pas valide.', 'prospection-claude' );
		}

		// Validation de la catégorie
		if ( empty( $this->category ) ) {
			$errors['category'] = __( 'La catégorie est requise.', 'prospection-claude' );
		} elseif ( ! Prospection_Claude_Validator::is_valid_category( $this->category ) ) {
			$errors['category'] = __( 'La catégorie n\'est pas valide.', 'prospection-claude' );
		}

		// Validation de la date de rencontre (optionnelle)
		if ( ! empty( $this->meeting_date ) && ! Prospection_Claude_Validator::is_valid_date( $this->meeting_date ) ) {
			$errors['meeting_date'] = __( 'La date de rencontre n\'est pas valide.', 'prospection-claude' );
		}

		return $errors;
	}

	/**
	 * Vérifie si le contact est valide.
	 *
	 * @return bool True si valide, false sinon.
	 */
	public function is_valid() {
		return empty( $this->validate() );
	}

	/**
	 * Convertit le contact en tableau.
	 *
	 * @return array Le contact sous forme de tableau.
	 */
	public function to_array() {
		return array(
			'id'               => $this->id,
			'first_name'       => $this->first_name,
			'last_name'        => $this->last_name,
			'company'          => $this->company,
			'email'            => $this->email,
			'phone'            => $this->phone,
			'category'         => $this->category,
			'context'          => $this->context,
			'meeting_location' => $this->meeting_location,
			'meeting_date'     => $this->meeting_date,
			'is_subscribed'    => $this->is_subscribed,
			'created_at'       => $this->created_at,
			'updated_at'       => $this->updated_at,
		);
	}

	/**
	 * Retourne le nom complet du contact.
	 *
	 * @return string Le nom complet.
	 */
	public function get_full_name() {
		return trim( $this->first_name . ' ' . $this->last_name );
	}

	/**
	 * Retourne le nom de la catégorie traduit.
	 *
	 * @return string Le nom de la catégorie.
	 */
	public function get_category_label() {
		$labels = array(
			'micrologiciel' => __( 'Micrologiciel', 'prospection-claude' ),
			'scientifique'  => __( 'Scientifique', 'prospection-claude' ),
			'informatique'  => __( 'Informatique', 'prospection-claude' ),
		);

		return isset( $labels[ $this->category ] ) ? $labels[ $this->category ] : $this->category;
	}
}
