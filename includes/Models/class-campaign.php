<?php
/**
 * Classe Model Campaign.
 *
 * @package ProspectionClaude
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe représentant une Campagne.
 *
 * Cette classe représente une entité Campaign avec ses propriétés
 * et méthodes de validation.
 */
class Prospection_Claude_Campaign {

	/**
	 * ID de la campagne.
	 *
	 * @var int|null
	 */
	public $id;

	/**
	 * Nom de la campagne.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * ID du template d'email.
	 *
	 * @var int
	 */
	public $template_id;

	/**
	 * Catégories ciblées (JSON array).
	 *
	 * @var string
	 */
	public $target_categories;

	/**
	 * Type de scheduling (daily, weekly, monthly, custom).
	 *
	 * @var string
	 */
	public $schedule_type;

	/**
	 * Configuration du scheduling (JSON).
	 *
	 * @var string|null
	 */
	public $schedule_config;

	/**
	 * Prochaine exécution.
	 *
	 * @var string|null
	 */
	public $next_run;

	/**
	 * Statut actif/inactif.
	 *
	 * @var bool
	 */
	public $is_active;

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
	 * @param array $data Données de la campagne.
	 */
	public function __construct( $data = array() ) {
		$this->id                = isset( $data['id'] ) ? (int) $data['id'] : null;
		$this->name              = isset( $data['name'] ) ? $data['name'] : '';
		$this->template_id       = isset( $data['template_id'] ) ? (int) $data['template_id'] : 0;
		$this->target_categories = isset( $data['target_categories'] ) ? $data['target_categories'] : '';
		$this->schedule_type     = isset( $data['schedule_type'] ) ? $data['schedule_type'] : '';
		$this->schedule_config   = isset( $data['schedule_config'] ) ? $data['schedule_config'] : null;
		$this->next_run          = isset( $data['next_run'] ) ? $data['next_run'] : null;
		$this->is_active         = isset( $data['is_active'] ) ? (bool) $data['is_active'] : true;
		$this->created_at        = isset( $data['created_at'] ) ? $data['created_at'] : null;
		$this->updated_at        = isset( $data['updated_at'] ) ? $data['updated_at'] : null;
	}

	/**
	 * Valide les données de la campagne.
	 *
	 * @return array Tableau d'erreurs (vide si valide).
	 */
	public function validate() {
		$errors = array();

		// Validation du nom
		if ( empty( $this->name ) ) {
			$errors['name'] = __( 'Le nom de la campagne est requis.', 'prospection-claude' );
		} elseif ( strlen( $this->name ) < 3 ) {
			$errors['name'] = __( 'Le nom doit contenir au moins 3 caractères.', 'prospection-claude' );
		}

		// Validation du template_id
		if ( empty( $this->template_id ) || $this->template_id <= 0 ) {
			$errors['template_id'] = __( 'Un template d\'email doit être sélectionné.', 'prospection-claude' );
		}

		// Validation des catégories cibles
		if ( empty( $this->target_categories ) ) {
			$errors['target_categories'] = __( 'Au moins une catégorie doit être sélectionnée.', 'prospection-claude' );
		}

		// Validation du type de scheduling
		if ( empty( $this->schedule_type ) ) {
			$errors['schedule_type'] = __( 'Le type de scheduling est requis.', 'prospection-claude' );
		} elseif ( ! Prospection_Claude_Validator::is_valid_schedule_type( $this->schedule_type ) ) {
			$errors['schedule_type'] = __( 'Le type de scheduling n\'est pas valide.', 'prospection-claude' );
		}

		return $errors;
	}

	/**
	 * Vérifie si la campagne est valide.
	 *
	 * @return bool True si valide, false sinon.
	 */
	public function is_valid() {
		return empty( $this->validate() );
	}

	/**
	 * Convertit la campagne en tableau.
	 *
	 * @return array La campagne sous forme de tableau.
	 */
	public function to_array() {
		return array(
			'id'                => $this->id,
			'name'              => $this->name,
			'template_id'       => $this->template_id,
			'target_categories' => $this->target_categories,
			'schedule_type'     => $this->schedule_type,
			'schedule_config'   => $this->schedule_config,
			'next_run'          => $this->next_run,
			'is_active'         => $this->is_active,
			'created_at'        => $this->created_at,
			'updated_at'        => $this->updated_at,
		);
	}

	/**
	 * Retourne les catégories cibles sous forme de tableau.
	 *
	 * @return array Les catégories cibles.
	 */
	public function get_target_categories_array() {
		$categories = json_decode( $this->target_categories, true );
		return is_array( $categories ) ? $categories : array();
	}

	/**
	 * Définit les catégories cibles depuis un tableau.
	 *
	 * @param array $categories Les catégories cibles.
	 */
	public function set_target_categories_array( $categories ) {
		$this->target_categories = wp_json_encode( $categories );
	}

	/**
	 * Retourne la configuration du scheduling sous forme de tableau.
	 *
	 * @return array La configuration.
	 */
	public function get_schedule_config_array() {
		if ( empty( $this->schedule_config ) ) {
			return array();
		}

		$config = json_decode( $this->schedule_config, true );
		return is_array( $config ) ? $config : array();
	}

	/**
	 * Définit la configuration du scheduling depuis un tableau.
	 *
	 * @param array $config La configuration.
	 */
	public function set_schedule_config_array( $config ) {
		$this->schedule_config = wp_json_encode( $config );
	}

	/**
	 * Retourne le nom du type de scheduling traduit.
	 *
	 * @return string Le nom du type.
	 */
	public function get_schedule_type_label() {
		$labels = array(
			'daily'   => __( 'Quotidien', 'prospection-claude' ),
			'weekly'  => __( 'Hebdomadaire', 'prospection-claude' ),
			'monthly' => __( 'Mensuel', 'prospection-claude' ),
			'custom'  => __( 'Personnalisé', 'prospection-claude' ),
		);

		return isset( $labels[ $this->schedule_type ] ) ? $labels[ $this->schedule_type ] : $this->schedule_type;
	}

	/**
	 * Retourne le statut en texte.
	 *
	 * @return string Le statut.
	 */
	public function get_status_label() {
		return $this->is_active ? __( 'Active', 'prospection-claude' ) : __( 'Inactive', 'prospection-claude' );
	}
}
