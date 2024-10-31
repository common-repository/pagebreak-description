<?php
/**
 * Plugin Name: Pagebreak Description
 * Plugin URI:  https://wordpress.org/plugins/pagebreak-description/
 * Description: Enter page breaks and before/after descriptions. Values are used in pagination.
 * Version:     1.09
 * Author:      Katsushi Kawamori
 * Author URI:  https://riverforest-wp.info/
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: pagebreak-description
 *
 * @package Pagebreak Description
 */

/*
	Copyright (c) 2023- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

$pagebreakdescription = new PagebreakDescription();

/** ==================================================
 * Main
 */
class PagebreakDescription {

	/** ==================================================
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		/* Pagebreak Description Block */
		add_action( 'init', array( $this, 'pagebreak_description_init' ) );

		/* Change contents inner pagination */
		add_action( 'wp_enqueue_scripts', array( $this, 'load_frontend_scripts' ) );
		add_filter( 'the_content', array( $this, 'pagebreak_description_wp_link_pages' ), 8 );
		add_filter( 'wp_link_pages_args', array( $this, 'wp_link_pages_args_add_next_and_number' ) );
		add_filter( 'wp_link_pages_link', array( $this, 'link_pages' ), 10, 2 );

		/* Management screen */
		add_action( 'admin_menu', array( $this, 'plugin_menu' ) );
		add_filter( 'plugin_action_links', array( $this, 'settings_link' ), 10, 2 );
	}

	/** ==================================================
	 * Frontend css
	 *
	 * @since 1.00
	 */
	public function load_frontend_scripts() {
		$css_url = apply_filters( 'pagebreak_description_css', plugin_dir_url( __FILE__ ) . 'css/style.css' );
		wp_enqueue_style( 'pagebreak-description-css', $css_url, null, '1.0.0' );
	}

	/** ==================================================
	 * Add contents inner pagination
	 * Performed before block generation priority 9
	 *
	 * @param string $content  contents.
	 * @since 1.00
	 */
	public function pagebreak_description_wp_link_pages( $content ) {

		global $post, $page, $numpages;

		/* Check for nextpage to display page links for paginated posts. */
		if ( $numpages > 1 && has_block( 'pagebreak-description/pagebreakdescription', $post->post_content ) ) {
			$nextpage = wp_link_pages( array( 'echo' => 0 ) );
			$content = str_replace( '<!-- /wp:pagebreak-description/pagebreakdescription -->', '', $content );
			$position = get_option( 'pagebreak-description-position', 'after' );
			switch ( $position ) {
				case 'before':
					$content = $nextpage . $content;
					break;
				case 'after':
					$content = $content . $nextpage;
					break;
				case 'both':
					$content = $nextpage . $content . $nextpage;
					break;
				default:
					$content = $content . $nextpage;
			}
		}

		return $content;
	}

	/** ==================================================
	 * Change contents inner pagination
	 *
	 * @param array $args  pagebreak args.
	 * @since 1.00
	 */
	public function wp_link_pages_args_add_next_and_number( $args ) {

		global $page, $numpages, $post;

		$descriptions = $this->descriptions_arr( $post->post_content, $post->ID );

		$prevpage = null;
		$nextpage = null;
		if ( ! empty( $descriptions ) ) {
			if ( $page < $numpages ) {
				if ( array_key_exists( $page, $descriptions ) ) {
					if ( ! empty( $descriptions[ $page ]['next'] ) ) {
						/* translators: Description */
						$description = sprintf( __( 'Next page: %s', 'pagebreak-description' ), $descriptions[ $page ]['next'] );
					} elseif ( ! empty( $descriptions[ $page + 1 ]['prev'] ) ) {
						/* translators: Description */
						$description = sprintf( __( 'Next page: %s', 'pagebreak-description' ), $descriptions[ $page + 1 ]['prev'] );
					} else {
						/* translators: Page number */
						$description = sprintf( __( 'Read page %s', 'pagebreak-description' ), $page + 1 );
					}
				} else {
					/* translators: Page number */
					$description = sprintf( __( 'Read page %s', 'pagebreak-description' ), $page + 1 );
				}
				$nextpage = _wp_link_page( $page + 1 ) . esc_html( $description ) . ' &gt;&gt;</a>';
			}
			if ( 1 < $page || $numpages === $page ) {
				if ( array_key_exists( $page, $descriptions ) ) {
					if ( ! empty( $descriptions[ $page - 1 ]['prev'] ) ) {
						/* translators: Description */
						$description = sprintf( __( 'Prev page: %s', 'pagebreak-description' ), $descriptions[ $page - 1 ]['prev'] );
					} elseif ( 2 < $page && ! empty( $descriptions[ $page - 2 ]['next'] ) ) {
						/* translators: Description */
						$description = sprintf( __( 'Prev page: %s', 'pagebreak-description' ), $descriptions[ $page - 2 ]['next'] );
					} else {
						/* translators: Page number */
						$description = sprintf( __( 'Read page %s', 'pagebreak-description' ), $page - 1 ) . '</a>';
					}
				} else {
					/* translators: Page number */
					$description = sprintf( __( 'Read page %s', 'pagebreak-description' ), $page - 1 ) . '</a>';
				}
				$prevpage = _wp_link_page( $page - 1 ) . '&lt;&lt; ' . esc_html( $description ) . '</a>';
			}
		}
		$args['before'] = '<div class="pagebreak-description-next-page-link">' . $nextpage . '</div>';
		$args['before'] .= '<div class="pagebreak-description-pagination">';
		$args['after'] .= '</div>';
		$args['after'] .= '<div class="pagebreak-description-for-over">' . esc_html__( 'Mouse over or long press for description.', 'pagebreak-description' ) . '</div>';
		$args['after'] .= '<div class="pagebreak-description-next-page-link">' . $prevpage . '</div>';
		$args['link_before'] = '';
		$args['link_after'] = '';
		$args['next_or_number'] = 'number';
		$args['separator'] = ' ';
		$args['pagelink'] = '%';
		$args['echo'] = 0;

		return $args;
	}

	/** ==================================================
	 * Make descriptions array
	 *
	 * @param string $post_content  contents.
	 * @param int    $id  post id.
	 * @since 1.02
	 */
	private function descriptions_arr( $post_content, $id ) {

		$descriptions = array();
		$descriptions[0] = array(
			'prev' => null,
			'next' => null,
		);
		$count = 0;
		if ( preg_match_all( '/<!-- wp:pagebreak-description\/pagebreakdescription ({.*?}) -->/ims', $post_content, $found ) !== false ) {
			if ( ! empty( $found[1] ) ) {
				foreach ( $found[1] as $value ) {
					$values = json_decode( $value, true );
					$thisdesc = null;
					$nextdesc = null;
					if ( array_key_exists( 'thisdesc', $values ) ) {
						$thisdesc = $values['thisdesc'];
					}
					if ( array_key_exists( 'nextdesc', $values ) ) {
						$nextdesc = $values['nextdesc'];
					}
					++$count;
					$descriptions[ $count ] = array(
						'prev' => $thisdesc,
						'next' => $nextdesc,
					);
				}
				$descriptions[ $count + 1 ] = array(
					'prev' => $nextdesc,
					'next' => null,
				);
			}
		}

		update_post_meta( $id, 'pagebreak_descriptions', $descriptions );

		return $descriptions;
	}

	/** ==================================================
	 * Add title for page link
	 *
	 * @param string $link  page link html.
	 * @param int    $i  page number.
	 * @since 1.02
	 */
	public function link_pages( $link, $i ) {

		global $post;
		$descriptions = get_post_meta( $post->ID, 'pagebreak_descriptions', true );
		$description = null;
		if ( ! empty( $descriptions ) ) {
			if ( ! is_null( $descriptions[ $i ]['prev'] ) ) {
				$description = $descriptions[ $i ]['prev'];
			} else {
				$description = $descriptions[ $i - 1 ]['next'];
			}
		}
		if ( ! is_null( $description ) ) {
			$link = '<span class="cp_tooltip_description">' . $link . '<span class="cp_tooltip_description_text">' . esc_html( $description ) . '</span></span>';
		}
		return $link;
	}

	/** ==================================================
	 * Attribute block
	 *
	 * @since 1.00
	 */
	public function pagebreak_description_init() {

		register_block_type(
			__DIR__ . '/block/build',
			array(
				'title' => _x( 'Page breaks and before/after descriptions', 'block title', 'pagebreak-description' ),
				'description' => _x( 'Enter page breaks and before/after descriptions. Values are used in pagination. Do not mix this block with the Page Break block, as it may cause the comment to be misaligned.', 'block description', 'pagebreak-description' ),
				'keywords' => array(
					_x( 'next page', 'block keyword', 'pagebreak-description' ),
					_x( 'prev page', 'block keyword', 'pagebreak-description' ),
					_x( 'pagebreak', 'block keyword', 'pagebreak-description' ),
					_x( 'pagination', 'block keyword', 'pagebreak-description' ),
				),
			)
		);

		$script_handle = generate_block_asset_handle( 'pagebreak-description/pagebreakdescription', 'editorScript' );
		wp_set_script_translations( $script_handle, 'pagebreak-description' );
		wp_localize_script(
			$script_handle,
			'pagebreak_description_preview_block',
			array(
				'url' => esc_url( plugin_dir_url( __FILE__ ) . 'assets/preview.png' ),
			)
		);
	}

	/** ==================================================
	 * Add a "Settings" link to the plugins page
	 *
	 * @param  array  $links  links array.
	 * @param  string $file   file.
	 * @return array  $links  links array.
	 * @since 1.09
	 */
	public function settings_link( $links, $file ) {
		static $this_plugin;
		if ( empty( $this_plugin ) ) {
			$this_plugin = 'pagebreak-description/class-pagebreakdescription.php';
		}
		if ( $file == $this_plugin ) {
			$links[] = '<a href="' . admin_url( 'options-general.php?page=PagebreakDescription' ) . '">' . __( 'Settings' ) . '</a>';
		}
		return $links;
	}

	/** ==================================================
	 * Settings page
	 *
	 * @since 1.09
	 */
	public function plugin_menu() {
		add_options_page(
			'Pagebreak Description Options',
			'Pagebreak Description',
			'manage_options',
			'PagebreakDescription',
			array( $this, 'plugin_options' ),
		);
	}

	/** ==================================================
	 * Settings page
	 *
	 * @since 1.09
	 */
	public function plugin_options() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
		}

		$this->options_updated();

		$scriptname = admin_url( 'options-general.php?page=PagebreakDescription' );
		$position = get_option( 'pagebreak-description-position', 'after' );

		$checked = array(
			'before' => array(
				'checked' => null,
				'label' => __( 'Before Content', 'pagebreak-description' ),
			),
			'after' => array(
				'checked' => null,
				'label' => __( 'After Content', 'pagebreak-description' ),
			),
			'both' => array(
				'checked' => null,
				'label' => __( 'Before and After Content', 'pagebreak-description' ),
			),
		);
		$checked[ $position ]['checked'] = 'checked';

		$plugin_page = __( 'https://wordpress.org/plugins/pagebreak-description/', 'pagebreak-description' );

		?>
		<div class="wrap">
		<h2>Pagebreak Description</h2>

			<details>
			<summary><strong><?php esc_html_e( 'Various links of this plugin', 'pagebreak-description' ); ?></strong></summary>
			<?php $this->credit(); ?>
			</details>

			<form method="post" action="<?php echo esc_url( $scriptname ); ?>" />
				<?php wp_nonce_field( 'pg_bk_desc_settings', 'pagebreakdescription_settings' ); ?>
				<div style="margin: 5px; padding: 5px;">
					<h3><?php esc_html_e( 'Output destination for pagination', 'pagebreak-description' ); ?></h3>
					<?php
					foreach ( $checked as $key => $value ) {
						?>
						<div style="line-height: 2rem;"><label><input type="radio" name="position" value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $checked[ $key ]['checked'] ); ?>><?php echo esc_attr( $checked[ $key ]['label'] ); ?></label></div>
						<?php
					}
					submit_button( __( 'Save Changes' ), 'large', 'pg_bk_desc_settings-apply', true );
					?>
				</div>
			</form>
			<hr />
			<div style="margin: 5px; padding: 5px;">
				<h3>
				<?php esc_html_e( 'Provides filter hooks to customize the appearance. Please read the plugin page.', 'pagebreak-description' ); ?>
				</h3>
				<a href="<?php echo esc_url( $plugin_page ); ?>" class="page-title-action" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Plugin page', 'pagebreak-description' ); ?></a>
			</div>
		</div>
		<?php
	}

	/** ==================================================
	 * Credit
	 *
	 * @since 1.09
	 */
	private function credit() {

		$plugin_name    = null;
		$plugin_ver_num = null;
		$plugin_path    = plugin_dir_path( __FILE__ );
		$plugin_dir     = untrailingslashit( wp_normalize_path( $plugin_path ) );
		$slugs          = explode( '/', $plugin_dir );
		$slug           = end( $slugs );
		$files          = scandir( $plugin_dir );
		foreach ( $files as $file ) {
			if ( '.' === $file || '..' === $file || is_dir( $plugin_path . $file ) ) {
				continue;
			} else {
				$exts = explode( '.', $file );
				$ext  = strtolower( end( $exts ) );
				if ( 'php' === $ext ) {
					$plugin_datas = get_file_data(
						$plugin_path . $file,
						array(
							'name'    => 'Plugin Name',
							'version' => 'Version',
						)
					);
					if ( array_key_exists( 'name', $plugin_datas ) && ! empty( $plugin_datas['name'] ) && array_key_exists( 'version', $plugin_datas ) && ! empty( $plugin_datas['version'] ) ) {
						$plugin_name    = $plugin_datas['name'];
						$plugin_ver_num = $plugin_datas['version'];
						break;
					}
				}
			}
		}
		$plugin_version = __( 'Version:' ) . ' ' . $plugin_ver_num;
		/* translators: FAQ Link & Slug */
		$faq       = sprintf( __( 'https://wordpress.org/plugins/%s/faq', 'pagebreak-description' ), $slug );
		$support   = 'https://wordpress.org/support/plugin/' . $slug;
		$review    = 'https://wordpress.org/support/view/plugin-reviews/' . $slug;
		$translate = 'https://translate.wordpress.org/projects/wp-plugins/' . $slug;
		$facebook  = 'https://www.facebook.com/katsushikawamori/';
		$twitter   = 'https://twitter.com/dodesyo312';
		$youtube   = 'https://www.youtube.com/channel/UC5zTLeyROkvZm86OgNRcb_w';
		$donate    = __( 'https://shop.riverforest-wp.info/donate/', 'pagebreak-description' );

		?>
		<span style="font-weight: bold;">
		<div>
		<?php echo esc_html( $plugin_version ); ?> | 
		<a style="text-decoration: none;" href="<?php echo esc_url( $faq ); ?>" target="_blank" rel="noopener noreferrer">FAQ</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $support ); ?>" target="_blank" rel="noopener noreferrer">Support Forums</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $review ); ?>" target="_blank" rel="noopener noreferrer">Reviews</a>
		</div>
		<div>
		<a style="text-decoration: none;" href="<?php echo esc_url( $translate ); ?>" target="_blank" rel="noopener noreferrer">
		<?php
		/* translators: Plugin translation link */
		echo esc_html( sprintf( __( 'Translations for %s' ), $plugin_name ) );
		?>
		</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $facebook ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-facebook"></span></a> | <a style="text-decoration: none;" href="<?php echo esc_url( $twitter ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-twitter"></span></a> | <a style="text-decoration: none;" href="<?php echo esc_url( $youtube ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-video-alt3"></span></a>
		</div>
		</span>

		<div style="width: 250px; height: 180px; margin: 5px; padding: 5px; border: #CCC 2px solid;">
		<h3><?php esc_html_e( 'Please make a donation if you like my work or would like to further the development of this plugin.', 'pagebreak-description' ); ?></h3>
		<div style="text-align: right; margin: 5px; padding: 5px;"><span style="padding: 3px; color: #ffffff; background-color: #008000">Plugin Author</span> <span style="font-weight: bold;">Katsushi Kawamori</span></div>
		<button type="button" style="margin: 5px; padding: 5px;" onclick="window.open('<?php echo esc_url( $donate ); ?>')"><?php esc_html_e( 'Donate to this plugin &#187;' ); ?></button>
		</div>

		<?php
	}

	/** ==================================================
	 * Update wp_options table.
	 *
	 * @since 1.09
	 */
	private function options_updated() {

		if ( isset( $_POST['pg_bk_desc_settings-apply'] ) && ! empty( $_POST['pg_bk_desc_settings-apply'] ) ) {
			if ( check_admin_referer( 'pg_bk_desc_settings', 'pagebreakdescription_settings' ) ) {
				if ( ! empty( $_POST['position'] ) ) {
					update_option( 'pagebreak-description-position', sanitize_text_field( wp_unslash( $_POST['position'] ) ) );
					echo '<div class="notice notice-success is-dismissible"><ul><li>' . esc_html( __( 'Settings' ) . ' --> ' . __( 'Settings saved.' ) ) . '</li></ul></div>';
				}
			}
		}
	}
}
