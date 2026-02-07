document.addEventListener('DOMContentLoaded', () => {
    console.log('main.js loaded');

    /*** 1. Smooth scroll for anchor links ***/
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetID = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetID);
            if (targetElement) targetElement.scrollIntoView({ behavior: 'smooth' });
        });
    });

    /*** 2. Bootstrap tooltips ***/
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
    });

    /*** 3. Pickup & Return Date Logic ***/
    const pickupDateInput = document.getElementById('pickupDate');
    const returnDateInput = document.getElementById('returnDate');
    
    if (pickupDateInput) {
        // Set min date to today for pickup
        const today = new Date().toISOString().split('T')[0];
        pickupDateInput.min = today;

        pickupDateInput.addEventListener('change', function () {
            if (!returnDateInput) return;

            const pickupDate = new Date(this.value);

            // Return date must be at least 1 day after pickup
            pickupDate.setDate(pickupDate.getDate() + 1);
            const minReturn = pickupDate.toISOString().split('T')[0];
            returnDateInput.min = minReturn;

            // Auto-fix return date if invalid
            if (returnDateInput.value && new Date(returnDateInput.value) < pickupDate) {
                returnDateInput.value = minReturn;
            }
        });
    }

    if (returnDateInput) {
        // By default, return date can't be before today
        returnDateInput.min = today;
    }

    /*** 4. Auto-dismiss Bootstrap alerts ***/
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    /*** 5. Back-to-top button ***/
    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            backToTop.classList.toggle('show', window.pageYOffset > 300);
        });
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
});
