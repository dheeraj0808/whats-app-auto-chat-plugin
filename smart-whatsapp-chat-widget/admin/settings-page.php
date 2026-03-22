<?php
/**
 * Admin Settings Page for Smart WhatsApp Chat Widget
 *
 * Registered under:  Dashboard → Settings → WhatsApp Chat Widget
 * Uses WordPress Settings API with proper sanitization and nonces.
 *
 * @package SmartWhatsAppChatWidget
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ───────────────────────────────────────────
 * 1.  Register Settings, Sections & Fields
 * ─────────────────────────────────────────── */
function swcw_register_settings() {

    register_setting(
        'swcw_settings_group',
        'swcw_settings',
        array(
            'sanitize_callback' => 'swcw_sanitize_settings',
        )
    );

    /* — General Section — */
    add_settings_section(
        'swcw_section_general',
        '🔧 General Settings',
        function () {
            echo '<p class="swcw-section-desc">Core configuration for your WhatsApp widget.</p>';
        },
        'smart-whatsapp-chat-widget'
    );

    /* — Appearance Section — */
    add_settings_section(
        'swcw_section_appearance',
        '🎨 Appearance',
        function () {
            echo '<p class="swcw-section-desc">Customize the look and feel of the widget.</p>';
        },
        'smart-whatsapp-chat-widget'
    );

    /* — Behavior Section — */
    add_settings_section(
        'swcw_section_behavior',
        '⚙️ Behavior',
        function () {
            echo '<p class="swcw-section-desc">Control when and how the widget opens.</p>';
        },
        'smart-whatsapp-chat-widget'
    );

    /* — FAQ Chatbot Section — */
    add_settings_section(
        'swcw_section_faq',
        '🤖 FAQ Chatbot Builder',
        function () {
            echo '<p class="swcw-section-desc">Build an interactive, hierarchical FAQ chatbot that guides visitors.</p>';
        },
        'smart-whatsapp-chat-widget'
    );

    /* ── Field Definitions ── */
    $fields = array(

        /* General */
        array( 'id' => 'enabled',         'title' => 'Enable Widget',           'section' => 'swcw_section_general' ),
        array( 'id' => 'company_name',    'title' => 'Company Name',            'section' => 'swcw_section_general' ),
        array( 'id' => 'wa_number',       'title' => 'WhatsApp Number',         'section' => 'swcw_section_general' ),
        array( 'id' => 'default_message', 'title' => 'Default Message',         'section' => 'swcw_section_general' ),
        array( 'id' => 'welcome_msg',     'title' => 'Welcome Message',         'section' => 'swcw_section_general' ),
        array( 'id' => 'reply_time',      'title' => 'Reply Time Text',         'section' => 'swcw_section_general' ),

        /* Appearance */
        array( 'id' => 'logo_url',        'title' => 'Company Logo',            'section' => 'swcw_section_appearance' ),
        array( 'id' => 'position',        'title' => 'Widget Position',         'section' => 'swcw_section_appearance' ),
        array( 'id' => 'btn_color',       'title' => 'Theme Color',             'section' => 'swcw_section_appearance' ),
        array( 'id' => 'blinking',        'title' => 'Enable Pulse Animation',  'section' => 'swcw_section_appearance' ),

        /* Behavior */
        array( 'id' => 'auto_open',       'title' => 'Auto Open Widget',        'section' => 'swcw_section_behavior' ),
        array( 'id' => 'delay',           'title' => 'Auto Open Delay (sec)',    'section' => 'swcw_section_behavior' ),

        /* FAQ Chatbot */
        array( 'id' => 'faqs',            'title' => 'FAQ Hierarchy',           'section' => 'swcw_section_faq' ),
    );

    foreach ( $fields as $f ) {
        add_settings_field(
            'swcw_' . $f['id'],
            $f['title'],
            'swcw_render_field',
            'smart-whatsapp-chat-widget',
            $f['section'],
            array( 'id' => $f['id'], 'title' => $f['title'] )
        );
    }
}
add_action( 'admin_init', 'swcw_register_settings' );

/* ───────────────────────────────────────────
 * 2.  Add Menu Item under Settings
 * ─────────────────────────────────────────── */
function swcw_add_admin_menu() {
    add_options_page(
        'WhatsApp Chat Widget',            // Page title
        'WhatsApp Widget',                 // Menu title
        'manage_options',                  // Capability
        'smart-whatsapp-chat-widget',      // Slug
        'swcw_render_settings_page'        // Callback
    );
}
add_action( 'admin_menu', 'swcw_add_admin_menu' );

/* ───────────────────────────────────────────
 * 3.  Sanitize All Input
 * ─────────────────────────────────────────── */
function swcw_sanitize_settings( $input ) {
    $clean = array();

    $clean['enabled']         = ! empty( $input['enabled'] ) ? 'on' : 'off';
    $clean['company_name']    = sanitize_text_field( $input['company_name'] ?? '' );
    $clean['wa_number']       = sanitize_text_field( $input['wa_number'] ?? '' );
    $clean['default_message'] = sanitize_text_field( $input['default_message'] ?? '' );
    $clean['welcome_msg']     = wp_kses_post( $input['welcome_msg'] ?? '' );
    $clean['reply_time']      = sanitize_text_field( $input['reply_time'] ?? '' );
    $clean['logo_url']        = esc_url_raw( $input['logo_url'] ?? '' );
    $clean['position']        = in_array( $input['position'] ?? '', array( 'right', 'left' ), true ) ? $input['position'] : 'right';
    $clean['btn_color']       = sanitize_hex_color( $input['btn_color'] ?? '#25D366' ) ?: '#25D366';
    $clean['blinking']        = ! empty( $input['blinking'] ) ? 'on' : 'off';
    $clean['auto_open']       = ! empty( $input['auto_open'] ) ? 'yes' : 'no';
    $clean['delay']           = absint( $input['delay'] ?? 3 );
    $clean['faqs']            = sanitize_textarea_field( $input['faqs'] ?? '' );

    return $clean;
}

/* ───────────────────────────────────────────
 * 4.  Render Individual Fields
 * ─────────────────────────────────────────── */
function swcw_render_field( $args ) {
    $options = swcw_get_options();
    $id      = $args['id'];
    $val     = $options[ $id ];

    switch ( $id ) {

        /* ── Toggle: Enabled ── */
        case 'enabled':
            echo '<label class="swcw-toggle">';
            echo '<input type="checkbox" name="swcw_settings[enabled]" value="on" ' . checked( $val, 'on', false ) . '>';
            echo '<span class="swcw-toggle__slider"></span>';
            echo '</label>';
            echo '<p class="description">Turn the chat widget on or off on the frontend.</p>';
            break;

        /* ── Toggle: Pulse ── */
        case 'blinking':
            echo '<label class="swcw-toggle">';
            echo '<input type="checkbox" name="swcw_settings[blinking]" value="on" ' . checked( $val, 'on', false ) . '>';
            echo '<span class="swcw-toggle__slider"></span>';
            echo '</label>';
            echo '<p class="description">Adds an attention-grabbing pulse ring around the floating button.</p>';
            break;

        /* ── Toggle: Auto Open ── */
        case 'auto_open':
            echo '<label class="swcw-toggle">';
            echo '<input type="checkbox" name="swcw_settings[auto_open]" value="yes" ' . checked( $val, 'yes', false ) . '>';
            echo '<span class="swcw-toggle__slider"></span>';
            echo '</label>';
            echo '<p class="description">Automatically pop open the chat widget after the delay below.</p>';
            break;

        /* ── Select: Position ── */
        case 'position':
            echo '<select name="swcw_settings[position]" class="swcw-select">';
            echo '<option value="right" ' . selected( $val, 'right', false ) . '>↘ Bottom Right</option>';
            echo '<option value="left"  ' . selected( $val, 'left', false )  . '>↙ Bottom Left</option>';
            echo '</select>';
            break;

        /* ── Color Picker ── */
        case 'btn_color':
            echo '<div class="swcw-color-wrap">';
            echo '<input type="color" name="swcw_settings[btn_color]" value="' . esc_attr( $val ) . '" class="swcw-color-input">';
            echo '<span class="swcw-color-hex">' . esc_html( $val ) . '</span>';
            echo '</div>';
            break;

        /* ── Media Upload: Logo ── */
        case 'logo_url':
            echo '<div class="swcw-upload-wrap">';
            if ( $val ) {
                echo '<img src="' . esc_url( $val ) . '" class="swcw-logo-preview" alt="Logo preview">';
            }
            echo '<input type="text" id="swcw_logo_url" name="swcw_settings[logo_url]" value="' . esc_url( $val ) . '" class="regular-text" placeholder="https://…">';
            echo ' <button type="button" class="button swcw-upload-btn" id="swcw_upload_btn">📁 Upload / Select</button>';
            if ( $val ) {
                echo ' <button type="button" class="button swcw-remove-logo" id="swcw_remove_btn">✕ Remove</button>';
            }
            echo '</div>';
            break;

        /* ── Number: Delay ── */
        case 'delay':
            echo '<input type="number" name="swcw_settings[delay]" value="' . esc_attr( $val ) . '" min="0" max="120" class="small-text"> <span class="description">seconds</span>';
            break;

        /* ── Textarea: Welcome Message ── */
        case 'welcome_msg':
            echo '<textarea name="swcw_settings[welcome_msg]" rows="3" class="large-text" placeholder="Hello! 👋 How can we help you?">' . esc_textarea( $val ) . '</textarea>';
            echo '<p class="description">Supports basic HTML. This is the first message visitors see.</p>';
            break;

        /* ── Textarea: FAQ Builder ── */
        case 'faqs':
            echo '<textarea name="swcw_settings[faqs]" rows="12" class="large-text code" placeholder="Pricing|Here are our pricing plans&#10;>Basic Plan|₹999/month&#10;>Premium Plan|₹1999/month&#10;>>Enterprise|Contact us for custom pricing&#10;Support|We are here to help&#10;>Technical Issue|Please describe your issue">' . esc_textarea( $val ) . '</textarea>';

            // ── Inline Help Card ──
            echo '<div class="swcw-help-card">';
            echo '<h4>📖 How to use the FAQ Builder</h4>';
            echo '<p>Each line defines a <strong>Question|Answer</strong> pair. Indent with <code>&gt;</code> to create sub-levels:</p>';
            echo '<table class="swcw-help-table">';
            echo '<tr><td><code>Question|Answer</code></td><td>Root level</td></tr>';
            echo '<tr><td><code>&gt;Sub Question|Answer</code></td><td>Level 1 (child)</td></tr>';
            echo '<tr><td><code>&gt;&gt;Deep Question|Answer</code></td><td>Level 2 (grandchild)</td></tr>';
            echo '</table>';
            echo '<p style="margin-top:10px;"><strong>Example:</strong></p>';
            echo '<pre class="swcw-help-pre">';
            echo "Pricing|Here are our pricing plans\n";
            echo "&gt;Basic Plan|₹999/month with essential features\n";
            echo "&gt;Premium Plan|₹1999/month with premium features\n";
            echo "&gt;&gt;Enterprise|Contact us for custom pricing\n";
            echo "Support|We are here to help!\n";
            echo "&gt;Technical Issue|Please describe your issue\n";
            echo "&gt;Billing|Email billing@example.com\n";
            echo "Contact|Reach us at hello@example.com";
            echo '</pre>';
            echo '</div>';
            break;

        /* ── Default: Text Input ── */
        default:
            $placeholder = '';
            if ( $id === 'wa_number' )       $placeholder = '+91XXXXXXXXXX';
            if ( $id === 'company_name' )    $placeholder = 'Your Company';
            if ( $id === 'default_message' ) $placeholder = 'Hi, I have a question…';
            if ( $id === 'reply_time' )      $placeholder = 'Usually replies within minutes';

            echo '<input type="text" name="swcw_settings[' . esc_attr( $id ) . ']" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="' . esc_attr( $placeholder ) . '">';
            break;
    }
}

/* ───────────────────────────────────────────
 * 5.  Settings Page HTML
 * ─────────────────────────────────────────── */
function swcw_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    ?>
    <!-- Admin page inline styles -->
    <style>
    /* ── Layout ── */
    .swcw-admin {
        max-width: 860px;
        margin: 30px auto 60px;
    }

    .swcw-admin-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 28px;
        padding-bottom: 22px;
        border-bottom: 2px solid #f0f0f0;
    }

    .swcw-admin-header svg {
        width: 42px;
        height: 42px;
    }

    .swcw-admin-header h1 {
        font-size: 26px;
        font-weight: 800;
        color: #1a1a1a;
        margin: 0;
    }

    .swcw-admin-header .swcw-version {
        background: #e8f5e9;
        color: #2e7d32;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 100px;
        letter-spacing: 0.04em;
    }

    .swcw-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04), 0 4px 20px rgba(0,0,0,0.03);
        padding: 36px 40px 40px;
    }

    /* ── Section Titles ── */
    .swcw-card h2 {
        font-size: 18px;
        font-weight: 700;
        color: #1a1a1a;
        margin: 32px 0 6px;
        padding-top: 24px;
        border-top: 1px solid #eee;
    }

    .swcw-card h2:first-of-type {
        margin-top: 0;
        padding-top: 0;
        border-top: none;
    }

    .swcw-section-desc {
        color: #777;
        font-size: 13px;
        margin: 0 0 16px;
    }

    /* ── Form Table ── */
    .form-table th {
        font-weight: 600;
        color: #444;
        padding: 18px 12px 18px 0;
        width: 220px;
        vertical-align: top;
    }

    .form-table td {
        padding: 16px 0;
    }

    .form-table .description {
        color: #999;
        font-size: 12px;
        margin-top: 6px;
    }

    /* ── Inputs ── */
    .swcw-card .regular-text,
    .swcw-card .large-text {
        border-radius: 10px;
        border: 1.5px solid #ddd;
        padding: 10px 16px;
        background: #fafafa;
        transition: all 0.2s;
        font-size: 14px;
    }

    .swcw-card .regular-text:focus,
    .swcw-card .large-text:focus {
        border-color: #25D366;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.12);
        outline: none;
    }

    .swcw-card .small-text {
        border-radius: 8px;
        border: 1.5px solid #ddd;
        padding: 8px 12px;
        width: 70px;
        text-align: center;
        font-size: 14px;
    }

    /* ── Toggle Switch ── */
    .swcw-toggle {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 28px;
        cursor: pointer;
    }

    .swcw-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .swcw-toggle__slider {
        position: absolute;
        inset: 0;
        background: #ccc;
        border-radius: 28px;
        transition: 0.3s;
    }

    .swcw-toggle__slider::before {
        content: '';
        position: absolute;
        width: 22px;
        height: 22px;
        left: 3px;
        bottom: 3px;
        background: #fff;
        border-radius: 50%;
        transition: 0.3s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }

    .swcw-toggle input:checked + .swcw-toggle__slider {
        background: #25D366;
    }

    .swcw-toggle input:checked + .swcw-toggle__slider::before {
        transform: translateX(22px);
    }

    /* ── Select ── */
    .swcw-select {
        border-radius: 10px;
        border: 1.5px solid #ddd;
        padding: 10px 16px;
        font-size: 14px;
        background: #fafafa;
        min-width: 200px;
        cursor: pointer;
    }

    .swcw-select:focus {
        border-color: #25D366;
        box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.12);
        outline: none;
    }

    /* ── Color Input ── */
    .swcw-color-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .swcw-color-input {
        width: 50px;
        height: 40px;
        border: 2px solid #ddd;
        border-radius: 10px;
        cursor: pointer;
        padding: 2px;
    }

    .swcw-color-hex {
        font-family: monospace;
        font-size: 14px;
        color: #666;
    }

    /* ── Upload ── */
    .swcw-upload-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .swcw-logo-preview {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        object-fit: cover;
        border: 2px solid #eee;
    }

    .swcw-upload-btn,
    .swcw-remove-logo {
        border-radius: 8px !important;
    }

    .swcw-remove-logo {
        color: #d32f2f !important;
    }

    /* ── FAQ Help Card ── */
    .swcw-help-card {
        background: linear-gradient(135deg, #f0f9f4, #e8f5e9);
        border-left: 4px solid #25D366;
        border-radius: 0 10px 10px 0;
        padding: 18px 22px;
        margin-top: 14px;
        font-size: 13px;
        line-height: 1.7;
    }

    .swcw-help-card h4 {
        margin: 0 0 8px;
        font-size: 14px;
        color: #075e54;
    }

    .swcw-help-card code {
        background: #fff;
        padding: 2px 7px;
        border-radius: 4px;
        font-size: 12px;
    }

    .swcw-help-table {
        border-collapse: collapse;
        margin-top: 6px;
    }

    .swcw-help-table td {
        padding: 4px 12px 4px 0;
        font-size: 13px;
    }

    .swcw-help-pre {
        background: #fff;
        border: 1px solid #d4edda;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 12px;
        overflow-x: auto;
        line-height: 1.8;
        margin: 6px 0 0;
    }

    /* ── Submit Button ── */
    .wp-core-ui .button-primary {
        background: linear-gradient(135deg, #25D366, #128c7e) !important;
        border: none !important;
        padding: 12px 36px !important;
        height: auto !important;
        border-radius: 100px !important;
        font-size: 14px !important;
        font-weight: 700 !important;
        letter-spacing: 0.02em !important;
        box-shadow: 0 4px 14px rgba(37, 211, 102, 0.3) !important;
        transition: all 0.25s !important;
        text-transform: none !important;
    }

    .wp-core-ui .button-primary:hover {
        background: linear-gradient(135deg, #128c7e, #075e54) !important;
        box-shadow: 0 6px 20px rgba(37, 211, 102, 0.35) !important;
        transform: translateY(-1px);
    }
    </style>

    <div class="wrap">
        <div class="swcw-admin">

            <!-- Header -->
            <div class="swcw-admin-header">
                <svg viewBox="0 0 48 48" fill="none">
                    <path d="M24 4C12.954 4 4 12.954 4 24c0 3.535.93 6.94 2.696 9.953L4.004 43.99l10.27-2.694A19.93 19.93 0 0024 44c11.046 0 20-8.954 20-20S35.046 4 24 4z" fill="#25D366"/>
                    <path d="M24 7.2c-9.26 0-16.8 7.54-16.8 16.8 0 2.96.77 5.84 2.24 8.39l.34.57-1.44 5.27 5.4-1.42.55.33A16.72 16.72 0 0024 40.8c9.26 0 16.8-7.54 16.8-16.8S33.26 7.2 24 7.2z" fill="#fff"/>
                    <path d="M17.472 14.382c-.372-.828-.764-.845-1.118-.86l-.954-.013c-.33 0-.868.124-1.322.618-.454.495-1.735 1.696-1.735 4.133s1.776 4.795 2.025 5.126c.248.33 3.43 5.495 8.467 7.489 4.185 1.657 5.038 1.327 5.946 1.245.908-.083 2.929-1.198 3.341-2.355.413-1.157.413-2.148.289-2.355-.124-.206-.454-.33-.95-.578-.495-.248-2.928-1.445-3.382-1.61-.454-.166-.784-.249-1.114.248-.33.495-1.28 1.61-1.569 1.94-.289.33-.578.372-1.073.124-.496-.248-2.092-.771-3.986-2.46-1.474-1.314-2.468-2.938-2.758-3.434-.289-.495-.03-.763.218-1.01.222-.222.495-.578.743-.868.248-.289.33-.495.496-.826.165-.33.082-.619-.042-.868-.124-.248-1.095-2.694-1.527-3.682z" fill="#25D366"/>
                </svg>
                <h1>Smart WhatsApp Chat Widget</h1>
                <span class="swcw-version">v<?php echo esc_html( SWCW_VERSION ); ?></span>
            </div>

            <!-- Settings Card -->
            <div class="swcw-card">
                <form action="options.php" method="post">
                    <?php
                    settings_fields( 'swcw_settings_group' );
                    do_settings_sections( 'smart-whatsapp-chat-widget' );
                    submit_button( '💾 Save Settings' );
                    ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Media Uploader + Color Hex Sync -->
    <script>
    (function(){
        // Wait for jQuery (loaded by WP admin).
        if (typeof jQuery === 'undefined') return;

        jQuery(document).ready(function($){

            /* ── Media Uploader ── */
            var mediaFrame;
            $(document).on('click', '#swcw_upload_btn', function(e){
                e.preventDefault();
                if (mediaFrame) { mediaFrame.open(); return; }

                mediaFrame = wp.media({
                    title:    'Select Company Logo',
                    button:   { text: 'Use this logo' },
                    multiple: false,
                    library:  { type: 'image' }
                });

                mediaFrame.on('select', function(){
                    var url = mediaFrame.state().get('selection').first().toJSON().url;
                    $('#swcw_logo_url').val(url);
                    // Show preview.
                    if ($('.swcw-logo-preview').length) {
                        $('.swcw-logo-preview').attr('src', url);
                    } else {
                        $('<img class="swcw-logo-preview" alt="Logo preview">').attr('src', url).prependTo('.swcw-upload-wrap');
                    }
                });

                mediaFrame.open();
            });

            /* ── Remove Logo ── */
            $(document).on('click', '#swcw_remove_btn', function(e){
                e.preventDefault();
                $('#swcw_logo_url').val('');
                $('.swcw-logo-preview').remove();
                $(this).remove();
            });

            /* ── Sync Color Hex Label ── */
            $(document).on('input', '.swcw-color-input', function(){
                $(this).siblings('.swcw-color-hex').text(this.value);
            });
        });
    })();
    </script>
    <?php
}

/* ───────────────────────────────────────────
 * 6.  Enqueue WP Media on Settings Page
 * ─────────────────────────────────────────── */
function swcw_admin_enqueue( $hook ) {
    if ( 'settings_page_smart-whatsapp-chat-widget' !== $hook ) {
        return;
    }
    wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'swcw_admin_enqueue' );
