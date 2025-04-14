<?php

use LoginWP\Core\Admin\RedirectionsPage;

$db_condition       = sanitize_text_field(loginwpPOST_var('rul_condition', loginwp_var($ruleData, 'rul_type', '')));
$db_condition_value = sanitize_text_field(loginwpPOST_var('rul_condition_value', loginwp_var($ruleData, 'rul_value', '')));
$db_condition_order = absint(loginwpPOST_var('rul_order', loginwp_var($ruleData, 'rul_order', 0)));
?>
<div class="ptr-loginwp-condition-wrap">
    <div class="ptr-loginwp-row">
        <div id="ptr-loginwp-condition-wrap" class="ptr-loginwp-col">
            <label>
                <select name="rul_condition">

                    <option value=""><?php esc_html_e('Select a condition', 'peters-login-redirect'); ?></option>

                    <?php foreach (RedirectionsPage::rule_condition_categories() as $id => $label) : ?>

                        <?php $conditions = RedirectionsPage::get_rule_conditions_by_category($id); ?>

                        <?php if ( ! empty($conditions)) : ?>

                            <optgroup label="<?= $label ?>">
                                <?php foreach ($conditions as $condition) : ?>
                                    <option value="<?php echo esc_attr($condition['id']) ?>" <?php selected($db_condition, $condition['id']) ?>>
                                        <?php echo esc_html($condition['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>

                        <?php endif; ?>

                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <div id="ptr-loginwp-condition-value-wrap" class="ptr-loginwp-col">
            <?php RedirectionsPage::condition_value_dropdown($db_condition, $db_condition_value); ?>
        </div>
    </div>
</div>

<div>
    <table class="form-table">
        <tbody>
        <?php do_action('loginwp_after_sub_condition_tbody', $ruleData); ?>
        <tr class="ptr-loginwp-order-wrap"<?php echo ! in_array($db_condition, RedirectionsPage::order_support_conditions()) ? ' style="display:none"' : ''; ?>>
            <th>
                <label for="loginwp-login-url"><?= esc_html__('Order', 'peters-login-redirect') ?></label>
            </th>
            <td>
                <input name="rul_order" type="number" id="loginwp-login-url" value="<?php echo $db_condition_order; ?>" class="regular-text">
            </td>
        </tr>
        </tbody>
    </table>
</div>