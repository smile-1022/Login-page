$(document).ready(function () {

    var token = localStorage.getItem('guvi_token');
    var userStr = localStorage.getItem('guvi_user');

    if (!token) {
        window.location.href = 'login.html';
        return;
    }

    var user = userStr ? JSON.parse(userStr) : {};
    var currentProfile = {};
    var editingField = '';

    function showAlert(message, type) {
        var $alert = $('#alert-msg');
        $alert.removeClass('alert-success alert-error').addClass('alert-' + type);
        $alert.text(message).css('display', 'block');
        setTimeout(function () { $alert.css('display', 'none'); }, 3000);
    }

    function setProfileHeader() {
        var fullName = (user.first_name || '') + ' ' + (user.last_name || '');
        $('#profile-name').text(fullName.trim() || 'User');
        $('#profile-email').text(user.email || '');
        var initials = ((user.first_name || 'U')[0] + (user.last_name || '')[0]).toUpperCase();
        $('#profile-avatar').text(initials || 'U');
    }

    function setProfileFields(profile) {
        $('#val-age').text(profile.age || '—');
        $('#val-dob').text(profile.dob || '—');
        $('#val-contact').text(profile.contact || '—');
        $('#val-city').text(profile.city || '—');
        $('#val-address').text(profile.address || '—');
        $('#val-bio').text(profile.bio || '—');
    }

    function loadProfile() {
        $.ajax({
            url: 'php/profile.php',
            type: 'GET',
            headers: { 'Authorization': 'Bearer ' + token },
            success: function (response) {
                if (response.success) {
                    currentProfile = response.profile || {};
                    setProfileHeader();
                    setProfileFields(currentProfile);
                } else {
                    window.location.href = 'login.html';
                }
            },
            error: function (xhr) {
                if (xhr.status === 401) {
                    localStorage.removeItem('guvi_token');
                    localStorage.removeItem('guvi_user');
                    window.location.href = 'login.html';
                }
            }
        });
    }

    setProfileHeader();
    loadProfile();

    $('.btn-edit').on('click', function () {
        editingField = $(this).data('field');
        var label    = $(this).data('label');
        var type     = $(this).data('type');

        $('#edit-modal-title').text('Edit ' + label);
        $('#edit-modal-label').text(label);
        $('#edit-modal-input')
            .attr('type', type)
            .val(currentProfile[editingField] || '');
        $('#edit-modal-overlay').css('display', 'flex');
        setTimeout(function () { $('#edit-modal-input').focus(); }, 100);
    });

    $('#btn-modal-cancel').on('click', function () {
        $('#edit-modal-overlay').css('display', 'none');
        editingField = '';
    });

    $('#edit-modal-overlay').on('click', function (e) {
        if ($(e.target).is('#edit-modal-overlay')) {
            $('#edit-modal-overlay').css('display', 'none');
            editingField = '';
        }
    });

    $('#btn-modal-save').on('click', function () {
        if (!editingField) return;

        var value = $.trim($('#edit-modal-input').val());
        if (!value) {
            return;
        }

        var updateData = {};
        updateData[editingField] = value;

        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="loading-spinner"></span> Saving...');

        $.ajax({
            url: 'php/profile.php',
            type: 'PUT',
            contentType: 'application/json',
            headers: { 'Authorization': 'Bearer ' + token },
            data: JSON.stringify(updateData),
            success: function (response) {
                if (response.success) {
                    currentProfile[editingField] = value;
                    setProfileFields(currentProfile);
                    $('#edit-modal-overlay').css('display', 'none');
                    editingField = '';
                    showAlert('Profile updated successfully!', 'success');
                } else {
                    showAlert(response.message || 'Update failed.', 'error');
                }
                $btn.prop('disabled', false).text('Save');
            },
            error: function (xhr) {
                var resp = xhr.responseJSON;
                showAlert(resp ? resp.message : 'Server error.', 'error');
                $btn.prop('disabled', false).text('Save');
            }
        });
    });

    $('#btn-logout').on('click', function () {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Logging out...');

        $.ajax({
            url: 'php/logout.php',
            type: 'POST',
            headers: { 'Authorization': 'Bearer ' + token },
            complete: function () {
                localStorage.removeItem('guvi_token');
                localStorage.removeItem('guvi_user');
                window.location.href = 'login.html';
            }
        });
    });

});
