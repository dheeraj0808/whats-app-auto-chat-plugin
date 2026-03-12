/**
 * Frontend JavaScript for Smart WhatsApp Chat Widget
 */

(function() {
    document.addEventListener('DOMContentLoaded', function() {
        const trigger = document.getElementById('swcwTrigger');
        const windowEl = document.getElementById('swcwWindow');
        const closeBtn = document.getElementById('swcwClose');
        const sendBtn = document.getElementById('swcwSend');
        const input = document.getElementById('swcwInput');

        if (!trigger || !windowEl || !closeBtn || !sendBtn || !input) return;

        /**
         * Open Widget
         */
        const openWidget = () => {
            windowEl.style.display = 'flex';
            setTimeout(() => {
                windowEl.classList.add('swcw-active');
                trigger.classList.add('swcw-hidden');
            }, 10);
        };

        /**
         * Close Widget
         */
        const closeWidget = () => {
            windowEl.classList.remove('swcw-active');
            setTimeout(() => {
                windowEl.style.display = 'none';
                trigger.classList.remove('swcw-hidden');
            }, 300);
        };

        /**
         * Send Message
         */
        const sendMessage = () => {
            const message = input.value.trim();
            const number = sendBtn.getAttribute('data-number');

            if (message && number) {
                const cleanNumber = number.replace(/\D/g, '');
                const url = `https://wa.me/${cleanNumber}?text=${encodeURIComponent(message)}`;
                window.open(url, '_blank');
            }
        };

        // Event Listeners
        trigger.addEventListener('click', openWidget);
        closeBtn.addEventListener('click', closeWidget);
        sendBtn.addEventListener('click', sendMessage);
        
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Auto Open Logic
        if (typeof swcwRemote !== 'undefined' && swcwRemote.autoOpen === 'yes') {
            const delay = parseInt(swcwRemote.delay) || 0;
            setTimeout(() => {
                // Only open if not already interacted with (optional polish)
                if (!windowEl.classList.contains('swcw-active')) {
                    openWidget();
                }
            }, delay * 1000);
        }
    });
})();
