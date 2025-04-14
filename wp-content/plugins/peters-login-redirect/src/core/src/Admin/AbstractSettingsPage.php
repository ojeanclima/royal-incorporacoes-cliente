<?php

namespace LoginWP\Core\Admin;

abstract class AbstractSettingsPage
{
    public function __construct()
    {
        add_action('loginwp_register_menu_page', array($this, 'register_menu_page'));
    }

    abstract function register_menu_page();

    abstract function header_menu_tabs();

    abstract function default_header_menu();

    public function settings_page_header($active_menu)
    {
        $logo_url       = PTR_LOGINWP_ASSETS_URL . 'images/loginwp.png';
        $submenus_count = count($this->header_menu_tabs());
?>
        <div class="loginwp-admin-banner<?= defined('LOGINWP_DETACH_LIBSODIUM') ? ' loginwp-pro' : '' ?><?= $submenus_count < 2 ? ' loginwp-no-submenu' : '' ?>">
            <div class="loginwp-admin-banner__logo">
                <img src="<?= $logo_url ?>" alt="">
            </div>
            <div class="loginwp-admin-banner__helplinks">
                <?php if (defined('LOGINWP_DETACH_LIBSODIUM')) : ?>
                    <span><a rel="noopener" href="https://loginwp.com/submit-ticket/" target="_blank">
                            <span class="dashicons dashicons-admin-users"></span> <?= __('Request Support', 'peters-login-redirect'); ?>
                        </a></span>
                <?php else : ?>
                    <span><a class="lwp-active" rel="noopener" href="https://loginwp.com/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=loginwp_header_topright_menu" target="_blank">
                            <span class="dashicons dashicons-info"></span> <?= __('Pro Upgrade', 'peters-login-redirect'); ?>
                        </a></span>
                <?php endif; ?>
                <span><a rel="noopener" href="https://wordpress.org/support/plugin/peters-login-redirect/reviews/?filter=5#new-post" target="_blank">
                        <span class="dashicons dashicons-star-filled"></span> <?= __('Review', 'peters-login-redirect'); ?>
                    </a></span>
                <span><a rel="noopener" href="https://loginwp.com/docs/" target="_blank">
                        <span class="dashicons dashicons-book"></span> <?= __('Documentation', 'peters-login-redirect'); ?>
                    </a></span>
            </div>
            <div class="clear"></div>
            <?php $this->settings_page_header_menus($active_menu); ?>
        </div>
    <?php
    }

    public function settings_page_header_menus($active_menu)
    {
        $menus = $this->header_menu_tabs();

        if (count($menus) < 2) return;
    ?>
        <div class="loginwp-header-menus">
            <nav class="loginwp-nav-tab-wrapper nav-tab-wrapper">
                <?php foreach ($menus as $id => $menu) : ?>
                    <a href="<?php echo esc_url(remove_query_arg(['action', 'id'] + wp_removable_query_args(), add_query_arg('tab', $id))); ?>" class="loginwp-nav-tab nav-tab<?= $id == $active_menu ? ' loginwp-nav-active' : '' ?>">
                        <?php echo $menu ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
<?php
    }

    public function admin_page_callback()
    {
        $active_menu = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : $this->default_header_menu();

        $this->settings_page_header($active_menu);

        do_action('loginwp_admin_settings_page_' . $active_menu);
    }

    public static function sidebar_args()
    {
        $sidebar_args = [
            [
                'section_title' => esc_html__('Upgrade to Pro', 'peters-login-redirect'),
                'content'       => self::pro_upsell(),
            ],
            [
                'section_title' => esc_html__('Need Support?', 'peters-login-redirect'),
                'content'       => self::sidebar_support_docs(),
            ],
            [
                'section_title' => esc_html__('Recommended Plugins', 'peters-login-redirect'),
                'content'       => self::sidebar_recommended_plugins(),
            ]
        ];

        if (defined('LOGINWP_DETACH_LIBSODIUM')) {
            unset($sidebar_args[0]);
        }

        return $sidebar_args;
    }

    public static function pro_upsell()
    {
        $integrations = [
            'WooCommerce',
            'Gravity Forms',
            'WPForms',
            'LearnDash',
            'ProfilePress',
            'MemberPress',
            'Paid Memberships Pro',
            'WishList Member',
            'MemberMouse',
            'Restrict Content Pro',
            'LifterLMS',
            'Easy Digital Downloads',
            'Tutor LMS',
            'LearnPress',
            'Ultimate Member',
            'WP User Frontend',
            'WP User Manager',
            'Uncanny Toolkit',
            'User Registration (WPEverest)',
            'Theme My Login',
            'WPML',
            'Polylang'
        ];

        $upsell_url = 'https://loginwp.com/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=sidebar_upsell';

        $content = '<p>';
        $content .= sprintf(
            esc_html__('Enhance the power of LoginWP with the Pro version featuring integrations with many plugins. %sLearn more%s', 'peters-login-redirect'),
            '<a target="_blank" href="' . $upsell_url . '">',
            '</a>'
        );
        $content .= '</p>';

        $content .= '<ul>';

        $content .= sprintf('<li>%s</li>', esc_html__('Redirect only on first time login', 'peters-login-redirect'));
        $content .= sprintf('<li>%s</li>', esc_html__('Redirect to referrer or previous page', 'peters-login-redirect'));
        $content .= sprintf('<li>%s</li>', esc_html__('Redirect to currently viewing page', 'peters-login-redirect'));

        foreach ($integrations as $integration) :
            $content .= sprintf('<li>%s</li>', $integration);
        endforeach;

        $content .= '</ul>';

        $content .= '<a href="' . $upsell_url . '" target="__blank" class="button-primary">' . esc_html__('Get LoginWP Pro →', 'peters-login-redirect') . '</a>';

        return $content;
    }

    public static function sidebar_support_docs()
    {
        $link_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="linkIcon"><path d="M18.2 17c0 .7-.6 1.2-1.2 1.2H7c-.7 0-1.2-.6-1.2-1.2V7c0-.7.6-1.2 1.2-1.2h3.2V4.2H7C5.5 4.2 4.2 5.5 4.2 7v10c0 1.5 1.2 2.8 2.8 2.8h10c1.5 0 2.8-1.2 2.8-2.8v-3.6h-1.5V17zM14.9 3v1.5h3.7l-6.4 6.4 1.1 1.1 6.4-6.4v3.7h1.5V3h-6.3z"></path></svg>';

        $content = '<p>';

        $support_url = 'https://wordpress.org/support/plugin/peters-login-redirect/';

        if (defined('LOGINWP_DETACH_LIBSODIUM')) {
            $support_url = 'https://loginwp.com/submit-ticket/';
        }

        $content .= sprintf(
            esc_html__('Whether you need help or have a new feature request, let us know. %sRequest Support%s', 'peters-login-redirect'),
            '<a class="loginwp-link" href="' . $support_url . '" target="_blank">',
            $link_icon . '</a>'
        );

        $content .= '</p>';

        $content .= '<p>';
        $content .= sprintf(
            esc_html__('Detailed documentation is also available on the plugin website. %sView Knowledge Base%s', 'peters-login-redirect'),
            '<a class="loginwp-link" href="https://loginwp.com/docs/" target="_blank">',
            $link_icon . '</a>'
        );

        $content .= '</p>';

        $content .= '<p>';
        $content .= sprintf(
            esc_html__('If you are enjoying LoginWP and find it useful, please consider leaving a ★★★★★ review on WordPress.org. %sLeave a Review%s', 'peters-login-redirect'),
            '<a class="loginwp-link" href="https://wordpress.org/support/plugin/peters-login-redirect/reviews/?filter=5#new-post" target="_blank">',
            $link_icon . '</a>'
        );
        $content .= '</p>';

        return $content;
    }

    public static function sidebar_recommended_plugins()
    {
        $link_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="linkIcon"><path d="M18.2 17c0 .7-.6 1.2-1.2 1.2H7c-.7 0-1.2-.6-1.2-1.2V7c0-.7.6-1.2 1.2-1.2h3.2V4.2H7C5.5 4.2 4.2 5.5 4.2 7v10c0 1.5 1.2 2.8 2.8 2.8h10c1.5 0 2.8-1.2 2.8-2.8v-3.6h-1.5V17zM14.9 3v1.5h3.7l-6.4 6.4 1.1 1.1 6.4-6.4v3.7h1.5V3h-6.3z"></path></svg>';

        $content = '<p>';
        $content .= esc_html__('Check out some of our other plugins + a few extra that we love using!', 'peters-login-redirect');
        $content .= '</p>';

        $content .= '<p>';

        $widgetopts_url = 'https://widget-options.com/ref/112/';

        $content .= sprintf(
            esc_html__('%sWidget Options%s The #1 WordPress Widget Control Plugin. Easily show or hide widgets based on a ton of factors such as page name, user level, devices, days of the week and more!', 'peters-login-redirect'),
            '<a class="loginwp-link" href="' . $widgetopts_url . '" target="_blank" style="margin-bottom: 5px;">',
            $link_icon . '</a>'
        );

        $content .= '</p>';

        $content .= '<p>';

        $wpdb_url = 'https://wpdiscussionboard.com/ref/6/';

        $content .= sprintf(
            esc_html__('%sWP Discussion Board%s An easy way to add a discussion board, message board or simple forum to your WordPress site with user profiles, boards, topics, notifications & more!', 'peters-login-redirect'),
            '<a class="loginwp-link" href="' . $wpdb_url . '" target="_blank" style="margin-bottom: 5px;">',
            $link_icon . '</a>'
        );

        $content .= '</p>';

        $content .= '<p>';

        $fusewp_url = 'https://fusewp.com/?sscid=81k7_pja98';

        $content .= sprintf(
            esc_html__('%sFuseWP%s Automatically Sync WordPress Users, customers, and members in ecommerce and membership plugins with your CRM and Email Marketing software.', 'peters-login-redirect'),
            '<a class="loginwp-link" href="' . $fusewp_url . '" target="_blank" style="margin-bottom: 5px;">',
            $link_icon . '</a>'
        );

        $content .= '</p>';

        return $content;
    }
}
