/**
 * Frontend JavaScript for Smart WhatsApp Chat Widget
 * Hierarchical FAQ Chatbot System
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

        // Get FAQ tree data from the inline script (set by PHP)
        let faqTree = [];
        if (typeof swcwFaqTree !== 'undefined' && Array.isArray(swcwFaqTree)) {
            faqTree = swcwFaqTree;
        }

        /**
         * Get current time string
         */
        const getTimeStr = () => {
            const now = new Date();
            return now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
        };

        /**
         * Create typing indicator
         */
        const createTypingIndicator = () => {
            const typing = document.createElement('div');
            typing.className = 'swcw-typing-indicator';
            typing.innerHTML = '<span></span><span></span><span></span>';
            bodyEl.appendChild(typing);
            bodyEl.scrollTop = bodyEl.scrollHeight;
            return typing;
        };

        /**
         * Create a user message bubble (right side, green)
         */
        const createUserBubble = (text) => {
            const userMsg = document.createElement('div');
            userMsg.className = 'swcw-user-msg';
            userMsg.innerHTML = `${text}<span class="swcw-time">${getTimeStr()}</span>`;
            bodyEl.appendChild(userMsg);
            bodyEl.scrollTop = bodyEl.scrollHeight;
        };

        /**
         * Create a bot reply bubble (left side, white)
         */
        const createBotBubble = (text) => {
            const bot = document.createElement('div');
            bot.className = 'swcw-bot-reply';
            bot.innerHTML = `${text}<span class="swcw-time">${getTimeStr()}</span>`;
            bodyEl.appendChild(bot);
            bodyEl.scrollTop = bodyEl.scrollHeight;
            return bot;
        };

        /**
         * Render FAQ chips for a given array of FAQ nodes
         */
        const renderFaqChips = (nodes) => {
            if (!nodes || nodes.length === 0) return;

            const container = document.createElement('div');
            container.className = 'swcw-faq-container swcw-faq-dynamic';

            nodes.forEach((node, index) => {
                const chip = document.createElement('button');
                chip.className = 'swcw-faq-chip';
                chip.setAttribute('data-faq-id', node.id);
                chip.textContent = node.question;
                container.appendChild(chip);
            });

            bodyEl.appendChild(container);
            bodyEl.scrollTop = bodyEl.scrollHeight;
        };

        /**
         * Find a node in the FAQ tree by its ID
         */
        const findNodeById = (nodes, id) => {
            for (const node of nodes) {
                if (node.id === id) return node;
                if (node.children && node.children.length > 0) {
                    const found = findNodeById(node.children, id);
                    if (found) return found;
                }
            }
            return null;
        };

        /**
         * Remove all dynamic FAQ chip containers (so old ones disappear)
         */
        const removeOldChips = () => {
            const oldChips = bodyEl.querySelectorAll('.swcw-faq-dynamic');
            oldChips.forEach(el => el.remove());
        };

        /**
         * Handle FAQ chip click — the core chatbot interaction
         */
        const handleFAQClick = (e) => {
            const chip = e.target.closest('.swcw-faq-chip');
            if (!chip) return;

            const faqId = chip.getAttribute('data-faq-id');
            const question = chip.textContent;
            
            // If it's the old flat FAQ format (no tree data)
            if (!faqId && chip.hasAttribute('data-answer')) {
                const answer = chip.getAttribute('data-answer');
                createUserBubble(question);
                removeOldChips();
                const typingEl = createTypingIndicator();
                setTimeout(() => {
                    typingEl.remove();
                    createBotBubble(answer);
                }, 800);
                return;
            }

            if (!faqId) return;

            // Find the node in the tree
            const node = findNodeById(faqTree, faqId);
            if (!node) return;

            // 1. Remove old dynamic chips
            removeOldChips();

            // 2. Show user question bubble
            createUserBubble(question);

            // 3. Show typing indicator, then bot answer + sub-chips
            const typingEl = createTypingIndicator();

            setTimeout(() => {
                typingEl.remove();

                // 4. Show bot answer
                createBotBubble(node.answer);

                // 5. If there are children, show them as new chips
                if (node.children && node.children.length > 0) {
                    setTimeout(() => {
                        renderFaqChips(node.children);
                    }, 300);
                }

                bodyEl.scrollTop = bodyEl.scrollHeight;
            }, 800);
        };

        bodyEl.addEventListener('click', handleFAQClick);

        /**
         * Render the initial root-level FAQ chips (if tree data exists)
         */
        if (faqTree.length > 0) {
            // Remove any server-rendered flat FAQ chips
            const existingFaqContainers = bodyEl.querySelectorAll('.swcw-faq-container');
            existingFaqContainers.forEach(el => el.remove());

            // Render root chips
            renderFaqChips(faqTree);
        }

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
         * Send Message via WhatsApp
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
                if (!windowEl.classList.contains('swcw-active')) {
                    openWidget();
                }
            }, delay * 1000);
        }
    });
})();
