<?php

if (! defined('ABSPATH')) {
	exit;
}

if (! class_exists('WP_Gatsby_Admin')) {

	class WP_Gatsby_Admin {

		private static $default = array(
      'preview' => array(
        'activated' => 0,
        'base' => '',
      ),
      'netlify' => array(
        'auto_publish' => 0,
        'build_hook' => '',
			),
		);

		public static function init() {
			self::hooks();
		}

		private static function hooks() {
			if ( apply_filters( 'gatsby_show_admin', true ) ) {
				if ( apply_filters( 'gatsby_show_admin_menu', true ) ) {
					add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
				}
				
				if ( apply_filters( 'gatsby_show_admin_bar_menu', true ) ) {
					add_action( 'admin_bar_menu', array( __CLASS__, 'admin_bar_menu' ), 999 );				
					add_filter('rest_cache_show_admin_bar_menu', function() {
						return false;
					});
				}				
			}
		}

		public static function admin_bar_menu( $wp_admin_bar ) {
			$args = array(
				'id'    => 'wp-gatsby-publish',
				'title' => __('Publish to Gatsby', 'wp-gatsby' ),
				'href'  => self::_publish_to_gatsby_url(),
			);
		
			$wp_admin_bar->add_node( $args );
		}

		public static function admin_menu() {
			add_submenu_page( 
				'options-general.php', 
				__( 'WP Gatsby', 'wp-gatsby' ), 
				__( 'WP Gatsby', 'wp-gatsby' ), 
				'manage_options', 
				'wp-gatsby', 
				array( __CLASS__, 'render_page' ) 
			);
		}

		public static function render_page() {
			$notice = null;

			if ( isset( $_REQUEST['gatsby_nonce'] ) && wp_verify_nonce( $_REQUEST['gatsby_nonce'], 'gatsby_options' ) ) {
				if ( isset( $_GET['publish'] ) && 1 == $_GET['publish'] ) {
			    $options = self::get_options();
          $deploy_response = WP_Gatsby::trigger_netlify_deploy($options['netlify']['build_hook']);
					if ( $deploy_response == 200  ) {
						$type    = 'updated';
						$message = __( 'A new Gatsby edition is being published. Check the site every 5 minutes.', 'wp-gatsby' );
					} else {
						$type    = 'error';
						$message = __( 'We were unable to start publishing a new Gatsby edition. Check your settings.', 'wp-gatsby' );
					}
				} elseif ( isset( $_POST['gatsby_options'] ) && ! empty( $_POST['gatsby_options'] ) ) {
					if ( self::_update_options( $_POST['gatsby_options'] ) ) {
						$type    = 'updated';
						$message = __( 'WP Gatsby options have been successfully updated.', 'wp-gatsby' );
					} else {
						$type    = 'error';
						$message = __( 'WP Gatsby options were not updated. Check the errors below.', 'wp-gatsby' );
					}
				}
				add_settings_error( 'wp-gatsby-notice', esc_attr( 'settings_updated' ), $message, $type );
			}

			$options = self::get_options();

			require_once dirname( __FILE__ ) . '/../views/html-options.php';
		}

		private static function _update_options( $options ) {
			$options = apply_filters( 'gatsby_update_options', $options );

			return update_option( 'gatsby_options', $options, 'yes' );
		}

		public static function get_options( $key = null ) {
			$options = apply_filters( 'gatsby_get_options', get_option( 'gatsby_options', self::$default ) );
			
			if ( is_string( $key ) && array_key_exists( $key, $options ) ) {
				return $options[$key];
			} 

			return $options;
		}

		private static function _publish_to_gatsby_url() {
			return wp_nonce_url( admin_url( 'options-general.php?page=wp-gatsby&publish=1' ), 'gatsby_options', 'gatsby_nonce' );
		}

	}

	WP_Gatsby_Admin::init();

}
