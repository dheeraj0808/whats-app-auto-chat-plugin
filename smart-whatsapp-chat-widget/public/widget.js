/**
 * Smart WhatsApp Chat Widget — Frontend Engine
 *
 * Pure Vanilla JS (NO jQuery).
 * Implements:
 *   - Hierarchical FAQ chatbot (tree traversal)
 *   - Typing animation
 *   - WhatsApp redirection
 *   - Auto-open logic
 *
 * @package SmartWhatsAppChatWidget
 * @version 2.0.0
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', init);

    /* ─── Persistent References ─── */
    let trigger, chatWindow, closeBtn, sendBtn, inputEl, bodyEl;
    let faqTree = [];

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       INITIALIZATION
       ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    function init() {
        trigger    = document.getElementById('swcwTrigger');
        chatWindow = document.getElementById('swcwWindow');
        closeBtn   = document.getElementById('swcwClose');
        sendBtn    = document.getElementById('swcwSend');
        inputEl    = document.getElementById('swcwInput');
        bodyEl     = document.getElementById('swcwBody');

        // Abort if essential elements are missing.
        if (!trigger || !chatWindow || !closeBtn || !sendBtn || !inputEl || !bodyEl) {
            return;
        }

        // Load FAQ tree injected by PHP.
        if (typeof swcwFaqTree !== 'undefined' && Array.isArray(swcwFaqTree)) {
            faqTree = swcwFaqTree;
        }

        // Render initial root-level FAQ chips.
        if (faqTree.length > 0) {
            renderFaqChips(faqTree, false); // false = no "Back" button for root
        }

        // Bind events.
        trigger.addEventListener('click', openWidget);
        closeBtn.addEventListener('click', closeWidget);
        sendBtn.addEventListener('click', handleSend);
        inputEl.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleSend();
            }
        });

        // Delegate clicks on FAQ chips.
        bodyEl.addEventListener('click', onBodyClick);

        // Auto-open logic.
        if (typeof swcwSettings !== 'undefined' && swcwSettings.autoOpen === 'yes') {
            var delay = parseInt(swcwSettings.delay, 10) || 0;
            setTimeout(function () {
                if (!chatWindow.classList.contains('swcw-active')) {
                    openWidget();
                }
            }, delay * 1000);
        }
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       WIDGET OPEN / CLOSE
       ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    function openWidget() {
        chatWindow.style.display = 'flex';
        // Force reflow so transition plays.
        void chatWindow.offsetHeight;
        chatWindow.classList.add('swcw-active');
        trigger.classList.add('swcw-hidden');
        scrollToBottom();
    }

    function closeWidget() {
        chatWindow.classList.remove('swcw-active');
        setTimeout(function () {
            chatWindow.style.display = 'none';
            trigger.classList.remove('swcw-hidden');
        }, 350);
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       SEND MESSAGE → WHATSAPP
       ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    function handleSend() {
        var message = inputEl.value.trim();
        var number  = sendBtn.getAttribute('data-number');

        if (!message || !number) return;

        // Show user bubble inside chat before redirecting.
        appendUserBubble(message);

        // Clean phone number.
        var cleanNum = number.replace(/\D/g, '');
        var url = 'https://wa.me/' + cleanNum + '?text=' + encodeURIComponent(message);

        // Small delay so user sees their bubble, then redirect.
        setTimeout(function () {
            window.open(url, '_blank');
        }, 400);

        inputEl.value = '';
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       BODY CLICK DELEGATION
       ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
    function onBodyClick(e) {
        var chip = e.target.closest('.swcw-chip');
        if (!chip) return;

        // "Back to main menu" chip.
        if (chip.classList.contains('swcw-chip--back')) {
            handleBackToMenu();
            return;
        }

        var faqId = chip.getAttribute('data-faq-id');
        if (!faqId) return;

        var node = findNodeById(faqTree, faqId);
        if (!node) return;

        handleFaqSelect(node);
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       FAQ CHATBOT ENGINE
       ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

    /**
     * When user clicks an FAQ chip:
     *  1. Remove old dynamic chips.
     *  2. Show user bubble with the question text.
     *  3. Show typing indicator.
     *  4. After delay, show bot answer + child chips (if any).
     */
    function handleFaqSelect(node) {
        removeAllChips();
        appendUserBubble(node.question);

        var typing = appendTypingIndicator();

        setTimeout(function () {
            typing.remove();
            appendBotBubble(node.answer);

            // If there are children, render them after a short pause.
            if (node.children && node.children.length > 0) {
                setTimeout(function () {
                    renderFaqChips(node.children, true); // true = show back button
                }, 250);
            } else {
                // Leaf node — show "Back to main menu" only.
                setTimeout(function () {
                    renderFaqChips([], true);
                }, 250);
            }

            scrollToBottom();
        }, 900);
    }

    /**
     * "Back to main menu" resets to root-level FAQ chips.
     */
    function handleBackToMenu() {
        removeAllChips();
        appendUserBubble('⬅ Main Menu');

        var typing = appendTypingIndicator();

        setTimeout(function () {
            typing.remove();
            appendBotBubble('Sure! Here are the main topics:');
            setTimeout(function () {
                renderFaqChips(faqTree, false);
            }, 200);
            scrollToBottom();
        }, 600);
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       DOM BUILDERS
       ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

    /** Bot message bubble (left, white) */
    function appendBotBubble(text) {
        var wrap = document.createElement('div');
        wrap.className = 'swcw-bubble swcw-bubble--bot';

        var content = document.createElement('div');
        content.className = 'swcw-bubble__content';
        content.innerHTML = text + '<span class="swcw-bubble__time">' + timeNow() + '</span>';

        wrap.appendChild(content);
        bodyEl.appendChild(wrap);
        scrollToBottom();
        return wrap;
    }

    /** User message bubble (right, green) */
    function appendUserBubble(text) {
        var wrap = document.createElement('div');
        wrap.className = 'swcw-bubble swcw-bubble--user';

        var content = document.createElement('div');
        content.className = 'swcw-bubble__content';
        content.textContent = text;

        var time = document.createElement('span');
        time.className = 'swcw-bubble__time';
        time.textContent = timeNow();

        content.appendChild(time);
        wrap.appendChild(content);
        bodyEl.appendChild(wrap);
        scrollToBottom();
        return wrap;
    }

    /** Typing indicator ("...") */
    function appendTypingIndicator() {
        var el = document.createElement('div');
        el.className = 'swcw-typing';
        el.innerHTML = '<span></span><span></span><span></span>';
        bodyEl.appendChild(el);
        scrollToBottom();
        return el;
    }

    /** Render FAQ chips for a given nodes array. */
    function renderFaqChips(nodes, showBack) {
        var container = document.createElement('div');
        container.className = 'swcw-faq-chips';

        // Individual chips.
        nodes.forEach(function (node) {
            var chip = document.createElement('button');
            chip.className = 'swcw-chip';
            chip.setAttribute('data-faq-id', node.id);
            chip.textContent = node.question;
            container.appendChild(chip);
        });

        // "Back to main menu" chip.
        if (showBack) {
            var back = document.createElement('button');
            back.className = 'swcw-chip swcw-chip--back';
            back.textContent = '⬅ Main Menu';
            container.appendChild(back);
        }

        bodyEl.appendChild(container);
        scrollToBottom();
    }

    /** Remove all current chip containers */
    function removeAllChips() {
        var chips = bodyEl.querySelectorAll('.swcw-faq-chips');
        for (var i = 0; i < chips.length; i++) {
            chips[i].remove();
        }
    }

    /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
       UTILITIES
       ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

    /** Current time formatted HH:MM */
    function timeNow() {
        var d = new Date();
        return pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    function pad(n) {
        return n < 10 ? '0' + n : '' + n;
    }

    /** Scroll body to bottom */
    function scrollToBottom() {
        setTimeout(function () {
            bodyEl.scrollTop = bodyEl.scrollHeight;
        }, 50);
    }

    /** Recursive tree search by node ID */
    function findNodeById(nodes, id) {
        for (var i = 0; i < nodes.length; i++) {
            if (nodes[i].id === id) return nodes[i];
            if (nodes[i].children && nodes[i].children.length > 0) {
                var found = findNodeById(nodes[i].children, id);
                if (found) return found;
            }
        }
        return null;
    }

})();
