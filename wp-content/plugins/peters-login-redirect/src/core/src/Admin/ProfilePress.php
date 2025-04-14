<?php

namespace LoginWP\Core\Admin;

use LoginWP\Core\Admin\Installer\PluginSilentUpgrader;
use LoginWP\Core\Admin\Installer\LoginWP_Install_Skin;

class ProfilePress
{
    const SLUG = 'loginwp-ppress';

    private $config = array(
        'lite_plugin'       => 'wp-user-avatar/wp-user-avatar.php',
        'lite_download_url' => 'https://downloads.wordpress.org/plugin/wp-user-avatar.latest-stable.zip',
        'ppress_settings'   => 'admin.php?page=ppress-forms',
    );

    private $output_data = array();

    public function __construct()
    {
        /* add_action('loginwp_admin_hooks', function () {
            $this->register_settings_page();
        }); */

        add_action('wp_ajax_loginwp_activate_plugin', [$this, 'loginwp_activate_plugin']);
        add_action('wp_ajax_loginwp_install_plugin', [$this, 'loginwp_install_plugin']);

        if (wp_doing_ajax()) {
            add_action('wp_ajax_loginwp_profilepress_page_check_plugin_status', array($this, 'ajax_check_plugin_status'));
        }

        // Check what page we are on.
        $page = isset($_GET['page']) ? \sanitize_key(\wp_unslash($_GET['page'])) : '';

        if (self::SLUG !== $page) return;

        add_action('admin_init', array($this, 'redirect_to_ppress_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function loginwp_install_plugin()
    {
        // Run a security check.
        check_ajax_referer('loginwp-admin-nonce', 'nonce');

        $generic_error = esc_html__('There was an error while performing your request.', 'peters-login-redirect');
        $type          = ! empty($_POST['type']) ? sanitize_key($_POST['type']) : 'plugin';

        if ( ! current_user_can('install_plugins')) {
            wp_send_json_error($generic_error);
        }

        // Determine whether file modifications are allowed.
        if ( ! wp_is_file_mod_allowed('loginwp_can_install')) {
            wp_send_json_error($generic_error);
        }

        $error = $type === 'plugin' ? esc_html__('Could not install plugin. Please download and install manually.', 'peters-login-redirect') : esc_html__('Could not install addon. Please download from wpforms.com and install manually.', 'peters-login-redirect');

        if (empty($_POST['plugin'])) {
            wp_send_json_error($error);
        }

        // Set the current screen to avoid undefined notices.
        set_current_screen('profilepress_page_loginwp-ppress');

        // Prepare variables.
        $url = esc_url_raw(
            add_query_arg(
                ['page' => 'loginwp-redirections'],
                admin_url('admin.php')
            )
        );

        ob_start();
        $creds = request_filesystem_credentials($url, '', false, false, null);

        // Hide the filesystem credentials form.
        ob_end_clean();

        // Check for file system permissions.
        if ($creds === false) {
            wp_send_json_error($error);
        }

        if ( ! WP_Filesystem($creds)) {
            wp_send_json_error($error);
        }

        /*
         * We do not need any extra credentials if we have gotten this far, so let's install the plugin.
         */

        // Do not allow WordPress to search/download translations, as this will break JS output.
        remove_action('upgrader_process_complete', ['Language_Pack_Upgrader', 'async_upgrade'], 20);

        // Create the plugin upgrader with our custom skin.
        $installer = new PluginSilentUpgrader(new LoginWP_Install_Skin());

        // Error check.
        if ( ! method_exists($installer, 'install') || empty($_POST['plugin'])) {
            wp_send_json_error($error);
        }

        $installer->install($_POST['plugin']); // phpcs:ignore

        // Flush the cache and return the newly installed plugin basename.
        wp_cache_flush();

        $plugin_basename = $installer->plugin_info();

        if (empty($plugin_basename)) {
            wp_send_json_error($error);
        }

        $result = [
            'msg'          => $generic_error,
            'is_activated' => false,
            'basename'     => $plugin_basename,
        ];

        // Check for permissions.
        if ( ! current_user_can('activate_plugins')) {
            $result['msg'] = $type === 'plugin' ? esc_html__('Plugin installed.', 'peters-login-redirect') : esc_html__('Addon installed.', 'peters-login-redirect');

            wp_send_json_success($result);
        }

        // Activate the plugin silently.
        $activated = activate_plugin($plugin_basename);

        if ( ! is_wp_error($activated)) {
            $result['is_activated'] = true;
            $result['msg']          = $type === 'plugin' ? esc_html__('Plugin installed & activated.', 'peters-login-redirect') : esc_html__('Addon installed & activated.', 'peters-login-redirect');

            wp_send_json_success($result);
        }

        // Fallback error just in case.
        wp_send_json_error($result);
    }

    public function loginwp_activate_plugin()
    {
        // Run a security check.
        check_ajax_referer('loginwp-admin-nonce', 'nonce');

        // Check for permissions.
        if ( ! current_user_can('activate_plugins')) {
            wp_send_json_error(esc_html__('Plugin activation is disabled for you on this site.', 'peters-login-redirect'));
        }

        if (isset($_POST['plugin'])) {

            $plugin   = sanitize_text_field(wp_unslash($_POST['plugin']));
            $activate = activate_plugins($plugin);

            if ( ! is_wp_error($activate)) {
                wp_send_json_success(esc_html__('Plugin activated.', 'peters-login-redirect'));
            }
        }

        wp_send_json_error(esc_html__('Could not activate plugin. Please activate from the Plugins page.', 'peters-login-redirect'));
    }

    /* public function register_settings_page()
    {
        add_submenu_page(
            PTR_LOGINWP_SETTINGS_PAGE_SLUG,
            'Login Forms',
            esc_html__('Login Forms', 'peters-login-redirect'),
            'manage_options',
            self::SLUG,
            array($this, 'output')
        );
    } */

    public function enqueue_assets()
    {
        wp_enqueue_script(
            'loginwp-admin-page-ppress',
            PTR_LOGINWP_ASSETS_URL . "js/profilepress.js",
            array('jquery'),
            PTR_LOGINWP_VERSION_NUMBER,
            true
        );

        \wp_localize_script(
            'loginwp-admin-page-ppress',
            'loginwp_pluginlanding',
            $this->get_js_strings()
        );

        \wp_localize_script('loginwp-admin-page-ppress', 'loginwp_installer_globals', [
            'nonce' => wp_create_nonce('loginwp-admin-nonce')
        ]);
    }

    /**
     * JS Strings.
     */
    protected function get_js_strings()
    {
        $error_could_not_install = sprintf(
            wp_kses( /* translators: %s - Lite plugin download URL. */
                __('Could not install plugin. Please <a href="%s">download</a> and install manually.', 'peters-login-redirect'),
                array(
                    'a' => array(
                        'href' => true,
                    ),
                )
            ),
            esc_url_raw($this->config['lite_download_url'])
        );

        $error_could_not_activate = sprintf(
            wp_kses( /* translators: %s - Lite plugin download URL. */
                __('Could not activate plugin. Please activate from the <a href="%s">Plugins page</a>.', 'peters-login-redirect'),
                array(
                    'a' => array(
                        'href' => true,
                    ),
                )
            ),
            esc_url_raw(admin_url('plugins.php'))
        );

        return array(
            'installing'               => esc_html__('Installing...', 'peters-login-redirect'),
            'activating'               => esc_html__('Activating...', 'peters-login-redirect'),
            'activated'                => esc_html__('ProfilePress Installed & Activated', 'peters-login-redirect'),
            'install_now'              => esc_html__('Install Now', 'peters-login-redirect'),
            'activate_now'             => esc_html__('Activate Now', 'peters-login-redirect'),
            'download_now'             => esc_html__('Download Now', 'peters-login-redirect'),
            'plugins_page'             => esc_html__('Go to Plugins page', 'peters-login-redirect'),
            'error_could_not_install'  => $error_could_not_install,
            'error_could_not_activate' => $error_could_not_activate,
            'manual_install_url'       => $this->config['lite_download_url'],
            'manual_activate_url'      => admin_url('plugins.php'),
            'ppress_settings_button'   => esc_html__('Go to ProfilePress Settings', 'peters-login-redirect'),
        );
    }

    /**
     * Generate and output page HTML.
     */
    public function output()
    {
        ?>
        <style>
            #loginwp-admin-ppress {
                width: 700px;
                margin: 0 auto;
            }

            #loginwp-admin-ppress .notice,
            #loginwp-admin-ppress .error {
                display: none
            }

            #loginwp-admin-ppress *, #loginwp-admin-ppress *::before, #loginwp-admin-ppress *::after {
                -webkit-box-sizing: border-box;
                -moz-box-sizing: border-box;
                box-sizing: border-box;
            }

            #loginwp-admin-ppress section {
                margin: 50px 0;
                text-align: left;
                clear: both;
            }

            #loginwp-admin-ppress section.screenshot {
                text-align: center;
            }

            #loginwp-admin-ppress .top {
                text-align: center;
            }

            #loginwp-admin-ppress .top img {
                margin-bottom: 38px;
            }

            #loginwp-admin-ppress .top h1 {
                font-size: 26px;
                font-weight: 600;
                margin-bottom: 0;
                padding: 0;
            }

            #loginwp-admin-ppress .top p {
                font-size: 17px;
                color: #777777;
                margin-top: .5em;
            }

            #loginwp-admin-ppress p {
                font-size: 15px;
            }

            #loginwp-admin-ppress .cont {
                display: inline-block;
                position: relative;
                width: 80%;
                padding: 5px;
                background-color: #ffffff;
                -webkit-box-shadow: 0px 2px 5px 0px rgb(0 0 0 / 5%);
                -moz-box-shadow: 0px 2px 5px 0px rgba(0, 0, 0, 0.05);
                box-shadow: 0px 2px 5px 0px rgb(0 0 0 / 5%);
                border-radius: 3px;
                box-sizing: border-box;
            }

            #loginwp-admin-ppress .screenshot > * {
                vertical-align: middle;
            }

            #loginwp-admin-ppress .screenshot .cont img {
                max-width: 100%;
                display: block;
            }

            #loginwp-admin-ppress .screenshot ul {
                display: inline-block;
                margin: 0 0 0 30px;
                list-style-type: none;
                max-width: 100%;
            }

            #loginwp-admin-ppress .screenshot li {
                margin: 16px 0;
                padding: 0 0 0 24px;
                font-size: 15px;
                color: #777777;
            }

            #loginwp-admin-ppress .step {
                background-color: #F9F9F9;
                -webkit-box-shadow: 0px 2px 5px 0px rgb(0 0 0 / 5%);
                -moz-box-shadow: 0px 2px 5px 0px rgba(0, 0, 0, 0.05);
                box-shadow: 0px 2px 5px 0px rgb(0 0 0 / 5%);
                border: 1px solid #E5E5E5;
                margin: 0 0 25px 0;
            }

            #loginwp-admin-ppress .step .num {
                display: inline-block;
                position: relative;
                width: 100px;
                height: 50px;
                text-align: center;
            }

            .loginwp-admin-plugin-landing .loader {
                margin: 0 auto;
                position: relative;
                text-indent: -9999em;
                border-top: 4px solid #969696;
                border-right: 4px solid #969696;
                border-bottom: 4px solid #969696;
                border-left: 4px solid #404040;
                -webkit-transform: translateZ(0);
                -ms-transform: translateZ(0);
                transform: translateZ(0);
                -webkit-animation: load8 1.1s infinite linear;
                animation: load8 1.1s infinite linear;
                background-color: transparent;
            }

            .loginwp-admin-plugin-landing .loader, .loginwp-admin-plugin-landing .loader:after {
                display: block;
                border-radius: 50%;
                width: 50px;
                height: 50px
            }

            @-webkit-keyframes load8 {
                0% {
                    -webkit-transform: rotate(0deg);
                    transform: rotate(0deg)
                }

                100% {
                    -webkit-transform: rotate(360deg);
                    transform: rotate(360deg)
                }
            }

            @keyframes load8 {
                0% {
                    -webkit-transform: rotate(0deg);
                    transform: rotate(0deg)
                }

                100% {
                    -webkit-transform: rotate(360deg);
                    transform: rotate(360deg)
                }
            }

            #loginwp-admin-ppress .step .loader {
                margin-top: -54px;
                transition: all .3s;
                opacity: 1;
            }

            #loginwp-admin-ppress .step .hidden {
                opacity: 0;
                transition: all .3s;
            }

            #loginwp-admin-ppress .step div {
                display: inline-block;
                width: calc(100% - 104px);
                background-color: #ffffff;
                padding: 30px;
                border-left: 1px solid #eeeeee;
            }

            #loginwp-admin-ppress .step h2 {
                font-size: 24px;
                line-height: 22px;
                margin-top: 0;
                margin-bottom: 15px;
            }

            #loginwp-admin-ppress .step p {
                font-size: 16px;
                color: #777777;
            }


            #loginwp-admin-ppress .step .button {
                font-weight: 500;
                box-shadow: none;
                padding: 12px;
                min-width: 200px;
                height: auto;
                line-height: 13px;
                text-align: center;
                font-size: 15px;
                transition: all .3s;
            }

            #loginwp-admin-ppress .grey {
                opacity: 0.5;
            }
        </style>
        <?php
        echo '<div id="loginwp-admin-ppress" class="wrap loginwp-admin-wrap loginwp-admin-plugin-landing">';

        $this->output_section_heading();
        $this->output_section_screenshot();
        $this->output_section_step_install();
        $this->output_section_step_setup();

        echo '</div>';
    }

    /**
     * Generate and output heading section HTML.
     */
    protected function output_section_heading()
    {
        // Heading section.
        printf(
            '<section class="top">
				<img class="img-top" src="%1$s" alt="%2$s"/>
				<h1>%3$s</h1>
				<p>%4$s</p>
			</section>',
            esc_url(PTR_LOGINWP_ASSETS_URL . 'images/loginwpXprofilepress.png'),
            esc_attr__('ProfilePress ♥ ProfilePress', 'peters-login-redirect'),
            esc_html__('Modern Custom Login Forms & Membership Plugin', 'peters-login-redirect'),
            esc_html__('ProfilePress lets you create beautiful frontend custom login forms, registration forms, member directories and user profiles. You can also protect sensitive contents and control user access.', 'peters-login-redirect')
        );
    }

    /**
     * Generate and output screenshot section HTML.
     */
    protected function output_section_screenshot()
    {
        printf(
            '<section class="screenshot">
				<div class="cont">
					<img src="%1$s" alt="%2$s"/>
				</div>
				<ul style="text-align: left">
					<li>%3$s</li>
					<li>%4$s</li>
					<li>%5$s</li>
					<li>%6$s</li>
					<li>%7$s</li>
				</ul>			
			</section>',
            PTR_LOGINWP_ASSETS_URL . 'images/ppress-login-form.png',
            esc_attr__('ProfilePress screenshot', 'peters-login-redirect'),
            esc_html__('Beautiful templates for login, registration, password reset & edit profile edit forms.', 'peters-login-redirect'),
            esc_html__('Fine-grained control over what content your users can see based on your protection rules.', 'peters-login-redirect'),
            esc_html__('Restrict access to pages, posts, custom post types, categories, tags and custom taxonomies.', 'peters-login-redirect'),
            esc_html__('Add beautiful user profiles to your site that can be customised to your specific requirements.', 'peters-login-redirect'),
            esc_html__('Create searchable and filterable member directories with avatars and user info allowing users to find each other.', 'peters-login-redirect')
        );
    }

    /**
     * Generate and output step 'Install' section HTML.
     */
    protected function output_section_step_install()
    {
        $step = $this->get_data_step_install();

        if (empty($step)) {
            return;
        }

        printf(
            '<section class="step step-install">
				<aside class="num">
					<img src="%1$s" alt="%2$s" />
					<i class="loader hidden"></i>
				</aside>
				<div>
					<h2>%3$s</h2>
					<p>%4$s</p>
					<button class="button %5$s" data-plugin="%6$s" data-action="%7$s">%8$s</button>
				</div>		
			</section>',
            esc_url(PTR_LOGINWP_ASSETS_URL . 'images/' . $step['icon']),
            esc_attr__('Step 1', 'peters-login-redirect'),
            esc_html__('Install and Activate ProfilePress', 'peters-login-redirect'),
            esc_html__('Install ProfilePress from the WordPress.org plugin repository.', 'peters-login-redirect'),
            esc_attr($step['button_class']),
            esc_attr($step['plugin']),
            esc_attr($step['button_action']),
            esc_html($step['button_text'])
        );
    }

    /**
     * Generate and output step 'Setup' section HTML.
     */
    protected function output_section_step_setup()
    {
        $step = $this->get_data_step_setup();

        if (empty($step)) {
            return;
        }

        printf(
            '<section class="step step-setup %1$s">
				<aside class="num">
					<img src="%2$s" alt="%3$s" />
					<i class="loader hidden"></i>
				</aside>
				<div>
					<h2>%4$s</h2>
					<p>%5$s</p>
					<button class="button %6$s" data-url="%7$s">%8$s</button>
				</div>		
			</section>',
            esc_attr($step['section_class']),
            esc_url(PTR_LOGINWP_ASSETS_URL . 'images/' . $step['icon']),
            esc_attr__('Step 2', 'peters-login-redirect'),
            esc_html__('Set Up ProfilePress', 'peters-login-redirect'),
            esc_html__('Configure and create your first login form.', 'peters-login-redirect'),
            esc_attr($step['button_class']),
            esc_url(admin_url($this->config['ppress_settings'])),
            esc_html($step['button_text'])
        );
    }

    /**
     * Step 'Install' data.
     */
    protected function get_data_step_install()
    {
        $step = array();

        $this->output_data['all_plugins']      = get_plugins();
        $this->output_data['plugin_installed'] = array_key_exists($this->config['lite_plugin'], $this->output_data['all_plugins']);
        $this->output_data['plugin_activated'] = false;
        $this->output_data['plugin_setup']     = false;

        if ( ! $this->output_data['plugin_installed']) {
            $step['icon']          = 'step-1.svg';
            $step['button_text']   = esc_html__('Install ProfilePress', 'peters-login-redirect');
            $step['button_class']  = '';
            $step['button_action'] = 'install';
            $step['plugin']        = $this->config['lite_download_url'];
        } else {
            $this->output_data['plugin_activated'] = $this->is_activated();
            $this->output_data['plugin_setup']     = $this->is_configured();
            $step['icon']                          = $this->output_data['plugin_activated'] ? 'step-complete.svg' : 'step-1.svg';
            $step['button_text']                   = $this->output_data['plugin_activated'] ? esc_html__('ProfilePress Installed & Activated', 'peters-login-redirect') : esc_html__('Activate ProfilePress', 'peters-login-redirect');
            $step['button_class']                  = $this->output_data['plugin_activated'] ? 'grey disabled' : '';
            $step['button_action']                 = $this->output_data['plugin_activated'] ? '' : 'activate';
            $step['plugin']                        = $this->config['lite_plugin'];
        }

        return $step;
    }

    /**
     * Step 'Setup' data.
     */
    protected function get_data_step_setup()
    {
        $step = array();

        $step['icon']          = 'step-2.svg';
        $step['section_class'] = $this->output_data['plugin_activated'] ? '' : 'grey';
        $step['button_text']   = esc_html__('Start Setup', 'peters-login-redirect');
        $step['button_class']  = 'grey disabled';

        if ($this->output_data['plugin_setup']) {
            $step['icon']          = 'step-complete.svg';
            $step['section_class'] = '';
            $step['button_text']   = esc_html__('Go to ProfilePress settings', 'peters-login-redirect');
        } else {
            $step['button_class'] = $this->output_data['plugin_activated'] ? '' : 'grey disabled';
        }

        return $step;
    }

    /**
     * Ajax endpoint. Check plugin setup status.
     * Used to properly init step 'Setup' section after completing step 'Install'.
     */
    public function ajax_check_plugin_status()
    {
        // Security checks.
        if (
            ! check_ajax_referer('loginwp-admin-nonce', 'nonce', false) ||
            ! current_user_can('activate_plugins')
        ) {
            wp_send_json_error(
                array(
                    'error' => esc_html__('You do not have permission.', 'peters-login-redirect'),
                )
            );
        }

        $result = array();

        if ( ! $this->is_activated()) {
            wp_send_json_error(
                array(
                    'error' => esc_html__('Plugin unavailable.', 'peters-login-redirect'),
                )
            );
        }

        $result['setup_status'] = (int)$this->is_configured();

        wp_send_json_success($result);
    }

    /**
     * Whether ProfilePress plugin configured or not.
     */
    protected function is_configured()
    {
        return $this->is_activated();
    }

    /**
     * Whether ProfilePress plugin active or not.
     */
    protected function is_activated()
    {
        return class_exists('\ProfilePress\Core\Base');
    }

    public function redirect_to_ppress_settings()
    {
        if ($this->is_configured()) {
            wp_safe_redirect(admin_url($this->config['ppress_settings']));
            exit;
        }
    }

    /**
     * @return self
     */
    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}