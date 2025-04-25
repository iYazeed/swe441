// Form validation for login
function validateLoginForm() {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    let isValid = true;
    
    // Reset error messages
    document.getElementById('username-error').textContent = '';
    document.getElementById('password-error').textContent = '';
    
    // Validate username
    if (username.trim() === '') {
        document.getElementById('username-error').textContent = 'Username is required';
        isValid = false;
    }
    
    // Validate password
    if (password.trim() === '') {
        document.getElementById('password-error').textContent = 'Password is required';
        isValid = false;
    }
    
    return isValid;
}

// Form validation for registration
function validateRegisterForm() {
    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    let isValid = true;
    
    // Reset error messages
    document.getElementById('username-error').textContent = '';
    document.getElementById('email-error').textContent = '';
    document.getElementById('password-error').textContent = '';
    document.getElementById('confirm-password-error').textContent = '';
    
    // Validate username
    if (username.trim() === '') {
        document.getElementById('username-error').textContent = 'Username is required';
        isValid = false;
    } else if (username.length < 3) {
        document.getElementById('username-error').textContent = 'Username must be at least 3 characters';
        isValid = false;
    }
    
    // Validate email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email.trim() === '') {
        document.getElementById('email-error').textContent = 'Email is required';
        isValid = false;
    } else if (!emailRegex.test(email)) {
        document.getElementById('email-error').textContent = 'Please enter a valid email address';
        isValid = false;
    }
    
    // Validate password
    if (password.trim() === '') {
        document.getElementById('password-error').textContent = 'Password is required';
        isValid = false;
    } else if (password.length < 6) {
        document.getElementById('password-error').textContent = 'Password must be at least 6 characters';
        isValid = false;
    }
    
    // Validate confirm password
    if (confirmPassword.trim() === '') {
        document.getElementById('confirm-password-error').textContent = 'Please confirm your password';
        isValid = false;
    } else if (password !== confirmPassword) {
        document.getElementById('confirm-password-error').textContent = 'Passwords do not match';
        isValid = false;
    }
    
    return isValid;
}

// Form validation for task creation/update
function validateTaskForm() {
    const title = document.getElementById('title').value;
    const dueDate = document.getElementById('due_date').value;
    let isValid = true;
    
    // Reset error messages
    document.getElementById('title-error').textContent = '';
    document.getElementById('due-date-error').textContent = '';
    
    // Validate title
    if (title.trim() === '') {
        document.getElementById('title-error').textContent = 'Title is required';
        isValid = false;
    }
    
    // Validate due date
    if (dueDate) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const selectedDate = new Date(dueDate);
        
        if (selectedDate < today) {
            document.getElementById('due-date-error').textContent = 'Due date cannot be in the past';
            isValid = false;
        }
    }
    
    return isValid;
}

// Attach event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Login form validation
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            if (!validateLoginForm()) {
                e.preventDefault();
            }
        });
    }
    
    // Register form validation
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            if (!validateRegisterForm()) {
                e.preventDefault();
            }
        });
    }
    
    // Task form validation
    const taskForm = document.getElementById('task-form');
    if (taskForm) {
        taskForm.addEventListener('submit', function(e) {
            if (!validateTaskForm()) {
                e.preventDefault();
            }
        });
    }
});