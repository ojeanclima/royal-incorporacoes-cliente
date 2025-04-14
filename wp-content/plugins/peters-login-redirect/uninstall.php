<?php

//if uninstall not called from WordPress exit
if ( ! defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

include_once(dirname(__FILE__) . '/wplogin_redirect.php');

function loginwp_mo_uninstall_function()
{
    $remove_plugin_data = loginwp_var(get_option('loginwp_settings', []), 'remove_plugin_data');

    if ($remove_plugin_data == 'true') {

        global $wpdb;

        $drop_tables[] = "DROP TABLE IF EXISTS " . $wpdb->prefix . PTR_LOGINWP_DB_TABLE_NAME;

        foreach ($drop_tables as $tables) {
            $wpdb->query($tables);
        }

        delete_option('rul_version');
        delete_option('rul_settings');
        delete_option('loginwp_from_ab_initio');
        delete_option('loginwp_install_date');
        delete_option('loginwp_redirection_settings');
        delete_option('loginwp_settings');

        delete_option('loginwp_license_status');
        delete_option('loginwp_license_expired_status');
        delete_option('loginwp_license_key');

        delete_site_option('pand-' . md5('loginwp-review-plugin-notice'));
        delete_site_option('pand-' . md5('ptlr_is_now_loginwp_notice'));

        wp_cache_flush();
    }
}

if ( ! is_multisite()) {
    loginwp_mo_uninstall_function();
} else {

    if ( ! wp_is_large_network()) {
        $site_ids = get_sites(['fields' => 'ids', 'number' => 0]);

        foreach ($site_ids as $site_id) {
            switch_to_blog($site_id);
            loginwp_mo_uninstall_function();
            restore_current_blog();
        }
    }
}