<?php
/**
 * Repository pour les Templates d'Emails.
 *
 * @package ProspectionClaude
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Template Repository.
 *
 * Cette classe gère toutes les opérations de base de données
 * pour les templates d'emails (CRUD + recherche).
 */
class Prospection_Claude_Template_Repository {

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
		$this->table_name = $wpdb->prefix . PROSPECTION_CLAUDE_TABLE_PREFIX . 'email_templates';
	}

	/**
	 * Crée un nouveau template.
	 *
	 * @param Prospection_Claude_Email_Template $template Le template à créer.
	 * @return int|false L'ID du template créé ou false en cas d'échec.
	 */
	public function create( $template ) {
		if ( ! $template->is_valid() ) {
			return false;
		}

		$data = array(
			'name'      => sanitize_text_field( $template->name ),
			'subject'   => sanitize_text_field( $template->subject ),
			'content'   => Prospection_Claude_Validator::sanitize_html_content( $template->content ),
			'category'  => $template->category,
			'variables' => $template->variables,
		);

		$formats = array( '%s', '%s', '%s', '%s', '%s' );

		$result = $this->wpdb->insert( $this->table_name, $data, $formats );

		if ( $result === false ) {
			return false;
		}

		return $this->wpdb->insert_id;
	}

	/**
	 * Récupère un template par son ID.
	 *
	 * @param int $id L'ID du template.
	 * @return Prospection_Claude_Email_Template|null Le template ou null si non trouvé.
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

		return new Prospection_Claude_Email_Template( $result );
	}

	/**
	 * Met à jour un template.
	 *
	 * @param Prospection_Claude_Email_Template $template Le template à mettre à jour.
	 * @return bool True en cas de succès, false sinon.
	 */
	public function update( $template ) {
		if ( ! $template->id || ! $template->is_valid() ) {
			return false;
		}

		$data = array(
			'name'      => sanitize_text_field( $template->name ),
			'subject'   => sanitize_text_field( $template->subject ),
			'content'   => Prospection_Claude_Validator::sanitize_html_content( $template->content ),
			'category'  => $template->category,
			'variables' => $template->variables,
		);

		$where         = array( 'id' => $template->id );
		$formats       = array( '%s', '%s', '%s', '%s', '%s' );
		$where_formats = array( '%d' );

		$result = $this->wpdb->update( $this->table_name, $data, $where, $formats, $where_formats );

		return $result !== false;
	}

	/**
	 * Supprime un template.
	 *
	 * @param int $id L'ID du template à supprimer.
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
	 * Récupère tous les templates avec pagination.
	 *
	 * @param int    $limit Nombre de résultats par page.
	 * @param int    $offset Offset pour la pagination.
	 * @param string $orderby Colonne pour le tri.
	 * @param string $order Direction du tri (ASC ou DESC).
	 * @return array Tableau de templates.
	 */
	public function find_all( $limit = 50, $offset = 0, $orderby = 'created_at', $order = 'DESC' ) {
		$allowed_orderby = array( 'id', 'name', 'category', 'created_at' );
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
				return new Prospection_Claude_Email_Template( $row );
			},
			$results
		);
	}

	/**
	 * Récupère les templates par catégorie.
	 *
	 * @param string $category La catégorie.
	 * @return array Tableau de templates.
	 */
	public function find_by_category( $category ) {
		if ( ! Prospection_Claude_Validator::is_valid_template_category( $category ) ) {
			return array();
		}

		$query = $this->wpdb->prepare(
			"SELECT * FROM {$this->table_name} WHERE category = %s OR category = 'all' ORDER BY created_at DESC",
			$category
		);

		$results = $this->wpdb->get_results( $query, ARRAY_A );

		return array_map(
			function ( $row ) {
				return new Prospection_Claude_Email_Template( $row );
			},
			$results
		);
	}

	/**
	 * Compte le nombre total de templates.
	 *
	 * @param array $filters Filtres optionnels (category).
	 * @return int Le nombre total de templates.
	 */
	public function count( $filters = array() ) {
		$query = "SELECT COUNT(*) FROM {$this->table_name} WHERE 1=1";
		$args  = array();

		if ( isset( $filters['category'] ) && Prospection_Claude_Validator::is_valid_template_category( $filters['category'] ) ) {
			$query  .= ' AND category = %s';
			$args[] = $filters['category'];
		}

		if ( ! empty( $args ) ) {
			$query = $this->wpdb->prepare( $query, $args );
		}

		return (int) $this->wpdb->get_var( $query );
	}
}
