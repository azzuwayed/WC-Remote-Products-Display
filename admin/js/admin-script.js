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
})(jQuery);
