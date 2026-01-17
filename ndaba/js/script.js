// Dr LK Ndaba & Partners - JavaScript Functionality

// Generate CAPTCHA on page load
function generateCaptcha() {
    const num1 = Math.floor(Math.random() * 10) + 1;
    const num2 = Math.floor(Math.random() * 10) + 1;
    const correctAnswer = num1 + num2;
    
    document.getElementById('captchaQuestion').textContent = `What is ${num1} + ${num2}?`;
    document.getElementById('captchaCorrect').value = correctAnswer;
}

// Smooth scroll to section
function scrollToSection(id) {
    const element = document.getElementById(id);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Set minimum date to today
function setMinimumDate() {
    const today = new Date().toISOString().split('T')[0];
    const dateInput = document.getElementById('preferredDate');
    if (dateInput) {
        dateInput.setAttribute('min', today);
    }
}

// Form submission handler
document.addEventListener('DOMContentLoaded', function() {
    generateCaptcha();
    setMinimumDate();
    
    const form = document.getElementById('appointmentForm');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }
});

function handleFormSubmit(e) {
    e.preventDefault();
    
    const formMessage = document.getElementById('formMessage');
    const submitBtn = document.querySelector('.btn-submit');
    
    // Validate CAPTCHA
    const userAnswer = parseInt(document.getElementById('captchaAnswer').value);
    const correctAnswer = parseInt(document.getElementById('captchaCorrect').value);
    
    if (userAnswer !== correctAnswer) {
        showMessage(formMessage, 'Security check failed. Please solve the math problem correctly.', 'error');
        generateCaptcha();
        document.getElementById('captchaAnswer').value = '';
        return;
    }
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';
    
    // Submit form via AJAX
    const formData = new FormData(this);
    
    fetch('submit_appointment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(formMessage, data.message, 'success');
            document.getElementById('appointmentForm').reset();
            generateCaptcha();
        } else {
            showMessage(formMessage, data.message, 'error');
        }
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Appointment Request';
    })
    .catch(error => {
        showMessage(formMessage, 'An error occurred. Please try again or contact us directly.', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Appointment Request';
    });
}

function showMessage(element, message, type) {
    element.textContent = message;
    element.className = 'form-message ' + type;
    element.style.display = 'block';
    
    // Scroll to message
    element.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href !== '#') {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    });
});

// Add active state to navigation links on scroll
window.addEventListener('scroll', function() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link');
    
    let current = '';
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.clientHeight;
        if (pageYOffset >= sectionTop - 200) {
            current = section.getAttribute('id');
        }
    });
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href').slice(1) === current) {
            link.classList.add('active');
        }
    });
});
