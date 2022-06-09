<?php

/**
 * Plugin Name: Import WP - Rank Math SEO Importer Addon
 * Plugin URI: https://www.importwp.com
 * Description: Allow Import WP to import Rank Math SEO fields.
 * Author: James Collings <james@jclabs.co.uk>
 * Version: 0.0.3 
 * Author URI: https://www.importwp.com
 * Network: True
 */

add_action('admin_init', 'iwp_rank_math_check');

function iwp_rank_math_requirements_met()
{
    return false === (is_admin() && current_user_can('activate_plugins') &&  (!function_exists('rank_math') || (!function_exists('import_wp_pro') && !function_exists('import_wp')) || version_compare(IWP_VERSION, '2.5.0', '<')));
}

function iwp_rank_math_check()
{
    if (!iwp_rank_math_requirements_met()) {

        add_action('admin_notices', 'iwp_rank_math_notice');

        deactivate_plugins(plugin_basename(__FILE__));

        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }
}

function iwp_rank_math_setup()
{
    if (!iwp_rank_math_requirements_met()) {
        return;
    }

    $base_path = dirname(__FILE__);

    require_once $base_path . '/setup.php';

    // Install updater
    if (file_exists($base_path . '/updater.php') && !class_exists('IWP_Updater')) {
        require_once $base_path . '/updater.php';
    }

    if (class_exists('IWP_Updater')) {
        $updater = new IWP_Updater(__FILE__, 'importwp-rank-math');
        $updater->initialize();
    }
}
add_action('plugins_loaded', 'iwp_rank_math_setup', 9);

function iwp_rank_math_notice()
{
    echo '<div class="error">';
    echo '<p><strong>Import WP - Rank Math SEO Importer Addon</strong> requires that you have <strong>Import WP v2.5.0 or newer</strong>, and <strong>Rank Math SEO</strong> installed.</p>';
    echo '</div>';
}
