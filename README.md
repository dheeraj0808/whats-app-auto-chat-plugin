# WhatsApp Chat Plugin

A premium, lightweight, and highly customizable WhatsApp chat widget for any website. Built with vanilla JavaScript and CSS (zero dependencies).

## Features
- 🚀 **Zero Dependencies**: Lightweight and fast.
- 🎨 **Fully Customizable**: Themes, names, avatars, and messages.
- 📱 **Responsive**: Works perfectly on all devices.
- ✨ **Premium Design**: Smooth animations and modern aesthetics.
- 🛠 **Easy to Use**: Simple initialization with a small configuration object.

## Installation

### 1. Include the Script
Copy `src/whatsapp-plugin.js` to your project and include it in your HTML:

```html
<script src="path/to/whatsapp-plugin.js"></script>
```

### 2. Initialize the Plugin
Add the following script block before the closing `</body>` tag:

```javascript
new WhatsAppPlugin({
    phoneNumber: '911234567890', // Your WhatsApp number (with country code, no +)
    displayName: 'Customer Support',
    statusText: 'Online',
    welcomeMessage: 'Hi there! How can we help you?',
    themeColor: '#0b6b63',
    buttonColor: '#25D366'
});
```

## Configuration Options

| Option | Type | Default | Description |
| --- | --- | --- | --- |
| `phoneNumber` | String | `'910000000000'` | WhatsApp number with country code. |
| `displayName` | String | `'Customer Support'` | Name displayed in the header. |
| `statusText` | String | `'Usually replies within minutes'` | Text below the display name. |
| `welcomeMessage` | String | `'Hello! How can we help you today?'` | Initial message in the bubble. |
| `placeholder` | String | `'Type your message...'` | Input field placeholder. |
| `defaultMessage` | String | `'I am interested in your services'` | Default text in the input field. |
| `themeColor` | String | `'#0b6b63'` | Background color of the header. |
| `buttonColor` | String | `'#25D366'` | Color of the floating trigger button. |
| `position` | String | `'right'` | Position of the widget (`'left'` or `'right'`). |
| `autoOpen` | Boolean | `false` | Whether to open the widget automatically on load. |
| `avatar` | String | `(generic icon)` | URL to the avatar image. |

## License
MIT
# What-s-app-Auto-Chat-Plugin
