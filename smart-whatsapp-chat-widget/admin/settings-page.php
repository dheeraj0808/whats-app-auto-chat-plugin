<?php
/**
 * Admin Settings Page for Smart WhatsApp Chat Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register settings and sections
 */
function swcw_register_settings() {
    register_setting( 'swcw_settings_group', 'swcw_settings', 'swcw_sanitize_settings' );

    add_settings_section(
        'swcw_main_section',
        'Widget Configuration',
        'swcw_section_cb',
        'smart-whatsapp-chat-widget'
    );

    $fields = array(
        'enabled'         => 'Enable / Disable Widget',
        'company_name'    => 'Company Name',
        'wa_number'       => 'WhatsApp Number',
        'default_message' => 'Default Message',
        'welcome_msg'     => 'Welcome Message',
        'reply_time'      => 'Reply Time Text',
        'position'        => 'Widget Position',
        'btn_color'       => 'Button Color',
        'logo_url'        => 'Company Logo',
        'blinking'        => 'Enable Pulse Animation',
        'auto_open'       => 'Widget Auto Open',
        'delay'           => 'Delay Before Opening (seconds)',
        'faqs'            => 'Quick FAQs (Question|Answer)',
    );

    foreach ( $fields as $id => $title ) {
        add_settings_field(
            'swcw_' . $id,
            $title,
            'swcw_field_cb',
            'smart-whatsapp-chat-widget',
            'swcw_main_section',
            array( 'id' => $id, 'title' => $title )
        );
    }
}
add_action( 'admin_init', 'swcw_register_settings' );

/**
 * Add Menu Page
 */
function swcw_add_admin_menu() {
    add_menu_page(
        'Smart WhatsApp Chat Widget',
        'WhatsApp Widget',
        'manage_options',
        'smart-whatsapp-chat-widget',
        'swcw_settings_page_html',
        'dashicons-admin-comments', // Standard chat icon
        100 // Position
    );
}
add_action( 'admin_menu', 'swcw_add_admin_menu' );

/**
 * Sanitize input
 */
function swcw_sanitize_settings($input) {
    $output = array();
    $output['enabled'] = isset($input['enabled']) ? 'on' : 'off';
    $output['company_name'] = sanitize_text_field($input['company_name']);
    $output['wa_number'] = sanitize_text_field($input['wa_number']);
    $output['default_message'] = sanitize_text_field($input['default_message']);
    $output['welcome_msg'] = wp_kses_post($input['welcome_msg']);
    $output['reply_time'] = sanitize_text_field($input['reply_time']);
    $output['position'] = in_array($input['position'], array('right', 'left')) ? $input['position'] : 'right';
    $output['btn_color'] = sanitize_hex_color($input['btn_color']);
    $output['logo_url'] = esc_url_raw($input['logo_url']);
    $output['blinking'] = isset($input['blinking']) ? 'on' : 'off';
    $output['auto_open'] = isset($input['auto_open']) ? 'yes' : 'no';
    $output['delay'] = absint($input['delay']);
    $output['faqs'] = sanitize_textarea_field($input['faqs']);
    
    return $output;
}

function swcw_section_cb() {
    echo 'Configure your WhatsApp chat widget appearance and behavior below.';
}

/**
 * Render fields
 */
function swcw_field_cb($args) {
    $options = get_option('swcw_settings');
    $id = $args['id'];
    $value = isset($options[$id]) ? $options[$id] : '';

    switch ($id) {
        case 'enabled':
            echo '<input type="checkbox" name="swcw_settings[enabled]" ' . checked($value, 'on', false) . '>';
            break;
        case 'position':
            echo '<select name="swcw_settings[position]">
                    <option value="right" ' . selected($value, 'right', false) . '>Right Bottom</option>
                    <option value="left" ' . selected($value, 'left', false) . '>Left Bottom</option>
                  </select>';
            break;
        case 'btn_color':
            echo '<input type="color" name="swcw_settings[btn_color]" value="' . esc_attr($value ?: '#25D366') . '">';
            break;
        case 'welcome_msg':
            echo '<textarea name="swcw_settings[welcome_msg]" rows="3" class="large-text">' . esc_textarea($value) . '</textarea>';
            break;
        case 'logo_url':
            echo '<input type="text" id="swcw_logo_url" name="swcw_settings[logo_url]" value="' . esc_url($value) . '" class="regular-text">';
            echo ' <button type="button" class="button" id="swcw_upload_btn">Upload/Select Image</button>';
            break;
        case 'blinking':
            echo '<input type="checkbox" name="swcw_settings[blinking]" ' . checked($value, 'on', false) . '>';
            echo '<p class="description">Adds an attention-grabbing pulse effect to the button.</p>';
            break;
        case 'auto_open':
            echo '<input type="checkbox" name="swcw_settings[auto_open]" ' . checked($value, 'yes', false) . '>';
            break;
        case 'delay':
            echo '<input type="number" name="swcw_settings[delay]" value="' . esc_attr($value ?: 0) . '" class="small-text"> seconds';
            break;
        case 'faqs':
            echo '<textarea name="swcw_settings[faqs]" rows="10" class="large-text" placeholder="Price|Our prices start from ₹250&#10;>Basic Plan|₹250/month with essential features&#10;>Pro Plan|₹500/month with premium features&#10;Services|We offer multiple services&#10;>Web Design|Professional website design&#10;>SEO|Search engine optimization&#10;Contact|Reach us at contact@example.com">' . esc_textarea($value) . '</textarea>';
            echo '<div class="swcw-faq-help" style="background:#f0f7f4;border-left:4px solid #25D366;padding:12px 16px;margin-top:10px;border-radius:4px;font-size:13px;line-height:1.8;">';
            echo '<strong style="color:#075e54;">📖 Hierarchical FAQ Format:</strong><br>';
            echo '<code style="background:#e8f5e9;padding:2px 6px;border-radius:3px;">Question|Answer</code> — Root level question<br>';
            echo '<code style="background:#e8f5e9;padding:2px 6px;border-radius:3px;">>Sub Question|Answer</code> — Sub question (child of above root)<br>';
            echo '<code style="background:#e8f5e9;padding:2px 6px;border-radius:3px;">>>Sub Sub Question|Answer</code> — Deeper level child<br><br>';
            echo '<strong>Example:</strong><br>';
            echo '<pre style="background:#fff;padding:10px;border-radius:6px;border:1px solid #ddd;font-size:12px;margin-top:5px;overflow-x:auto;">';
            echo "Price|Our prices start from ₹250\n";
            echo ">Basic Plan|₹250/month with essential features\n";
            echo ">Pro Plan|₹500/month with premium features\n";
            echo ">>Pro Monthly|₹500 billed monthly\n";
            echo ">>Pro Yearly|₹5000 billed yearly (save ₹1000!)\n";
            echo "Services|We offer multiple services\n";
            echo ">Web Design|Professional website design\n";
            echo ">SEO|Search engine optimization\n";
            echo "Contact|Reach us at contact@example.com";
            echo '</pre>';
            echo '</div>';
            break;
        default:
            echo '<input type="text" name="swcw_settings[' . $id . ']" value="' . esc_attr($value) . '" class="regular-text">';
            break;
    }
}

/**
 * Settings Page HTML
 */
function swcw_settings_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    ?>
    <style>
        .swcw-admin-wrap {
            max-width: 800px;
            margin-top: 30px;
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .swcw-admin-wrap h1 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 30px;
            color: #1a1a1a;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 20px;
        }
        .form-table th {
            font-weight: 600;
            color: #444;
            padding: 25px 10px;
            width: 250px;
        }
        .form-table td {
            padding: 20px 10px;
        }
        .regular-text, .large-text {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 10px 15px;
            background: #fafafa;
        }
        .regular-text:focus, .large-text:focus {
            border-color: #25D366;
            box-shadow: 0 0 0 2px rgba(37, 211, 102, 0.1);
            background: #fff;
        }
        .wp-core-ui .button-primary {
            background: #25D366;
            border-color: #25D366;
            padding: 10px 30px;
            height: auto;
            border-radius: 100px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .wp-core-ui .button-primary:hover {
            background: #0b6b63;
            border-color: #0b6b63;
        }
    </style>
    <div class="wrap">
        <div class="swcw-admin-wrap">
            <h1>Smart WhatsApp Chat Widget</h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'swcw_settings_group' );
                do_settings_sections( 'smart-whatsapp-chat-widget' );
                submit_button('Save Widget Settings');
                ?>
            </form>
        </div>
    </div>
    <script>
    jQuery(document).ready(function($){
        // Media Uploader
        var frame;
        $('#swcw_upload_btn').on('click', function(e){
            e.preventDefault();
            if(frame){ frame.open(); return; }
            frame = wp.media({
                title: 'Select Company Logo',
                button: { text: 'Use this logo' },
                multiple: false
            });
            frame.on('select', function(){
                var attachment = frame.state().get('selection').first().toJSON();
                $('#swcw_logo_url').val(attachment.url);
            });
            frame.open();
        });
    });
    </script>
    <?php
}

/**
 * Enqueue Media Scripts for Settings Page
 */
function swcw_admin_scripts($hook) {
    if ( $hook !== 'settings_page_smart-whatsapp-chat-widget' ) return;
    wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'swcw_admin_scripts' );
