<?php
/**
 * Template pour le formulaire de campagne.
 *
 * @package ProspectionClaude
 *
 * Variables disponibles:
 * @var Prospection_Claude_Campaign|null $campaign La campagne (null si création)
 * @var bool $is_edit Mode édition ou création
 * @var array $templates Liste des templates disponibles
 * @var array $available_categories Catégories disponibles
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$page_title = $is_edit ? __( 'Modifier la campagne', 'prospection-claude' ) : __( 'Nouvelle campagne', 'prospection-claude' );
$button_text = $is_edit ? __( 'Mettre à jour', 'prospection-claude' ) : __( 'Créer', 'prospection-claude' );
$action_value = $is_edit ? 'update' : 'create';

$selected_categories = $is_edit && $campaign ? $campaign->get_target_categories_array() : array();
$schedule_type = $is_edit && $campaign ? $campaign->schedule_type : 'daily';
$schedule_config = $is_edit && $campaign ? $campaign->get_schedule_config_array() : array();
?>

<div class="wrap prospection-claude">
	<h1><?php echo esc_html( $page_title ); ?></h1>

	<form method="post" action="" class="prospection-campaign-form">
		<?php wp_nonce_field( 'prospection_claude_campaign_action', 'prospection_claude_campaign_nonce' ); ?>
		<input type="hidden" name="action" value="<?php echo esc_attr( $action_value ); ?>">
		<?php if ( $is_edit && $campaign ) : ?>
			<input type="hidden" name="campaign_id" value="<?php echo esc_attr( $campaign->id ); ?>">
		<?php endif; ?>

		<table class="form-table" role="presentation">
			<tbody>
				<!-- Nom de la campagne -->
				<tr>
					<th scope="row">
						<label for="name"><?php esc_html_e( 'Nom de la campagne', 'prospection-claude' ); ?> <span class="required">*</span></label>
					</th>
					<td>
						<input type="text"
							   name="name"
							   id="name"
							   class="regular-text"
							   value="<?php echo $is_edit && $campaign ? esc_attr( $campaign->name ) : ''; ?>"
							   required>
						<p class="description"><?php esc_html_e( 'Un nom descriptif pour identifier cette campagne.', 'prospection-claude' ); ?></p>
					</td>
				</tr>

				<!-- Template d'email -->
				<tr>
					<th scope="row">
						<label for="template_id"><?php esc_html_e( 'Template d\'email', 'prospection-claude' ); ?> <span class="required">*</span></label>
					</th>
					<td>
						<?php if ( empty( $templates ) ) : ?>
							<p class="description error-message">
								<?php esc_html_e( 'Aucun template disponible.', 'prospection-claude' ); ?>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=prospection-claude-templates&action=new' ) ); ?>">
									<?php esc_html_e( 'Créer un template', 'prospection-claude' ); ?>
								</a>
							</p>
						<?php else : ?>
							<select name="template_id" id="template_id" required>
								<option value=""><?php esc_html_e( '— Sélectionner un template —', 'prospection-claude' ); ?></option>
								<?php foreach ( $templates as $template ) : ?>
									<option value="<?php echo esc_attr( $template->id ); ?>"
										<?php echo ( $is_edit && $campaign && $template->id === $campaign->template_id ) ? 'selected' : ''; ?>>
										<?php echo esc_html( $template->name . ' (' . $template->get_category_label() . ')' ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Le template d\'email qui sera envoyé aux contacts.', 'prospection-claude' ); ?></p>
						<?php endif; ?>
					</td>
				</tr>

				<!-- Catégories cibles -->
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Catégories cibles', 'prospection-claude' ); ?> <span class="required">*</span>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php esc_html_e( 'Catégories cibles', 'prospection-claude' ); ?></span></legend>
							<?php foreach ( $available_categories as $cat_value => $cat_label ) : ?>
								<label>
									<input type="checkbox"
										   name="target_categories[]"
										   value="<?php echo esc_attr( $cat_value ); ?>"
										   <?php checked( in_array( $cat_value, $selected_categories, true ) ); ?>>
									<?php echo esc_html( $cat_label ); ?>
								</label><br>
							<?php endforeach; ?>
						</fieldset>
						<p class="description"><?php esc_html_e( 'Les catégories de contacts qui recevront cet email. Au moins une catégorie doit être sélectionnée.', 'prospection-claude' ); ?></p>
					</td>
				</tr>

				<!-- Type de scheduling -->
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Fréquence d\'envoi', 'prospection-claude' ); ?> <span class="required">*</span>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php esc_html_e( 'Fréquence d\'envoi', 'prospection-claude' ); ?></span></legend>
							<label>
								<input type="radio"
									   name="schedule_type"
									   value="daily"
									   <?php checked( $schedule_type, 'daily' ); ?>>
								<?php esc_html_e( 'Quotidien', 'prospection-claude' ); ?>
							</label><br>
							<label>
								<input type="radio"
									   name="schedule_type"
									   value="weekly"
									   <?php checked( $schedule_type, 'weekly' ); ?>>
								<?php esc_html_e( 'Hebdomadaire', 'prospection-claude' ); ?>
							</label><br>
							<label>
								<input type="radio"
									   name="schedule_type"
									   value="monthly"
									   <?php checked( $schedule_type, 'monthly' ); ?>>
								<?php esc_html_e( 'Mensuel', 'prospection-claude' ); ?>
							</label><br>
							<label>
								<input type="radio"
									   name="schedule_type"
									   value="custom"
									   <?php checked( $schedule_type, 'custom' ); ?>>
								<?php esc_html_e( 'Personnalisé (date unique)', 'prospection-claude' ); ?>
							</label>
						</fieldset>
						<p class="description"><?php esc_html_e( 'La fréquence à laquelle cette campagne sera envoyée.', 'prospection-claude' ); ?></p>

						<!-- Champ de date personnalisée (conditionnel) -->
						<div id="custom-date-field" style="margin-top: 15px; <?php echo 'custom' !== $schedule_type ? 'display: none;' : ''; ?>">
							<label for="custom_date"><?php esc_html_e( 'Date et heure d\'envoi', 'prospection-claude' ); ?></label><br>
							<input type="datetime-local"
								   name="custom_date"
								   id="custom_date"
								   value="<?php echo isset( $schedule_config['custom_date'] ) ? esc_attr( gmdate( 'Y-m-d\TH:i', strtotime( $schedule_config['custom_date'] ) ) ) : ''; ?>">
							<p class="description"><?php esc_html_e( 'La date et l\'heure exacte d\'envoi pour cette campagne unique.', 'prospection-claude' ); ?></p>
						</div>
					</td>
				</tr>

				<!-- Statut actif/inactif -->
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Statut', 'prospection-claude' ); ?>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php esc_html_e( 'Statut', 'prospection-claude' ); ?></span></legend>
							<label for="is_active">
								<input type="checkbox"
									   name="is_active"
									   id="is_active"
									   value="1"
									   <?php checked( ! $is_edit || ( $campaign && $campaign->is_active ) ); ?>>
								<?php esc_html_e( 'Campagne active', 'prospection-claude' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'Une campagne inactive ne sera pas exécutée même si sa date d\'envoi est atteinte.', 'prospection-claude' ); ?></p>
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>

		<p class="submit">
			<button type="submit" class="button button-primary">
				<?php echo esc_html( $button_text ); ?>
			</button>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=prospection-claude-campaigns' ) ); ?>" class="button">
				<?php esc_html_e( 'Annuler', 'prospection-claude' ); ?>
			</a>
		</p>
	</form>
</div>
