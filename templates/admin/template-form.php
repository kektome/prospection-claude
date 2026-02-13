<?php
/**
 * Template pour le formulaire de template d'email.
 *
 * @package ProspectionClaude
 *
 * Variables disponibles:
 * @var Prospection_Claude_Email_Template|null $template Le template (null si création)
 * @var bool $is_edit Mode édition ou création
 * @var array $available_variables Variables disponibles
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$page_title = $is_edit ? __( 'Modifier le template', 'prospection-claude' ) : __( 'Nouveau template', 'prospection-claude' );
$button_text = $is_edit ? __( 'Mettre à jour', 'prospection-claude' ) : __( 'Créer', 'prospection-claude' );
$action_value = $is_edit ? 'update' : 'create';
?>

<div class="wrap prospection-claude">
	<h1><?php echo esc_html( $page_title ); ?></h1>

	<form method="post" action="" class="prospection-template-form">
		<?php wp_nonce_field( 'prospection_claude_template_action', 'prospection_claude_template_nonce' ); ?>
		<input type="hidden" name="action" value="<?php echo esc_attr( $action_value ); ?>">
		<?php if ( $is_edit && $template ) : ?>
			<input type="hidden" name="template_id" value="<?php echo esc_attr( $template->id ); ?>">
		<?php endif; ?>

		<table class="form-table" role="presentation">
			<tbody>
				<!-- Nom du template -->
				<tr>
					<th scope="row">
						<label for="name"><?php esc_html_e( 'Nom du template', 'prospection-claude' ); ?> <span class="required">*</span></label>
					</th>
					<td>
						<input type="text"
							   name="name"
							   id="name"
							   class="regular-text"
							   value="<?php echo $is_edit && $template ? esc_attr( $template->name ) : ''; ?>"
							   required>
						<p class="description"><?php esc_html_e( 'Un nom descriptif pour identifier ce template (ex: "Bienvenue Micrologiciel").', 'prospection-claude' ); ?></p>
					</td>
				</tr>

				<!-- Sujet de l'email -->
				<tr>
					<th scope="row">
						<label for="subject"><?php esc_html_e( 'Sujet de l\'email', 'prospection-claude' ); ?> <span class="required">*</span></label>
					</th>
					<td>
						<input type="text"
							   name="subject"
							   id="subject"
							   class="large-text"
							   value="<?php echo $is_edit && $template ? esc_attr( $template->subject ) : ''; ?>"
							   required>
						<p class="description"><?php esc_html_e( 'Le sujet de l\'email. Vous pouvez utiliser les variables disponibles.', 'prospection-claude' ); ?></p>
					</td>
				</tr>

				<!-- Catégorie -->
				<tr>
					<th scope="row">
						<label for="category"><?php esc_html_e( 'Catégorie', 'prospection-claude' ); ?> <span class="required">*</span></label>
					</th>
					<td>
						<select name="category" id="category" required>
							<option value="all" <?php echo ( $is_edit && $template && 'all' === $template->category ) ? 'selected' : ''; ?>>
								<?php esc_html_e( 'Tous', 'prospection-claude' ); ?>
							</option>
							<option value="micrologiciel" <?php echo ( $is_edit && $template && 'micrologiciel' === $template->category ) ? 'selected' : ''; ?>>
								<?php esc_html_e( 'Micrologiciel', 'prospection-claude' ); ?>
							</option>
							<option value="scientifique" <?php echo ( $is_edit && $template && 'scientifique' === $template->category ) ? 'selected' : ''; ?>>
								<?php esc_html_e( 'Scientifique', 'prospection-claude' ); ?>
							</option>
							<option value="informatique" <?php echo ( $is_edit && $template && 'informatique' === $template->category ) ? 'selected' : ''; ?>>
								<?php esc_html_e( 'Informatique', 'prospection-claude' ); ?>
							</option>
						</select>
						<p class="description"><?php esc_html_e( 'La catégorie de contacts qui recevront ce template (ou "Tous" pour toutes les catégories).', 'prospection-claude' ); ?></p>
					</td>
				</tr>

				<!-- Variables disponibles -->
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Variables disponibles', 'prospection-claude' ); ?>
					</th>
					<td>
						<div class="template-variables-box">
							<p class="description"><?php esc_html_e( 'Cliquez sur une variable pour la copier dans le presse-papiers :', 'prospection-claude' ); ?></p>
							<div class="variables-list">
								<?php foreach ( $available_variables as $variable => $description ) : ?>
									<button type="button" class="button button-small variable-button" data-variable="<?php echo esc_attr( $variable ); ?>">
										<code><?php echo esc_html( $variable ); ?></code>
										<span class="variable-description"><?php echo esc_html( $description ); ?></span>
									</button>
								<?php endforeach; ?>
							</div>
							<p class="description" style="margin-top: 10px;">
								<?php esc_html_e( 'Ces variables seront automatiquement remplacées par les données du contact lors de l\'envoi.', 'prospection-claude' ); ?>
							</p>
						</div>
					</td>
				</tr>

				<!-- Contenu de l'email -->
				<tr>
					<th scope="row">
						<label for="content"><?php esc_html_e( 'Contenu de l\'email', 'prospection-claude' ); ?> <span class="required">*</span></label>
					</th>
					<td>
						<?php
						$content = $is_edit && $template ? $template->content : '';
						wp_editor(
							$content,
							'content',
							array(
								'textarea_name' => 'content',
								'textarea_rows' => 15,
								'media_buttons' => false,
								'teeny'         => false,
								'quicktags'     => true,
								'tinymce'       => array(
									'toolbar1' => 'formatselect,bold,italic,underline,bullist,numlist,link,unlink,alignleft,aligncenter,alignright',
									'toolbar2' => 'forecolor,undo,redo,removeformat',
								),
							)
						);
						?>
						<p class="description"><?php esc_html_e( 'Le contenu HTML de votre email. Vous pouvez utiliser les variables ci-dessus.', 'prospection-claude' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>

		<p class="submit">
			<button type="submit" class="button button-primary">
				<?php echo esc_html( $button_text ); ?>
			</button>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=prospection-claude-templates' ) ); ?>" class="button">
				<?php esc_html_e( 'Annuler', 'prospection-claude' ); ?>
			</a>
		</p>
	</form>
</div>
