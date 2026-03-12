<?php
/**
 * Plugin Name:       Smart WhatsApp Chat Widget
 * Plugin URI:        https://github.com/dheeraj0808/What-s-app-Auto-Chat-Plugin
 * Description:       A premium, customizable WhatsApp chat widget for your WordPress site.
 * Version:           1.0.0
 * Author:            Antigravity
 * Author URI:        https://google.com
 * Text Domain:       smart-whatsapp-chat-widget
 * License:           GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Constants
 */
define( 'SWCW_VERSION', '1.0.0' );
define( 'SWCW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SWCW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Include the Admin Settings
 */
if ( is_admin() ) {
	require_once SWCW_PLUGIN_DIR . 'admin/settings-page.php';
}

/**
 * Enqueue scripts and styles for the frontend.
 */
function swcw_enqueue_scripts() {
    $options = get_option('swcw_settings');
    
    // Only enqueue if the widget is enabled
    if ( empty($options['enabled']) || $options['enabled'] !== 'on' ) {
        return;
    }

	wp_enqueue_style( 'swcw-widget-css', SWCW_PLUGIN_URL . 'public/widget.css', array(), SWCW_VERSION );
	wp_enqueue_script( 'swcw-widget-js', SWCW_PLUGIN_URL . 'public/widget.js', array(), SWCW_VERSION, true );

    // Pass settings to JS
    wp_localize_script( 'swcw-widget-js', 'swcwRemote', array(
        'autoOpen' => isset($options['auto_open']) ? $options['auto_open'] : 'no',
        'delay'    => isset($options['delay']) ? (int)$options['delay'] : 0,
    ));
}
add_action( 'wp_enqueue_scripts', 'swcw_enqueue_scripts' );

/**
 * Inject the widget HTML into the footer.
 */
function swcw_inject_widget() {
    $options = get_option('swcw_settings');

    // Check if enabled
    if ( empty($options['enabled']) || $options['enabled'] !== 'on' ) {
        return;
    }

    $company_name = !empty($options['company_name']) ? esc_html($options['company_name']) : 'Customer Support';
    $wa_number    = !empty($options['wa_number']) ? esc_attr($options['wa_number']) : '';
    $welcome_msg  = !empty($options['welcome_msg']) ? wp_kses_post($options['welcome_msg']) : 'Hi there! How can we help you?';
    $reply_time   = !empty($options['reply_time']) ? esc_html($options['reply_time']) : 'Usually replies within minutes';
    $placeholder  = !empty($options['default_message']) ? esc_attr($options['default_message']) : 'Type your message...';
    $position     = !empty($options['position']) ? $options['position'] : 'right';
    $btn_color    = !empty($options['btn_color']) ? $options['btn_color'] : '#25D366';
    $logo_url     = !empty($options['logo_url']) ? esc_url($options['logo_url']) : '';
    
    // Position classes
    $container_class = 'swcw-container swcw-pos-' . $position;

    ?>
    <div class="<?php echo $container_class; ?>" id="swcwWidgetContainer" style="--swcw-btn-color: <?php echo $btn_color; ?>;">
        <div class="swcw-window" id="swcwWindow">
            <div class="swcw-header">
                <div class="swcw-header-left">
                    <div class="swcw-logo-box">
                        <?php if ($logo_url): ?>
                            <img src="<?php echo $logo_url; ?>" alt="<?php echo $company_name; ?>">
                        <?php else: ?>
                            <svg viewBox="0 0 32 32" fill="none"><circle cx="16" cy="16" r="16" fill="#25D366"/><path d="M23.2 18.9c-.3-.2-1.8-.9-2.1-1-.3-.1-.5-.2-.7.2-.2.3-.8 1-1 1.2-.2.2-.4.2-.7.1-.3-.2-1.4-.5-2.6-1.6-1-1-1.6-2.1-1.8-2.4-.2-.3 0-.5.1-.7.1-.1.3-.4.5-.5.2-.2.2-.3.3-.5.1-.2 0-.4 0-.6 0-.2-.7-1.8-1-2.4-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.5-.3.3-1.2 1.2-1.2 2.8 0 1.7 1.2 3.3 1.4 3.5.2.2 2.4 3.7 5.8 5.1.8.4 1.5.6 2 .8.8.2 1.6.2 2.2.1.7-.1 1.8-.8 2.1-1.5.3-.7.3-1.3.2-1.5-.1-.2-.3-.2-.6-.4Z" fill="white"/></svg>
                        <?php endif; ?>
                    </div>
                    <div class="swcw-header-text">
                        <h4><?php echo $company_name; ?></h4>
                        <p><?php echo $reply_time; ?></p>
                    </div>
                </div>
                <button class="swcw-close" id="swcwClose">&times;</button>
            </div>
            <div class="swcw-body">
                <div class="swcw-msg-bubble">
                    <?php echo $welcome_msg; ?>
                    <span class="swcw-time"><?php echo date('H:i'); ?></span>
                </div>
            </div>
            <div class="swcw-footer">
                <div class="swcw-input-wrap">
                    <input type="text" id="swcwInput" placeholder="Type your message..." value="<?php echo $placeholder; ?>">
                    <button id="swcwSend" data-number="<?php echo $wa_number; ?>">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path></svg>
                    </button>
                </div>
            </div>
        </div>
        <div class="swcw-trigger" id="swcwTrigger">
            <svg viewBox="0 0 32 32" fill="none">
                <path d="M23.2 18.9c-.3-.2-1.8-.9-2.1-1-.3-.1-.5-.2-.7.2-.2.3-.8 1-1 1.2-.2.2-.4.2-.7.1-.3-.2-1.4-.5-2.6-1.6-1-1-1.6-2.1-1.8-2.4-.2-.3 0-.5.1-.7.1-.1.3-.4.5-.5.2-.2.2-.3.3-.5.1-.2 0-.4 0-.6 0-.2-.7-1.8-1-2.4-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.5-.3.3-1.2 1.2-1.2 2.8 0 1.7 1.2 3.3 1.4 3.5.2.2 2.4 3.7 5.8 5.1.8.4 1.5.6 2 .8.8.2 1.6.2 2.2.1.7-.1 1.8-.8 2.1-1.5.3-.7.3-1.3.2-1.5-.1-.2-.3-.2-.6-.4Z" fill="white"></path>
            </svg>
        </div>
    </div>
    <?php
}
add_action( 'wp_footer', 'swcw_inject_widget' );
