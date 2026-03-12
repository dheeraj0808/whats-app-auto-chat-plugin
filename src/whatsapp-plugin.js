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
            avatar: options.avatar || 'https://cdn-icons-png.flaticon.com/512/10285/10285437.png',
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

            .wa-avatar {
                width: 48px;
                height: 48px;
                background: white;
                border-radius: 50%;
                padding: 2px;
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
                fill: white;
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
                        <img src="${this.options.avatar}" alt="Avatar" class="wa-avatar">
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
                    <svg viewBox="0 0 448 512">
                        <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.7 17.7 68.9 27.1 106 27.1h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67-157.1zM223.9 445.9c-33.1 0-65.7-8.9-94.1-25.7l-6.7-4-69.8 18.3 18.7-68.1-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-5.5-2.8-23.4-8.6-44.6-27.4-16.5-14.7-27.6-32.8-30.8-38.4-3.2-5.6-.3-8.6 2.5-11.4 2.5-2.5 5.5-6.5 8.3-9.7 2.8-3.2 3.7-5.5 5.5-9.2 1.9-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 13.2 5.7 23.5 9.2 31.6 11.8 13.3 4.2 25.4 3.6 35 2.2 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"></path>
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
