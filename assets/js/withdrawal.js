jQuery(document).ready(function ($) {
    // Organization Details Check
    if ($('#is_organization').length) {
        let checkbox = $('#is_organization')

        function is_organization(element) {
            if (element.is(':checked')) {
                $('form #organization-details').show()
                $('form .toggle-id-number').hide()
            } else {
                $('form #organization-details').hide()
                $('form .toggle-id-number').show()
            }
        }

        checkbox.on("click", function () {
            is_organization(checkbox)
        })

        is_organization(checkbox)
    }

    // Banking Details Check
    if ($('#change_details').length) {
        let checkbox = $('#change_details')

        function change_details(element) {
            if (element.is(':checked')) {
                $('form #banking-details').show()
            } else {
                $('form #banking-details').hide()
            }
        }

        checkbox.on("click", function () {
            change_details(checkbox)
        })

        change_details(checkbox)
    }
})