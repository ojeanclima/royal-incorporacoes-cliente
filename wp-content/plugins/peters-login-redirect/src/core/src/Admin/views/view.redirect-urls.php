<?php

$login_url  = loginwpPOST_var('rul_login_url', loginwp_var($ruleData, 'rul_url', ''));
$logout_url = loginwpPOST_var('rul_logout_url', loginwp_var($ruleData, 'rul_url_logout', ''));

$modal_title = esc_html__('View Available Placeholders', 'peters-login-redirect');
?>
<div class="ptr-loginwp-redirect-wrap">
    <div class="loginwp-view-variables">
        <a href="#TB_inline?&width=600&height=400&inlineId=loginwp-view-placeholders" class="thickbox" title="<?= $modal_title ?>">
            <?php echo $modal_title; ?>
        </a>
    </div>
    <table class="form-table">
        <tbody>
        <tr>
            <th>
                <label for="rul_login_url"><?= esc_html__('Login URL', 'peters-login-redirect') ?></label>
            </th>
            <td>
                <input name="rul_login_url" type="text" id="rul_login_url" value="<?php echo esc_attr($login_url); ?>" class="regular-text">
            </td>
        </tr>
        <tr>
            <th>
                <label for="rul_logout_url"><?= esc_html__('Logout URL', 'peters-login-redirect') ?></label>
            </th>
            <td>
                <input name="rul_logout_url" type="text" id="rul_logout_url" value="<?php echo esc_attr($logout_url); ?>" class="regular-text">
            </td>
        </tr>
        </tbody>
    </table>
</div>