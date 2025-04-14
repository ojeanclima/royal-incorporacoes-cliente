<?php

namespace LoginWP\Core\Admin;

use PAnD;

class Admin
{
    public function __construct()
    {
        SettingsPage::get_instance();
        RedirectionsPage::get_instance();

        add_action('admin_enqueue_scripts', array($this, 'admin_assets'));

        add_filter('admin_footer_text', [$this, 'custom_admin_footer']);

        add_action('admin_menu', array($this, 'register_core_menu'));

        $basename = plugin_basename(PTR_LOGINWP_SYSTEM_FILE_PATH);
        $prefix   = is_network_admin() ? 'network_admin_' : '';
        add_filter("{$prefix}plugin_action_links_$basename", [$this, 'loginwp_action_links'], 10, 4);

        add_filter('plugin_row_meta', array(__CLASS__, 'plugin_row_meta'), 10, 2);

        add_filter('removable_query_args', array($this, 'removable_query_args'));

        if (class_exists('PAnD')) {
            // persist admin notice dismissal initialization
            add_action('admin_init', array('PAnD', 'init'));
        }

        add_action('admin_init', array($this, 'act_on_request'));
    }

    public function register_core_menu()
    {
        add_menu_page(
            __('Settings – LoginWP', 'peters-login-redirect'),
            __('LoginWP', 'peters-login-redirect'),
            'manage_options',
            PTR_LOGINWP_SETTINGS_PAGE_SLUG,
            '',
            $this->getMenuIcon(),
            '80.0015'
        );

        do_action('loginwp_register_menu_page');

        do_action('loginwp_admin_hooks');

        add_filter('admin_body_class', [$this, 'add_admin_body_class']);

        add_action('admin_notices', [$this, 'review_plugin_notice']);
        add_action('admin_notices', [$this, 'ptlr_is_now_loginwp_notice']);
        add_action('admin_notices', [$this, 'loginwp_display_free_license_admin_notice']);
        add_action('admin_init', [$this, 'loginwp_activate_free_license']);
    }

    public static function set_admin_notice_cache($id, $timeout)
    {
        $cache_key = 'pand-' . md5($id);
        update_site_option($cache_key, $timeout);

        return true;
    }

    public function act_on_request()
    {
        if ( ! empty($_GET['loginwp_admin_action'])) {

            if ($_GET['loginwp_admin_action'] == 'dismiss_leave_review_forever') {
                self::set_admin_notice_cache('loginwp-review-plugin-notice', 'forever');
            }

            if ($_GET['loginwp_admin_action'] == 'dismiss_ptlr_now_loginwp') {
                self::set_admin_notice_cache('ptlr_is_now_loginwp_notice', 'forever');
            }

            wp_safe_redirect(esc_url_raw(remove_query_arg('loginwp_admin_action')));
            exit;
        }
    }

    public function admin_assets()
    {
        if (isset(get_current_screen()->base) && strpos(get_current_screen()->base, 'loginwp') !== false) {
            wp_enqueue_style('ptr-loginwp-admin', PTR_LOGINWP_ASSETS_URL . 'css/admin.css', [], PTR_LOGINWP_VERSION_NUMBER);
            wp_enqueue_script('ptr-loginwp-admin', PTR_LOGINWP_ASSETS_URL . 'js/admin.js', ['jquery', 'wp-util'], PTR_LOGINWP_VERSION_NUMBER, true);

            wp_localize_script('ptr-loginwp-admin', 'loginwp_globals', [
                'confirm_delete' => esc_html__('Are you sure?', 'peters-login-redirect')
            ]);
        }
    }

    public function add_admin_body_class($classes)
    {
        $current_screen = get_current_screen();

        if (empty ($current_screen)) return $classes;

        if (false !== strpos($current_screen->id, 'loginwp')) {
            // Leave space on both sides so other plugins do not conflict.
            $classes .= ' loginwp-admin ';
        }

        return $classes;
    }

    public function custom_admin_footer($text)
    {
        if (strpos(loginwpGET_var('page'), 'loginwp') !== false) {
            $text = sprintf(
                __('Thank you for using LoginWP. Please rate the plugin %1$s on %2$sWordPress.org%3$s to help us spread the word.', 'block-visibility'),
                '<a href="https://wordpress.org/support/plugin/peters-login-redirect/reviews/?filter=5#new-post" target="_blank" rel="noopener noreferrer">★★★★★</a>',
                '<a href="https://wordpress.org/support/plugin/peters-login-redirect/reviews/?filter=5#new-post" target="_blank" rel="noopener">',
                '</a>'
            );
        }

        return $text;
    }

    private function getMenuIcon()
    {
        return 'data:image/svg+xml;base64,' . base64_encode('<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill-rule="evenodd" image-rendering="optimizeQuality" shape-rendering="geometricPrecision" viewBox="0 0 58.78 58.78" xmlns:v="https://vecta.io/nano"><path d="M55.87 25.04c.81.76 1.31 1.84 1.31 3.03v25.57a4.19 4.19 0 0 1-4.17 4.17H32.35c-1-17.92 4.14-19.85 4.14-24.27 0-3.92-3.18-7.1-7.1-7.1s-7.1 3.18-7.1 7.1c0 4.42 5.14 6.34 4.14 24.27H5.77a4.19 4.19 0 0 1-4.17-4.17V28.07a4.14 4.14 0 0 1 1.42-3.12L26.69 2.1c.69-.69 1.65-1.12 2.71-1.12 1.08 0 1.94.4 2.71 1.12 3.79 3.55 7.52 7.26 11.26 10.87l12.52 12.08z" fill="#a6aaad"/></svg>');
    }

    /**
     * Action links in plugin listing page.
     */
    public function loginwp_action_links($actions, $plugin_file, $plugin_data, $context)
    {
        $custom_actions = array(
            'loginwp_redirections' => sprintf('<a href="%s">%s</a>', PTR_LOGINWP_REDIRECTIONS_PAGE_URL, __('Settings', 'peters-login-redirect')),
        );

        if ( ! defined('LOGINWP_DETACH_LIBSODIUM')) {
            $custom_actions['loginwp_upgrade'] = sprintf(
                '<a style="color:#d54e21;font-weight:bold" href="%s" target="_blank">%s</a>', 'https://loginwp.com/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=action_link',
                __('Go Premium', 'peters-login-redirect')
            );
        }

        // add the links to the front of the actions list
        return array_merge($custom_actions, $actions);
    }

    /**
     * Show row meta on the plugin screen.
     *
     * @param mixed $links Plugin Row Meta
     * @param mixed $file Plugin Base file
     *
     * @return    array
     */
    public static function plugin_row_meta($links, $file)
    {
        if (strpos($file, 'wplogin_redirect.php') !== false) {
            $row_meta = array(
                'docs'    => '<a target="_blank" href="' . esc_url('https://loginwp.com/docs/') . '" aria-label="' . esc_attr__('View LoginWP documentation', 'peters-login-redirect') . '">' . esc_html__('Docs', 'peters-login-redirect') . '</a>',
                'support' => '<a target="_blank" href="' . esc_url('https://loginwp.com/support/') . '" aria-label="' . esc_attr__('Visit customer support', 'peters-login-redirect') . '">' . esc_html__('Support', 'peters-login-redirect') . '</a>',
            );

            if ( ! defined('LOGINWP_DETACH_LIBSODIUM')) {
                $url                     = 'https://loginwp.com/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=row_meta';
                $row_meta['upgrade_pro'] = '<a target="_blank" style="color:#d54e21;font-weight:bold" href="' . esc_url($url) . '" aria-label="' . esc_attr__('Upgrade to PRO', 'peters-login-redirect') . '">' . esc_html__('Go Premium', 'peters-login-redirect') . '</a>';
            }

            return array_merge($links, $row_meta);
        }

        return (array)$links;
    }

    public function ptlr_is_now_loginwp_notice()
    {
        if ( ! PAnD::is_admin_notice_active('ptlr_is_now_loginwp_notice-forever')) return;

        if (get_option('loginwp_from_ab_initio', false) == 'true') return;

        $dismiss_url = esc_url(add_query_arg('loginwp_admin_action', 'dismiss_ptlr_now_loginwp'));

        $notice = sprintf(
            __('Important news! %1$sPeters Login Redirect%2$s has been rebranded to %1$sLoginWP%2$s with a new UI. %3$sCheck It Out%5$s | %4$sDismiss Notice%5$s', 'peters-login-redirect'),
            '<strong>', '</strong>',
            '<a href="' . PTR_LOGINWP_REDIRECTIONS_PAGE_URL . '">', '<a href="' . $dismiss_url . '">', '</a>'
        );

        echo '<div data-dismissible="ptlr_is_now_loginwp_notice-forever" class="notice notice-warning is-dismissible">';
        echo "<p>$notice</p>";
        echo '</div>';
    }

    /**
     * Display one-time admin notice to review plugin at least 7 days after installation
     */
    public function review_plugin_notice()
    {
        if ( ! current_user_can('manage_options')) return;

        if ( ! PAnD::is_admin_notice_active('loginwp-review-plugin-notice-forever')) return;

        $install_date = get_option('loginwp_install_date', '');

        if (empty($install_date)) return;

        $diff = round((time() - strtotime($install_date)) / 24 / 60 / 60);

        if ($diff < 7) return;

        $review_url = 'https://wordpress.org/support/plugin/peters-login-redirect/reviews/?filter=5#new-post';

        $dismiss_url = esc_url_raw(add_query_arg('loginwp_admin_action', 'dismiss_leave_review_forever'));

        $notice = sprintf(
            __('Hey, I noticed you have been using LoginWP (Formerly Peter\'s Login Redirect) for a while now - that\'s awesome! Could you please do me a BIG favor and give it a %1$s5-star rating on WordPress?%2$s This will help us spread the word and boost our motivation - thanks!', 'peters-login-redirect'),
            '<a href="' . $review_url . '" target="_blank">',
            '</a>'
        );
        $label  = __('Sure! I\'d love to give a review', 'peters-login-redirect');

        $dismiss_label = __('Dismiss', 'peters-login-redirect');

        $notice .= "<div style=\"margin:10px 0 0;\"><a href=\"$review_url\" target='_blank' class=\"button-primary\">$label</a></div>";
        $notice .= "<div style=\"margin:10px 0 0;\"><a href=\"$dismiss_url\">$dismiss_label</a></div>";

        echo '<div data-dismissible="loginwp-review-plugin-notice-forever" class="update-nag notice notice-warning is-dismissible">';
        echo "<p>$notice</p>";
        echo '</div>';
    }

    /**
     * Show a notice to subscribe to newsletter
     */
    public function loginwp_display_free_license_admin_notice()
    {
        $license_key = get_option('loginwp_free_license');
        if (!current_user_can('update_plugins') || !empty($license_key))
            return;

        //show rating notice to page that matters most
        global $pagenow;
        if (!in_array($pagenow, array('admin.php'))) {
            return;
        }

        if ($pagenow == 'admin.php' && function_exists('get_current_screen')) {
            $screen = get_current_screen();
            if (isset($screen->base) && ($screen->base != 'toplevel_page_loginwp-settings' && $screen->base != 'loginwp_page_loginwp-redirections')) {
                return;
            }
        }

        $htmlNotice = '
            <div class="notice loginwp-notice" style="border-left-color: #064466">
                <form method="post">
                    <h3>' . __( 'Free License', 'peters-login-redirect' ) .'</h3>
                    <p>' . __( "You're currently using the free version of LoginWP. To register a free license for the plugin, please fill in your email below. This is not required but helps us support you better.", 'peters-login-redirect' ) . '</p>
                    <input type="text" name="email" placeholder="' . __( 'Email Address', 'peters-login-redirect' ) . '" />
                    ' . wp_nonce_field( 'loginwp_free_license_action', 'loginwp_free_license_field' ) . '
                    <input type="submit" name="loginwp_free_license_activator" value="Register Free License" class="button button-primary" />
                    <input type="button" name="loginwp_free_license_dismiss" value="Dismiss" class="button button-secondary" /><br><br>
                    <input type="checkbox" name="loginwp_free_license_subscribe" value="1" checked /> Add me to your newsletter and keep me updated whenever you release news, updates and promos.
                    <p><small>* ' . __( 'Your email is secure with us! We will keep you updated on new feature releases and major announcements about LoginWP.', 'peters-login-redirect' ) . '</small></p>
                </form>
                <form method="post" id="loginwp_free_license_dismiss_form">
                    ' . wp_nonce_field( 'loginwp_free_license_dismiss', 'loginwp_free_license_dismiss_field' ) . '
                    <input type="hidden" name="loginwp_free_dismiss" value="1" />
                </form>
            </div>
            <script>
            jQuery( document ).ready(function( $ ) {

                jQuery(\'input[name="loginwp_free_license_dismiss"]\').on("click", function(e){
                    e.preventDefault();
                    jQuery("#loginwp_free_license_dismiss_form").submit();
                });

            });
            </script>
        ';

        echo $htmlNotice;
    }
    public function loginwp_activate_free_license () {
        if ( ! empty( $_POST['loginwp_free_license_activator'] ) ) {
            if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'loginwp_free_license_field' ] ) ), 'loginwp_free_license_action' ) ) {
                return;
            }

            $email = sanitize_email( wp_unslash( $_POST['email'] ) );

            if ( is_email( $email ) ) {
                $user       = get_user_by( 'email', $email );
                $first_name = '';
                $last_name  = '';
                $url        = rawurlencode( home_url() );

                if ( is_a( $user, 'WP_User' ) ) {
                    $first_name = $user->first_name;
                    $last_name  = $user->last_name;
                }

                if ( ! empty( $_POST['loginwp_free_license_subscribe'] ) ) {
                    // Make request, save key.
                    $request = wp_remote_post(
                        'https://loginwp.com/wp-admin/admin-ajax.php',
                        array(
                            'body'    =>
                                array(
                                    'action' => 'loginwp_free_license',
                                    'email_address' => $email,
                                    'fname'    => $first_name,
                                    'lname'     => $last_name,
                                    'url'           => $url,
                                )
                        )
                    );

                    if ( ! is_wp_error( $request ) ) {
                        $license = $email;

                        if ( ! empty( $license ) ) {
                            update_option( 'loginwp_free_license', sanitize_text_field( $license ), false );

                            add_action(
                                'admin_notices',
                                function() {
                                    ?>
                                    <div class="notice notice-success">
                                        <p><?php esc_html_e( 'Free license activated!', 'peters-login-redirect' ); ?></p>
                                    </div>
                                    <?php
                                }
                            );
                        }
                    } else {
                        add_action(
                            'admin_notices',
                            function() {
                                ?>
                                <div class="notice notice-error">
                                    <p><?php esc_html_e( 'Something went wrong! Try again later.', 'peters-login-redirect' ); ?></p>
                                </div>
                                <?php
                            }
                        );
                    }
                } else {
                    $license = $email;

                    if ( ! empty( $license ) ) {
                        update_option( 'loginwp_free_license', sanitize_text_field( $license ), false );

                        add_action(
                            'admin_notices',
                            function() {
                                ?>
                                <div class="notice notice-success">
                                    <p><?php esc_html_e( 'Free license activated!', 'peters-login-redirect' ); ?></p>
                                </div>
                                <?php
                            }
                        );
                    }
                }
            } else {
                add_action(
                    'admin_notices',
                    function() {
                        ?>
                        <div class="notice notice-error">
                            <p><?php esc_html_e( 'Invalid email address!', 'peters-login-redirect' ); ?></p>
                        </div>
                        <?php
                    }
                );
            }
        }
        if ( ! empty( $_POST['loginwp_free_dismiss'] ) ) {
            if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'loginwp_free_license_dismiss_field' ] ) ), 'loginwp_free_license_dismiss' ) ) {
                return;
            }

            update_option( 'loginwp_free_license', sanitize_text_field( 'NA' ), false );
        }
    }

    public function removable_query_args($args)
    {
        $args[] = 'license-settings-updated';
        $args[] = 'license';

        return $args;
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