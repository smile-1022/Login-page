$(document).ready(function () {

    function showAlert(message, type) {
        var $alert = $('#alert-msg');
        $alert.removeClass('alert-success alert-error').addClass('alert-' + type);
        $alert.text(message).css('display', 'block');
    }

    function hideAlert() {
        $('#alert-msg').css('display', 'none');
    }

    $('#btn-forgot').on('click', function () {
        hideAlert();

        var email = $.trim($('#email').val());

        if (!email) {
            showAlert('Please enter your email address.', 'error');
            return;
        }

        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showAlert('Please enter a valid email address.', 'error');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="loading-spinner"></span> Sending...');

        $.ajax({
            url: 'php/forgot-password.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ email: email }),
            success: function (response) {
                if (response.success) {
                    $('#form-section').hide();
                    var resetUrl = window.location.origin + window.location.pathname.replace('forgot-password.html', '') + 'reset-password.html?token=' + response.token;
                    $('#reset-link-anchor').attr('href', resetUrl).text(resetUrl);
                    $('#reset-link-section').show();
                } else {
                    showAlert(response.message || 'Something went wrong.', 'error');
                    $btn.prop('disabled', false).text('Send Reset Link');
                }
            },
            error: function (xhr) {
                var resp = xhr.responseJSON;
                showAlert(resp ? resp.message : 'Server error. Please try again.', 'error');
                $btn.prop('disabled', false).text('Send Reset Link');
            }
        });
    });

    $('#email').on('keypress', function (e) {
        if (e.which === 13) {
            $('#btn-forgot').trigger('click');
        }
    });

});
