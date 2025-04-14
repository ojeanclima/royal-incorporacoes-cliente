<?php

namespace LoginWP\Core\Admin;

class SettingsPage extends AbstractSettingsPage
{
    protected $settings_page_instance;

    public function __construct()
    {
        parent::__construct();

        add_action('loginwp_admin_settings_page_general', [$this, 'settings_page_callback']);

        $this->settings_page_instance = SettingsPageApi::instance();
        $this->settings_page_instance->option_name('loginwp_settings');
        add_action('admin_init', [$this->settings_page_instance, 'persist_plugin_settings']);
    }

    public function register_menu_page()
    {
        $menus = $this->header_menu_tabs();

        $active_menu = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';

        $page_title = __('Settings - LoginWP', 'peters-login-redirect');

        if (isset($menus[$active_menu])) {
            $page_title = $menus[$active_menu] . ' ' . __('Settings', 'peters-login-redirect');
        }

        add_submenu_page(
            PTR_LOGINWP_SETTINGS_PAGE_SLUG,
            $page_title,
            __('Settings', 'peters-login-redirect'),
            'manage_options',
            PTR_LOGINWP_SETTINGS_PAGE_SLUG,
            [$this, 'admin_page_callback'],
            1
        );
    }

    public function default_header_menu()
    {
        return 'general';
    }

    public function header_menu_tabs()
    {
        $tabs['general'] = esc_html__('General', 'peters-login-redirect');

        return apply_filters('loginwp_settings_header_menu_tabs', $tabs);
    }

    public function settings_page_callback()
    {
        $settings = [
            [
                'remove_plugin_data' => [
                    'label'          => esc_html__('Remove Data on Uninstall', 'peters-login-redirect'),
                    'checkbox_label' => esc_html__('Activate', 'peters-login-redirect'),
                    'description'    => esc_html__('Check this box if you would like LoginWP to completely remove all of its data when uninstalled.', 'peters-login-redirect'),
                    'type'           => 'checkbox',
                ]
            ]
        ];

        $this->settings_page_instance->page_header(esc_html__('General', 'peters-login-redirect'));
        $this->settings_page_instance->sidebar(AbstractSettingsPage::sidebar_args());
        $this->settings_page_instance->remove_white_design();
        $this->settings_page_instance->header_without_frills();
        $this->settings_page_instance->main_content($settings);
        $this->settings_page_instance->build();
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
