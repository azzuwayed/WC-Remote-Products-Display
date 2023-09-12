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
    // Common function to display error messages
    function displayErrorMessage(element, message) {
        element.text(message).addClass('has-error');
    }

    async function postData(url, data) {
        return $.ajax({
            type: "POST",
            url: url,
            data: data
        });
    }

    $('#api-connection-settings-form').on('submit', async function (e) {
        e.preventDefault();
        let isValid = true;

        // Client-side validation
        $(this).find('input[required]').each(function () {
            const fieldName = $(this).attr('id');
            const fieldError = $(`.error-feedback[data-for="${fieldName}"]`);
            if (!$(this).val()) {
                isValid = false;
                fieldError.text('This field is required.').show();
            } else {
                fieldError.text('').hide();
            }
        });

        if (!isValid) {
            $('.global-error-message').text('Please fill out all required fields.');
            return;
        }

        // Start AJAX request for saving API Connection settings
        const formData = $(this).serializeArray().reduce((obj, item) => {
            obj[item.name] = item.value;
            return obj;
        }, {});

        // Show spinner and disable save button
        $('.save-spinner').show();
        $('input[type="submit"]').addClass('saving').prop('disabled', true);

        try {
            const response = await postData(woorpd_ajax_object.ajax_url, {
                action: 'save_woorpd_options',
                nonce: $('input[name="nonce"]').val(),
                ...formData
            });

            if (response.success) {
                $('.server-error-feedback').text('').hide();
                $('.save-success').show().delay(1000).fadeOut();
            } else {
                if (response.data.field_errors) {
                    Object.keys(response.data.field_errors).forEach(fieldName => {
                        const errorMessage = response.data.field_errors[fieldName];
                        $(`.server-error-feedback[data-for="${fieldName}"]`).text(errorMessage).show();
                    });
                }
                displayErrorMessage($('.error-message'), 'An error occurred: ' + response.data);
            }
        } catch (error) {
            displayErrorMessage($('.error-message'), error.statusText || 'Unknown error');
        } finally {
            // Hide spinner and re-enable save button
            $('.save-spinner').hide();
            $('input[type="submit"]').removeClass('saving').prop('disabled', false);
        }
    });

    // Reset settings
    $('#woorpd-reset-settings-button').on('click', async function (e) {
        e.preventDefault();
        
        try {
            const response = await postData(woorpd_ajax_object.ajax_url, {
                action: 'woorpd_delete_options',
                nonce: $('input[name="nonce"]').val()
            });

            if (response.success) {
                $('.server-error-feedback').text('').hide();
                $('input[type="text"]').val('');
            } else {
                displayErrorMessage($('.error-message'), 'An error occurred: ' + response.data);
            }
        } catch (error) {
            displayErrorMessage($('.error-message'), error.statusText || 'Unknown error');
        }
    });
})(jQuery);