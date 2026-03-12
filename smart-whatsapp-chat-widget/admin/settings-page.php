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
        'auto_open'       => 'Widget Auto Open',
        'delay'           => 'Delay Before Opening (seconds)',
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
    add_options_page(
        'Smart WhatsApp Chat Widget',
        'WhatsApp Widget',
        'manage_options',
        'smart-whatsapp-chat-widget',
        'swcw_settings_page_html'
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
    $output['auto_open'] = isset($input['auto_open']) ? 'yes' : 'no';
    $output['delay'] = absint($input['delay']);
    
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
        case 'auto_open':
            echo '<input type="checkbox" name="swcw_settings[auto_open]" ' . checked($value, 'yes', false) . '>';
            break;
        case 'delay':
            echo '<input type="number" name="swcw_settings[delay]" value="' . esc_attr($value ?: 0) . '" class="small-text"> seconds';
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
    <div class="wrap">
        <h1>Smart WhatsApp Chat Widget</h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'swcw_settings_group' );
            do_settings_sections( 'smart-whatsapp-chat-widget' );
            submit_button();
            ?>
        </form>
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
