/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
import './listeners/global-notifications';
import './listeners/resource-updates';

// Auto-init typeahead widgets present on the page
import Typeahead from './modules/typeahead';

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-typeahead="users"]').forEach(el => {
        const hiddenIdSelector = el.dataset.targetId || el.getAttribute('data-target-id');
        const hiddenEl = hiddenIdSelector ? document.getElementById(hiddenIdSelector) : null;
        const instance = new Typeahead(el, {
            fetchUrl: el.dataset.fetchUrl || '/api/users/search?q=',
            minChars: 2,
            onSelect: (item) => {
                if (hiddenEl) hiddenEl.value = item.id;
            }
        });
        // Clear hidden id if user manually edits after selection
        el.addEventListener('input', function() {
            // if current input doesn't match selected name, clear the id
            if (hiddenEl && hiddenEl.value && this.value !== '') {
                // Only clear when it's clearly different (no exact match from items)
                const match = instance.items.find(i => i.name === this.value);
                if (!match) hiddenEl.value = '';
            }
        });
    });
});