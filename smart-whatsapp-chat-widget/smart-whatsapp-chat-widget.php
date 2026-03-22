<?php
/**
 * Plugin Name:       Smart WhatsApp Chat Widget
 * Plugin URI:        https://github.com/dheeraj0808/What-s-app-Auto-Chat-Plugin
 * Description:       A premium, highly customizable WhatsApp chat widget with an interactive hierarchical FAQ chatbot system for WordPress.
 * Version:           2.0.0
 * Author:            Dheeraj Singh
 * Author URI:        https://github.com/dheeraj0808
 * Text Domain:       smart-whatsapp-chat-widget
 * License:           GPL-2.0+
 * Requires at least: 5.6
 * Requires PHP:      7.4
 */

// Prevent direct access.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/* ───────────────────────────────────────────
 * Constants
 * ─────────────────────────────────────────── */
define( 'SWCW_VERSION',    '2.0.0' );
define( 'SWCW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SWCW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/* ───────────────────────────────────────────
 * Include Files
 * ─────────────────────────────────────────── */
require_once SWCW_PLUGIN_DIR . 'includes/helpers.php';

if ( is_admin() ) {
    require_once SWCW_PLUGIN_DIR . 'admin/settings-page.php';
}

/* ───────────────────────────────────────────
 * Enqueue Frontend Assets
 * ─────────────────────────────────────────── */
function swcw_enqueue_scripts() {
    $options = swcw_get_options();

    // Only load when widget is enabled.
    if ( $options['enabled'] !== 'on' ) {
        return;
    }

    wp_enqueue_style(
        'swcw-widget-css',
        SWCW_PLUGIN_URL . 'public/widget.css',
        array(),
        SWCW_VERSION
    );

    wp_enqueue_script(
        'swcw-widget-js',
        SWCW_PLUGIN_URL . 'public/widget.js',
        array(),
        SWCW_VERSION,
        true // Load in footer.
    );

    // Pass settings to JS via localized object.
    wp_localize_script( 'swcw-widget-js', 'swcwSettings', array(
        'autoOpen' => $options['auto_open'],
        'delay'    => (int) $options['delay'],
    ) );
}
add_action( 'wp_enqueue_scripts', 'swcw_enqueue_scripts' );

/* ───────────────────────────────────────────
 * Render Widget HTML in Footer
 * ─────────────────────────────────────────── */
function swcw_inject_widget() {
    $options = swcw_get_options();

    // Bail if disabled.
    if ( $options['enabled'] !== 'on' ) {
        return;
    }

    // Load the template file for the widget markup.
    include SWCW_PLUGIN_DIR . 'public/widget.php';
}
add_action( 'wp_footer', 'swcw_inject_widget' );

/* ───────────────────────────────────────────
 * Plugin Activation — Set Default Options
 * ─────────────────────────────────────────── */
function swcw_activate() {
    if ( false === get_option( 'swcw_settings' ) ) {
        add_option( 'swcw_settings', swcw_get_defaults() );
    }
}
register_activation_hook( __FILE__, 'swcw_activate' );

/* ───────────────────────────────────────────
 * Add Settings Link on Plugins Page
 * ─────────────────────────────────────────── */
function swcw_plugin_action_links( $links ) {
    $settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=smart-whatsapp-chat-widget' ) ) . '">' . esc_html__( 'Settings', 'smart-whatsapp-chat-widget' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'swcw_plugin_action_links' );
