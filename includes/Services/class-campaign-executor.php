<?php
/**
 * Service d'exécution des campagnes.
 *
 * @package ProspectionClaude
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Campaign_Executor pour l'exécution des campagnes.
 *
 * Cette classe gère l'exécution automatique des campagnes planifiées.
 */
class Prospection_Claude_Campaign_Executor {

	/**
	 * Repository des campagnes.
	 *
	 * @var Prospection_Claude_Campaign_Repository
	 */
	private $campaign_repository;

	/**
	 * Repository des contacts.
	 *
	 * @var Prospection_Claude_Contact_Repository
	 */
	private $contact_repository;

	/**
	 * Service d'envoi d'emails.
	 *
	 * @var Prospection_Claude_Email_Service
	 */
	private $email_service;

	/**
	 * Constructeur.
	 */
	public function __construct() {
		$this->campaign_repository = new Prospection_Claude_Campaign_Repository();
		$this->contact_repository  = new Prospection_Claude_Contact_Repository();
		$this->email_service       = new Prospection_Claude_Email_Service();
	}

	/**
	 * Exécute toutes les campagnes dues.
	 *
	 * @return array Résultats de l'exécution.
	 */
	public function execute_due_campaigns() {
		$due_campaigns = $this->campaign_repository->find_due_campaigns();
		$results       = array();

		foreach ( $due_campaigns as $campaign ) {
			$results[] = $this->execute_campaign( $campaign );
		}

		return $results;
	}

	/**
	 * Exécute une campagne spécifique.
	 *
	 * @param Prospection_Claude_Campaign $campaign La campagne à exécuter.
	 * @return array Résultat de l'exécution.
	 */
	public function execute_campaign( $campaign ) {
		$result = array(
			'campaign_id'   => $campaign->id,
			'campaign_name' => $campaign->name,
			'executed_at'   => current_time( 'mysql' ),
			'contacts'      => 0,
			'sent'          => 0,
			'failed'        => 0,
		);

		// Récupérer les contacts éligibles
		$contacts = $this->get_eligible_contacts( $campaign );
		$result['contacts'] = count( $contacts );

		if ( empty( $contacts ) ) {
			$result['message'] = 'Aucun contact éligible';
			return $result;
		}

		// Envoyer les emails
		$stats = $this->email_service->send_campaign_to_contacts( $campaign, $contacts );
		$result['sent']   = $stats['sent'];
		$result['failed'] = $stats['failed'];

		// Mettre à jour la prochaine exécution
		$this->update_campaign_next_run( $campaign );

		$result['message'] = sprintf(
			'Campagne exécutée: %d envoyés, %d échecs',
			$stats['sent'],
			$stats['failed']
		);

		return $result;
	}

	/**
	 * Récupère les contacts éligibles pour une campagne.
	 *
	 * @param Prospection_Claude_Campaign $campaign La campagne.
	 * @return array Liste des contacts éligibles.
	 */
	private function get_eligible_contacts( $campaign ) {
		$target_categories = $campaign->get_target_categories_array();
		$eligible_contacts = array();

		foreach ( $target_categories as $category ) {
			// Récupérer les contacts de cette catégorie qui sont abonnés
			$category_contacts = $this->contact_repository->find_by_category( $category );

			foreach ( $category_contacts as $contact ) {
				// Vérifier qu'il est abonné et pas déjà dans la liste
				if ( $contact->is_subscribed && ! isset( $eligible_contacts[ $contact->id ] ) ) {
					$eligible_contacts[ $contact->id ] = $contact;
				}
			}
		}

		return array_values( $eligible_contacts );
	}

	/**
	 * Met à jour la prochaine exécution d'une campagne.
	 *
	 * @param Prospection_Claude_Campaign $campaign La campagne.
	 */
	private function update_campaign_next_run( $campaign ) {
		$schedule_config = $campaign->get_schedule_config_array();
		$current_next_run = strtotime( $campaign->next_run );
		$new_next_run = $current_next_run;

		switch ( $campaign->schedule_type ) {
			case 'daily':
				$new_next_run = strtotime( '+1 day', $current_next_run );
				break;
			case 'weekly':
				$new_next_run = strtotime( '+1 week', $current_next_run );
				break;
			case 'monthly':
				$new_next_run = strtotime( '+1 month', $current_next_run );
				break;
			case 'custom':
				// Pour custom, désactiver la campagne après exécution
				$this->campaign_repository->toggle_active( $campaign->id, false );
				return; // Ne pas mettre à jour next_run
		}

		// Mettre à jour la campagne
		$campaign->next_run = gmdate( 'Y-m-d H:i:s', $new_next_run );
		$this->campaign_repository->update( $campaign );
	}

	/**
	 * Exécute une campagne manuellement (mode test).
	 *
	 * @param int   $campaign_id ID de la campagne.
	 * @param array $contact_ids Liste des IDs de contacts (optionnel).
	 * @return array Résultat de l'exécution.
	 */
	public function execute_campaign_manual( $campaign_id, $contact_ids = array() ) {
		$campaign = $this->campaign_repository->find_by_id( $campaign_id );

		if ( ! $campaign ) {
			return array(
				'success' => false,
				'message' => 'Campagne non trouvée',
			);
		}

		// Si des IDs de contacts sont fournis, les utiliser
		if ( ! empty( $contact_ids ) ) {
			$contacts = array();
			foreach ( $contact_ids as $contact_id ) {
				$contact = $this->contact_repository->find_by_id( $contact_id );
				if ( $contact && $contact->is_subscribed ) {
					$contacts[] = $contact;
				}
			}
		} else {
			// Sinon, récupérer tous les contacts éligibles
			$contacts = $this->get_eligible_contacts( $campaign );
		}

		if ( empty( $contacts ) ) {
			return array(
				'success' => false,
				'message' => 'Aucun contact éligible',
			);
		}

		// Envoyer les emails
		$stats = $this->email_service->send_campaign_to_contacts( $campaign, $contacts );

		return array(
			'success'  => true,
			'message'  => sprintf(
				'Envoi manuel: %d emails envoyés, %d échecs sur %d contacts',
				$stats['sent'],
				$stats['failed'],
				count( $contacts )
			),
			'sent'     => $stats['sent'],
			'failed'   => $stats['failed'],
			'contacts' => count( $contacts ),
		);
	}
}
