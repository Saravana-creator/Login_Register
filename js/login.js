$(document).ready(function() {
    $('#loginForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: 'php/auth/login.php',
            type: 'POST',
            data: {
                email: $('#email').val(),
                password: $('#password').val()
            },
            success: function(response) {
                let res = JSON.parse(response);
                if(res.status === 'success'){
                    localStorage.setItem('userEmail', $('#email').val());
                    localStorage.setItem('sessionId', res.sessionId);
                    window.location.href = 'profile.html';
                } else {
                    $('#msg').html('<div class="alert alert-danger">'+res.message+'</div>');
                }
            },
            error: function() {
                $('#msg').html('<div class="alert alert-danger">Login failed. Please try again.</div>');
            }
        });
    });
});
