<?php
/**
 * Template pour la liste des campagnes.
 *
 * @package ProspectionClaude
 *
 * Variables disponibles:
 * @var array $campaigns Liste des campagnes
 * @var int $total_items Nombre total d'items
 * @var int $total_pages Nombre total de pages
 * @var int $page Page actuelle
 * @var string $filter_active Filtre statut
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Récupérer le repository de templates pour afficher les noms
$template_repo = new Prospection_Claude_Template_Repository();
?>

<div class="wrap prospection-claude">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Campagnes', 'prospection-claude' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=prospection-claude-campaigns&action=new' ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Ajouter une campagne', 'prospection-claude' ); ?>
	</a>

	<hr class="wp-header-end">

	<!-- Barre de filtres -->
	<div class="tablenav top">
		<div class="alignleft actions">
			<!-- Filtre par statut -->
			<select name="filter_active" id="filter-by-status">
				<option value=""><?php esc_html_e( 'Tous les statuts', 'prospection-claude' ); ?></option>
				<option value="1" <?php selected( $filter_active, '1' ); ?>>
					<?php esc_html_e( 'Actives', 'prospection-claude' ); ?>
				</option>
				<option value="0" <?php selected( $filter_active, '0' ); ?>>
					<?php esc_html_e( 'Inactives', 'prospection-claude' ); ?>
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

	<!-- Table des campagnes -->
	<?php if ( empty( $campaigns ) ) : ?>
		<div class="no-campaigns">
			<p><?php esc_html_e( 'Aucune campagne trouvée.', 'prospection-claude' ); ?></p>
			<?php if ( ! empty( $filter_active ) ) : ?>
				<p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=prospection-claude-campaigns' ) ); ?>">
						<?php esc_html_e( 'Réinitialiser les filtres', 'prospection-claude' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped campaigns">
			<thead>
				<tr>
					<th scope="col" class="manage-column column-name column-primary">
						<?php esc_html_e( 'Nom', 'prospection-claude' ); ?>
					</th>
					<th scope="col" class="manage-column">
						<?php esc_html_e( 'Template', 'prospection-claude' ); ?>
					</th>
					<th scope="col" class="manage-column">
						<?php esc_html_e( 'Catégories', 'prospection-claude' ); ?>
					</th>
					<th scope="col" class="manage-column">
						<?php esc_html_e( 'Scheduling', 'prospection-claude' ); ?>
					</th>
					<th scope="col" class="manage-column">
						<?php esc_html_e( 'Prochaine exécution', 'prospection-claude' ); ?>
					</th>
					<th scope="col" class="manage-column">
						<?php esc_html_e( 'Statut', 'prospection-claude' ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $campaigns as $campaign ) :
					$template = $template_repo->find_by_id( $campaign->template_id );
					$template_name = $template ? $template->name : __( 'Template supprimé', 'prospection-claude' );
					$categories = $campaign->get_target_categories_array();
					?>
					<tr>
						<td class="column-name column-primary" data-colname="<?php esc_attr_e( 'Nom', 'prospection-claude' ); ?>">
							<strong>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=prospection-claude-campaigns&action=edit&id=' . $campaign->id ) ); ?>">
									<?php echo esc_html( $campaign->name ); ?>
								</a>
							</strong>
							<div class="row-actions">
								<span class="edit">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=prospection-claude-campaigns&action=edit&id=' . $campaign->id ) ); ?>">
										<?php esc_html_e( 'Modifier', 'prospection-claude' ); ?>
									</a>
								</span>
								|
								<span class="toggle">
									<?php
									$toggle_text = $campaign->is_active ? __( 'Désactiver', 'prospection-claude' ) : __( 'Activer', 'prospection-claude' );
									$toggle_active = $campaign->is_active ? 0 : 1;
									?>
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=prospection-claude-campaigns&action=toggle&id=' . $campaign->id . '&is_active=' . $toggle_active ), 'toggle_campaign' ) ); ?>" class="toggle-campaign">
										<?php echo esc_html( $toggle_text ); ?>
									</a>
								</span>
								|
								<span class="delete">
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=prospection-claude-campaigns&action=delete&id=' . $campaign->id ), 'delete_campaign' ) ); ?>" class="delete-campaign">
										<?php esc_html_e( 'Supprimer', 'prospection-claude' ); ?>
									</a>
								</span>
							</div>
						</td>
						<td data-colname="<?php esc_attr_e( 'Template', 'prospection-claude' ); ?>">
							<?php echo esc_html( $template_name ); ?>
						</td>
						<td data-colname="<?php esc_attr_e( 'Catégories', 'prospection-claude' ); ?>">
							<?php
							if ( ! empty( $categories ) ) {
								$category_labels = array();
								foreach ( $categories as $cat ) {
									$labels = array(
										'micrologiciel' => __( 'Micrologiciel', 'prospection-claude' ),
										'scientifique'  => __( 'Scientifique', 'prospection-claude' ),
										'informatique'  => __( 'Informatique', 'prospection-claude' ),
									);
									$category_labels[] = isset( $labels[ $cat ] ) ? $labels[ $cat ] : $cat;
								}
								echo esc_html( implode( ', ', $category_labels ) );
							}
							?>
						</td>
						<td data-colname="<?php esc_attr_e( 'Scheduling', 'prospection-claude' ); ?>">
							<span class="schedule-badge schedule-<?php echo esc_attr( $campaign->schedule_type ); ?>">
								<?php echo esc_html( $campaign->get_schedule_type_label() ); ?>
							</span>
						</td>
						<td data-colname="<?php esc_attr_e( 'Prochaine exécution', 'prospection-claude' ); ?>">
							<?php
							if ( $campaign->next_run ) {
								echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $campaign->next_run ) );
							} else {
								echo '—';
							}
							?>
						</td>
						<td data-colname="<?php esc_attr_e( 'Statut', 'prospection-claude' ); ?>">
							<?php if ( $campaign->is_active ) : ?>
								<span class="status-badge status-active"><?php esc_html_e( 'Active', 'prospection-claude' ); ?></span>
							<?php else : ?>
								<span class="status-badge status-inactive"><?php esc_html_e( 'Inactive', 'prospection-claude' ); ?></span>
							<?php endif; ?>
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
