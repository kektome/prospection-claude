<?php
/**
 * Template pour le formulaire de contact.
 *
 * @package ProspectionClaude
 *
 * Variables disponibles:
 * @var Prospection_Claude_Contact|null $contact Le contact (null pour création)
 * @var bool $is_edit Mode édition ou création
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$page_title = $is_edit ? __( 'Modifier le contact', 'prospection-claude' ) : __( 'Ajouter un contact', 'prospection-claude' );
$action     = $is_edit ? 'update' : 'create';
?>

<div class="wrap prospection-claude">
	<h1><?php echo esc_html( $page_title ); ?></h1>

	<form method="post" action="" class="prospection-contact-form">
		<?php wp_nonce_field( 'prospection_claude_contact_action', 'prospection_claude_contact_nonce' ); ?>
		<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">
		<?php if ( $is_edit && $contact ) : ?>
			<input type="hidden" name="contact_id" value="<?php echo esc_attr( $contact->id ); ?>">
		<?php endif; ?>

		<table class="form-table">
			<tbody>
				<!-- Prénom -->
				<tr>
					<th scope="row">
						<label for="first_name">
							<?php esc_html_e( 'Prénom', 'prospection-claude' ); ?>
							<span class="required">*</span>
						</label>
					</th>
					<td>
						<input type="text"
							   name="first_name"
							   id="first_name"
							   class="regular-text"
							   value="<?php echo $contact ? esc_attr( $contact->first_name ) : ''; ?>"
							   required>
					</td>
				</tr>

				<!-- Nom -->
				<tr>
					<th scope="row">
						<label for="last_name">
							<?php esc_html_e( 'Nom', 'prospection-claude' ); ?>
							<span class="required">*</span>
						</label>
					</th>
					<td>
						<input type="text"
							   name="last_name"
							   id="last_name"
							   class="regular-text"
							   value="<?php echo $contact ? esc_attr( $contact->last_name ) : ''; ?>"
							   required>
					</td>
				</tr>

				<!-- Entreprise -->
				<tr>
					<th scope="row">
						<label for="company"><?php esc_html_e( 'Entreprise', 'prospection-claude' ); ?></label>
					</th>
					<td>
						<input type="text"
							   name="company"
							   id="company"
							   class="regular-text"
							   value="<?php echo $contact ? esc_attr( $contact->company ) : ''; ?>">
					</td>
				</tr>

				<!-- Email -->
				<tr>
					<th scope="row">
						<label for="email">
							<?php esc_html_e( 'Email', 'prospection-claude' ); ?>
							<span class="required">*</span>
						</label>
					</th>
					<td>
						<input type="email"
							   name="email"
							   id="email"
							   class="regular-text"
							   value="<?php echo $contact ? esc_attr( $contact->email ) : ''; ?>"
							   required>
					</td>
				</tr>

				<!-- Téléphone -->
				<tr>
					<th scope="row">
						<label for="phone"><?php esc_html_e( 'Téléphone', 'prospection-claude' ); ?></label>
					</th>
					<td>
						<input type="tel"
							   name="phone"
							   id="phone"
							   class="regular-text"
							   value="<?php echo $contact ? esc_attr( $contact->phone ) : ''; ?>"
							   placeholder="+1 (555) 123-4567">
					</td>
				</tr>

				<!-- Catégorie -->
				<tr>
					<th scope="row">
						<label for="category">
							<?php esc_html_e( 'Catégorie', 'prospection-claude' ); ?>
							<span class="required">*</span>
						</label>
					</th>
					<td>
						<select name="category" id="category" required>
							<option value=""><?php esc_html_e( '-- Sélectionner --', 'prospection-claude' ); ?></option>
							<option value="micrologiciel" <?php echo $contact && 'micrologiciel' === $contact->category ? 'selected' : ''; ?>>
								<?php esc_html_e( 'Micrologiciel', 'prospection-claude' ); ?>
							</option>
							<option value="scientifique" <?php echo $contact && 'scientifique' === $contact->category ? 'selected' : ''; ?>>
								<?php esc_html_e( 'Scientifique', 'prospection-claude' ); ?>
							</option>
							<option value="informatique" <?php echo $contact && 'informatique' === $contact->category ? 'selected' : ''; ?>>
								<?php esc_html_e( 'Informatique', 'prospection-claude' ); ?>
							</option>
						</select>
					</td>
				</tr>

				<!-- Contexte de la discussion -->
				<tr>
					<th scope="row">
						<label for="context"><?php esc_html_e( 'Contexte de la discussion', 'prospection-claude' ); ?></label>
					</th>
					<td>
						<textarea name="context"
								  id="context"
								  rows="5"
								  class="large-text"
								  placeholder="<?php esc_attr_e( 'Notes sur la discussion, besoins identifiés, etc.', 'prospection-claude' ); ?>"><?php echo $contact ? esc_textarea( $contact->context ) : ''; ?></textarea>
					</td>
				</tr>

				<!-- Lieu de rencontre -->
				<tr>
					<th scope="row">
						<label for="meeting_location"><?php esc_html_e( 'Lieu de rencontre', 'prospection-claude' ); ?></label>
					</th>
					<td>
						<input type="text"
							   name="meeting_location"
							   id="meeting_location"
							   class="regular-text"
							   value="<?php echo $contact ? esc_attr( $contact->meeting_location ) : ''; ?>"
							   placeholder="<?php esc_attr_e( 'Ex: Congrès XYZ 2026, Paris', 'prospection-claude' ); ?>">
					</td>
				</tr>

				<!-- Date de rencontre -->
				<tr>
					<th scope="row">
						<label for="meeting_date"><?php esc_html_e( 'Date de rencontre', 'prospection-claude' ); ?></label>
					</th>
					<td>
						<input type="date"
							   name="meeting_date"
							   id="meeting_date"
							   value="<?php echo $contact ? esc_attr( $contact->meeting_date ) : ''; ?>">
					</td>
				</tr>

				<!-- Statut d'abonnement -->
				<tr>
					<th scope="row">
						<label for="is_subscribed"><?php esc_html_e( 'Abonnement', 'prospection-claude' ); ?></label>
					</th>
					<td>
						<label>
							<input type="checkbox"
								   name="is_subscribed"
								   id="is_subscribed"
								   value="1"
								   <?php echo ! $contact || $contact->is_subscribed ? 'checked' : ''; ?>>
							<?php esc_html_e( 'Le contact est abonné aux newsletters', 'prospection-claude' ); ?>
						</label>
						<p class="description">
							<?php esc_html_e( 'Si décoché, le contact ne recevra aucun email automatique.', 'prospection-claude' ); ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>

		<p class="submit">
			<button type="submit" class="button button-primary">
				<?php echo $is_edit ? esc_html__( 'Mettre à jour', 'prospection-claude' ) : esc_html__( 'Créer le contact', 'prospection-claude' ); ?>
			</button>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=prospection-claude-contacts' ) ); ?>" class="button">
				<?php esc_html_e( 'Annuler', 'prospection-claude' ); ?>
			</a>
		</p>
	</form>
</div>
