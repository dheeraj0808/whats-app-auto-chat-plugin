<?php
/**
 * Widget Template — Rendered in wp_footer
 *
 * All variables come from the parent scope in swcw_inject_widget().
 * This file MUST be included, never accessed directly.
 *
 * @package SmartWhatsAppChatWidget
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ── Retrieve & Escape Options ────── */
$company_name = esc_html( $options['company_name'] );
$wa_number    = esc_attr( swcw_sanitize_phone( $options['wa_number'] ) );
$welcome_msg  = wp_kses_post( $options['welcome_msg'] );
$reply_time   = esc_html( $options['reply_time'] );
$placeholder  = esc_attr( $options['default_message'] );
$position     = $options['position']; // Already validated (left|right).
$btn_color    = esc_attr( $options['btn_color'] );
$logo_url     = esc_url( $options['logo_url'] );
$pulse_class  = ( $options['blinking'] === 'on' ) ? ' swcw-pulse' : '';

/* ── Parse FAQ Tree ────── */
$faq_tree = swcw_parse_faq_tree( $options['faqs'] );
?>

<!-- Smart WhatsApp Chat Widget v2.0.0 -->
<div class="swcw-container swcw-pos-<?php echo $position; ?>"
     id="swcwWidgetContainer"
     style="--swcw-btn-color: <?php echo $btn_color; ?>;">

    <!-- ══════ Chat Window ══════ -->
    <div class="swcw-window" id="swcwWindow">

        <!-- ── Header ── -->
        <div class="swcw-header">
            <div class="swcw-header-left">
                <div class="swcw-avatar">
                    <?php if ( $logo_url ) : ?>
                        <img src="<?php echo $logo_url; ?>" alt="<?php echo $company_name; ?>">
                    <?php else : ?>
                        <!-- Default WhatsApp-style avatar -->
                        <svg viewBox="0 0 212 212" width="44" height="44"><path fill="#DFE5E7" d="M106.251.5C164.653.5 212 47.846 212 106.25S164.653 212 106.25 212C47.846 212 .5 164.654.5 106.25S47.846.5 106.251.5z"/><path fill="#FFF" d="M173.561 171.615a62.767 62.767 0 0 0-2.065-2.955 67.7 67.7 0 0 0-2.608-3.299 70.112 70.112 0 0 0-3.184-3.527 71.097 71.097 0 0 0-5.924-5.47 72.458 72.458 0 0 0-10.204-7.027 75.2 75.2 0 0 0-5.98-3.055c-.281-.121-.563-.238-.849-.351a74.907 74.907 0 0 0-3.888-1.459c0-.012 0-.024-.005-.036a53.03 53.03 0 0 1-2.445-1.327c-.283-.399-.564-.801-.838-1.21a47.79 47.79 0 0 1-1.913-3.071 53.07 53.07 0 0 1-1.592-3.106 51.07 51.07 0 0 1-2.752-8.045 51.929 51.929 0 0 1-1.15-7.32 52.36 52.36 0 0 1-.084-7.607 52.35 52.35 0 0 1 .874-6.84 51.6 51.6 0 0 1 3.579-11.397 47.156 47.156 0 0 1 3.07-5.395c.455-.679.927-1.345 1.413-1.995a56.835 56.835 0 0 1 1.703-7.31c.161-.541.312-1.098.448-1.671.137-.574.261-1.163.368-1.764a47.53 47.53 0 0 0 .544-5.028 46.52 46.52 0 0 0-.012-4.643c-.076-.84-.186-1.657-.326-2.447a30.593 30.593 0 0 0-.533-2.317c-.219-.746-.47-1.461-.752-2.135-.282-.675-.594-1.311-.935-1.899-.34-.588-.711-1.127-1.11-1.607a12.487 12.487 0 0 0-1.312-1.37 10.247 10.247 0 0 0-1.533-1.092 8.79 8.79 0 0 0-1.787-.793 8.048 8.048 0 0 0-2.063-.424 8.394 8.394 0 0 0-2.373.108 9.862 9.862 0 0 0-2.726.87 13.42 13.42 0 0 0-3.125 2.072 18.888 18.888 0 0 0-3.537 4.382c-.535.849-1.034 1.774-1.492 2.77a35.06 35.06 0 0 0-1.218 3.278c-.354 1.146-.659 2.351-.91 3.607a48.487 48.487 0 0 0-.66 7.971c-.013.97.001 1.939.043 2.906a50.66 50.66 0 0 0 .473 5.704c.103.684.22 1.363.354 2.037.133.672.282 1.339.446 2a53.27 53.27 0 0 0 2.107 6.777c.258.646.528 1.281.808 1.903.28.622.571 1.233.872 1.831a48.94 48.94 0 0 0 1.89 3.389 44.39 44.39 0 0 0 2.037 3.03c.352.469.713.928 1.085 1.376-.119 2.033-.335 4.053-.648 6.054a74.44 74.44 0 0 1-1.414 6.617c-.561 2.134-1.222 4.209-1.974 6.212a69.31 69.31 0 0 1-1.254 3.106 71.35 71.35 0 0 1-1.41 3.063 73.32 73.32 0 0 1-5.441 9.22 75.66 75.66 0 0 1-2.935 3.869 77.11 77.11 0 0 1-2.165 2.529 73.09 73.09 0 0 1-3.393 3.545 75.384 75.384 0 0 1-5.713 5.008 76.735 76.735 0 0 1-4.692 3.44 75.87 75.87 0 0 1-3.292 2.058c-.377.215-.753.424-1.13.624 49.18 1.212 88.785-38.88 90.543-88.102z"/></svg>
                    <?php endif; ?>
                    <span class="swcw-online-dot"></span>
                </div>
                <div class="swcw-header-info">
                    <h4><?php echo $company_name; ?></h4>
                    <p><?php echo $reply_time; ?></p>
                </div>
            </div>
            <button class="swcw-close-btn" id="swcwClose" aria-label="Close chat">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <!-- ── Chat Body ── -->
        <div class="swcw-body" id="swcwBody">
            <!-- Welcome Bubble -->
            <div class="swcw-bubble swcw-bubble--bot">
                <div class="swcw-bubble__content">
                    <?php echo $welcome_msg; ?>
                    <span class="swcw-bubble__time"><?php echo esc_html( current_time( 'H:i' ) ); ?></span>
                </div>
            </div>
        </div>

        <!-- ── Footer / Input ── -->
        <div class="swcw-footer">
            <div class="swcw-input-group">
                <input type="text"
                       id="swcwInput"
                       placeholder="<?php echo $placeholder; ?>"
                       autocomplete="off">
                <button id="swcwSend"
                        data-number="<?php echo $wa_number; ?>"
                        aria-label="Send message">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                    </svg>
                </button>
            </div>
            <p class="swcw-powered">Powered by WhatsApp</p>
        </div>
    </div>

    <!-- ══════ Floating Trigger Button ══════ -->
    <div class="swcw-trigger<?php echo $pulse_class; ?>" id="swcwTrigger" aria-label="Open WhatsApp chat">
        <!-- Official WhatsApp Icon -->
        <svg viewBox="0 0 48 48" width="34" height="34" fill="none">
            <path d="M24 4C12.954 4 4 12.954 4 24c0 3.535.93 6.94 2.696 9.953L4.004 43.99l10.27-2.694A19.93 19.93 0 0024 44c11.046 0 20-8.954 20-20S35.046 4 24 4z" fill="#25D366"/>
            <path d="M24 7.2c-9.26 0-16.8 7.54-16.8 16.8 0 2.96.77 5.84 2.24 8.39l.34.57-1.44 5.27 5.4-1.42.55.33A16.72 16.72 0 0024 40.8c9.26 0 16.8-7.54 16.8-16.8S33.26 7.2 24 7.2z" fill="#fff"/>
            <path d="M17.472 14.382c-.372-.828-.764-.845-1.118-.86l-.954-.013c-.33 0-.868.124-1.322.618-.454.495-1.735 1.696-1.735 4.133s1.776 4.795 2.025 5.126c.248.33 3.43 5.495 8.467 7.489 4.185 1.657 5.038 1.327 5.946 1.245.908-.083 2.929-1.198 3.341-2.355.413-1.157.413-2.148.289-2.355-.124-.206-.454-.33-.95-.578-.495-.248-2.928-1.445-3.382-1.61-.454-.166-.784-.249-1.114.248-.33.495-1.28 1.61-1.569 1.94-.289.33-.578.372-1.073.124-.496-.248-2.092-.771-3.986-2.46-1.474-1.314-2.468-2.938-2.758-3.434-.289-.495-.03-.763.218-1.01.222-.222.495-.578.743-.868.248-.289.33-.495.496-.826.165-.33.082-.619-.042-.868-.124-.248-1.095-2.694-1.527-3.682z" fill="#25D366"/>
        </svg>
    </div>
</div>

<?php if ( ! empty( $faq_tree ) ) : ?>
<!-- FAQ tree data for the client-side chatbot engine -->
<script>var swcwFaqTree = <?php echo wp_json_encode( $faq_tree ); ?>;</script>
<?php endif; ?>
