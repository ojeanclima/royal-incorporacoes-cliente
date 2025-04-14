/* global loginwp_pluginlanding, loginwp_installer_globals */

'use strict';

var LoginWPPagesProfilePress = window.LoginWPPagesProfilePress || (function (document, window, $) {

    var el = {};

    var app = {

        init: function () {

            $(document).ready(app.ready);
        },

        ready: function () {

            app.initVars();
            app.events();
        },

        initVars: function () {

            el = {
                $stepInstall: $('section.step-install'),
                $stepInstallNum: $('section.step-install .num img'),
                $stepSetup: $('section.step-setup'),
                $stepSetupNum: $('section.step-setup .num img'),
            };
        },

        events: function () {

            // Step 'Install' button click.
            el.$stepInstall.on('click', 'button', app.stepInstallClick);

            // Step 'Setup' button click.
            el.$stepSetup.on('click', 'button', app.gotoURL);
        },

        stepInstallClick: function () {

            var $btn = $(this),
                action = $btn.attr('data-action'),
                plugin = $btn.attr('data-plugin'),
                ajaxAction = '';

            if ($btn.hasClass('disabled')) {
                return;
            }

            switch (action) {
                case 'activate':
                    ajaxAction = 'loginwp_activate_plugin';
                    $btn.text(loginwp_pluginlanding.activating);
                    break;

                case 'install':
                    ajaxAction = 'loginwp_install_plugin';
                    $btn.text(loginwp_pluginlanding.installing);
                    break;

                case 'goto-url':
                    window.location.href = $btn.attr('data-url');
                    return;

                default:
                    return;
            }

            $btn.addClass('disabled');
            app.showSpinner(el.$stepInstallNum);

            var data = {
                action: ajaxAction,
                nonce: loginwp_installer_globals.nonce,
                plugin: plugin,
                type: 'plugin',
            };

            $.post(ajaxurl, data)
                .done(function (res) {
                    app.stepInstallDone(res, $btn, action);
                })
                .always(function () {
                    app.hideSpinner(el.$stepInstallNum);
                });
        },

        /**
         * Done part of the 'Install' step.
         */
        stepInstallDone: function (res, $btn, action) {

            if (res.success) {
                el.$stepInstallNum.attr('src', el.$stepInstallNum.attr('src').replace('step-1.', 'step-complete.'));
                $btn.addClass('grey').text(loginwp_pluginlanding.activated);
                app.stepInstallPluginStatus();
            } else {
                var url = 'install' === action ? loginwp_pluginlanding.manual_install_url : loginwp_pluginlanding.manual_activate_url,
                    msg = 'install' === action ? loginwp_pluginlanding.error_could_not_install : loginwp_pluginlanding.error_could_not_activate,
                    btn = 'install' === action ? loginwp_pluginlanding.download_now : loginwp_pluginlanding.plugins_page;

                $btn.removeClass('grey disabled').text(btn).attr('data-action', 'goto-url').attr('data-url', url);
                $btn.after('<p class="error">' + msg + '</p>');
            }
        },

        /**
         * Callback for step 'Install' completion.
         */
        stepInstallPluginStatus: function () {

            var data = {
                action: 'loginwp_profilepress_page_check_plugin_status',
                nonce: loginwp_installer_globals.nonce,
            };
            $.post(ajaxurl, data)
                .done(app.stepInstallPluginStatusDone);
        },

        /**
         * Done part of the callback for step 'Install' completion.
         */
        stepInstallPluginStatusDone: function (res) {

            if (!res.success) {
                return;
            }

            el.$stepSetup.removeClass('grey');
            el.$stepSetupBtn = el.$stepSetup.find('button');
            el.$stepSetupBtn.removeClass('grey disabled');

            if (res.data.setup_status > 0) {
                el.$stepSetupNum.attr('src', el.$stepSetupNum.attr('src').replace('step-2.svg', 'step-complete.svg'));
                el.$stepSetupBtn.text(loginwp_pluginlanding.smtp_settings_button);
            }
        },

        /**
         * Go to URL by click on the button.
         */
        gotoURL: function () {

            var $btn = $(this);

            if ($btn.hasClass('disabled')) {
                return;
            }

            window.location.href = $btn.attr('data-url');
        },

        /**
         * Display spinner.
         */
        showSpinner: function ($el) {

            $el.siblings('.loader').removeClass('hidden');
        },

        /**
         * Hide spinner.
         */
        hideSpinner: function ($el) {

            $el.siblings('.loader').addClass('hidden');
        },
    };

    // Provide access to public functions/properties.
    return app;

}(document, window, jQuery));

// Initialize.
LoginWPPagesProfilePress.init();
