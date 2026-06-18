$(document).ready(function () {

    if (localStorage.getItem('guvi_token')) {
        window.location.href = 'profile.html';
    }

    function showAlert(message, type) {
        var $alert = $('#alert-msg');
        $alert.removeClass('alert-success alert-error').addClass('alert-' + type);
        $alert.text(message).css('display', 'block');
    }

    function hideAlert() {
        $('#alert-msg').css('display', 'none');
    }

    $('#btn-login').on('click', function () {
        hideAlert();

        var email    = $.trim($('#email').val());
        var password = $('#password').val();

        if (!email || !password) {
            showAlert('Please enter your email and password.', 'error');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="loading-spinner"></span> Logging in...');

        $.ajax({
            url: 'php/login.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                email:    email,
                password: password
            }),
            success: function (response) {
                if (response.success) {
                    localStorage.setItem('guvi_token', response.token);
                    localStorage.setItem('guvi_user', JSON.stringify(response.user));
                    showAlert('Login successful! Redirecting...', 'success');
                    setTimeout(function () {
                        window.location.href = 'profile.html';
                    }, 800);
                } else {
                    showAlert(response.message || 'Login failed.', 'error');
                    $btn.prop('disabled', false).text('Login');
                }
            },
            error: function (xhr) {
                var resp = xhr.responseJSON;
                showAlert(resp ? resp.message : 'Server error. Please try again.', 'error');
                $btn.prop('disabled', false).text('Login');
            }
        });
    });

    $('#password').on('keypress', function (e) {
        if (e.which === 13) {
            $('#btn-login').trigger('click');
        }
    });

});
