<?php

namespace LoginWP\Core;

global $wpdb;

use LoginWP\Core\Redirections\Redirections;

define('PTR_LOGINWP_DB_TABLE_NAME', 'login_redirects');
define('PTR_LOGINWP_DB_TABLE', $wpdb->prefix . PTR_LOGINWP_DB_TABLE_NAME);
define('PTR_LOGINWP_SETTINGS_PAGE_SLUG', 'loginwp-settings');
define('PTR_LOGINWP_REDIRECTION_PAGE_SLUG', 'loginwp-redirections');
define('PTR_LOGINWP_REDIRECTIONS_PAGE_URL', admin_url('admin.php?page=' . PTR_LOGINWP_REDIRECTION_PAGE_SLUG));

define('PTR_LOGINWP_URL', plugin_dir_url(PTR_LOGINWP_SYSTEM_FILE_PATH));
define('PTR_LOGINWP_ASSETS_DIR', wp_normalize_path(dirname(PTR_LOGINWP_SYSTEM_FILE_PATH) . '/assets/'));

define('PTR_LOGINWP_ASSETS_URL', PTR_LOGINWP_URL . 'src/core/assets/');

class Core
{
    public function __construct()
    {
        register_activation_hook(PTR_LOGINWP_SYSTEM_FILE_PATH, [__CLASS__, 'rul_activate_plugin']);
        add_filter('wpmu_drop_tables', [$this, 'rul_drop_tables']);
        add_action('activate_blog', [$this, 'rul_site_added']);

        // Wpmu_new_blog has been deprecated in 5.1 and replaced by wp_insert_site.
        global $wp_version;
        if (version_compare($wp_version, '5.1', '<')) {
            add_action('wpmu_new_blog', [$this, 'rul_site_added']);
        } else {
            add_action('wp_initialize_site', [$this, 'rul_site_added'], 99);
        }

        add_action('admin_init', [$this, 'rul_upgrade']);

        Redirections::get_instance();
        Admin\Admin::get_instance();
    }

    public static function rul_install()
    {
        global $wpdb;

        // important we don't use PTR_LOGINWP_DB_TABLE, rather call $wpdb->prefix . 'table name'
        $rul_db_addresses = $wpdb->prefix . PTR_LOGINWP_DB_TABLE_NAME;

        // Add the table to hold group information and moderator rules
        if ($rul_db_addresses != $wpdb->get_var("SHOW TABLES LIKE '$rul_db_addresses'")) {
            $sql = "CREATE TABLE $rul_db_addresses (
            `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
            `rul_type` varchar(100) NOT NULL,
            `rul_value` varchar(191) NULL default NULL,
            `rul_url` LONGTEXT NULL default NULL,
            `rul_url_logout` LONGTEXT NULL default NULL,
            `rul_order` int(2) NOT NULL default '0',
            `meta_data` longtext NULL,
            PRIMARY KEY (id)
            )";

            $wpdb->query($sql);

            // Insert the "all" redirect entry
            $wpdb->insert($rul_db_addresses,
                array('rul_type' => 'all')
            );

            // Insert the "on-register" redirect entry
            $wpdb->insert($rul_db_addresses,
                array('rul_type' => 'register')
            );

            // Set the version number in the database
            add_option('rul_version', PTR_LOGINWP_VERSION_NUMBER, '', 'no');
        }

        add_option('loginwp_from_ab_initio', 'true');
        add_option('loginwp_install_date', current_time('mysql'));

        self::rul_upgrade();
    }

    public static function rul_activate_plugin($networkwide)
    {
        // Executes when plugin is activated
        global $wpdb;

        if (function_exists('is_multisite') && is_multisite() && $networkwide) {
            $blogs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
            foreach ($blogs as $blog) {
                switch_to_blog($blog);
                self::rul_install();
                restore_current_blog();
            }
        } else {
            self::rul_install();
        }
    }

    public function rul_site_added($blog)
    {
        if ( ! is_int($blog)) {
            $blog = $blog->id;
        }

        switch_to_blog($blog);
        self::rul_install();
        restore_current_blog();
    }

    public function rul_drop_tables($tables)
    {
        global $wpdb;

        $tables[] = $wpdb->prefix . PTR_LOGINWP_DB_TABLE_NAME;

        return $tables;
    }

    // Perform upgrade functions
    // Some newer operations are duplicated from rul_install() as there's no guarantee that the user will follow a specific upgrade procedure
    public static function rul_upgrade()
    {
        if (!current_user_can('manage_options')) return;

        global $wpdb;

        // important we don't use PTR_LOGINWP_DB_TABLE, rather call $wpdb->prefix . 'table name'
        $rul_db_addresses = $wpdb->prefix . PTR_LOGINWP_DB_TABLE_NAME;

        // necessary cos pro starts with version 4.
        $cmp_current_version = preg_replace('/^4\.(.+)/', '3.$1', get_option('rul_version'));
        // Turn version into an integer for comparisons
        $cmp_current_version = intval(str_replace('.', '', $cmp_current_version));

        if ($cmp_current_version < 220) {
            $wpdb->query("ALTER TABLE `$rul_db_addresses` ADD `rul_url_logout` LONGTEXT NOT NULL default '' AFTER `rul_url`");
        }

        if ($cmp_current_version < 250) {

            $wpdb->query("ALTER TABLE `$rul_db_addresses` CHANGE `rul_type` `rul_type` ENUM( 'user', 'role', 'level', 'all', 'register' ) NOT NULL");
            $wpdb->insert($rul_db_addresses,
                array('rul_type' => 'register')
            );
        }

        if ($cmp_current_version < 253) {
            // Allow NULL values for non-essential fields
            $wpdb->query("ALTER TABLE `$rul_db_addresses` CHANGE `rul_value` `rul_value` varchar(255) NULL default NULL");
            $wpdb->query("ALTER TABLE `$rul_db_addresses` CHANGE `rul_url` `rul_url` LONGTEXT NULL default NULL");
            $wpdb->query("ALTER TABLE `$rul_db_addresses` CHANGE `rul_url_logout` `rul_url_logout` LONGTEXT NULL default NULL");
        }

        if ($cmp_current_version < 291) {
            // Reduce size of rul_value field to support utf8mb4 character encoding
            $wpdb->query("ALTER TABLE `$rul_db_addresses` CHANGE `rul_value` `rul_value` varchar(191) NULL default NULL");
        }

        if ($cmp_current_version < 3000) {
            $wpdb->query("ALTER TABLE $rul_db_addresses ADD id BIGINT NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST");
        }

        if ($cmp_current_version < 3003) {
            $wpdb->query("ALTER TABLE $rul_db_addresses DROP INDEX rul_type");
        }

        if ($cmp_current_version < 3006) {
            $wpdb->query("ALTER TABLE $rul_db_addresses CHANGE rul_type rul_type varchar(100) NOT NULL");
        }

        if ($cmp_current_version < 3040) {
            $wpdb->query("ALTER TABLE $rul_db_addresses ADD COLUMN meta_data longtext NULL");
        }

        update_option('rul_version', PTR_LOGINWP_VERSION_NUMBER, 'no');

        add_option('loginwp_install_date', current_time('mysql'));
    }

    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}