<?php
/**
 * Service d'envoi d'emails.
 *
 * @package ProspectionClaude
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Email_Service pour l'envoi d'emails.
 *
 * Cette classe gère l'envoi d'emails aux contacts avec remplacement
 * des variables et logging des résultats.
 */
class Prospection_Claude_Email_Service {

	/**
	 * Repository des templates.
	 *
	 * @var Prospection_Claude_Template_Repository
	 */
	private $template_repository;

	/**
	 * Repository des logs.
	 *
	 * @var Prospection_Claude_Log_Repository
	 */
	private $log_repository;

	/**
	 * Constructeur.
	 */
	public function __construct() {
		$this->template_repository = new Prospection_Claude_Template_Repository();
		$this->log_repository      = new Prospection_Claude_Log_Repository();
	}

	/**
	 * Envoie un email à un contact pour une campagne donnée.
	 *
	 * @param Prospection_Claude_Campaign $campaign La campagne.
	 * @param Prospection_Claude_Contact  $contact Le contact.
	 * @return bool True en cas de succès, false sinon.
	 */
	public function send_campaign_email( $campaign, $contact ) {
		// Vérifier que le contact est abonné
		if ( ! $contact->is_subscribed ) {
			$this->log_email( $campaign->id, $contact->id, 'failed', 'Contact désabonné' );
			return false;
		}

		// Récupérer le template
		$template = $this->template_repository->find_by_id( $campaign->template_id );
		if ( ! $template ) {
			$this->log_email( $campaign->id, $contact->id, 'failed', 'Template non trouvé' );
			return false;
		}

		// Générer le lien de désinscription
		$unsubscribe_url = $this->generate_unsubscribe_link( $contact );

		// Remplacer les variables dans le template
		$email_data = $template->replace_variables( $contact, $unsubscribe_url );

		// Préparer l'email
		$to      = $contact->email;
		$subject = $email_data['subject'];
		$message = $email_data['content'];
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		// Envoyer l'email
		$sent = wp_mail( $to, $subject, $message, $headers );

		// Logger le résultat
		if ( $sent ) {
			$this->log_email( $campaign->id, $contact->id, 'sent' );
			return true;
		} else {
			$this->log_email( $campaign->id, $contact->id, 'failed', 'Erreur d\'envoi wp_mail' );
			return false;
		}
	}

	/**
	 * Envoie un email de test (sans campagne).
	 *
	 * @param Prospection_Claude_Email_Template $template Le template.
	 * @param Prospection_Claude_Contact        $contact Le contact.
	 * @return bool True en cas de succès, false sinon.
	 */
	public function send_test_email( $template, $contact ) {
		// Générer le lien de désinscription
		$unsubscribe_url = $this->generate_unsubscribe_link( $contact );

		// Remplacer les variables dans le template
		$email_data = $template->replace_variables( $contact, $unsubscribe_url );

		// Préparer l'email
		$to      = $contact->email;
		$subject = '[TEST] ' . $email_data['subject'];
		$message = $email_data['content'];
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		// Envoyer l'email
		return wp_mail( $to, $subject, $message, $headers );
	}

	/**
	 * Envoie des emails à tous les contacts d'une campagne.
	 *
	 * @param Prospection_Claude_Campaign $campaign La campagne.
	 * @param array                       $contacts Liste des contacts.
	 * @return array Statistiques d'envoi (sent, failed).
	 */
	public function send_campaign_to_contacts( $campaign, $contacts ) {
		$stats = array(
			'sent'   => 0,
			'failed' => 0,
		);

		foreach ( $contacts as $contact ) {
			if ( $this->send_campaign_email( $campaign, $contact ) ) {
				$stats['sent']++;
			} else {
				$stats['failed']++;
			}

			// Pause pour éviter de surcharger le serveur mail
			usleep( 100000 ); // 0.1 seconde entre chaque email
		}

		return $stats;
	}

	/**
	 * Génère un lien de désinscription pour un contact.
	 *
	 * @param Prospection_Claude_Contact $contact Le contact.
	 * @return string L'URL de désinscription.
	 */
	private function generate_unsubscribe_link( $contact ) {
		// Générer un token sécurisé
		$token = wp_hash( $contact->id . $contact->email . wp_salt() );

		// Construire l'URL
		$unsubscribe_url = add_query_arg(
			array(
				'prospection_unsubscribe' => 1,
				'contact_id'              => $contact->id,
				'token'                   => $token,
			),
			home_url( '/' )
		);

		return esc_url( $unsubscribe_url );
	}

	/**
	 * Vérifie un token de désinscription.
	 *
	 * @param int    $contact_id ID du contact.
	 * @param string $token Token à vérifier.
	 * @param Prospection_Claude_Contact $contact Le contact.
	 * @return bool True si valide, false sinon.
	 */
	public function verify_unsubscribe_token( $contact_id, $token, $contact ) {
		$expected_token = wp_hash( $contact->id . $contact->email . wp_salt() );
		return hash_equals( $expected_token, $token );
	}

	/**
	 * Enregistre un log d'email.
	 *
	 * @param int    $campaign_id ID de la campagne.
	 * @param int    $contact_id ID du contact.
	 * @param string $status Statut (sent, failed, bounced).
	 * @param string $error_message Message d'erreur éventuel.
	 */
	private function log_email( $campaign_id, $contact_id, $status, $error_message = '' ) {
		$log_data = array(
			'campaign_id'   => $campaign_id,
			'contact_id'    => $contact_id,
			'status'        => $status,
			'error_message' => $error_message,
			'sent_at'       => current_time( 'mysql' ),
		);

		$log = new Prospection_Claude_Email_Log( $log_data );
		$this->log_repository->create( $log );
	}

	/**
	 * Récupère les statistiques d'envoi pour une campagne.
	 *
	 * @param int $campaign_id ID de la campagne.
	 * @return array Statistiques (total, sent, failed, bounced).
	 */
	public function get_campaign_stats( $campaign_id ) {
		$all_logs = $this->log_repository->find_by_campaign( $campaign_id );

		$stats = array(
			'total'   => count( $all_logs ),
			'sent'    => 0,
			'failed'  => 0,
			'bounced' => 0,
		);

		foreach ( $all_logs as $log ) {
			if ( isset( $stats[ $log->status ] ) ) {
				$stats[ $log->status ]++;
			}
		}

		return $stats;
	}
}
