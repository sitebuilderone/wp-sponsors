<?php
/**
 * Plugin Name: WP Sponsors
 * Description: Manage sponsors and display active sponsors with the [wp_sponsors] shortcode.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 * Text Domain: wp-sponsors
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WP_Sponsors {
	const POST_TYPE = 'wps_sponsor';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'styles' ) );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column' ), 10, 2 );
		add_shortcode( 'wp_sponsors', array( __CLASS__, 'shortcode' ) );
	}

	public static function register_post_type() {
		register_post_type( self::POST_TYPE, array(
			'labels' => array(
				'name' => __( 'Sponsors', 'wp-sponsors' ),
				'singular_name' => __( 'Sponsor', 'wp-sponsors' ),
				'add_new_item' => __( 'Add New Sponsor', 'wp-sponsors' ),
				'edit_item' => __( 'Edit Sponsor', 'wp-sponsors' ),
				'new_item' => __( 'New Sponsor', 'wp-sponsors' ),
				'search_items' => __( 'Search Sponsors', 'wp-sponsors' ),
				'not_found' => __( 'No sponsors found.', 'wp-sponsors' ),
			),
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_rest' => false,
			'exclude_from_search' => true,
			'has_archive' => false,
			'rewrite' => false,
			'query_var' => false,
			'menu_icon' => 'dashicons-heart',
			'supports' => array( 'title' ),
		) );
	}

	public static function meta_boxes() {
		add_meta_box( 'wps-details', __( 'Sponsor Details', 'wp-sponsors' ), array( __CLASS__, 'fields' ), self::POST_TYPE, 'normal', 'high' );
	}

	public static function fields( $post ) {
		wp_nonce_field( 'wps_save', 'wps_nonce' );
		$logo = absint( get_post_meta( $post->ID, '_wps_logo', true ) );
		?>
		<p><?php esc_html_e( 'Enter the sponsor name in the title field above.', 'wp-sponsors' ); ?></p>
		<p><label for="wps-description"><strong><?php esc_html_e( 'Short description', 'wp-sponsors' ); ?></strong></label></p>
		<textarea id="wps-description" name="wps_description" rows="4" class="widefat"><?php echo esc_textarea( get_post_meta( $post->ID, '_wps_description', true ) ); ?></textarea>
		<p><strong><?php esc_html_e( 'Logo', 'wp-sponsors' ); ?></strong></p>
		<div id="wps-logo-preview"><?php echo $logo ? wp_get_attachment_image( $logo, 'thumbnail' ) : ''; ?></div>
		<input type="hidden" id="wps-logo" name="wps_logo" value="<?php echo esc_attr( $logo ); ?>">
		<p><button type="button" class="button" id="wps-choose-logo"><?php esc_html_e( 'Choose logo', 'wp-sponsors' ); ?></button>
		<button type="button" class="button" id="wps-remove-logo"><?php esc_html_e( 'Remove logo', 'wp-sponsors' ); ?></button></p>
		<p><label><input type="checkbox" name="wps_active" value="1" <?php checked( get_post_meta( $post->ID, '_wps_active', true ), '1' ); ?>> <?php esc_html_e( 'Active — display this sponsor when published', 'wp-sponsors' ); ?></label></p>
		<p><label for="wps-url"><strong><?php esc_html_e( 'Website URL', 'wp-sponsors' ); ?></strong></label></p>
		<input type="url" id="wps-url" name="wps_url" class="widefat" placeholder="https://example.com" value="<?php echo esc_attr( get_post_meta( $post->ID, '_wps_url', true ) ); ?>">
		<p><?php echo esc_html( sprintf( __( 'Shortcode for this sponsor: [wp_sponsors ids="%d"]', 'wp-sponsors' ), $post->ID ) ); ?></p>
		<?php
	}

	public static function save( $post_id ) {
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( ! isset( $_POST['wps_nonce'] ) || ! is_string( $_POST['wps_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wps_nonce'] ) ), 'wps_save' ) ) {
			return;
		}
		$description = isset( $_POST['wps_description'] ) && is_string( $_POST['wps_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wps_description'] ) ) : '';
		$url = isset( $_POST['wps_url'] ) && is_string( $_POST['wps_url'] ) ? esc_url_raw( wp_unslash( $_POST['wps_url'] ), array( 'http', 'https' ) ) : '';
		$logo = isset( $_POST['wps_logo'] ) && is_scalar( $_POST['wps_logo'] ) ? absint( $_POST['wps_logo'] ) : 0;
		update_post_meta( $post_id, '_wps_description', $description );
		update_post_meta( $post_id, '_wps_url', $url );
		update_post_meta( $post_id, '_wps_logo', wp_attachment_is_image( $logo ) ? $logo : 0 );
		update_post_meta( $post_id, '_wps_active', isset( $_POST['wps_active'] ) && '1' === $_POST['wps_active'] ? '1' : '0' );
	}

	public static function admin_assets() {
		$screen = get_current_screen();
		if ( ! $screen || self::POST_TYPE !== $screen->post_type || 'post' !== $screen->base ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_script( 'wp-sponsors-admin', plugins_url( 'assets/admin.js', __FILE__ ), array( 'jquery', 'media-views' ), '1.0.0', true );
		wp_localize_script( 'wp-sponsors-admin', 'wpSponsors', array( 'title' => __( 'Choose sponsor logo', 'wp-sponsors' ), 'button' => __( 'Use this logo', 'wp-sponsors' ) ) );
	}

	public static function styles() {
		wp_enqueue_style( 'wp-sponsors', plugins_url( 'assets/sponsors.css', __FILE__ ), array(), '1.0.0' );
	}

	public static function columns( $columns ) {
		$columns['wps_active'] = __( 'Active', 'wp-sponsors' );
		$columns['wps_shortcode'] = __( 'Shortcode', 'wp-sponsors' );
		return $columns;
	}

	public static function column( $column, $post_id ) {
		if ( 'wps_active' === $column ) {
			echo esc_html( '1' === get_post_meta( $post_id, '_wps_active', true ) ? __( 'Yes', 'wp-sponsors' ) : __( 'No', 'wp-sponsors' ) );
		} elseif ( 'wps_shortcode' === $column ) {
			echo '<code>' . esc_html( '[wp_sponsors ids="' . $post_id . '"]' ) . '</code>';
		}
	}

	public static function shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'ids' => '', 'limit' => -1, 'columns' => 3, 'orderby' => 'title', 'order' => 'ASC',
			'show_name' => 'yes', 'show_description' => 'yes', 'show_logo' => 'yes', 'new_tab' => 'no',
		), $atts, 'wp_sponsors' );
		$args = array(
			'post_type' => self::POST_TYPE, 'post_status' => 'publish',
			'posts_per_page' => (int) $atts['limit'] > 0 ? (int) $atts['limit'] : -1,
			'orderby' => in_array( $atts['orderby'], array( 'title', 'date', 'rand', 'post__in' ), true ) ? $atts['orderby'] : 'title',
			'order' => 'DESC' === strtoupper( $atts['order'] ) ? 'DESC' : 'ASC',
			'meta_key' => '_wps_active', 'meta_value' => '1', 'no_found_rows' => true,
		);
		if ( '' !== trim( $atts['ids'] ) ) {
			$ids = array_filter( array_map( 'absint', explode( ',', $atts['ids'] ) ) );
			if ( ! $ids ) {
				return '';
			}
			$args['post__in'] = $ids;
		}
		$query = new WP_Query( $args );
		if ( ! $query->posts ) {
			return '';
		}
		$enabled = static function ( $value ) { return in_array( strtolower( (string) $value ), array( 'yes', 'true', '1' ), true ); };
		$html = '<div class="wp-sponsors wp-sponsors--columns-' . max( 1, min( 6, (int) $atts['columns'] ) ) . '">';
		foreach ( $query->posts as $sponsor ) {
			$name = get_the_title( $sponsor );
			$url = esc_url( get_post_meta( $sponsor->ID, '_wps_url', true ), array( 'http', 'https' ) );
			$logo = $enabled( $atts['show_logo'] ) ? wp_get_attachment_image( absint( get_post_meta( $sponsor->ID, '_wps_logo', true ) ), 'medium', false, array( 'class' => 'wp-sponsors__logo', 'alt' => $name ) ) : '';
			$identity = $logo;
			if ( $enabled( $atts['show_name'] ) ) {
				$identity .= '<span class="wp-sponsors__name">' . esc_html( $name ) . '</span>';
			}
			$html .= '<div class="wp-sponsors__sponsor">';
			if ( $identity && $url ) {
				$target = $enabled( $atts['new_tab'] ) ? ' target="_blank" rel="sponsored noopener noreferrer"' : ' rel="sponsored"';
				$html .= '<a class="wp-sponsors__link" href="' . $url . '"' . $target . '>' . $identity . '</a>';
			} else {
				$html .= $identity;
			}
			$description = get_post_meta( $sponsor->ID, '_wps_description', true );
			if ( $enabled( $atts['show_description'] ) && '' !== $description ) {
				$html .= '<p class="wp-sponsors__description">' . nl2br( esc_html( $description ) ) . '</p>';
			}
			$html .= '</div>';
		}
		return $html . '</div>';
	}
}
WP_Sponsors::init();
