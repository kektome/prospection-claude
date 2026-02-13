<?php
/**
 * Template pour la liste des templates d'emails.
 *
 * @package ProspectionClaude
 *
 * Variables disponibles:
 * @var array $templates Liste des templates
 * @var int $total_items Nombre total d'items
 * @var int $total_pages Nombre total de pages
 * @var int $page Page actuelle
 * @var string $category Catégorie filtrée
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap prospection-claude">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Templates d\'emails', 'prospection-claude' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=prospection-claude-templates&action=new' ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Ajouter un template', 'prospection-claude' ); ?>
	</a>

	<hr class="wp-header-end">

	<!-- Barre de filtres -->
	<div class="tablenav top">
		<div class="alignleft actions">
			<!-- Filtre par catégorie -->
			<select name="category" id="filter-by-category">
				<option value=""><?php esc_html_e( 'Toutes les catégories', 'prospection-claude' ); ?></option>
				<option value="micrologiciel" <?php selected( $category, 'micrologiciel' ); ?>>
					<?php esc_html_e( 'Micrologiciel', 'prospection-claude' ); ?>
				</option>
				<option value="scientifique" <?php selected( $category, 'scientifique' ); ?>>
					<?php esc_html_e( 'Scientifique', 'prospection-claude' ); ?>
				</option>
				<option value="informatique" <?php selected( $category, 'informatique' ); ?>>
					<?php esc_html_e( 'Informatique', 'prospection-claude' ); ?>
				</option>
				<option value="all" <?php selected( $category, 'all' ); ?>>
					<?php esc_html_e( 'Tous', 'prospection-claude' ); ?>
				</option>
			</select>
			<button type="button" id="filter-submit" class="button">
				<?php esc_html_e( 'Filtrer', 'prospection-claude' ); ?>
			</button>
		</div>

		<?php if ( $total_pages > 1 ) : ?>
			<div class="tablenav-pages">
				<span class="displaying-num">
					<?php
					/* translators: %s: nombre d'items */
					printf( esc_html( _n( '%s élément', '%s éléments', $total_items, 'prospection-claude' ) ), esc_html( number_format_i18n( $total_items ) ) );
					?>
				</span>
				<?php
				echo wp_kses(
					paginate_links(
						array(
							'base'      => add_query_arg( 'paged', '%#%' ),
							'format'    => '',
							'prev_text' => __( '&laquo;', 'prospection-claude' ),
							'next_text' => __( '&raquo;', 'prospection-claude' ),
							'total'     => $total_pages,
							'current'   => $page,
						)
					),
					array(
						'a'    => array(
							'class' => array(),
							'href'  => array(),
						),
						'span' => array(
							'class'       => array(),
							'aria-current' => array(),
						),
					)
				);
				?>
			</div>
		<?php endif; ?>
	</div>

	<!-- Table des templates -->
	<?php if ( empty( $templates ) ) : ?>
		<div class="no-templates">
			<p><?php esc_html_e( 'Aucun template trouvé.', 'prospection-claude' ); ?></p>
			<?php if ( ! empty( $category ) ) : ?>
				<p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=prospection-claude-templates' ) ); ?>">
						<?php esc_html_e( 'Réinitialiser les filtres', 'prospection-claude' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped templates">
			<thead>
				<tr>
					<th scope="col" class="manage-column column-name column-primary">
						<?php esc_html_e( 'Nom', 'prospection-claude' ); ?>
					</th>
					<th scope="col" class="manage-column">
						<?php esc_html_e( 'Sujet', 'prospection-claude' ); ?>
					</th>
					<th scope="col" class="manage-column">
						<?php esc_html_e( 'Catégorie', 'prospection-claude' ); ?>
					</th>
					<th scope="col" class="manage-column">
						<?php esc_html_e( 'Date de création', 'prospection-claude' ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $templates as $template ) : ?>
					<tr>
						<td class="column-name column-primary" data-colname="<?php esc_attr_e( 'Nom', 'prospection-claude' ); ?>">
							<strong>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=prospection-claude-templates&action=edit&id=' . $template->id ) ); ?>">
									<?php echo esc_html( $template->name ); ?>
								</a>
							</strong>
							<div class="row-actions">
								<span class="edit">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=prospection-claude-templates&action=edit&id=' . $template->id ) ); ?>">
										<?php esc_html_e( 'Modifier', 'prospection-claude' ); ?>
									</a>
								</span>
								|
								<span class="delete">
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=prospection-claude-templates&action=delete&id=' . $template->id ), 'delete_template' ) ); ?>" class="delete-template">
										<?php esc_html_e( 'Supprimer', 'prospection-claude' ); ?>
									</a>
								</span>
							</div>
						</td>
						<td data-colname="<?php esc_attr_e( 'Sujet', 'prospection-claude' ); ?>">
							<?php echo esc_html( wp_trim_words( $template->subject, 10, '...' ) ); ?>
						</td>
						<td data-colname="<?php esc_attr_e( 'Catégorie', 'prospection-claude' ); ?>">
							<span class="category-badge category-<?php echo esc_attr( $template->category ); ?>">
								<?php echo esc_html( $template->get_category_label() ); ?>
							</span>
						</td>
						<td data-colname="<?php esc_attr_e( 'Date de création', 'prospection-claude' ); ?>">
							<?php echo esc_html( mysql2date( get_option( 'date_format' ), $template->created_at ) ); ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<!-- Pagination bas de page -->
		<?php if ( $total_pages > 1 ) : ?>
			<div class="tablenav bottom">
				<div class="tablenav-pages">
					<?php
					echo wp_kses(
						paginate_links(
							array(
								'base'      => add_query_arg( 'paged', '%#%' ),
								'format'    => '',
								'prev_text' => __( '&laquo;', 'prospection-claude' ),
								'next_text' => __( '&raquo;', 'prospection-claude' ),
								'total'     => $total_pages,
								'current'   => $page,
							)
						),
						array(
							'a'    => array(
								'class' => array(),
								'href'  => array(),
							),
							'span' => array(
								'class'       => array(),
								'aria-current' => array(),
							),
						)
					);
					?>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
