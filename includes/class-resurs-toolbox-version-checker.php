<?php

if (!defined('ABSPATH')) {
    exit;
}

class Tornevall_Resurs_Toolbox_Version_Checker {
    const BITBUCKET_API_URL = 'https://api.bitbucket.org/2.0/repositories/resursbankplugins/resursbank-woocommerce/refs/tags';

    /**
     * Get latest version from Bitbucket
     */
    public static function get_latest_version() {

        // Request with pagelen=100 to get more tags, sorted by name descending
        $url = self::BITBUCKET_API_URL . '?pagelen=100&sort=-name';

        $all_tags = [];

        // Fetch all pages if there are more than 100 tags
        $page_url = $url;
        $page_count = 0;
        $max_pages = 10; // Safety limit

        while ($page_url && $page_count < $max_pages) {
            $response = wp_remote_get($page_url, [
                'timeout' => 10,
                'sslverify' => true,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            if (is_wp_error($response)) {
                return [
                    'success' => false,
                    'error' => 'Could not connect to Bitbucket: ' . $response->get_error_message(),
                ];
            }

            $status_code = wp_remote_retrieve_response_code($response);
            if ($status_code !== 200) {
                return [
                    'success' => false,
                    'error' => 'Bitbucket API returned status ' . $status_code,
                ];
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (!isset($data['values']) || !is_array($data['values'])) {
                break;
            }

            $all_tags = array_merge($all_tags, $data['values']);

            // Check for next page (Bitbucket uses 'next' not 'pagelen_next')
            $page_url = isset($data['next']) ? $data['next'] : null;
            $page_count++;
        }

        if (empty($all_tags)) {
            return [
                'success' => false,
                'error' => 'No releases found on Bitbucket',
            ];
        }

        // Find the latest (highest version) tag by semantic versioning
        $latest_version = null;
        $latest_tag = null;

        foreach ($all_tags as $tag) {
            if (!isset($tag['name'])) {
                continue;
            }

            $version = $tag['name'];
            // Skip non-version tags (only match x.y.z pattern)
            if (!preg_match('/^\d+\.\d+\.\d+/', $version)) {
                continue;
            }

            // Convert version string to comparable format
            $normalized = self::normalize_version($version);

            if ($latest_version === null || version_compare($normalized, $latest_version, '>')) {
                $latest_version = $normalized;
                $latest_tag = $version;
            }
        }

        if ($latest_tag === null) {
            return [
                'success' => false,
                'error' => 'No valid version tags found on Bitbucket',
            ];
        }

        return [
            'success' => true,
            'latest_version' => $latest_tag,
            'timestamp' => current_time('mysql'),
        ];
    }

    /**
     * Compare installed version with latest
     */
    public static function check_for_updates($installed_version) {
        $latest = self::get_latest_version();

        if (!$latest['success']) {
            return $latest;
        }

        $latest_version = $latest['latest_version'];
        $installed_normalized = self::normalize_version($installed_version);
        $latest_normalized = self::normalize_version($latest_version);

        $comparison = version_compare($installed_normalized, $latest_normalized);

        $status_message = '';
        if ($comparison > 0) {
            // Installed version is higher than latest tagged version
            $status_message = 'You are running a development or pre-release version that is newer than the latest stable release on Bitbucket.';
        } elseif ($comparison === 0) {
            // Same version
            $status_message = 'You are running the latest version.';
        } else {
            // Installed version is lower (outdated)
            $status_message = 'An update is available.';
        }

        return [
            'success' => true,
            'installed' => $installed_version,
            'latest' => $latest_version,
            'has_update' => $comparison < 0,
            'is_outdated' => $comparison < 0,
            'is_dev_version' => $comparison > 0,
            'comparison' => $comparison, // -1 = outdated, 0 = same, 1 = dev/newer
            'status_message' => $status_message,
        ];
    }

    /**
     * Normalize version string for comparison
     * Removes 'v' prefix, handles semantic versioning
     */
    private static function normalize_version($version) {
        // Remove 'v' prefix if present
        $version = ltrim($version, 'v');

        // Handle versions like "1.0.0" or "1.0.0-beta"
        // Extract only the numeric part for comparison
        if (preg_match('/^(\d+\.\d+\.\d+)/', $version, $matches)) {
            return $matches[1];
        }

        return $version;
    }

    /**
     * Clear version cache
     */
    public static function clear_cache() {
        // No cache to clear - always fetching live data from Bitbucket
    }
}


