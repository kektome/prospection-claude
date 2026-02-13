<?php
/**
 * Repository pour les Campagnes.
 *
 * @package ProspectionClaude
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Campaign Repository.
 *
 * Cette classe gère toutes les opérations de base de données
 * pour les campagnes (CRUD + recherche).
 */
class Prospection_Claude_Campaign_Repository {

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
		$this->table_name = $wpdb->prefix . PROSPECTION_CLAUDE_TABLE_PREFIX . 'campaigns';
	}

	/**
	 * Crée une nouvelle campagne.
	 *
	 * @param Prospection_Claude_Campaign $campaign La campagne à créer.
	 * @return int|false L'ID de la campagne créée ou false en cas d'échec.
	 */
	public function create( $campaign ) {
		if ( ! $campaign->is_valid() ) {
			return false;
		}

		$data = array(
			'name'              => sanitize_text_field( $campaign->name ),
			'template_id'       => $campaign->template_id,
			'target_categories' => $campaign->target_categories,
			'schedule_type'     => $campaign->schedule_type,
			'schedule_config'   => $campaign->schedule_config,
			'next_run'          => $campaign->next_run,
			'is_active'         => $campaign->is_active ? 1 : 0,
		);

		$formats = array( '%s', '%d', '%s', '%s', '%s', '%s', '%d' );

		$result = $this->wpdb->insert( $this->table_name, $data, $formats );

		if ( $result === false ) {
			return false;
		}

		return $this->wpdb->insert_id;
	}

	/**
	 * Récupère une campagne par son ID.
	 *
	 * @param int $id L'ID de la campagne.
	 * @return Prospection_Claude_Campaign|null La campagne ou null si non trouvée.
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

		return new Prospection_Claude_Campaign( $result );
	}

	/**
	 * Met à jour une campagne.
	 *
	 * @param Prospection_Claude_Campaign $campaign La campagne à mettre à jour.
	 * @return bool True en cas de succès, false sinon.
	 */
	public function update( $campaign ) {
		if ( ! $campaign->id || ! $campaign->is_valid() ) {
			return false;
		}

		$data = array(
			'name'              => sanitize_text_field( $campaign->name ),
			'template_id'       => $campaign->template_id,
			'target_categories' => $campaign->target_categories,
			'schedule_type'     => $campaign->schedule_type,
			'schedule_config'   => $campaign->schedule_config,
			'next_run'          => $campaign->next_run,
			'is_active'         => $campaign->is_active ? 1 : 0,
		);

		$where         = array( 'id' => $campaign->id );
		$formats       = array( '%s', '%d', '%s', '%s', '%s', '%s', '%d' );
		$where_formats = array( '%d' );

		$result = $this->wpdb->update( $this->table_name, $data, $where, $formats, $where_formats );

		return $result !== false;
	}

	/**
	 * Supprime une campagne.
	 *
	 * @param int $id L'ID de la campagne à supprimer.
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
	 * Récupère toutes les campagnes avec pagination.
	 *
	 * @param int    $limit Nombre de résultats par page.
	 * @param int    $offset Offset pour la pagination.
	 * @param string $orderby Colonne pour le tri.
	 * @param string $order Direction du tri (ASC ou DESC).
	 * @return array Tableau de campagnes.
	 */
	public function find_all( $limit = 50, $offset = 0, $orderby = 'created_at', $order = 'DESC' ) {
		$allowed_orderby = array( 'id', 'name', 'next_run', 'is_active', 'created_at' );
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
				return new Prospection_Claude_Campaign( $row );
			},
			$results
		);
	}

	/**
	 * Récupère les campagnes actives.
	 *
	 * @return array Tableau de campagnes.
	 */
	public function find_active() {
		$query = "SELECT * FROM {$this->table_name} WHERE is_active = 1 ORDER BY next_run ASC";

		$results = $this->wpdb->get_results( $query, ARRAY_A );

		return array_map(
			function ( $row ) {
				return new Prospection_Claude_Campaign( $row );
			},
			$results
		);
	}

	/**
	 * Récupère les campagnes à exécuter maintenant.
	 *
	 * @return array Tableau de campagnes.
	 */
	public function find_due_campaigns() {
		$current_time = current_time( 'mysql' );

		$query = $this->wpdb->prepare(
			"SELECT * FROM {$this->table_name}
			WHERE is_active = 1
			AND next_run IS NOT NULL
			AND next_run <= %s
			ORDER BY next_run ASC",
			$current_time
		);

		$results = $this->wpdb->get_results( $query, ARRAY_A );

		return array_map(
			function ( $row ) {
				return new Prospection_Claude_Campaign( $row );
			},
			$results
		);
	}

	/**
	 * Compte le nombre total de campagnes.
	 *
	 * @param array $filters Filtres optionnels (is_active).
	 * @return int Le nombre total de campagnes.
	 */
	public function count( $filters = array() ) {
		$query = "SELECT COUNT(*) FROM {$this->table_name} WHERE 1=1";
		$args  = array();

		if ( isset( $filters['is_active'] ) ) {
			$query  .= ' AND is_active = %d';
			$args[] = $filters['is_active'] ? 1 : 0;
		}

		if ( ! empty( $args ) ) {
			$query = $this->wpdb->prepare( $query, $args );
		}

		return (int) $this->wpdb->get_var( $query );
	}

	/**
	 * Active ou désactive une campagne.
	 *
	 * @param int  $id L'ID de la campagne.
	 * @param bool $is_active Le nouveau statut.
	 * @return bool True en cas de succès, false sinon.
	 */
	public function toggle_active( $id, $is_active ) {
		$id = Prospection_Claude_Validator::validate_id( $id );
		if ( ! $id ) {
			return false;
		}

		$data          = array( 'is_active' => $is_active ? 1 : 0 );
		$where         = array( 'id' => $id );
		$formats       = array( '%d' );
		$where_formats = array( '%d' );

		$result = $this->wpdb->update( $this->table_name, $data, $where, $formats, $where_formats );

		return $result !== false;
	}
}
