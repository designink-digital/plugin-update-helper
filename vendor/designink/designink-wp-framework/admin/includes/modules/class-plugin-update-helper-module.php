<?php
/**
 * DesignInk WordPress Framework
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to answers@designdigitalsolutions.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the plugin to newer
 * versions in the future. If you wish to customize the plugin for your
 * needs please refer to https://designinkdigital.com
 *
 * @package   Designink/WordPress/Framework
 * @author    DesignInk Digital
 * @copyright Copyright (c) 2008-2020, DesignInk, LLC
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace Designink\WordPress\Framework\v1_0_1\Admin;

defined( 'ABSPATH' ) or exit;

use Designink\WordPress\Framework\v1_0_1\Module;

if ( ! class_exists( '\Designink\WordPress\Framework\v1_0_1\Admin\Plugin_Update_Helper_Module', false ) ) {

	final class Plugin_Update_Helper_Module extends Module {

		/** @var string The path used to access plugin transient data */
		const PLUGIN_TRANSIENT_QUERY_PATH = '/wp-json/designink/api/plugin-updates/transients';

		/** @var string The path used to access plugin information */
		const PLUGINS_API_QUERY_PATH = '/wp-json/designink/api/plugin-updates/plugins-api';

		/** @var string The option name for the DesignInk settings page */
		const SSL_OPTION_NAME = '_Designink_Utility_settings_updates_ssl';

		/**
		 * Module entry point.
		 */
		public static function construct() {
			// add_action( 'admin_init', array( __CLASS__, '_admin_init' ) );
		}

		/**
		 * The WordPress 'admin_init' hook.
		 */
		public static function _admin_init() {
			self::addSSLSettings();
			add_filter('pre_set_site_transient_update_plugins', array('Designink\Plugin_Update_Helper', 'retrievePluginTransient'), 11, 1);
			add_filter('plugins_api', array('Designink\Plugin_Update_Helper', 'retrievePluginsApiInfo'), 10, 3);
			add_filter('upgrader_pre_download', array('Designink\Plugin_Update_Helper', 'hookUpgradeDownloads'), 10, 3);
			add_filter('upgrader_post_install', array('Designink\Plugin_Update_Helper', 'hookUpgradePostInstall'), 10, 3);
		}

		public static function hookUpgradeDownloads(bool $reply, string $package_url, \WP_Upgrader $instance) {
			$current = get_site_transient('update_plugins');

			if(is_array($current->response)) {
				foreach($current->response as $plugin => $options) {
					if(isset($options->token) && $options->package === $package_url) {
						$ssl = self::getSSLKey();
						$token = base64_decode($options->token);
						$decode = openssl_decrypt($token, 'aes-256-cbc', $ssl['key'], OPENSSL_RAW_DATA, $ssl['iv']);
						$url = add_query_arg('access_token', $decode, $package_url);

						return download_url($url, 300, FALSE);
					}
				}
			}

			return FALSE;
		}

		public static function hookUpgradePostInstall(bool $response, $hook_extra, $result) {
			if(isset($hook_extra['plugin'])) {
				global $wp_filesystem;
				$pluginName = $hook_extra['plugin'];
				$slug = self::parseSlugFromPluginName($pluginName);
				$pluginFolder = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $slug;

				$wp_filesystem->move($result['destination'], $pluginFolder);
				$result['destination'] = $pluginFolder;

				return $result;
			}
		}

		public static function retrievePluginTransient(\stdClass $transient) {
			$versions = array();

			if(!empty($transient->checked)) {
				$versions = $transient->checked;
			} else {
				$versions = self::getPluginVersions(apply_filters('ds_fetch_custom_plugin_update_info', array()));
			}

			$domainRequests = self::groupCustomPluginsByDomain();

			foreach($domainRequests as $domain => $pluginSlugs) {
				$pluginInfo = self::getRemotePluginTransientInfo($domain, $pluginSlugs);

				if(is_array($pluginInfo)) {
					foreach($pluginInfo as $plugin) {
						$doUpdate = version_compare($plugin->new_version, $versions[$plugin->plugin]) === 1;

						if($doUpdate) {
							$transient->response[$plugin->plugin] = $plugin;
						}
					}
				}
			}

			return $transient;
		}

		public static function retrievePluginsApiInfo($result, string $action, \stdClass $args) {
			if($action === 'plugin_information') {
				$slugs = apply_filters('ds_fetch_custom_plugin_update_info', array());
				$slug = $args->slug;

				if(key_exists($slug, $slugs)) {
					$domain = $slugs[$slug];
					$pluginInfo = self::getRemotePluginsApiInfo($domain, $slug);
					return $pluginInfo;
				}
			}

			return FALSE;
		}

		public static function getPluginVersions($pluginSlugs) {
			$versions = array();

			foreach($pluginSlugs as $slug => $url) {
				$data = get_plugin_data(dirname(plugin_dir_path(__FILE__)) . "/../$slug/$slug.php");
				$versions["$slug/$slug.php"] = $data['Version'];
			}

			return $versions;
		}

		/*
		 * Groups all plugins on similar domains to reduce number of requests
		 */
		public static function groupCustomPluginsByDomain() {
			$plugins = apply_filters('ds_fetch_custom_plugin_update_info', array());
			$domainRequests = array();

			foreach($plugins as $pluginSlug => $url) {
				$urlParts = wp_parse_url($url);

				$scheme = $urlParts['scheme'] ?: 'http';
				$host = $urlParts['host'];

				$resourceDomain = $scheme . '://' . $host;

				if(!empty($domainRequests[$resourceDomain]) && !is_array($domainRequests[$resourceDomain])) {
					$domainRequests[$resourceDomain] = array();
				}

				$domainRequests[$resourceDomain][] = $pluginSlug;
			}

			return $domainRequests;
		}

		/*
		 * Creates your URL and gets plugin info from a Digital Solutions server implementation
		 */
		public static function getRemotePluginTransientInfo(string $domain, array $plugins) {
			$url = $domain . self::$pluginTransientQueryPath . '?plugins=' . implode(',', $plugins);
			$request = wp_remote_get($url, array('timeout' => 12));
			$pluginInfo = json_decode(wp_remote_retrieve_body($request));

			if(wp_remote_retrieve_response_code($request) == 200) {
				foreach($pluginInfo as $plugin) {
					foreach(get_object_vars($plugin) as $property => $value) {
						if(gettype($value) === 'object') {
							$plugin->{$property} = Designink_Utility::convertObjectToArray($value);
						}
					}
				}
			} else {
				return FALSE;
			}

			return $pluginInfo;
		}

		public static function getRemotePluginsApiInfo(string $domain, string $plugin) {
			$url = $domain . self::$pluginsApiQueryPath . "?plugin=$plugin";
			$request = wp_remote_get($url, array('timeout' => 12));
			$pluginInfo = json_decode(wp_remote_retrieve_body($request));

			if(wp_remote_retrieve_response_code($request) == 200) {
				foreach(get_object_vars($pluginInfo) as $property => $value) {
					if(gettype($value) === 'object') {
						$pluginInfo->{$property} = Designink_Utility::convertObjectToArray($value);
					}
				}
			} else {
				return FALSE;
			}

			return $pluginInfo;
		}

		public static function addSSLSettings() {
			new Utility_Settings_Section(array(
				'section' => 'updates_ssl',
				'label' => 'Custom Plugin Updates SSL',
				'description' => 'The SSL key and initialization vector for custom plugin updates if using encryption for a private key.',
				'inputs' => array(
					array(
						'label' => "SSL Key",
						'name' => 'key'
					),
					array(
						'label' => "SSL IV",
						'name' => 'iv'
					)
				)
			));
		}

		public static function getSSLKey() {
			return get_option(self::$sslOptionName);
		}

		/**
		 * Return the slug from a properly formatted ID string, or FALSE.
		 */
		public static function parseSlugFromId(string $id) {
			if(preg_match("/^ds-update\/plugin\/([a-zA-Z0-9-]+)$/", $id, $matches)) {
				return $matches[1];
			} else {
				return FALSE;
			}
		}

		/**
		 * Return the slug from a properly formatted plugin name, or FALSE.
		 */
		public static function parseSlugFromPluginName(string $id) {
			if(preg_match("/^(?:[a-zA-Z0-9-]+)\/([a-zA-Z0-9-]+)\.php$/", $id, $matches)) {
				return $matches[1];
			} else {
				return FALSE;
			}
		}

	}

}