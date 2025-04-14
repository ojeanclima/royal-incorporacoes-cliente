<?php

use LoginWP\Core\Helpers;
use LoginWP\Core\Redirections\Redirections;

// Typically this function is used in templates, similarly to the wp_register function
// It returns a link to the administration panel or the one that was custom defined
// If no user is logged in, it returns the "Register" link
// You can specify tags to go around the returned link (or wrap it with no tags); by default this is a list item
// You can also specify whether to print out the link or just return it
function rul_register($before = '<li>', $after = '</li>', $give_echo = true)
{
    global $current_user;

    if ( ! is_user_logged_in()) {
        if (get_option('users_can_register')) {
            $link = $before . '<a href="' . wp_registration_url() . '">' . __('Register', 'peters-login-redirect') . '</a>' . $after;
        } else {
            $link = '';
        }
    } else {
        $link = $before . '<a href="' . Helpers::login_redirect_logic_callback('', '', $current_user) . '">' . __('Site Admin', 'peters-login-redirect') . '</a>' . $after;
    }

    if ($give_echo) {
        echo $link;
    } else {
        return $link;
    }
}

function loginwpPOST_var($key, $default = false, $empty = false, $bucket = false)
{
    $bucket = ! $bucket ? $_POST : $bucket;

    if ($empty) {
        return ! empty($bucket[$key]) ? $bucket[$key] : $default;
    }

    return isset($bucket[$key]) ? $bucket[$key] : $default;
}

function loginwpGET_var($key, $default = false, $empty = false)
{
    $bucket = $_GET;

    if ($empty) {
        return ! empty($bucket[$key]) ? $bucket[$key] : $default;
    }

    return isset($bucket[$key]) ? $bucket[$key] : $default;
}

function loginwp_var($bucket, $key, $default = false, $empty = false)
{
    if ($empty) {
        return ! empty($bucket[$key]) ? $bucket[$key] : $default;
    }

    return isset($bucket[$key]) ? $bucket[$key] : $default;
}

function loginwp_var_obj($bucket, $key, $default = false, $empty = false)
{
    if ($empty) {
        return ! empty($bucket->$key) ? $bucket->$key : $default;
    }

    return isset($bucket->$key) ? $bucket->$key : $default;
}

/**
 * Return currently viewed page url without query string.
 *
 * @return string
 */
function loginwp_get_current_url()
{
    $protocol = 'http://';

    if ((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1))
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
    ) {
        $protocol = 'https://';
    }

    return esc_url_raw($protocol . $_SERVER['HTTP_HOST'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
}

/**
 * Return currently viewed page url with query string.
 *
 * @return string
 */
function loginwp_get_current_url_query_string()
{
    $protocol = 'http://';

    if ((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1))
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
    ) {
        $protocol = 'https://';
    }

    $url = $protocol . $_SERVER['HTTP_HOST'];

    $url .= $_SERVER['REQUEST_URI'];

    return esc_url_raw($url);
}

function loginwp_is_json($str)
{
    $json = json_decode($str);

    return $json && $str != $json;
}

function redirect_to_front_page($redirect_to, $requested_redirect_to, $user)
{
    return Helpers::login_redirect_logic_callback($redirect_to, $requested_redirect_to, $user);
}

function wplogin_redirect_control_function()
{
    $redirect_url = Redirections::login_redirect_callback(admin_url(), '', wp_get_current_user());
    nocache_headers();
    wp_redirect($redirect_url);
    exit;
}
