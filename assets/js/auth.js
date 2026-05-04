// assets/js/auth.js=

document.addEventListener('DOMContentLoaded', function() {
    
    const confirmLogoutBtn = document.getElementById('confirmLogoutBtn');

    if (confirmLogoutBtn) {
        confirmLogoutBtn.addEventListener('click', function() {
            
            // Visual feedback
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging out...';
            this.disabled = true;

            // AJAX request to backend
            fetch('../api/logout_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Redirect to login page
                    window.location.href = data.redirect;
                } else {
                    alert('Error logging out. Please try again.');
                    this.innerHTML = 'Yes, Log Out';
                    this.disabled = false;
                }
            })
            .catch(error => {
                console.error('Logout Error:', error);
                alert('A network error occurred.');
                this.innerHTML = 'Yes, Log Out';
                this.disabled = false;
            });
        });
    }
});