<?php
/**
 * Classe Model EmailTemplate.
 *
 * @package ProspectionClaude
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe représentant un Template d'Email.
 *
 * Cette classe représente une entité EmailTemplate avec ses propriétés
 * et méthodes de validation.
 */
class Prospection_Claude_Email_Template {

	/**
	 * ID du template.
	 *
	 * @var int|null
	 */
	public $id;

	/**
	 * Nom du template.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Sujet de l'email.
	 *
	 * @var string
	 */
	public $subject;

	/**
	 * Contenu HTML de l'email.
	 *
	 * @var string
	 */
	public $content;

	/**
	 * Catégorie ciblée (micrologiciel, scientifique, informatique, all).
	 *
	 * @var string
	 */
	public $category;

	/**
	 * Variables disponibles dans le template (JSON).
	 *
	 * @var string|null
	 */
	public $variables;

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
	 * @param array $data Données du template.
	 */
	public function __construct( $data = array() ) {
		$this->id         = isset( $data['id'] ) ? (int) $data['id'] : null;
		$this->name       = isset( $data['name'] ) ? $data['name'] : '';
		$this->subject    = isset( $data['subject'] ) ? $data['subject'] : '';
		$this->content    = isset( $data['content'] ) ? $data['content'] : '';
		$this->category   = isset( $data['category'] ) ? $data['category'] : 'all';
		$this->variables  = isset( $data['variables'] ) ? $data['variables'] : null;
		$this->created_at = isset( $data['created_at'] ) ? $data['created_at'] : null;
		$this->updated_at = isset( $data['updated_at'] ) ? $data['updated_at'] : null;
	}

	/**
	 * Valide les données du template.
	 *
	 * @return array Tableau d'erreurs (vide si valide).
	 */
	public function validate() {
		$errors = array();

		// Validation du nom
		if ( empty( $this->name ) ) {
			$errors['name'] = __( 'Le nom du template est requis.', 'prospection-claude' );
		} elseif ( strlen( $this->name ) < 3 ) {
			$errors['name'] = __( 'Le nom doit contenir au moins 3 caractères.', 'prospection-claude' );
		}

		// Validation du sujet
		if ( empty( $this->subject ) ) {
			$errors['subject'] = __( 'Le sujet de l\'email est requis.', 'prospection-claude' );
		} elseif ( strlen( $this->subject ) < 5 ) {
			$errors['subject'] = __( 'Le sujet doit contenir au moins 5 caractères.', 'prospection-claude' );
		}

		// Validation du contenu
		if ( empty( $this->content ) ) {
			$errors['content'] = __( 'Le contenu de l\'email est requis.', 'prospection-claude' );
		} elseif ( strlen( $this->content ) < 20 ) {
			$errors['content'] = __( 'Le contenu doit contenir au moins 20 caractères.', 'prospection-claude' );
		}

		// Validation de la catégorie
		if ( ! Prospection_Claude_Validator::is_valid_template_category( $this->category ) ) {
			$errors['category'] = __( 'La catégorie n\'est pas valide.', 'prospection-claude' );
		}

		return $errors;
	}

	/**
	 * Vérifie si le template est valide.
	 *
	 * @return bool True si valide, false sinon.
	 */
	public function is_valid() {
		return empty( $this->validate() );
	}

	/**
	 * Convertit le template en tableau.
	 *
	 * @return array Le template sous forme de tableau.
	 */
	public function to_array() {
		return array(
			'id'         => $this->id,
			'name'       => $this->name,
			'subject'    => $this->subject,
			'content'    => $this->content,
			'category'   => $this->category,
			'variables'  => $this->variables,
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at,
		);
	}

	/**
	 * Retourne les variables disponibles sous forme de tableau.
	 *
	 * @return array Les variables disponibles.
	 */
	public function get_available_variables() {
		if ( ! empty( $this->variables ) ) {
			$vars = json_decode( $this->variables, true );
			if ( is_array( $vars ) ) {
				return $vars;
			}
		}

		// Variables par défaut
		return array(
			'{first_name}'        => __( 'Prénom du contact', 'prospection-claude' ),
			'{last_name}'         => __( 'Nom du contact', 'prospection-claude' ),
			'{full_name}'         => __( 'Nom complet du contact', 'prospection-claude' ),
			'{company}'           => __( 'Entreprise', 'prospection-claude' ),
			'{email}'             => __( 'Email', 'prospection-claude' ),
			'{unsubscribe_link}' => __( 'Lien de désinscription', 'prospection-claude' ),
		);
	}

	/**
	 * Remplace les variables dans le contenu avec les valeurs du contact.
	 *
	 * @param Prospection_Claude_Contact $contact Le contact.
	 * @param string                     $unsubscribe_url L'URL de désinscription.
	 * @return string Le contenu avec les variables remplacées.
	 */
	public function replace_variables( $contact, $unsubscribe_url = '' ) {
		$content = $this->content;
		$subject = $this->subject;

		// Remplacement des variables
		$replacements = array(
			'{first_name}'        => esc_html( $contact->first_name ),
			'{last_name}'         => esc_html( $contact->last_name ),
			'{full_name}'         => esc_html( $contact->get_full_name() ),
			'{company}'           => esc_html( $contact->company ),
			'{email}'             => esc_html( $contact->email ),
			'{unsubscribe_link}' => $unsubscribe_url,
		);

		foreach ( $replacements as $variable => $value ) {
			$content = str_replace( $variable, $value, $content );
			$subject = str_replace( $variable, $value, $subject );
		}

		return array(
			'subject' => $subject,
			'content' => $content,
		);
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
			'all'           => __( 'Tous', 'prospection-claude' ),
		);

		return isset( $labels[ $this->category ] ) ? $labels[ $this->category ] : $this->category;
	}
}
