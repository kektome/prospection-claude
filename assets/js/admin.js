/**
 * JavaScript pour l'administration du plugin Prospection Claude.
 *
 * @package ProspectionClaude
 */

(function($) {
	'use strict';

	/**
	 * Initialisation au chargement du DOM.
	 */
	$(document).ready(function() {
		initContactsPage();
	});

	/**
	 * Initialisation de la page des contacts.
	 */
	function initContactsPage() {
		// Filtre par catégorie
		$('#filter-submit').on('click', function(e) {
			e.preventDefault();
			filterByCategory();
		});

		// Filtre sur changement de select
		$('#filter-by-category').on('change', function() {
			filterByCategory();
		});

		// Confirmation de suppression
		$('a[href*="action=delete"]').on('click', function(e) {
			const confirmText = $(this).data('confirm') || 'Êtes-vous sûr de vouloir supprimer ce contact ?';
			if (!confirm(confirmText)) {
				e.preventDefault();
				return false;
			}
		});

		// Auto-focus sur le champ de recherche si présent
		if ($('input[name="s"]').length) {
			const searchInput = $('input[name="s"]');
			if (searchInput.val() === '') {
				// Ne pas focus automatiquement pour ne pas gêner l'utilisateur
			}
		}

		// Validation du formulaire de contact
		$('.prospection-contact-form').on('submit', function(e) {
			return validateContactForm($(this));
		});
	}

	/**
	 * Filtre la liste des contacts par catégorie.
	 */
	function filterByCategory() {
		const category = $('#filter-by-category').val();
		const currentUrl = new URL(window.location.href);

		// Supprimer les paramètres de recherche et pagination
		currentUrl.searchParams.delete('s');
		currentUrl.searchParams.delete('paged');

		if (category) {
			currentUrl.searchParams.set('category', category);
		} else {
			currentUrl.searchParams.delete('category');
		}

		window.location.href = currentUrl.toString();
	}

	/**
	 * Valide le formulaire de contact avant soumission.
	 *
	 * @param {jQuery} $form Le formulaire
	 * @return {boolean} True si valide, false sinon
	 */
	function validateContactForm($form) {
		let isValid = true;
		const errors = [];

		// Validation email
		const email = $form.find('#email').val();
		if (email && !isValidEmail(email)) {
			errors.push('L\'adresse email n\'est pas valide.');
			$form.find('#email').addClass('error');
			isValid = false;
		} else {
			$form.find('#email').removeClass('error');
		}

		// Validation téléphone (si rempli)
		const phone = $form.find('#phone').val();
		if (phone && !isValidPhone(phone)) {
			errors.push('Le numéro de téléphone n\'est pas valide.');
			$form.find('#phone').addClass('error');
			isValid = false;
		} else {
			$form.find('#phone').removeClass('error');
		}

		// Afficher les erreurs
		if (!isValid) {
			alert(errors.join('\n'));
		}

		return isValid;
	}

	/**
	 * Vérifie si un email est valide.
	 *
	 * @param {string} email L'email à vérifier
	 * @return {boolean} True si valide, false sinon
	 */
	function isValidEmail(email) {
		const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		return re.test(email);
	}

	/**
	 * Vérifie si un téléphone est valide.
	 *
	 * @param {string} phone Le téléphone à vérifier
	 * @return {boolean} True si valide, false sinon
	 */
	function isValidPhone(phone) {
		// Permet différents formats de téléphone
		const cleaned = phone.replace(/[\s\-\(\)\.]/g, '');
		const re = /^\+?[0-9]{10,15}$/;
		return re.test(cleaned);
	}

})(jQuery);
