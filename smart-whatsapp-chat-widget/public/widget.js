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
        const bodyEl = document.getElementById('swcwBody');

        if (!trigger || !windowEl || !closeBtn || !sendBtn || !input || !bodyEl) return;

        /**
         * Handle FAQ Clicks
         */
        const handleFAQClick = (e) => {
            const chip = e.target.closest('.swcw-faq-chip');
            if (!chip) return;

            const answer = chip.getAttribute('data-answer');
            const question = chip.textContent;
            if (!answer) return;

            const now = new Date();
            const timeStr = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');

            // 1. Create User Question Bubble (on right)
            const userMsg = document.createElement('div');
            userMsg.className = 'swcw-user-msg';
            userMsg.innerHTML = `
                ${question}
                <span class="swcw-time">${timeStr}</span>
            `;
            bodyEl.appendChild(userMsg);

            // 2. Create Bot Reply Bubble (on left)
            setTimeout(() => {
                const botReply = document.createElement('div');
                botReply.className = 'swcw-bot-reply';
                botReply.innerHTML = `
                    ${answer}
                    <span class="swcw-time">${timeStr}</span>
                `;
                bodyEl.appendChild(botReply);
                bodyEl.scrollTop = bodyEl.scrollHeight;
            }, 600); // Small delay for "typing" effect
            
            // Scroll to bottom
            bodyEl.scrollTop = bodyEl.scrollHeight;
        };

        bodyEl.addEventListener('click', handleFAQClick);

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
