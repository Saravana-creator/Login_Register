$(document).ready(function() {
    $('#registerForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: 'php/auth/register.php',
            type: 'POST',
            data: {
                username: $('#username').val(),
                email: $('#email').val(),
                password: $('#password').val()
            },
            success: function(response) {
                $('#msg').html(response);
                if(response.includes('successful')) {
                    $('#registerForm')[0].reset();
                }
            },
            error: function() {
                $('#msg').html('<div class="alert alert-danger">Registration failed. Please try again.</div>');
            }
        });
    });
});
