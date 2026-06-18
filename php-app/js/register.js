$(document).ready(function () {

    function showAlert(message, type) {
        var $alert = $('#alert-msg');
        $alert.removeClass('alert-success alert-error').addClass('alert-' + type);
        $alert.text(message).css('display', 'block');
    }

    function hideAlert() {
        $('#alert-msg').css('display', 'none');
    }

    $('#btn-register').on('click', function () {
        hideAlert();

        var firstName = $.trim($('#first_name').val());
        var lastName  = $.trim($('#last_name').val());
        var email     = $.trim($('#email').val());
        var password  = $('#password').val();
        var agreed    = $('#terms').is(':checked');

        if (!firstName || !lastName || !email || !password) {
            showAlert('Please fill in all fields.', 'error');
            return;
        }

        if (!agreed) {
            showAlert('Please agree to the processing of personal data.', 'error');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="loading-spinner"></span> Signing up...');

        $.ajax({
            url: 'php/register.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                first_name: firstName,
                last_name:  lastName,
                email:      email,
                password:   password
            }),
            success: function (response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    setTimeout(function () {
                        window.location.href = 'login.html';
                    }, 1500);
                } else {
                    showAlert(response.message || 'Registration failed.', 'error');
                    $btn.prop('disabled', false).text('Sign Up');
                }
            },
            error: function (xhr) {
                var resp = xhr.responseJSON;
                showAlert(resp ? resp.message : 'Server error. Please try again.', 'error');
                $btn.prop('disabled', false).text('Sign Up');
            }
        });
    });

    $('#password').on('keypress', function (e) {
        if (e.which === 13) {
            $('#btn-register').trigger('click');
        }
    });

});
