<?php

namespace LoginWP\Core\Admin;

use LoginWP\Core\Helpers;

if ( ! class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class RedirectWPList extends \WP_List_Table
{
    private $table;

    /** @var \wpdb */
    private $wpdb;

    public function __construct($wpdb)
    {
        $this->wpdb  = $wpdb;
        $this->table = PTR_LOGINWP_DB_TABLE;

        parent::__construct(array(
                'singular' => __('loginwp_redirect', 'peters-login-redirect'),
                'plural'   => __('loginwp_redirects', 'peters-login-redirect'),
                'ajax'     => false
            )
        );

        $this->process_actions();
    }

    /**
     * Retrieve campaigns data from the database
     *
     * @param int $per_page
     * @param int $current_page
     *
     * @return mixed
     */
    public function get_redirect_rules($per_page, $current_page = 1)
    {
        $per_page     = absint($per_page);
        $current_page = absint($current_page);

        $offset = ($current_page - 1) * $per_page;

        $sql = "SELECT * FROM {$this->table} WHERE rul_type NOT IN ('all','register')";

        $sql    .= " LIMIT %d";
        $args[] = $per_page;

        if ($current_page > 1) {
            $sql    .= "  OFFSET %d";
            $args[] = $offset;
        }

        return $this->wpdb->get_results(
            $this->wpdb->prepare($sql, $args),
            'ARRAY_A'
        );
    }

    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public function record_count()
    {
        global $wpdb;

        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE rul_type NOT IN ('all','register')";

        return $wpdb->get_var($sql);
    }

    /** Text displayed when no email campaign is available */
    public function no_items()
    {
        printf(
            __('No redirection rule found. %sConsider creating one%s', 'peters-login-redirect'),
            '<a href="' . add_query_arg('new', '1', PTR_LOGINWP_REDIRECTIONS_PAGE_URL) . '">',
            '</a>'
        );
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="redirect_rule_ids[]" value="%s" />', $item['id']
        );
    }

    /**
     * @param int $id
     *
     * @return string
     */
    public static function edit_rule_url($id)
    {
        return add_query_arg(['action' => 'edit', 'id' => absint($id)], PTR_LOGINWP_REDIRECTIONS_PAGE_URL);
    }

    /**
     * @param int $id
     *
     * @return string
     */
    public static function delete_rule_url($id)
    {
        return wp_nonce_url(
            add_query_arg(['action' => 'delete', 'id' => absint($id)], PTR_LOGINWP_REDIRECTIONS_PAGE_URL),
            'loginwp-delete-rule'
        );
    }

    /**
     * @param mixed $item
     *
     * @return string
     */
    public function column_rul_url($item)
    {
        $rule_id = absint($item['id']);

        $edit_url = self::edit_rule_url($rule_id);

        $label = ! empty($item['rul_url']) ? sanitize_text_field($item['rul_url']) : esc_html__('[Not Set]', 'peters-login-redirect');

        $name = "<strong><a href=\"$edit_url\">" . $label . '</a></strong>';

        $actions = [
            'edit'   => sprintf('<a href="%s">%s</a>', $edit_url, __('Edit', 'peters-login-redirect')),
            'delete' => sprintf('<a class="loginwp-delete-prompt delete" href="%s">%s</a>', esc_url(self::delete_rule_url($rule_id)), __('Delete', 'peters-login-redirect')),
        ];

        return $name . $this->row_actions($actions);
    }

    /**
     * @param mixed $item
     *
     * @return string
     */
    public function column_rul_url_logout($item)
    {
        $rule_id = absint($item['id']);

        $edit_url = self::edit_rule_url($rule_id);

        $label = ! empty($item['rul_url_logout']) ? sanitize_text_field($item['rul_url_logout']) : esc_html__('[Not Set]', 'peters-login-redirect');

        return "<strong><a href=\"$edit_url\">" . $label . '</a></strong>';
    }

    /**
     * @param mixed $item
     *
     * @return string
     */
    public function column_rul_type($item)
    {
        $val      = wp_list_filter(RedirectionsPage::get_rule_conditions(), ['id' => $item['rul_type']]);
        $label    = @array_values(wp_list_pluck($val, 'label'))[0];
        $category_id    = @array_values(wp_list_pluck($val, 'category'))[0];
        $category = loginwp_var(RedirectionsPage::rule_condition_categories(), $category_id);

        if ( ! empty($category_id) && $category_id != RedirectionsPage::STANDARD_CATEGORY) {
            $category .= ': ';
        } else {
            $category = '';
        }

        return sprintf('%s' . $label, "<strong>$category</strong>");
    }

    /**
     * @param mixed $item
     *
     * @return string
     */
    public function column_rul_value($item)
    {
        $value = sanitize_text_field($item['rul_value']);

        if ($item['rul_type'] == 'role') {
            $value = loginwp_var(Helpers::user_role_list(), $item['rul_value']);
        }

        return apply_filters('rul_column_rul_value', $value, $item);
    }

    /**
     * @param object $item
     * @param string $column_name
     *
     * @return string
     */
    public function column_default($item, $column_name)
    {
        return sanitize_text_field($item[$column_name]);
    }

    /**
     *  Associative array of columns
     *
     * @return array
     */
    public function get_columns()
    {
        $columns = array(
            'cb'             => '<input type="checkbox">',
            'rul_url'        => __('Login URL', 'peters-login-redirect'),
            'rul_url_logout' => __('Logout URL', 'peters-login-redirect'),
            'rul_type'       => __('Condition', 'peters-login-redirect'),
            'rul_value'      => __('Condition Value', 'peters-login-redirect')
        );

        return $columns;
    }

    /**
     * Columns to make sortable.
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'name' => array('name', true),
        );

        return $sortable_columns;
    }

    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    public function get_bulk_actions()
    {
        $actions = array(
            'bulk-delete' => __('Delete', 'peters-login-redirect'),
        );

        return $actions;
    }

    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items()
    {
        $this->_column_headers = $this->get_column_info();
        $per_page              = $this->get_items_per_page('redirections_per_page', 10);
        $current_page          = $this->get_pagenum();
        $total_items           = $this->record_count();
        $this->set_pagination_args(array(
                'total_items' => $total_items, //WE have to calculate the total number of items
                'per_page'    => $per_page //WE have to determine how many items to show on a page
            )
        );

        $this->items = $this->get_redirect_rules($per_page, $current_page);
    }

    public static function delete_rule($rule_id)
    {
        global $wpdb;

        $wpdb->delete(
            PTR_LOGINWP_DB_TABLE,
            ['id' => $rule_id],
            ['%d']
        );
    }

    public function process_actions()
    {
        // bail if user is not an admin or without admin privileges.
        if ( ! current_user_can('manage_options')) {
            return;
        }

        if ('delete' === $this->current_action() && ! empty($_GET['id'])) {

            check_admin_referer('loginwp-delete-rule');

            self::delete_rule(absint($_GET['id']));

            wp_safe_redirect(add_query_arg('deleted', 'true', PTR_LOGINWP_REDIRECTIONS_PAGE_URL));
            exit;
        }

        if ('bulk-delete' === $this->current_action()) {

            check_admin_referer('bulk-' . $this->_args['plural']);

            $delete_ids = array_map('absint', $_POST['redirect_rule_ids']);

            foreach ($delete_ids as $id) {
                self::delete_rule($id);
            }

            wp_safe_redirect(add_query_arg('deleted', 'true', PTR_LOGINWP_REDIRECTIONS_PAGE_URL));
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
            $instance = new self($GLOBALS['wpdb']);
        }

        return $instance;
    }
}