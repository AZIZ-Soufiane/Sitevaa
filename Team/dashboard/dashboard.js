document.addEventListener('DOMContentLoaded', function() {
    const contentArea = document.getElementById('content-area');
    const navLinks = document.getElementsByClassName('nav-links')[0].getElementsByTagName('a');
    const pageTitle = document.getElementById('page-title');
    const userRole = document.querySelector('.user-role');
    let isAdmin = false;

    // Check if admin
    if (userRole && userRole.textContent == 'admin') {
        isAdmin = true;
        loadStats();
    }

    // Load default page
    if (isAdmin) {
        loadPage('overview');
    } else {
        loadPage('tasks');
    }

    // Add click handlers to navigation
    for (let i = 0; i < navLinks.length; i++) {
        navLinks[i].onclick = function(e) {
            e.preventDefault();
            const page = this.getAttribute('data-page');
            
            // Check admin pages
            if ((page == 'task-management' || page == 'projects') && !isAdmin) {
                showError('Access denied: Admin only feature');
                return;
            }
            
            // Remove active class from all links
            for (let j = 0; j < navLinks.length; j++) {
                navLinks[j].classList.remove('active');
            }
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // Update title
            pageTitle.textContent = page.charAt(0).toUpperCase() + page.slice(1);
            
            // Load page
            loadPage(page);
        };
    }

    function showError(message) {
        const error = document.createElement('div');
        error.className = 'error-message';
        error.textContent = message;
        contentArea.insertBefore(error, contentArea.firstChild);
        
        setTimeout(function() {
            error.remove();
        }, 3000);
    }
});