# Smart WhatsApp Chat Widget

> A premium, highly customizable WhatsApp chat widget with an **interactive hierarchical FAQ chatbot** for WordPress.

![Version](https://img.shields.io/badge/version-2.0.0-25D366?style=flat-square)
![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=flat-square)
![WordPress](https://img.shields.io/badge/WordPress-5.6+-21759B?style=flat-square)
![License](https://img.shields.io/badge/license-GPL--2.0+-blue?style=flat-square)

---

## ✨ Features

| Feature | Description |
|---|---|
| 💬 **Floating Widget** | WhatsApp-style chat window with premium animations |
| 🤖 **FAQ Chatbot** | Hierarchical, tree-based FAQ system (like banking chatbots) |
| 🎨 **Customizable** | Theme color, position, logo, welcome message, and more |
| 📱 **Responsive** | Full-width on mobile, perfect on all screen sizes |
| ⚡ **Lightweight** | Pure Vanilla JS — zero jQuery on frontend |
| 🔄 **Auto-Open** | Configurable auto-open with delay |
| 💫 **Pulse Animation** | Attention-grabbing button effect |
| 🔒 **Secure** | Proper sanitization, escaping, and nonces throughout |

---

## 📂 Plugin Structure

```
smart-whatsapp-chat-widget/
├── smart-whatsapp-chat-widget.php   # Main plugin file
├── admin/
│   └── settings-page.php            # Admin settings (Settings → WhatsApp Widget)
├── public/
│   ├── widget.php                   # Widget HTML template
│   ├── widget.css                   # Premium frontend styles
│   └── widget.js                    # Chatbot engine (Vanilla JS)
└── includes/
    └── helpers.php                  # Utility functions & FAQ parser
```

---

## 🚀 Installation

1. Download / clone into `wp-content/plugins/smart-whatsapp-chat-widget/`
2. Activate the plugin in **Plugins → Installed Plugins**
3. Go to **Settings → WhatsApp Widget**
4. Configure your company name, WhatsApp number, and other settings
5. Build your FAQ tree in the FAQ Chatbot Builder

---

## 🤖 FAQ Chatbot Builder

Create interactive, hierarchical FAQs with a simple text format:

```
Pricing|Here are our pricing plans
>Basic Plan|₹999/month with essential features
>Premium Plan|₹1999/month with premium features
>>Enterprise|Contact us for custom pricing
Support|We are here to help!
>Technical Issue|Please describe your issue
>Billing|Email billing@example.com
Contact|Reach us at hello@example.com
```

**Format:**
- `Question|Answer` → Root level
- `>Sub Question|Answer` → Level 1 (child)
- `>>Deep Question|Answer` → Level 2 (grandchild)
- And so on...

---

## ⚙️ Settings

| Setting | Description |
|---|---|
| Enable Widget | Turn the widget on/off |
| Company Name | Shown in the chat header |
| WhatsApp Number | Number to redirect chat to |
| Default Message | Pre-filled message text |
| Welcome Message | First bot message (supports HTML) |
| Reply Time Text | Shown under company name |
| Company Logo | Upload via media library |
| Widget Position | Bottom Left / Bottom Right |
| Theme Color | Custom brand color |
| Pulse Animation | Eye-catching button pulse |
| Auto Open | Open widget after delay |
| Auto Open Delay | Seconds before auto-open |
| FAQ Hierarchy | Build your chatbot tree |

---

## 🛠 Technical Details

- **No jQuery** on the frontend (pure Vanilla JS)
- All scripts/styles properly enqueued via `wp_enqueue_*`
- Widget injected via `wp_footer` hook
- All user inputs sanitized and escaped
- WordPress Settings API with nonce verification
- ES5-compatible for maximum browser support

---

## 📄 License

GPL-2.0+ — Free to use, modify, and distribute.

---

Built with ❤️ by [Dheeraj Singh](https://github.com/dheeraj0808)
