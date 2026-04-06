<?php
/**
 * Update Service
 *
 * Handles automatic plugin updates from GitHub releases
 * using yahnis-elsts/plugin-update-checker.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CWOT_Update_Service {

    /**
     * Default GitHub repository URL.
     */
    const DEFAULT_REPOSITORY_URL = 'https://github.com/michalstaniecko/ihumbak-woo-order-tracking/';

    /**
     * Plugin slug.
     */
    const PLUGIN_SLUG = 'carramba-woo-order-tracking';

    /**
     * Update checker instance.
     *
     * @var object|null
     */
    private $update_checker = null;

    /**
     * Check if updates are enabled.
     *
     * @return bool
     */
    public function is_enabled() {
        if (defined('CWOT_DISABLE_UPDATES') && CWOT_DISABLE_UPDATES) {
            return false;
        }

        /**
         * Filter whether automatic updates are enabled.
         *
         * @param bool $enabled Whether updates are enabled. Default true.
         */
        return (bool) apply_filters('cwot_updates_enabled', true);
    }

    /**
     * Initialize the update checker.
     *
     * @return void
     */
    public function init() {
        if (!class_exists('YahnisElsts\PluginUpdateChecker\v5\PucFactory')) {
            return;
        }

        $repository_url = $this->get_repository_url();
        $plugin_file    = $this->get_plugin_file();

        $this->update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
            $repository_url,
            $plugin_file,
            self::PLUGIN_SLUG
        );

        // Enable release assets for ZIP downloads from GitHub releases.
        $api = $this->update_checker->getVcsApi();
        if (method_exists($api, 'enableReleaseAssets')) {
            $api->enableReleaseAssets();
        }

        // Configure authentication if token is available.
        $token = $this->get_github_access_token();
        if (!empty($token)) {
            $this->update_checker->setAuthentication($token);
        }

        // Add filter for update info modification.
        $this->update_checker->addFilter(
            'request_info_result',
            array($this, 'filter_update_info')
        );
    }

    /**
     * Get the repository URL.
     *
     * @return string
     */
    public function get_repository_url() {
        /**
         * Filter the GitHub repository URL.
         *
         * @param string $url Repository URL.
         */
        return apply_filters('cwot_update_repository_url', self::DEFAULT_REPOSITORY_URL);
    }

    /**
     * Get the GitHub access token.
     *
     * @return string
     */
    public function get_github_access_token() {
        if (defined('CWOT_GITHUB_ACCESS_TOKEN') && is_string(CWOT_GITHUB_ACCESS_TOKEN)) {
            return CWOT_GITHUB_ACCESS_TOKEN;
        }

        /**
         * Filter the GitHub access token for private repos or higher rate limits.
         *
         * @param string $token GitHub access token. Default empty.
         */
        return apply_filters('cwot_github_access_token', '');
    }

    /**
     * Get the plugin main file path.
     *
     * @return string
     */
    public function get_plugin_file() {
        if (defined('CWOT_FILE')) {
            return CWOT_FILE;
        }

        return dirname(__DIR__) . '/carramba-woo-order-tracking.php';
    }

    /**
     * Filter update info before it's used.
     *
     * @param object|null $info Update info object.
     * @return object|null Modified update info.
     */
    public function filter_update_info($info) {
        if (null === $info) {
            return $info;
        }

        /**
         * Filter the update info object.
         *
         * @param object $info Update info object containing version, download URL, etc.
         */
        return apply_filters('cwot_update_info', $info);
    }

    /**
     * Get the update checker instance.
     *
     * @return object|null
     */
    public function get_update_checker() {
        return $this->update_checker;
    }
}
