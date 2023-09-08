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

(function($) {
    // Set the default tab to be displayed
    openTab('API Connection', $('.tablink[data-tab="API Connection"]')[0]);

    // AJAX for saving API Connection settings
    $('#api-connection-settings-form').on('submit', function(e) {
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
        }, function(response) {
            // Hide spinner, show checkmark, and re-enable save button
            $('.save-spinner').hide();
            if (response.success) {
                $('.save-success').show().delay(2000).fadeOut();
            } else {
                // Display the error message and show the error icon
                $('.error-message').text('An error occurred: ' + response.data).addClass('has-error');
            }
            $('input[type="submit"]').removeClass('saving').prop('disabled', false);
        });
    });
})(jQuery);
