(function ($) {

    $('.loginwp-delete-prompt').on('click', function (e) {
        e.preventDefault();
        if (confirm(loginwp_globals.confirm_delete)) {
            window.location.href = $(this).attr('href');
        }
    });

    $(document).on('ready', function () {

        $('#ptr-loginwp-condition-wrap select').on('change', function () {

            var template = wp.template('loginwp-condition-' + this.value);

            $('#ptr-loginwp-condition-value-wrap').html(
                template()
            );

            $('.ptr-loginwp-order-wrap').toggle(
                $.inArray(this.value, rul_conditions_order_support) !== -1
            );
        })
    });

})(jQuery);