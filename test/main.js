// main.js — core frontend interactions

document.addEventListener('DOMContentLoaded', () => {
    console.log('main.js loaded');

    //smooth scrool for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetID = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetID);
            if (targetElement) {
                targetElement.scrollIntoView({ behavior: 'smooth' });
            } else {
                console.warn(`Element with ID ${targetID} not found.`);
            }
        });
    });

    // Example: Initialize tooltips if using Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // Placeholder for later JS features
  // e.g. car filters, booking AJAX, gallery zoom, etc.

});