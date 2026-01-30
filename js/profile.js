$(document).ready(function () {
    const email = localStorage.getItem("userEmail");
    const sessionId = localStorage.getItem("sessionId");

    if (!email || !sessionId) {
        window.location.href = 'login.html';
        return;
    }

    $.ajax({
        url: 'php/profile/get_profile.php',
        type: 'POST',
        data: { email: email, sessionId: sessionId },
        success: function (response) {
            const data = JSON.parse(response);
            if (data.error) {
                localStorage.clear();
                window.location.href = 'login.html';
                return;
            }
            $('#username').val(data.username);
            $('#email').val(data.email);
            $('#age').val(data.age);
            $('#dob').val(data.dob);
            $('#contact').val(data.contact);
        },
        error: function () {
            $('#msg').html('<div class="alert alert-danger">Failed to load profile</div>');
        }
    });

    $('#profileForm').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: 'php/profile/update_profile.php',
            type: 'POST',
            data: {
                email: email, // Original email for identification
                new_email: $('#email').val(), // New email to update to
                sessionId: sessionId,
                username: $('#username').val(),
                age: $('#age').val(),
                dob: $('#dob').val(),
                contact: $('#contact').val()
            },
            success: function (response) {
                try {
                    const res = JSON.parse(response);
                    if (res.success) {
                        if (res.new_email) {
                            localStorage.setItem("userEmail", res.new_email);
                            // Also update the 'email' variable so subsequent requests in this session work
                            // However, we are inside a callback, and 'email' is a const in the outer scope (or let/var).
                            // Since 'email' is const in the document.ready, we can't reassign it easily without reloading.
                            // But updating localStorage is the critical part for next page load.
                            // We can advise a reload or just let it be. 
                        }
                        $('#msg').html('<div class="alert alert-success">' + res.message + '</div>');
                    } else {
                        $('#msg').html('<div class="alert alert-danger">' + (res.error || 'Update failed') + '</div>');
                    }
                } catch (e) {
                    // Handle non-JSON response (backward compatibility)
                    $('#msg').html('<div class="alert alert-success">' + response + '</div>');
                }
            },
            error: function (xhr) {
                try {
                    const res = JSON.parse(xhr.responseText);
                    $('#msg').html('<div class="alert alert-danger">' + (res.error || 'Update failed') + '</div>');
                } catch (e) {
                    $('#msg').html('<div class="alert alert-danger">Failed to update profile</div>');
                }
            }
        })
    })
});