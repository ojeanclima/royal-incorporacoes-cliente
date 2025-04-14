<?php

use LoginWP\Core\Helpers;

$other_users_rule = Helpers::get_other_users_rule();

$rul_allvalue        = $other_users_rule['rul_url'];
$rul_allvalue_logout = $other_users_rule['rul_url_logout'];
$after_registration  = Helpers::get_after_registration_rule();

$rul_settings                            = Helpers::redirectFunctionCollection_get_settings();
$rul_allow_post_redirect_override        = $rul_settings['rul_allow_post_redirect_override'];
$rul_allow_post_redirect_override_logout = $rul_settings['rul_allow_post_redirect_override_logout'];

$modal_title = esc_html__('View Available Placeholders', 'peters-login-redirect');
?>

<div style="margin: 30px 0 10px;padding:0 12px 12px;">

    <h3><?php _e('All Other Users', 'peters-login-redirect'); ?></h3>

    <a href="#TB_inline?&width=600&height=400&inlineId=loginwp-view-placeholders" class="thickbox" title="<?= $modal_title ?>">
        <?php echo $modal_title; ?>
    </a>

    <form name="rul_allform" method="post">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><label for="rul_all"><?php _e('Login URL:', 'peters-login-redirect') ?></label>
                </th>
                <td>
                    <input id="rul_all" class="regular-text" type="text" size="90" maxlength="500" name="rul_all" value="<?php echo esc_attr($rul_allvalue); ?>"/>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="rul_all_logout"><?php _e('Logout URL:', 'peters-login-redirect') ?></label>
                </th>
                <td>
                    <input id="rul_all_logout" class="regular-text" type="text" size="90" maxlength="500" name="rul_all_logout" value="<?php echo esc_attr($rul_allvalue_logout); ?>"/>
                </td>
            </tr>
            </tbody>
        </table>
        <?php wp_nonce_field('rul_allupdatesubmit', 'rul-security'); ?>
        <p>
            <input class="button-primary" type="submit" name="rul_allupdatesubmit" value="<?php _e('Save Changes', 'peters-login-redirect'); ?>">
        </p>
    </form>
</div>

<hr>

<div style="margin: 15px 0;padding:0 12px 12px;">

    <h3><?php _e('After Registration', 'peters-login-redirect'); ?></h3>

    <form name="rul_registerform" method="post">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <label for="rul_register"><?php _e('URL:', 'peters-login-redirect') ?></label>
                </th>
                <td>
                    <input id="rul_register" class="regular-text" type="text" size="90" maxlength="500" name="rul_register" value="<?php echo esc_attr($after_registration); ?>"/>
                </td>
            </tr>
            </tbody>
        </table>
        <?php wp_nonce_field('rul_registerupdatesubmit', 'rul-security'); ?>
        <p>
            <input class="button-primary" type="submit" name="rul_registerupdatesubmit" value="<?php _e('Save Changes', 'peters-login-redirect'); ?>">
        </p>
    </form>
</div>
<hr>
<div style="margin: 15px 0;padding:0 12px 12px;">
    <h3><?php _e('Redirect Settings', 'peters-login-redirect'); ?></h3>
    <form name="rul_settingsform" method="post">
        <table class="widefat">
            <tr>
                <td>
                    <p>
                        <strong><?php _e('Allow a POST or GET &#34;redirect_to&#34; login variable to take redirect precedence', 'peters-login-redirect'); ?></strong>
                    </p>
                </td>
                <td>
                    <label>
                        <select name="rul_allow_post_redirect_override">
                            <option value="1" <?php selected($rul_allow_post_redirect_override, '1'); ?>><?php _e('Yes', 'peters-login-redirect'); ?></option>
                            <option value="0" <?php selected($rul_allow_post_redirect_override, '0'); ?>><?php _e('No', 'peters-login-redirect'); ?></option>
                        </select>
                    </label>
                </td>
            </tr>
            <tr>
                <td>
                    <p>
                        <strong><?php _e('Allow a POST or GET &#34;redirect_to&#34; logout variable to take redirect precedence', 'peters-login-redirect'); ?></strong>
                    </p>
                </td>
                <td>
                    <label>
                        <select name="rul_allow_post_redirect_override_logout">
                            <option value="1" <?php selected($rul_allow_post_redirect_override_logout, '1'); ?>><?php _e('Yes', 'peters-login-redirect'); ?></option>
                            <option value="0" <?php selected($rul_allow_post_redirect_override_logout, '0'); ?>><?php _e('No', 'peters-login-redirect'); ?></option>
                        </select>
                    </label>
                </td>
            </tr>
        </table>
        <?php wp_nonce_field('rul_settingssubmit', 'rul-security'); ?>
        <p class="submit">
            <input class="button button-primary" name="rul_settingssubmit" type="submit" value="<?php _e('Save Changes', 'peters-login-redirect'); ?>"/>
        </p>
    </form>
</div>