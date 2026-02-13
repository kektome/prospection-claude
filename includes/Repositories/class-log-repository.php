<?php
/**
 * Repository pour les Logs d'Emails.
 *
 * @package ProspectionClaude
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Log Repository.
 *
 * Cette classe gère toutes les opérations de base de données
 * pour les logs d'emails (CRUD + recherche).
 */
class Prospection_Claude_Log_Repository {

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
		$this->table_name = $wpdb->prefix . PROSPECTION_CLAUDE_TABLE_PREFIX . 'email_logs';
	}

	/**
	 * Crée un nouveau log.
	 *
	 * @param Prospection_Claude_Email_Log $log Le log à créer.
	 * @return int|false L'ID du log créé ou false en cas d'échec.
	 */
	public function create( $log ) {
		if ( ! $log->is_valid() ) {
			return false;
		}

		$data = array(
			'contact_id'    => $log->contact_id,
			'campaign_id'   => $log->campaign_id,
			'template_id'   => $log->template_id,
			'subject'       => sanitize_text_field( $log->subject ),
			'status'        => $log->status,
			'error_message' => $log->error_message,
			'sent_at'       => $log->sent_at,
		);

		$formats = array( '%d', '%d', '%d', '%s', '%s', '%s', '%s' );

		$result = $this->wpdb->insert( $this->table_name, $data, $formats );

		if ( $result === false ) {
			return false;
		}

		return $this->wpdb->insert_id;
	}

	/**
	 * Récupère un log par son ID.
	 *
	 * @param int $id L'ID du log.
	 * @return Prospection_Claude_Email_Log|null Le log ou null si non trouvé.
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

		return new Prospection_Claude_Email_Log( $result );
	}

	/**
	 * Met à jour un log.
	 *
	 * @param Prospection_Claude_Email_Log $log Le log à mettre à jour.
	 * @return bool True en cas de succès, false sinon.
	 */
	public function update( $log ) {
		if ( ! $log->id || ! $log->is_valid() ) {
			return false;
		}

		$data = array(
			'status'        => $log->status,
			'error_message' => $log->error_message,
			'sent_at'       => $log->sent_at,
		);

		$where         = array( 'id' => $log->id );
		$formats       = array( '%s', '%s', '%s' );
		$where_formats = array( '%d' );

		$result = $this->wpdb->update( $this->table_name, $data, $where, $formats, $where_formats );

		return $result !== false;
	}

	/**
	 * Supprime un log.
	 *
	 * @param int $id L'ID du log à supprimer.
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
	 * Récupère tous les logs avec pagination.
	 *
	 * @param int    $limit Nombre de résultats par page.
	 * @param int    $offset Offset pour la pagination.
	 * @param string $orderby Colonne pour le tri.
	 * @param string $order Direction du tri (ASC ou DESC).
	 * @return array Tableau de logs.
	 */
	public function find_all( $limit = 50, $offset = 0, $orderby = 'created_at', $order = 'DESC' ) {
		$allowed_orderby = array( 'id', 'status', 'sent_at', 'created_at' );
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
				return new Prospection_Claude_Email_Log( $row );
			},
			$results
		);
	}

	/**
	 * Récupère les logs par contact.
	 *
	 * @param int $contact_id L'ID du contact.
	 * @param int $limit Limite de résultats.
	 * @return array Tableau de logs.
	 */
	public function find_by_contact( $contact_id, $limit = 50 ) {
		$contact_id = Prospection_Claude_Validator::validate_id( $contact_id );
		if ( ! $contact_id ) {
			return array();
		}

		$query = $this->wpdb->prepare(
			"SELECT * FROM {$this->table_name} WHERE contact_id = %d ORDER BY created_at DESC LIMIT %d",
			$contact_id,
			$limit
		);

		$results = $this->wpdb->get_results( $query, ARRAY_A );

		return array_map(
			function ( $row ) {
				return new Prospection_Claude_Email_Log( $row );
			},
			$results
		);
	}

	/**
	 * Récupère les logs par campagne.
	 *
	 * @param int $campaign_id L'ID de la campagne.
	 * @param int $limit Limite de résultats.
	 * @return array Tableau de logs.
	 */
	public function find_by_campaign( $campaign_id, $limit = 50 ) {
		$campaign_id = Prospection_Claude_Validator::validate_id( $campaign_id );
		if ( ! $campaign_id ) {
			return array();
		}

		$query = $this->wpdb->prepare(
			"SELECT * FROM {$this->table_name} WHERE campaign_id = %d ORDER BY created_at DESC LIMIT %d",
			$campaign_id,
			$limit
		);

		$results = $this->wpdb->get_results( $query, ARRAY_A );

		return array_map(
			function ( $row ) {
				return new Prospection_Claude_Email_Log( $row );
			},
			$results
		);
	}

	/**
	 * Récupère les logs par statut.
	 *
	 * @param string $status Le statut.
	 * @param int    $limit Limite de résultats.
	 * @return array Tableau de logs.
	 */
	public function find_by_status( $status, $limit = 50 ) {
		if ( ! Prospection_Claude_Validator::is_valid_email_status( $status ) ) {
			return array();
		}

		$query = $this->wpdb->prepare(
			"SELECT * FROM {$this->table_name} WHERE status = %s ORDER BY created_at DESC LIMIT %d",
			$status,
			$limit
		);

		$results = $this->wpdb->get_results( $query, ARRAY_A );

		return array_map(
			function ( $row ) {
				return new Prospection_Claude_Email_Log( $row );
			},
			$results
		);
	}

	/**
	 * Compte le nombre total de logs.
	 *
	 * @param array $filters Filtres optionnels (status, contact_id, campaign_id).
	 * @return int Le nombre total de logs.
	 */
	public function count( $filters = array() ) {
		$query = "SELECT COUNT(*) FROM {$this->table_name} WHERE 1=1";
		$args  = array();

		if ( isset( $filters['status'] ) && Prospection_Claude_Validator::is_valid_email_status( $filters['status'] ) ) {
			$query  .= ' AND status = %s';
			$args[] = $filters['status'];
		}

		if ( isset( $filters['contact_id'] ) ) {
			$query  .= ' AND contact_id = %d';
			$args[] = (int) $filters['contact_id'];
		}

		if ( isset( $filters['campaign_id'] ) ) {
			$query  .= ' AND campaign_id = %d';
			$args[] = (int) $filters['campaign_id'];
		}

		if ( ! empty( $args ) ) {
			$query = $this->wpdb->prepare( $query, $args );
		}

		return (int) $this->wpdb->get_var( $query );
	}

	/**
	 * Récupère les statistiques d'envoi.
	 *
	 * @return array Statistiques par statut.
	 */
	public function get_statistics() {
		$query = "SELECT status, COUNT(*) as count
				  FROM {$this->table_name}
				  GROUP BY status";

		$results = $this->wpdb->get_results( $query, ARRAY_A );

		$stats = array(
			'pending' => 0,
			'sent'    => 0,
			'failed'  => 0,
			'bounced' => 0,
			'total'   => 0,
		);

		foreach ( $results as $row ) {
			$stats[ $row['status'] ] = (int) $row['count'];
			$stats['total']         += (int) $row['count'];
		}

		return $stats;
	}
}
