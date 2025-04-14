<?php
// This assumes that this files sits in "wp-content/plugins/peters-login-redirect/wplogin_redirect_control.php" and that you haven't moved your wp-content folder

if (file_exists('../../../wp-load.php')) {
    include '../../../wp-load.php';
} else {
    print 'Plugin paths not configured correctly.';
}

wplogin_redirect_control_function();