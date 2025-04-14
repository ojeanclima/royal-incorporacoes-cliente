<?php

if (!class_exists('\WO_Admin_Notice')) {

    class WO_Admin_Notice
    {
        public function __construct()
        {
            add_action('admin_notices', array($this, 'admin_notice'));
            add_action('network_admin_notices', array($this, 'admin_notice'));

            add_action('admin_init', array($this, 'dismiss_admin_notice'));
        }

        public function dismiss_admin_notice()
        {
            if (!isset($_GET['wo-adaction']) || $_GET['wo-adaction'] != 'wo_dismiss_adnotice') {
                return;
            }

            $url = admin_url();
            update_option('wo_dismiss_adnotice', 'true');

            wp_redirect($url);
            exit;
        }

        public function admin_notice()
        {

            global $pagenow;

            //Remove this condition to display the admin notice for widget option plugin
            if (1 == 1) {
                return;
            }

            if ($pagenow != 'index.php') return;

            if (get_option('wo_dismiss_adnotice', 'false') == 'true') {
                return;
            }

            if ($this->is_plugin_installed() && $this->is_plugin_active()) {
                return;
            }

            $dismiss_url = esc_url_raw(
                add_query_arg(
                    array(
                        'wo-adaction' => 'wo_dismiss_adnotice'
                    ),
                    admin_url()
                )
            );
            $this->notice_css();
            $install_url = wp_nonce_url(
                admin_url('update.php?action=install-plugin&plugin=widget-options'),
                'install-plugin_widget-options'
            );

            $activate_url = wp_nonce_url(admin_url('plugins.php?action=activate&plugin=widget-options%2Fplugin.php'), 'activate-plugin_widget-options/plugin.php');
?>
            <div class="wo-admin-notice notice notice-success">
                <div class="wo-notice-first-half">
                    <p>Take Full Control over your WordPress Widgets</p>
                    <p>
                        <?php
                        printf(
                            __("Add Widget options for better widget control. It is the best WordPress widgets settings plugin available. Widget Options lets you add more options to widget settings so you can take full control of your website's widgets.", 'peters-login-redirect'),
                            '<span class="mo-stylize"><strong>',
                            '</strong></span>'
                        );
                        ?>
                    </p>
                    <p style="text-decoration: underline;font-size: 12px;">Recommended by LoginWP</p>
                </div>
                <div class="wo-notice-other-half">
                    <?php if (!$this->is_plugin_installed()) : ?>
                        <a class="button button-primary button-hero" id="wo-install-widget-options-plugin" href="<?php echo $install_url; ?>">
                            <?php _e('Install Widget Options Now for Free!', 'peters-login-redirect'); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ($this->is_plugin_installed() && !$this->is_plugin_active()) : ?>
                        <a class="button button-primary button-hero" id="wo-activate-mailoptin-plugin" href="<?php echo $activate_url; ?>">
                            <?php _e('Activate Widget Options Now!', 'peters-login-redirect'); ?>
                        </a>
                    <?php endif; ?>
                    <div class="wo-notice-learn-more">
                        <a target="_blank" href="https://widget-options.com/">Learn more</a>
                    </div>
                </div>
                <a href="<?php echo $dismiss_url; ?>">
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text"><?php _e('Dismiss this notice', 'peters-login-redirect'); ?>.</span>
                    </button>
                </a>
            </div>
        <?php
        }

        public function current_admin_url()
        {
            $parts = parse_url(home_url());
            $uri   = $parts['scheme'] . '://' . $parts['host'];

            if (array_key_exists('port', $parts)) {
                $uri .= ':' . $parts['port'];
            }

            $uri .= add_query_arg(array());

            return $uri;
        }

        public function is_plugin_installed()
        {
            $installed_plugins = get_plugins();

            return isset($installed_plugins['widget-options/plugin.php']);
        }

        public function is_plugin_active()
        {
            return is_plugin_active('widget-options/plugin.php');
        }

        public function notice_css()
        {
        ?>
            <style type="text/css">
                .wo-admin-notice {
                    background: #fff;
                    color: #000;
                    border-left-color: #46b450;
                    position: relative;
                }

                .wo-admin-notice .notice-dismiss:before {
                    color: #72777c;
                }

                .wo-admin-notice .wo-stylize {
                    line-height: 2;
                }

                .wo-admin-notice .button-primary {
                    background: #006799;
                    text-shadow: none;
                    border: 0;
                    box-shadow: none;
                }

                .wo-notice-first-half {
                    width: 66%;
                    display: inline-block;
                    margin: 10px 0;
                }

                .wo-notice-other-half {
                    width: 33%;
                    display: inline-block;
                    padding: 20px 0;
                    position: absolute;
                    text-align: center;
                }

                .wo-notice-first-half p {
                    font-size: 14px;
                }

                .wo-notice-learn-more a {
                    margin: 10px;
                }

                .wo-notice-learn-more {
                    margin-top: 10px;
                }
            </style>
<?php
        }

        public static function instance()
        {
            static $instance = null;

            if (is_null($instance)) {
                $instance = new self();
            }

            return $instance;
        }
    }

    WO_Admin_Notice::instance();
}
