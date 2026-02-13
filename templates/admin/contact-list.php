<?php
/**
 * Template pour la liste des contacts.
 *
 * @package ProspectionClaude
 *
 * Variables disponibles:
 * @var array $contacts Liste des contacts
 * @var int $total_items Nombre total d'items
 * @var int $total_pages Nombre total de pages
 * @var int $page Page actuelle
 * @var string $search Terme de recherche
 * @var string $category Catégorie filtrée
 */

// Si ce fichier est appelé directement, quitter.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap prospection-claude">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Contacts', 'prospection-claude' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=prospection-claude-contacts&action=new' ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Ajouter un contact', 'prospection-claude' ); ?>
	</a>

	<hr class="wp-header-end">

	<!-- Barre de recherche et filtres -->
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
			</select>
			<button type="button" id="filter-submit" class="button">
				<?php esc_html_e( 'Filtrer', 'prospection-claude' ); ?>
			</button>
		</div>

		<div class="alignleft actions">
			<form method="get" action="">
				<input type="hidden" name="page" value="prospection-claude-contacts">
				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Rechercher...', 'prospection-claude' ); ?>">
				<button type="submit" class="button"><?php esc_html_e( 'Rechercher', 'prospection-claude' ); ?></button>
			</form>
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

	<!-- Table des contacts -->
	<?php if ( empty( $contacts ) ) : ?>
		<div class="no-contacts">
			<p><?php esc_html_e( 'Aucun contact trouvé.', 'prospection-claude' ); ?></p>
			<?php if ( ! empty( $search ) || ! empty( $category ) ) : ?>
				<p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=prospection-claude-contacts' ) ); ?>">
						<?php esc_html_e( 'Réinitialiser les filtres', 'prospection-claude' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped contacts">
			<thead>
				<tr>
					<th scope="col" class="manage-column column-name column-primary">
						<?php esc_html_e( 'Nom', 'prospection-claude' ); ?>
					</th>
					<th scope="col" class="manage-column">
						<?php esc_html_e( 'Entreprise', 'prospection-claude' ); ?>
					</th>
					<th scope="col" class="manage-column">
						<?php esc_html_e( 'Email', 'prospection-claude' ); ?>
					</th>
					<th scope="col" class="manage-column">
						<?php esc_html_e( 'Téléphone', 'prospection-claude' ); ?>
					</th>
					<th scope="col" class="manage-column">
						<?php esc_html_e( 'Catégorie', 'prospection-claude' ); ?>
					</th>
					<th scope="col" class="manage-column">
						<?php esc_html_e( 'Statut', 'prospection-claude' ); ?>
					</th>
					<th scope="col" class="manage-column">
						<?php esc_html_e( 'Date d\'ajout', 'prospection-claude' ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $contacts as $contact ) : ?>
					<tr>
						<td class="column-name column-primary" data-colname="<?php esc_attr_e( 'Nom', 'prospection-claude' ); ?>">
							<strong>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=prospection-claude-contacts&action=edit&id=' . $contact->id ) ); ?>">
									<?php echo esc_html( $contact->get_full_name() ); ?>
								</a>
							</strong>
							<div class="row-actions">
								<span class="edit">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=prospection-claude-contacts&action=edit&id=' . $contact->id ) ); ?>">
										<?php esc_html_e( 'Modifier', 'prospection-claude' ); ?>
									</a>
								</span>
								|
								<span class="delete">
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=prospection-claude-contacts&action=delete&id=' . $contact->id ), 'delete_contact' ) ); ?>"
									   onclick="return confirm('<?php echo esc_js( __( 'Êtes-vous sûr de vouloir supprimer ce contact ?', 'prospection-claude' ) ); ?>');">
										<?php esc_html_e( 'Supprimer', 'prospection-claude' ); ?>
									</a>
								</span>
							</div>
						</td>
						<td data-colname="<?php esc_attr_e( 'Entreprise', 'prospection-claude' ); ?>">
							<?php echo esc_html( $contact->company ); ?>
						</td>
						<td data-colname="<?php esc_attr_e( 'Email', 'prospection-claude' ); ?>">
							<a href="mailto:<?php echo esc_attr( $contact->email ); ?>">
								<?php echo esc_html( $contact->email ); ?>
							</a>
						</td>
						<td data-colname="<?php esc_attr_e( 'Téléphone', 'prospection-claude' ); ?>">
							<?php echo esc_html( $contact->phone ); ?>
						</td>
						<td data-colname="<?php esc_attr_e( 'Catégorie', 'prospection-claude' ); ?>">
							<span class="category-badge category-<?php echo esc_attr( $contact->category ); ?>">
								<?php echo esc_html( $contact->get_category_label() ); ?>
							</span>
						</td>
						<td data-colname="<?php esc_attr_e( 'Statut', 'prospection-claude' ); ?>">
							<?php if ( $contact->is_subscribed ) : ?>
								<span class="status-badge status-active"><?php esc_html_e( 'Abonné', 'prospection-claude' ); ?></span>
							<?php else : ?>
								<span class="status-badge status-inactive"><?php esc_html_e( 'Désabonné', 'prospection-claude' ); ?></span>
							<?php endif; ?>
						</td>
						<td data-colname="<?php esc_attr_e( 'Date d\'ajout', 'prospection-claude' ); ?>">
							<?php echo esc_html( mysql2date( get_option( 'date_format' ), $contact->created_at ) ); ?>
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
