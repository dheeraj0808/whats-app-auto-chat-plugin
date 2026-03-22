<?php
/**
 * Helper Functions for Smart WhatsApp Chat Widget
 *
 * Contains utility functions used across the plugin:
 * - FAQ tree parser
 * - Phone number sanitizer
 * - Default options generator
 *
 * @package SmartWhatsAppChatWidget
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Returns the default plugin options.
 *
 * @return array Associative array of default settings.
 */
function swcw_get_defaults() {
    return array(
        'enabled'         => 'off',
        'company_name'    => 'Customer Support',
        'wa_number'       => '',
        'default_message' => 'Hi, I have a question…',
        'welcome_msg'     => 'Hello! 👋 How can we help you today?',
        'reply_time'      => 'Usually replies within minutes',
        'logo_url'        => '',
        'position'        => 'right',
        'btn_color'       => '#25D366',
        'auto_open'       => 'no',
        'delay'           => 3,
        'blinking'        => 'off',
        'faqs'            => '',
    );
}

/**
 * Returns the current plugin options merged with defaults.
 *
 * @return array Merged options.
 */
function swcw_get_options() {
    $defaults = swcw_get_defaults();
    $options  = get_option( 'swcw_settings', array() );
    return wp_parse_args( $options, $defaults );
}

/**
 * Parse hierarchical FAQ text into a nested tree array.
 *
 * Input format (one entry per line):
 *   Question|Answer            → depth 0 (root)
 *   >Sub Question|Answer       → depth 1
 *   >>Sub Sub Question|Answer  → depth 2
 *
 * @param string $raw Raw textarea content.
 * @return array Nested tree structure.
 */
function swcw_parse_faq_tree( $raw ) {
    if ( empty( $raw ) ) {
        return array();
    }

    $lines   = explode( "\n", str_replace( "\r", '', $raw ) );
    $tree    = array();
    $counter = 0;
    $stack   = array(); // References to parents at each depth level.

    foreach ( $lines as $line ) {
        $trimmed = rtrim( $line );

        // Skip blank lines and lines without the separator.
        if ( empty( $trimmed ) || strpos( $trimmed, '|' ) === false ) {
            continue;
        }

        // Determine depth by counting leading ">" characters.
        $depth = 0;
        $clean = $trimmed;
        while ( isset( $clean[0] ) && $clean[0] === '>' ) {
            $depth++;
            $clean = substr( $clean, 1 );
        }
        $clean = trim( $clean );

        // Split into question and answer.
        $parts = explode( '|', $clean, 2 );
        if ( count( $parts ) < 2 ) {
            continue;
        }

        $node = array(
            'id'       => 'faq_' . $counter,
            'question' => trim( $parts[0] ),
            'answer'   => trim( $parts[1] ),
            'children' => array(),
        );
        $counter++;

        if ( $depth === 0 ) {
            // Root-level node.
            $tree[] = $node;
            $stack  = array( &$tree[ count( $tree ) - 1 ] );
        } else {
            // Child node — attach to parent at ($depth - 1).
            $parent_idx = $depth - 1;
            if ( isset( $stack[ $parent_idx ] ) ) {
                $stack[ $parent_idx ]['children'][] = $node;
                $child_count     = count( $stack[ $parent_idx ]['children'] );
                $stack[ $depth ] = &$stack[ $parent_idx ]['children'][ $child_count - 1 ];

                // Trim the stack beyond current depth.
                $stack = array_slice( $stack, 0, $depth + 1 );
            }
        }
    }

    return $tree;
}

/**
 * Sanitize a phone number string, keeping only digits and leading "+".
 *
 * @param string $number Raw phone number.
 * @return string Sanitized number.
 */
function swcw_sanitize_phone( $number ) {
    // Keep only digits.
    return preg_replace( '/[^\d]/', '', $number );
}
