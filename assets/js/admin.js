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
		initTemplatesPage();
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

	/* =====================================================
	   TEMPLATES D'EMAILS - Phase 4
	   ===================================================== */

	/**
	 * Initialisation de la page des templates.
	 */
	function initTemplatesPage() {
		// Filtre par catégorie (même logique que contacts)
		$('#filter-submit').on('click', function(e) {
			e.preventDefault();
			filterByCategory();
		});

		$('#filter-by-category').on('change', function() {
			filterByCategory();
		});

		// Confirmation de suppression des templates
		$('a.delete-template').on('click', function(e) {
			const confirmText = 'Êtes-vous sûr de vouloir supprimer ce template ?';
			if (!confirm(confirmText)) {
				e.preventDefault();
				return false;
			}
		});

		// Gestion des boutons de variables
		$('.variable-button').on('click', function(e) {
			e.preventDefault();
			const variable = $(this).data('variable');
			copyToClipboard(variable);
			showCopiedToast(variable);
		});
	}

	/**
	 * Copie du texte dans le presse-papiers.
	 *
	 * @param {string} text Le texte à copier
	 */
	function copyToClipboard(text) {
		// Utiliser l'API moderne du presse-papiers si disponible
		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(text).catch(function(err) {
				console.error('Erreur lors de la copie:', err);
				fallbackCopyToClipboard(text);
			});
		} else {
			// Fallback pour les navigateurs plus anciens
			fallbackCopyToClipboard(text);
		}
	}

	/**
	 * Méthode de fallback pour copier dans le presse-papiers.
	 *
	 * @param {string} text Le texte à copier
	 */
	function fallbackCopyToClipboard(text) {
		const textarea = document.createElement('textarea');
		textarea.value = text;
		textarea.style.position = 'fixed';
		textarea.style.left = '-9999px';
		document.body.appendChild(textarea);
		textarea.select();
		try {
			document.execCommand('copy');
		} catch (err) {
			console.error('Erreur lors de la copie:', err);
		}
		document.body.removeChild(textarea);
	}

	/**
	 * Affiche un toast de confirmation de copie.
	 *
	 * @param {string} variable La variable copiée
	 */
	function showCopiedToast(variable) {
		// Supprimer les toasts existants
		$('.variable-copied-toast').remove();

		// Créer le nouveau toast
		const toast = $('<div class="variable-copied-toast"></div>')
			.text('Variable ' + variable + ' copiée !');

		$('body').append(toast);

		// Supprimer le toast après 3 secondes
		setTimeout(function() {
			toast.remove();
		}, 3000);
	}

})(jQuery);
