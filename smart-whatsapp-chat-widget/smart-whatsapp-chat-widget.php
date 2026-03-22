<?php
/**
 * Plugin Name:       Smart WhatsApp Chat Widget
 * Plugin URI:        https://github.com/dheeraj0808/What-s-app-Auto-Chat-Plugin
 * Description:       A premium, customizable WhatsApp chat widget for your WordPress site.
 * Version:           1.0.2
 * Author:            Dheeraj Singh
 * Author URI:        https://github.com/dheeraj0808
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
            <div class="swcw-body" id="swcwBody">
                <div class="swcw-msg-bubble">
                    <?php echo $welcome_msg; ?>
                    <span class="swcw-time"><?php echo date('H:i'); ?></span>
                </div>
                
                <?php
                // Parse hierarchical FAQ tree
                $faqs_str = !empty($options['faqs']) ? $options['faqs'] : '';
                $faq_tree = array();
                
                if ($faqs_str) {
                    $lines = explode("\n", str_replace("\r", "", $faqs_str));
                    $counter = 0;
                    $stack = array(); // Stack to track parent at each depth
                    
                    foreach ($lines as $line) {
                        $trimmed = rtrim($line);
                        if (empty($trimmed)) continue;
                        if (strpos($trimmed, '|') === false) continue;
                        
                        // Count leading '>' to determine depth
                        $depth = 0;
                        $clean = $trimmed;
                        while (isset($clean[0]) && $clean[0] === '>') {
                            $depth++;
                            $clean = substr($clean, 1);
                        }
                        $clean = trim($clean);
                        
                        $parts = explode('|', $clean, 2);
                        if (count($parts) < 2) continue;
                        
                        $node = array(
                            'id' => 'faq_' . $counter,
                            'question' => trim($parts[0]),
                            'answer' => trim($parts[1]),
                            'children' => array(),
                        );
                        $counter++;
                        
                        if ($depth === 0) {
                            // Root level
                            $faq_tree[] = $node;
                            $stack = array(&$faq_tree[count($faq_tree) - 1]);
                        } else {
                            // Child level — attach to parent at ($depth - 1)
                            $parent_depth = $depth - 1;
                            if (isset($stack[$parent_depth])) {
                                $stack[$parent_depth]['children'][] = $node;
                                // Update stack at current depth
                                $children_count = count($stack[$parent_depth]['children']);
                                $stack[$depth] = &$stack[$parent_depth]['children'][$children_count - 1];
                                // Trim stack beyond current depth
                                $stack = array_slice($stack, 0, $depth + 1);
                            }
                        }
                    }
                    
                    // Render root-level chips server-side as initial state
                    if (!empty($faq_tree)) {
                        echo '<div class="swcw-faq-container">';
                        foreach ($faq_tree as $root_node) {
                            echo '<button class="swcw-faq-chip" data-faq-id="' . esc_attr($root_node['id']) . '">' . esc_html($root_node['question']) . '</button>';
                        }
                        echo '</div>';
                    }
                }
                ?>
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
        <?php 
        $pulse_class = (!empty($options['blinking']) && $options['blinking'] === 'on') ? ' swcw-pulse' : '';
        ?>
        <div class="swcw-trigger<?php echo $pulse_class; ?>" id="swcwTrigger">
            <svg viewBox="0 0 32 32" fill="none" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                <path d="M16 0C7.16344 0 0 7.16344 0 16C0 18.84 0.74 21.51 2.04 23.84L0.04 31.11L7.52 29.15C9.64 30.64 12.18 31.43 14.88 31.43C23.7166 31.43 30.88 24.2666 30.88 15.43C30.88 6.59344 23.7166 -0.573438 14.88 -0.573438L16 0ZM23.21 21.78C22.88 22.71 21.57 23.49 20.6 23.7C19.93 23.84 19.06 23.95 16.14 22.74C12.4 21.19 9.99 17.4 9.8 17.16C9.62 16.92 8.27 15.13 8.27 13.27C8.27 11.41 9.22 10.51 9.61 10.12C9.91 9.82 10.4 9.68 10.87 9.68C11.02 9.68 11.16 9.69 11.29 9.69C11.67 9.71 11.86 9.73 12.11 10.33C12.42 11.08 13.17 12.91 13.26 13.1C13.35 13.29 13.44 13.54 13.32 13.78C13.2 14.02 13.1 14.13 12.91 14.35C12.72 14.57 12.54 14.71 12.35 14.95C12.18 15.15 11.99 15.37 12.21 15.75C12.43 16.12 13.2 17.38 14.33 18.39C15.79 19.69 17 20.11 17.43 20.29C17.76 20.43 18.15 20.39 18.39 20.14C18.69 19.82 19.06 19.29 19.44 18.76C19.71 18.38 20.04 18.33 20.4 18.47C20.76 18.61 22.65 19.55 23.03 19.74C23.41 19.93 23.66 20.02 23.75 20.18C23.84 20.34 23.84 21.1 23.51 22.03L23.21 21.78Z" fill="white"/>
            </svg>
        </div>
    </div>
    <?php if (!empty($faq_tree)): ?>
    <script>var swcwFaqTree = <?php echo json_encode($faq_tree); ?>;</script>
    <?php endif; ?>
    <?php
}
add_action( 'wp_footer', 'swcw_inject_widget' );
