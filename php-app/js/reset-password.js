$(document).ready(function () {

    var urlParams = new URLSearchParams(window.location.search);
    var token = urlParams.get('token');

    function showAlert(message, type) {
        var $alert = $('#alert-msg');
        $alert.removeClass('alert-success alert-error').addClass('alert-' + type);
        $alert.text(message).css('display', 'block');
    }

    function hideAlert() {
        $('#alert-msg').css('display', 'none');
    }

    if (!token) {
        $('#form-section').hide();
        $('#expired-section').show();
        return;
    }

    $.ajax({
        url: 'php/reset-password.php',
        type: 'GET',
        data: { token: token },
        success: function (response) {
            if (!response.valid) {
                $('#form-section').hide();
                $('#expired-section').show();
            }
        },
        error: function () {
            $('#form-section').hide();
            $('#expired-section').show();
        }
    });

    $('#btn-reset').on('click', function () {
        hideAlert();

        var newPassword     = $('#new-password').val();
        var confirmPassword = $('#confirm-password').val();

        if (!newPassword || !confirmPassword) {
            showAlert('Please fill in both password fields.', 'error');
            return;
        }

        if (newPassword.length < 6) {
            showAlert('Password must be at least 6 characters.', 'error');
            return;
        }

        if (newPassword !== confirmPassword) {
            showAlert('Passwords do not match.', 'error');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="loading-spinner"></span> Resetting...');

        $.ajax({
            url: 'php/reset-password.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ token: token, new_password: newPassword }),
            success: function (response) {
                if (response.success) {
                    showAlert('Password reset successfully! Redirecting to login...', 'success');
                    $('#form-section input').prop('disabled', true);
                    $btn.prop('disabled', true).text('Done');
                    setTimeout(function () {
                        window.location.href = 'login.html';
                    }, 2000);
                } else {
                    showAlert(response.message || 'Reset failed.', 'error');
                    $btn.prop('disabled', false).text('Reset Password');
                }
            },
            error: function (xhr) {
                var resp = xhr.responseJSON;
                showAlert(resp ? resp.message : 'Server error. Please try again.', 'error');
                $btn.prop('disabled', false).text('Reset Password');
            }
        });
    });

    $('#confirm-password').on('keypress', function (e) {
        if (e.which === 13) {
            $('#btn-reset').trigger('click');
        }
    });

});
