function openTab(tabName, elmnt) {
    // Hide all tab contents
    var tabcontent = document.getElementsByClassName("tabcontent");
    for (var i = 0; i < tabcontent.length; i++) {
        tabcontent[i].classList.remove("active-content");
    }

    // Deactivate all tab links
    var tablinks = document.getElementsByClassName("tablink");
    for (var i = 0; i < tablinks.length; i++) {
        tablinks[i].classList.remove("active");
    }

    // Show the selected tab content and activate the tab link
    document.getElementById(tabName).classList.add("active-content");
    elmnt.classList.add("active");
}

(function ($) {
    // Set the default tab to be displayed
    openTab('API Connection', $('.tablink[data-tab="API Connection"]')[0]);

    // Form validation
    $('#api-connection-settings-form').on('submit', function (e) {
        let isValid = true;
        $(this).find('input[required]').each(function () {
            if (!$(this).val()) {
                isValid = false;
                const fieldName = $(this).attr('id');
                $(`.error-feedback[data-for="${fieldName}"]`).text('This field is required.').show();
            } else {
                const fieldName = $(this).attr('id');
                $(`.error-feedback[data-for="${fieldName}"]`).text('').hide();
            }
        });

        if (!isValid) {
            e.preventDefault();
            $('.global-error-message').text('Please fill out all required fields.');
        } else {
            $('.global-error-message').text('');
        }
    });
    // AJAX for saving API Connection settings
    $('#api-connection-settings-form').on('submit', function (e) {
        e.preventDefault();

        // Show spinner and disable save button
        $('.save-spinner').show();
        $('input[type="submit"]').addClass('saving').prop('disabled', true);

        // Hide any previous error messages
        $('.error-message').text('').removeClass('has-error');

        $.post(woorpd_ajax_object.ajax_url, {
            action: 'save_woorpd_options',
            nonce: $('input[name="nonce"]').val(),
            ...$(this).serializeArray().reduce((obj, item) => {
                obj[item.name] = item.value;
                return obj;
            }, {})
        }, function (response) {
            // Hide spinner, show checkmark, and re-enable save button
            $('.save-spinner').hide();
            if (response.success) {
                $('.save-success').show().delay(1000).fadeOut();
            } else {
                // Display the error message and show the error icon
                $('.error-message').text('An error occurred: ' + response.data).addClass('has-error');
            }
            $('input[type="submit"]').removeClass('saving').prop('disabled', false);
        });
    });
    // AJAX for resetting API Connection settings
    $('#woorpd-reset-settings-button').on('click', function (e) {
        e.preventDefault();

        // Hide any previous error messages
        $('.error-message').text('').removeClass('has-error');

        $.post(woorpd_ajax_object.ajax_url, {
            action: 'woorpd_delete_options',
            nonce: $('input[name="nonce"]').val()
        }, function (response) {
            if (response.success) {
                // Clear the form fields and show a success message
                $('input[name="woorpd_api_woo_url"]').val('');
                $('input[name="woorpd_api_woo_ck"]').val('');
                $('input[name="woorpd_api_woo_cs"]').val('');
            } else {
                // Display the error message and show the error icon
                $('.error-message').text('An error occurred: ' + response.data).addClass('has-error');
            }
        });
    });
})(jQuery);
