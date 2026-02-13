<?php
/**
 * Classe de validation des données.
 *
 * @package ProspectionClaude
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Validator pour la validation des données.
 *
 * Cette classe fournit des méthodes statiques pour valider différents types
 * de données (emails, téléphones, dates, etc.).
 */
class Prospection_Claude_Validator {

	/**
	 * Valide une adresse email.
	 *
	 * @param string $email L'email à valider.
	 * @return bool True si valide, false sinon.
	 */
	public static function is_valid_email( $email ) {
		if ( empty( $email ) ) {
			return false;
		}

		return is_email( $email ) !== false;
	}

	/**
	 * Valide un numéro de téléphone.
	 *
	 * Format accepté: international ou local avec différents formats.
	 *
	 * @param string $phone Le numéro de téléphone à valider.
	 * @return bool True si valide, false sinon.
	 */
	public static function is_valid_phone( $phone ) {
		if ( empty( $phone ) ) {
			return true; // Le téléphone est optionnel
		}

		// Enlever les espaces, tirets, parenthèses
		$cleaned = preg_replace( '/[\s\-\(\)\.]+/', '', $phone );

		// Vérifier que ce sont uniquement des chiffres et éventuellement un +
		return preg_match( '/^\+?[0-9]{10,15}$/', $cleaned );
	}

	/**
	 * Valide une catégorie de contact.
	 *
	 * @param string $category La catégorie à valider.
	 * @return bool True si valide, false sinon.
	 */
	public static function is_valid_category( $category ) {
		$valid_categories = array( 'micrologiciel', 'scientifique', 'informatique' );
		return in_array( $category, $valid_categories, true );
	}

	/**
	 * Valide une date au format Y-m-d.
	 *
	 * @param string $date La date à valider.
	 * @return bool True si valide, false sinon.
	 */
	public static function is_valid_date( $date ) {
		if ( empty( $date ) ) {
			return true; // La date est optionnelle
		}

		$d = DateTime::createFromFormat( 'Y-m-d', $date );
		return $d && $d->format( 'Y-m-d' ) === $date;
	}

	/**
	 * Valide une URL.
	 *
	 * @param string $url L'URL à valider.
	 * @return bool True si valide, false sinon.
	 */
	public static function is_valid_url( $url ) {
		if ( empty( $url ) ) {
			return true; // L'URL est optionnelle
		}

		return filter_var( $url, FILTER_VALIDATE_URL ) !== false;
	}

	/**
	 * Valide un type de scheduling.
	 *
	 * @param string $schedule_type Le type de scheduling à valider.
	 * @return bool True si valide, false sinon.
	 */
	public static function is_valid_schedule_type( $schedule_type ) {
		$valid_types = array( 'daily', 'weekly', 'monthly', 'custom' );
		return in_array( $schedule_type, $valid_types, true );
	}

	/**
	 * Valide un statut d'email.
	 *
	 * @param string $status Le statut à valider.
	 * @return bool True si valide, false sinon.
	 */
	public static function is_valid_email_status( $status ) {
		$valid_statuses = array( 'pending', 'sent', 'failed', 'bounced' );
		return in_array( $status, $valid_statuses, true );
	}

	/**
	 * Valide une catégorie de template.
	 *
	 * @param string $category La catégorie à valider.
	 * @return bool True si valide, false sinon.
	 */
	public static function is_valid_template_category( $category ) {
		$valid_categories = array( 'micrologiciel', 'scientifique', 'informatique', 'all' );
		return in_array( $category, $valid_categories, true );
	}

	/**
	 * Sanitize et valide un nom (prénom, nom de famille).
	 *
	 * @param string $name Le nom à valider.
	 * @return string|false Le nom sanitizé ou false si invalide.
	 */
	public static function sanitize_name( $name ) {
		if ( empty( $name ) ) {
			return false;
		}

		$sanitized = sanitize_text_field( $name );

		// Vérifier qu'il reste quelque chose après sanitization
		if ( empty( $sanitized ) || strlen( $sanitized ) < 2 ) {
			return false;
		}

		return $sanitized;
	}

	/**
	 * Sanitize un email.
	 *
	 * @param string $email L'email à sanitizer.
	 * @return string|false L'email sanitizé ou false si invalide.
	 */
	public static function sanitize_email( $email ) {
		$sanitized = sanitize_email( $email );

		if ( ! self::is_valid_email( $sanitized ) ) {
			return false;
		}

		return $sanitized;
	}

	/**
	 * Sanitize un numéro de téléphone.
	 *
	 * @param string $phone Le téléphone à sanitizer.
	 * @return string|false Le téléphone sanitizé ou false si invalide.
	 */
	public static function sanitize_phone( $phone ) {
		if ( empty( $phone ) ) {
			return '';
		}

		$sanitized = sanitize_text_field( $phone );

		if ( ! self::is_valid_phone( $sanitized ) ) {
			return false;
		}

		return $sanitized;
	}

	/**
	 * Sanitize un texte long (contexte, description).
	 *
	 * @param string $text Le texte à sanitizer.
	 * @return string Le texte sanitizé.
	 */
	public static function sanitize_long_text( $text ) {
		return wp_kses_post( $text );
	}

	/**
	 * Sanitize du contenu HTML (pour les emails).
	 *
	 * @param string $html Le HTML à sanitizer.
	 * @return string Le HTML sanitizé.
	 */
	public static function sanitize_html_content( $html ) {
		// Autoriser les balises HTML courantes pour les emails
		$allowed_tags = array(
			'a'      => array( 'href' => true, 'title' => true, 'target' => true ),
			'br'     => array(),
			'p'      => array( 'style' => true ),
			'strong' => array(),
			'b'      => array(),
			'em'     => array(),
			'i'      => array(),
			'u'      => array(),
			'h1'     => array( 'style' => true ),
			'h2'     => array( 'style' => true ),
			'h3'     => array( 'style' => true ),
			'ul'     => array(),
			'ol'     => array(),
			'li'     => array(),
			'div'    => array( 'style' => true, 'class' => true ),
			'span'   => array( 'style' => true, 'class' => true ),
			'img'    => array( 'src' => true, 'alt' => true, 'width' => true, 'height' => true ),
			'table'  => array( 'style' => true, 'class' => true ),
			'tr'     => array(),
			'td'     => array( 'style' => true ),
			'th'     => array( 'style' => true ),
		);

		return wp_kses( $html, $allowed_tags );
	}

	/**
	 * Valide un ID (doit être un entier positif).
	 *
	 * @param mixed $id L'ID à valider.
	 * @return int|false L'ID validé ou false si invalide.
	 */
	public static function validate_id( $id ) {
		$id = intval( $id );

		if ( $id <= 0 ) {
			return false;
		}

		return $id;
	}

	/**
	 * Valide un boolean.
	 *
	 * @param mixed $value La valeur à valider.
	 * @return bool Le boolean validé.
	 */
	public static function validate_boolean( $value ) {
		return (bool) $value;
	}
}
