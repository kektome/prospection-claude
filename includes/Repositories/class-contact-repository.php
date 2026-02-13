<?php
/**
 * Repository pour les Contacts.
 *
 * @package ProspectionClaude
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Contact Repository.
 *
 * Cette classe gère toutes les opérations de base de données
 * pour les contacts (CRUD + recherche).
 */
class Prospection_Claude_Contact_Repository {

	/**
	 * Instance de $wpdb.
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Nom de la table.
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Constructeur.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb       = $wpdb;
		$this->table_name = $wpdb->prefix . PROSPECTION_CLAUDE_TABLE_PREFIX . 'contacts';
	}

	/**
	 * Crée un nouveau contact.
	 *
	 * @param Prospection_Claude_Contact $contact Le contact à créer.
	 * @return int|false L'ID du contact créé ou false en cas d'échec.
	 */
	public function create( $contact ) {
		// Valider le contact
		if ( ! $contact->is_valid() ) {
			return false;
		}

		$data = array(
			'first_name'       => sanitize_text_field( $contact->first_name ),
			'last_name'        => sanitize_text_field( $contact->last_name ),
			'company'          => sanitize_text_field( $contact->company ),
			'email'            => sanitize_email( $contact->email ),
			'phone'            => sanitize_text_field( $contact->phone ),
			'category'         => $contact->category,
			'context'          => wp_kses_post( $contact->context ),
			'meeting_location' => sanitize_text_field( $contact->meeting_location ),
			'meeting_date'     => $contact->meeting_date,
			'is_subscribed'    => $contact->is_subscribed ? 1 : 0,
		);

		$formats = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d' );

		$result = $this->wpdb->insert( $this->table_name, $data, $formats );

		if ( $result === false ) {
			return false;
		}

		return $this->wpdb->insert_id;
	}

	/**
	 * Récupère un contact par son ID.
	 *
	 * @param int $id L'ID du contact.
	 * @return Prospection_Claude_Contact|null Le contact ou null si non trouvé.
	 */
	public function find_by_id( $id ) {
		$id = Prospection_Claude_Validator::validate_id( $id );
		if ( ! $id ) {
			return null;
		}

		$query = $this->wpdb->prepare(
			"SELECT * FROM {$this->table_name} WHERE id = %d",
			$id
		);

		$result = $this->wpdb->get_row( $query, ARRAY_A );

		if ( ! $result ) {
			return null;
		}

		return new Prospection_Claude_Contact( $result );
	}

	/**
	 * Récupère un contact par son email.
	 *
	 * @param string $email L'email du contact.
	 * @return Prospection_Claude_Contact|null Le contact ou null si non trouvé.
	 */
	public function find_by_email( $email ) {
		$email = Prospection_Claude_Validator::sanitize_email( $email );
		if ( ! $email ) {
			return null;
		}

		$query = $this->wpdb->prepare(
			"SELECT * FROM {$this->table_name} WHERE email = %s",
			$email
		);

		$result = $this->wpdb->get_row( $query, ARRAY_A );

		if ( ! $result ) {
			return null;
		}

		return new Prospection_Claude_Contact( $result );
	}

	/**
	 * Met à jour un contact.
	 *
	 * @param Prospection_Claude_Contact $contact Le contact à mettre à jour.
	 * @return bool True en cas de succès, false sinon.
	 */
	public function update( $contact ) {
		if ( ! $contact->id || ! $contact->is_valid() ) {
			return false;
		}

		$data = array(
			'first_name'       => sanitize_text_field( $contact->first_name ),
			'last_name'        => sanitize_text_field( $contact->last_name ),
			'company'          => sanitize_text_field( $contact->company ),
			'email'            => sanitize_email( $contact->email ),
			'phone'            => sanitize_text_field( $contact->phone ),
			'category'         => $contact->category,
			'context'          => wp_kses_post( $contact->context ),
			'meeting_location' => sanitize_text_field( $contact->meeting_location ),
			'meeting_date'     => $contact->meeting_date,
			'is_subscribed'    => $contact->is_subscribed ? 1 : 0,
		);

		$where = array( 'id' => $contact->id );

		$formats       = array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d' );
		$where_formats = array( '%d' );

		$result = $this->wpdb->update( $this->table_name, $data, $where, $formats, $where_formats );

		return $result !== false;
	}

	/**
	 * Supprime un contact.
	 *
	 * @param int $id L'ID du contact à supprimer.
	 * @return bool True en cas de succès, false sinon.
	 */
	public function delete( $id ) {
		$id = Prospection_Claude_Validator::validate_id( $id );
		if ( ! $id ) {
			return false;
		}

		$where         = array( 'id' => $id );
		$where_formats = array( '%d' );

		$result = $this->wpdb->delete( $this->table_name, $where, $where_formats );

		return $result !== false;
	}

	/**
	 * Récupère tous les contacts avec pagination.
	 *
	 * @param int    $limit Nombre de résultats par page.
	 * @param int    $offset Offset pour la pagination.
	 * @param string $orderby Colonne pour le tri.
	 * @param string $order Direction du tri (ASC ou DESC).
	 * @return array Tableau de contacts.
	 */
	public function find_all( $limit = 50, $offset = 0, $orderby = 'created_at', $order = 'DESC' ) {
		$allowed_orderby = array( 'id', 'first_name', 'last_name', 'email', 'company', 'category', 'created_at' );
		$orderby         = in_array( $orderby, $allowed_orderby, true ) ? $orderby : 'created_at';
		$order           = in_array( strtoupper( $order ), array( 'ASC', 'DESC' ), true ) ? strtoupper( $order ) : 'DESC';

		$query = $this->wpdb->prepare(
			"SELECT * FROM {$this->table_name} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
			$limit,
			$offset
		);

		$results = $this->wpdb->get_results( $query, ARRAY_A );

		return array_map(
			function ( $row ) {
				return new Prospection_Claude_Contact( $row );
			},
			$results
		);
	}

	/**
	 * Récupère les contacts par catégorie.
	 *
	 * @param string $category La catégorie.
	 * @param bool   $subscribed_only Seulement les abonnés.
	 * @return array Tableau de contacts.
	 */
	public function find_by_category( $category, $subscribed_only = false ) {
		if ( ! Prospection_Claude_Validator::is_valid_category( $category ) ) {
			return array();
		}

		$query = "SELECT * FROM {$this->table_name} WHERE category = %s";

		if ( $subscribed_only ) {
			$query .= ' AND is_subscribed = 1';
		}

		$query .= ' ORDER BY created_at DESC';

		$query = $this->wpdb->prepare( $query, $category );

		$results = $this->wpdb->get_results( $query, ARRAY_A );

		return array_map(
			function ( $row ) {
				return new Prospection_Claude_Contact( $row );
			},
			$results
		);
	}

	/**
	 * Recherche des contacts par terme.
	 *
	 * @param string $search_term Le terme de recherche.
	 * @param int    $limit Limite de résultats.
	 * @return array Tableau de contacts.
	 */
	public function search( $search_term, $limit = 50 ) {
		$search_term = sanitize_text_field( $search_term );
		$like        = '%' . $this->wpdb->esc_like( $search_term ) . '%';

		$query = $this->wpdb->prepare(
			"SELECT * FROM {$this->table_name}
			WHERE first_name LIKE %s
			OR last_name LIKE %s
			OR email LIKE %s
			OR company LIKE %s
			ORDER BY created_at DESC
			LIMIT %d",
			$like,
			$like,
			$like,
			$like,
			$limit
		);

		$results = $this->wpdb->get_results( $query, ARRAY_A );

		return array_map(
			function ( $row ) {
				return new Prospection_Claude_Contact( $row );
			},
			$results
		);
	}

	/**
	 * Compte le nombre total de contacts.
	 *
	 * @param array $filters Filtres optionnels (category, is_subscribed).
	 * @return int Le nombre total de contacts.
	 */
	public function count( $filters = array() ) {
		$query = "SELECT COUNT(*) FROM {$this->table_name} WHERE 1=1";
		$args  = array();

		if ( isset( $filters['category'] ) && Prospection_Claude_Validator::is_valid_category( $filters['category'] ) ) {
			$query  .= ' AND category = %s';
			$args[] = $filters['category'];
		}

		if ( isset( $filters['is_subscribed'] ) ) {
			$query  .= ' AND is_subscribed = %d';
			$args[] = $filters['is_subscribed'] ? 1 : 0;
		}

		if ( ! empty( $args ) ) {
			$query = $this->wpdb->prepare( $query, $args );
		}

		return (int) $this->wpdb->get_var( $query );
	}

	/**
	 * Marque un contact comme désinscrit.
	 *
	 * @param int $id L'ID du contact.
	 * @return bool True en cas de succès, false sinon.
	 */
	public function unsubscribe( $id ) {
		$id = Prospection_Claude_Validator::validate_id( $id );
		if ( ! $id ) {
			return false;
		}

		$data          = array( 'is_subscribed' => 0 );
		$where         = array( 'id' => $id );
		$formats       = array( '%d' );
		$where_formats = array( '%d' );

		$result = $this->wpdb->update( $this->table_name, $data, $where, $formats, $where_formats );

		return $result !== false;
	}
}
