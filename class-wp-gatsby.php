<?php
/**
 * Plugin Name: WP Gatsby
 * Description: Tools for working with Wordpress and GatsbyJS together.
 * Author: Geoffrey Sechter
 * Author URI: http://github.com/lightstrike
 * Version: 0.2.0
 * Plugin URI: https://github.com/lightstrike/wp-gatsby
 * License: MIT
 */

use WP_Gatsby_Admin;

if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('WP_Gatsby')) {

    /**
     * Core class to manage all plugin functionality.
     * Enables:
     * publishing WP content to a Gatsby site hosted by Netlify
     * custom preview URLs
     */
    class WP_Gatsby
    {

		const VERSION = '0.3.0';

		private static $refresh = null;

		public static function init() {
			self::includes();
			self::hooks();
		}

		private static function includes() {
			require_once dirname( __FILE__ ) . '/includes/admin/classes/class-wp-gatsby-admin.php';
		}

		private static function hooks() {
			$options = get_option('gatsby_options', array() );
			if ($options['dev_preview']['activated'] == 1) {
				self::add_updates_rest_api_endpoint();
				self::set_gatsby_refresh();
			}
			if ($options['prod_preview']['activated'] == 1) {
				self::add_preview_rest_api_endpoint();
				self::set_custom_visit_site_url();
				self::set_custom_view_url();
				self::set_custom_preview_url();
			}
			if ($options['netlify']['auto_publish'] == 1) {
				self::set_netlify_auto_publish();
			}
		}

		public static function add_updates_rest_api_endpoint() {
			/**
			* Get latest updates for all post types.
			*
			* @since  0.2.0
			* @param  $request
			* @return array content updates data
			*/
			function get_latest_updates( $request ) {
				$since = esc_html( $request['since'] );
				if ( !$since ) {
					$since = '1 day ago';
				}
				$args = array(
					'post_status' => array('inherit', 'draft', 'auto-draft'),
					// FIXME: Enable dynamically setting this for ACF support.
					'post_type' => array('post', 'page', 'revision'),
					'date_query' => array(
						array(
							'column' => 'post_modified_gmt',
							'after'  => $since,
						),
					),
					'posts_per_page' => -1,
					'suppress_filters' => true,
				);
				$query = new WP_Query( $args );
				$posts = $query->get_posts('orderby=modified&sort_order=desc');
				$all_updates = [];
				foreach($posts as $post) {
					// get parent post information
					// FIXME: Allow dynamically setting which update fields propagate, important for ACF.
					if ($post->post_parent) {
						$parent = get_post($post->post_parent);
						$parent->post_content = apply_filters( 'the_content', $post->post_content );
						$parent->post_title = $post->post_title;
						$parent->post_modified = $post->post_modified;
						$parent->post_modified_gmt = $post->post_modified_gmt;
						$post_data = $parent;
					} else {
						$post_data = $post;
					}
					array_push($all_updates, $post_data);
				}

				// Adapted from: https://stackoverflow.com/a/7872079
				function remove_older_duplicate_posts($posts) {
					$uniqueness = array();

					foreach ($posts as $post) {
					  if (isset($uniqueness[$post->ID])) {
						// Skip since more recent update already found.
						// Note this depends on the descending orderby in `get_posts` for `$query`.
						continue;
					  }
					  // Remember this update as the most recent.
					  $uniqueness[$post->ID] = $post;
					}
					$data = array_values($uniqueness);
					return $data;
				}

				$latest_updates = remove_older_duplicate_posts($all_updates);

				$testing = array(
					'posts' => $posts,
					'query' => $query->request,
				);
				return $latest_updates;
			}

			add_action( 'rest_api_init', function () {
				register_rest_route( 'wp/v2', '/updates', array(
					'methods' => 'GET',
					'callback' => 'get_latest_updates',
				));
			});
		}

		public static function add_preview_rest_api_endpoint() {
			/**
			* Get latest revision for a post slug.
			*
			* @since  0.1.0
			* @param  $request
			* @return array content preview data
			*/
			function get_latest_revision( $request ) {
				$id = esc_html( $request['id'] );
				$post = get_post($id);
				$latest_revision = array_shift(wp_get_post_revisions($post));
				$data['title'] = array(
					'raw'      => $latest_revision->post_title,
					'rendered' => get_the_title( $post->ID ),
				);
				$data['content'] = array(
					'raw'      => $post->post_content,
					/** This filter is documented in wp-includes/post-template.php */
					'rendered' => apply_filters( 'the_content', $latest_revision->post_content ),
				);
				return $data;
			}

			add_action( 'rest_api_init', function () {
				register_rest_route( 'wp/v2', '/preview/(?P<id>[\d]+)', array(
					'methods' => 'GET',
					'callback' => 'get_latest_revision',
				));
			});
		}

		/*
		 * See: https://wordpress.stackexchange.com/a/147780
		 */
		public static function set_custom_visit_site_url() {
			function custom_visit_site_url( $wp_admin_bar ) {
				$options = get_option('gatsby_options', array() );
				// Get a reference to the view-site node to modify.
				$node = $wp_admin_bar->get_node('view-site');

				// Change target
				$node->meta['target'] = '_blank';
				$node->meta['rel'] = 'noopener noreferrer';
				$node->href = $options['url'];

				// Update Node
				$wp_admin_bar->add_node($node);

				// Site name node
				$node = $wp_admin_bar->get_node('site-name');

				// Change target
				$node->meta['target'] = '_blank';
				$node->meta['rel'] = 'noopener noreferrer';
				$node->href = $options['url'];

				// Update Node
				$wp_admin_bar->add_node($node);
			}
			add_action( 'admin_bar_menu', 'custom_visit_site_url', 80 );
		}

		/*
		 * See: https://developer.wordpress.org/reference/hooks/page_link/
		 */
		public static function set_custom_view_url() {
			function custom_view_link() {
				$options = get_option('gatsby_options', array() );
				$post_slug = get_post_field('post_name');
				return $options['url'].$post_slug;
			}
			add_filter( 'post_link', 'custom_view_link' );
			add_filter( 'page_link', 'custom_view_link' );
		}

		/*
		 * See: https://www.cyberciti.biz/faq/php-wordpress-change-post-url-via-preview_post_link-filter/
		 */
    	public static function set_custom_preview_url() {
			function custom_preview_link() {
				$options = get_option('gatsby_options', array() );
				$post_id = get_the_ID();
				return $options['url'].$options['preview']['base'].$post_id;
			}
			add_filter( 'preview_post_link', 'custom_preview_link' );
			add_filter( 'preview_page_link', 'custom_preview_link' );
    	}

        public static function clear_cache($cache) {
			if ( $cache->wp_rest_api_cache ) {
				$cache = new WP_REST_Cache;
				$cache->empty_cache();
		  }
		}

		/**
		* Trigger a Gatsby build by posting to a specified hook.
		*
		* @since  0.2.0
		* @param  $build_hook
		* @param  $args
		* @param  $cache
		* @return integer HTTP status code
		*/
        public static function trigger_build($build_hook, $args, $cache) {
			if ( $cache ) {
				clear_cache($cache);
			}
			$response = wp_remote_post($build_hook, $args);
			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				add_settings_error( 'wp-gatsby-notice', esc_attr( 'settings_updated' ), $error_message, 'error' );
				return 500;
			}
			add_settings_error( 'wp-gatsby-notice', esc_attr( 'settings_updated' ), $response['headers']['code'], 'updated' );
			return $response['headers']['code'];
		}

		/*
		 * Clear cache if set, then post to Gatsby Refresh endpoint.
		 */
        public static function trigger_gatsby_refresh($build_hook, $token, $cache) {
			$args = array (
				'headers' => array(
					'Authorization' => $token
				)
			);
			return trigger_build($build_hook, $args, $cache);
		}

		/*
		 * Clear cache if set, then post to Netlify build web hook.
		 */
        public static function trigger_netlify_deploy($build_hook, $cache) {
			return trigger_build($build_hook, array(), $cache);
		}

		/*
		 * See: https://wordpress.stackexchange.com/a/191008
		 */
		public static function set_gatsby_refresh() {
			function gatsby_auto_refresh() {
				$options = get_option('gatsby_options', array() );
				WP_Gatsby::trigger_gatsby_refresh(
					$options['dev_preview']['build_hook'],
					$options['dev_preview']['refresh_token'],
					$options['cache']
				);
			}
			add_action( 'save_post', 'gatsby_auto_refresh' );
    	}

		/*
		 * See: https://wordpress.stackexchange.com/a/41916
		 */
		public static function set_netlify_auto_publish() {
			function netlify_auto_publish() {
				$options = get_option('gatsby_options', array() );
				// See, maybe a better option: https://stackoverflow.com/a/17027307
				WP_Gatsby::trigger_netlify_deploy(
					$options['netlify']['build_hook'],
					$options['cache']
				);
			}
			add_action( 'save_post', 'netlify_auto_publish' );
			// Scheduled posts support
			// See: https://wordpress.stackexchange.com/a/125814
			add_action( 'publish_future_post', 'netlify_auto_publish' );
    	}
	}

	add_action( 'init', array( 'WP_Gatsby', 'init' ) );
}
