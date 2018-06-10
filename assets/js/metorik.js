(function($) {
    /**
     * Initialize sourcebuster.js.
     */
    sbjs.init({
        lifetime: metorik_params.life,
        session_length: metorik_params.session,
        timezone_offset: '0', // utc
    });

    /**
     * Set values.
     */
    var setFields = function() {
        if (sbjs.get) {
            $('input[name="metorik_source_type"]').val(sbjs.get.current.typ);
            $('input[name="metorik_source_url"]').val(sbjs.get.current_add.rf);
            $('input[name="metorik_source_mtke"]').val(sbjs.get.current.mtke);

            $('input[name="metorik_source_utm_campaign"]').val(sbjs.get.current.cmp);
            $('input[name="metorik_source_utm_source"]').val(sbjs.get.current.src);
            $('input[name="metorik_source_utm_medium"]').val(sbjs.get.current.mdm);
            $('input[name="metorik_source_utm_content"]').val(sbjs.get.current.cnt);
            $('input[name="metorik_source_utm_id"]').val(sbjs.get.current.id);
            $('input[name="metorik_source_utm_term"]').val(sbjs.get.current.trm);

            $('input[name="metorik_source_session_entry"]').val(sbjs.get.current_add.ep);
            $('input[name="metorik_source_session_start_time"]').val(sbjs.get.current_add.fd);
            $('input[name="metorik_source_session_pages"]').val(sbjs.get.session.pgs);
            $('input[name="metorik_source_session_count"]').val(sbjs.get.udata.vst);
        }
    };

    /**
     * Add source values to checkout.
     */
    $(document.body).on('init_checkout', function(event) {
        setFields();
    });

    /**
     * Add source values to register.
     */
    if ($('.woocommerce form.register').length) {
        setFields();
    }

    /**
     * Send cart data.
     * @todo Only if cart token set up.
     */
    var sendCartData = function() {
        var email = isValidEmail($('#billing_email').val()) ? $('#billing_email').val() : null;

        var data = {
            action: 'metorik_send_cart',
            security: metorik_params.nonce,
            email: email,
        };

        $.post(ajaxurl, data, function(response) {
            //
        });
    };

    var isValidEmail = function(email) {
        return /[^\s@]+@[^\s@]+\.[^\s@]+/.test(email);
    };

    /**
     * Listen for events then send cart data.
     */
    $(document.body).on(
        'added_to_cart removed_from_cart updated_cart_totals updated_shipping_method applied_coupon removed_coupon updated_checkout',
        function(event) {
            sendCartData();
        }
    );

    /**
     * Watch for email input changes.
     */
    var timer;
    $('#billing_email').bind('blur', function(e) {
        var _this = $(this);

        clearTimeout(timer);
        timer = setTimeout(function() {
            if (isValidEmail(_this.val())) {
                sendCartData();
            }
        }, 500);
    });
})(jQuery);
