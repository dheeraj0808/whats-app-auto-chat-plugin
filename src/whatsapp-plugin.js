/**
 * WhatsApp Chat Plugin
 * A premium, customizable WhatsApp chat widget for any website.
 */

class WhatsAppPlugin {
    constructor(options = {}) {
        this.options = {
            phoneNumber: options.phoneNumber || '910000000000',
            displayName: options.displayName || 'Customer Support',
            statusText: options.statusText || 'Usually replies within minutes',
            welcomeMessage: options.welcomeMessage || 'Hello! How can we help you today?',
            placeholder: options.placeholder || 'Type your message...',
            defaultMessage: options.defaultMessage || 'I am interested in your services',
            position: options.position || 'right', // 'left' or 'right'
            themeColor: options.themeColor || '#0b6b63',
            buttonColor: options.buttonColor || '#25D366',
            autoOpen: options.autoOpen !== undefined ? options.autoOpen : false,
            avatar: options.avatar || 'whatsapp', // 'whatsapp' for default icon
            ...options
        };

        this.init();
    }

    init() {
        this.injectStyles();
        this.render();
        this.bindEvents();
        
        if (this.options.autoOpen) {
            this.openWidget();
        }
    }

    injectStyles() {
        const styleId = 'whatsapp-plugin-styles';
        if (document.getElementById(styleId)) return;

        const isRight = this.options.position === 'right';
        const styles = `
            :root {
                --wa-theme: ${this.options.themeColor};
                --wa-button: ${this.options.buttonColor};
                --wa-bg: #efeae2;
            }

            .wa-plugin-container {
                position: fixed;
                bottom: 24px;
                ${isRight ? 'right: 24px;' : 'left: 24px;'}
                z-index: 100000;
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                display: flex;
                flex-direction: column;
                align-items: ${isRight ? 'flex-end' : 'flex-start'};
            }

            /* Widget Window */
            .wa-widget {
                width: 360px;
                max-width: 90vw;
                background: var(--wa-bg);
                border-radius: 20px;
                overflow: hidden;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
                margin-bottom: 20px;
                display: none;
                opacity: 0;
                transform: translateY(20px) scale(0.95);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                pointer-events: none;
            }

            .wa-widget.active {
                display: block;
                opacity: 1;
                transform: translateY(0) scale(1);
                pointer-events: all;
            }

            /* Header */
            .wa-header {
                background: var(--wa-theme);
                color: white;
                padding: 20px;
                display: flex;
                align-items: center;
                gap: 12px;
                position: relative;
            }

            .wa-avatar-container {
                width: 48px;
                height: 48px;
                background: white;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                flex-shrink: 0;
            }

            .wa-avatar-container svg {
                width: 100%;
                height: 100%;
            }

            .wa-avatar-img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .wa-header-info h4 {
                margin: 0;
                font-size: 16px;
                font-weight: 600;
            }

            .wa-header-info p {
                margin: 2px 0 0;
                font-size: 13px;
                opacity: 0.9;
            }

            .wa-close-btn {
                position: absolute;
                top: 15px;
                right: 15px;
                background: none;
                border: none;
                color: white;
                font-size: 24px;
                cursor: pointer;
                opacity: 0.7;
                transition: opacity 0.2s;
            }

            .wa-close-btn:hover {
                opacity: 1;
            }

            /* Body */
            .wa-body {
                padding: 24px;
                height: 200px;
                background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');
                background-size: contain;
                overflow-y: auto;
            }

            .wa-message-bubble {
                background: white;
                padding: 12px 16px;
                border-radius: 0 18px 18px 18px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.05);
                max-width: 85%;
                font-size: 15px;
                line-height: 1.5;
                color: #333;
                position: relative;
            }

            .wa-message-bubble::before {
                content: "";
                position: absolute;
                left: -10px;
                top: 0;
                border-style: solid;
                border-width: 0 10px 10px 0;
                border-color: transparent white transparent transparent;
            }

            .wa-timestamp {
                display: block;
                font-size: 10px;
                color: #888;
                margin-top: 5px;
                text-align: right;
            }

            /* Footer */
            .wa-footer {
                padding: 15px;
                background: white;
            }

            .wa-input-container {
                display: flex;
                align-items: center;
                gap: 10px;
                background: #f0f2f5;
                padding: 8px 15px;
                border-radius: 25px;
            }

            .wa-input {
                flex: 1;
                border: none;
                background: none;
                outline: none;
                padding: 8px 0;
                font-size: 14px;
                color: #333;
            }

            .wa-send-btn {
                background: none;
                border: none;
                color: var(--wa-theme);
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: transform 0.2s;
            }

            .wa-send-btn:hover {
                transform: scale(1.1);
            }

            /* Trigger Button */
            .wa-trigger {
                width: 60px;
                height: 60px;
                background-color: var(--wa-button);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
                transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            }

            .wa-trigger:hover {
                transform: scale(1.08);
                box-shadow: 0 8px 25px rgba(37, 211, 102, 0.5);
            }

            .wa-trigger svg {
                width: 32px;
                height: 32px;
            }

            .wa-trigger.hidden {
                transform: scale(0);
                opacity: 0;
            }

            @media (max-width: 480px) {
                .wa-plugin-container {
                    bottom: 15px;
                    ${isRight ? 'right: 15px;' : 'left: 15px;'}
                }
                .wa-widget {
                    width: calc(100vw - 30px);
                    bottom: 80px;
                    position: fixed;
                }
            }
        `;

        const styleTag = document.createElement('style');
        styleTag.id = styleId;
        styleTag.textContent = styles;
        document.head.appendChild(styleTag);
    }

    render() {
        const currentTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        
        const html = `
            <div class="wa-plugin-container" id="waPluginContainer">
                <div class="wa-widget" id="waWidget">
                    <div class="wa-header">
                        <div class="wa-avatar-container">
                            ${this.options.avatar === 'whatsapp' 
                                ? `<svg viewBox="0 0 32 32" fill="none"><circle cx="16" cy="16" r="16" fill="#25D366"/><path d="M23.2 18.9c-.3-.2-1.8-.9-2.1-1-.3-.1-.5-.2-.7.2-.2.3-.8 1-1 1.2-.2.2-.4.2-.7.1-.3-.2-1.4-.5-2.6-1.6-1-1-1.6-2.1-1.8-2.4-.2-.3 0-.5.1-.7.1-.1.3-.4.5-.5.2-.2.2-.3.3-.5.1-.2 0-.4 0-.6 0-.2-.7-1.8-1-2.4-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.5-.3.3-1.2 1.2-1.2 2.8 0 1.7 1.2 3.3 1.4 3.5.2.2 2.4 3.7 5.8 5.1.8.4 1.5.6 2 .8.8.2 1.6.2 2.2.1.7-.1 1.8-.8 2.1-1.5.3-.7.3-1.3.2-1.5-.1-.2-.3-.2-.6-.4Z" fill="white"/></svg>`
                                : `<img src="${this.options.avatar}" alt="Avatar" class="wa-avatar-img">`
                            }
                        </div>
                        <div class="wa-header-info">
                            <h4>${this.options.displayName}</h4>
                            <p>${this.options.statusText}</p>
                        </div>
                        <button class="wa-close-btn" id="waCloseBtn">&times;</button>
                    </div>
                    <div class="wa-body">
                        <div class="wa-message-bubble">
                            ${this.options.welcomeMessage}
                            <span class="wa-timestamp">${currentTime}</span>
                        </div>
                    </div>
                    <div class="wa-footer">
                        <div class="wa-input-container">
                            <input type="text" class="wa-input" id="waInput" placeholder="${this.options.placeholder}" value="${this.options.defaultMessage}">
                            <button class="wa-send-btn" id="waSendBtn">
                                <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="wa-trigger" id="waTrigger">
                    <svg viewBox="0 0 32 32" fill="none">
                        <path d="M23.2 18.9c-.3-.2-1.8-.9-2.1-1-.3-.1-.5-.2-.7.2-.2.3-.8 1-1 1.2-.2.2-.4.2-.7.1-.3-.2-1.4-.5-2.6-1.6-1-1-1.6-2.1-1.8-2.4-.2-.3 0-.5.1-.7.1-.1.3-.4.5-.5.2-.2.2-.3.3-.5.1-.2 0-.4 0-.6 0-.2-.7-1.8-1-2.4-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.5-.3.3-1.2 1.2-1.2 2.8 0 1.7 1.2 3.3 1.4 3.5.2.2 2.4 3.7 5.8 5.1.8.4 1.5.6 2 .8.8.2 1.6.2 2.2.1.7-.1 1.8-.8 2.1-1.5.3-.7.3-1.3.2-1.5-.1-.2-.3-.2-.6-.4Z" fill="white"></path>
                    </svg>
                </div>
            </div>
        `;

        const container = document.createElement('div');
        container.innerHTML = html;
        document.body.appendChild(container);
    }

    bindEvents() {
        const trigger = document.getElementById('waTrigger');
        const widget = document.getElementById('waWidget');
        const closeBtn = document.getElementById('waCloseBtn');
        const sendBtn = document.getElementById('waSendBtn');
        const input = document.getElementById('waInput');

        trigger.addEventListener('click', () => this.openWidget());
        closeBtn.addEventListener('click', () => this.closeWidget());
        
        sendBtn.addEventListener('click', () => this.sendMessage());
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.sendMessage();
        });
    }

    openWidget() {
        const trigger = document.getElementById('waTrigger');
        const widget = document.getElementById('waWidget');
        
        widget.classList.add('active');
        trigger.classList.add('hidden');
    }

    closeWidget() {
        const trigger = document.getElementById('waTrigger');
        const widget = document.getElementById('waWidget');
        
        widget.classList.remove('active');
        trigger.classList.remove('hidden');
    }

    sendMessage() {
        const input = document.getElementById('waInput');
        const message = input.value.trim();
        
        if (message) {
            const url = `https://wa.me/${this.options.phoneNumber}?text=${encodeURIComponent(message)}`;
            window.open(url, '_blank');
        }
    }
}

// Export for different environments
if (typeof module !== 'undefined' && module.exports) {
    module.exports = WhatsAppPlugin;
} else {
    window.WhatsAppPlugin = WhatsAppPlugin;
}
