/**
 * Date and Time Restrictions
 * Prevents users from selecting past dates and times
 */

(function() {
    'use strict';

    // Get today's date in YYYY-MM-DD format
    function getTodayDateString() {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Get current time in HH:MM format
    function getCurrentTimeString() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        return `${hours}:${minutes}`;
    }

    // Apply min date restriction to all date inputs
    function applyDateRestrictions() {
        const todayString = getTodayDateString();
        const dateInputs = document.querySelectorAll('input[type="date"]');
        
        dateInputs.forEach(input => {
            // Skip report date range filters (date_from can be past, only date_to is restricted)
            const fieldName = input.getAttribute('name');
            if (fieldName === 'date_from' || fieldName === 'from_date') {
                return; // Allow past dates for "from" filters
            }

            // Set minimum date to today for all other date inputs
            input.setAttribute('min', todayString);
            
            // Add change event to validate and correct invalid dates
            input.addEventListener('change', function() {
                const selectedDate = this.value;
                if (selectedDate && selectedDate < todayString) {
                    this.value = todayString;
                    showDateWarning(this);
                }
            });

            // Add blur event as additional validation
            input.addEventListener('blur', function() {
                const selectedDate = this.value;
                if (selectedDate && selectedDate < todayString) {
                    this.value = todayString;
                    showDateWarning(this);
                }
            });

            console.log('[DateRestrictions] Applied to field:', fieldName);
        });
    }

    // Apply min time restriction to time inputs (only for today)
    function applyTimeRestrictions() {
        const dateInputs = document.querySelectorAll('input[type="date"]');
        const timeInputs = document.querySelectorAll('input[type="time"]');
        const todayString = getTodayDateString();
        const currentTimeString = getCurrentTimeString();

        timeInputs.forEach(timeInput => {
            // Find associated date input (usually nearby in the DOM)
            const dateInput = findNearbyDateInput(timeInput);
            
            if (dateInput) {
                const updateTimeRestriction = () => {
                    const selectedDate = dateInput.value;
                    
                    if (selectedDate && selectedDate === todayString) {
                        // For today's date, restrict to current time or later
                        timeInput.setAttribute('min', currentTimeString);
                        console.log('[DateRestrictions] Time restricted to today:', currentTimeString);
                    } else if (selectedDate && selectedDate > todayString) {
                        // For future dates, allow any time
                        timeInput.removeAttribute('min');
                    }
                };

                // Apply on page load
                updateTimeRestriction();

                // Update when date changes
                dateInput.addEventListener('change', updateTimeRestriction);
                
                // Validate time on change
                timeInput.addEventListener('change', function() {
                    const selectedDate = dateInput.value;
                    const selectedTime = this.value;
                    
                    if (selectedDate === todayString && selectedTime < currentTimeString) {
                        this.value = currentTimeString;
                        showTimeWarning(this);
                    }
                });
            }
        });
    }

    // Helper: Find nearby date input relative to a time input
    function findNearbyDateInput(timeInput) {
        // Check same parent
        let dateInput = timeInput.parentElement.querySelector('input[type="date"]');
        if (dateInput) return dateInput;

        // Check grandparent
        if (timeInput.parentElement.parentElement) {
            dateInput = timeInput.parentElement.parentElement.querySelector('input[type="date"]');
            if (dateInput) return dateInput;
        }

        // Search form for date input
        const form = timeInput.closest('form');
        if (form) {
            dateInput = form.querySelector('input[type="date"]');
            if (dateInput) return dateInput;
        }

        return null;
    }

    // Show warning toast when user tries to select past date
    function showDateWarning(element) {
        if (element.dataset.warningShown) return; // Prevent duplicate warnings
        
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-amber-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center animate-fade-in';
        toast.innerHTML = `
            <i class="fas fa-calendar-times mr-3 text-lg"></i>
            <span class="font-medium">Past dates are not allowed. Using today's date.</span>
        `;
        
        document.body.appendChild(toast);
        element.dataset.warningShown = true;
        
        setTimeout(() => {
            toast.remove();
            delete element.dataset.warningShown;
        }, 3000);
    }

    // Show warning toast when user tries to select past time
    function showTimeWarning(element) {
        if (element.dataset.warningShown) return;
        
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-amber-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center animate-fade-in';
        toast.innerHTML = `
            <i class="fas fa-clock mr-3 text-lg"></i>
            <span class="font-medium">Past times are not allowed for today. Using current time.</span>
        `;
        
        document.body.appendChild(toast);
        element.dataset.warningShown = true;
        
        setTimeout(() => {
            toast.remove();
            delete element.dataset.warningShown;
        }, 3000);
    }

    // Initialize on DOM ready
    function init() {
        console.log('[DateRestrictions] Initializing date and time restrictions...');
        
        // Apply immediately if DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                applyDateRestrictions();
                applyTimeRestrictions();
            });
        } else {
            applyDateRestrictions();
            applyTimeRestrictions();
        }

        // Also watch for dynamically added inputs
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    applyDateRestrictions();
                    applyTimeRestrictions();
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        console.log('[DateRestrictions] Initialization complete');
    }

    // Start when ready
    init();
})();
