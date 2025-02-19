<?php
/**
 * Handles admin interface and settings
 */
namespace AI_Content_Generator\Admin;

class AI_Content_Generator_Admin {
    
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_admin_menu']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
        add_action('woocommerce_product_options_general_product_data', [__CLASS__, 'add_product_meta_box']);
    }

    public static function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('AI Content Generator Settings', 'ai-content-generator'),
            __('AI Content Generator', 'ai-content-generator'),
            'manage_options',
            'ai-content-generator-settings',
            [__CLASS__, 'render_settings_page']
        );
    }

    public static function register_settings() {
        register_setting(
            'wc_ai_content_generator',
            'wc_ai_content_generator_api_key',
            ['sanitize_callback' => 'sanitize_text_field']
        );

        register_setting(
            'wc_ai_content_generator',
            'wc_ai_default_temperature',
            [
                'sanitize_callback' => function($input) {
                    $value = floatval($input);
                    return max(0, min(1, $value));
                },
                'default' => 0.7
            ]
        );

        add_settings_section(
            'wc_ai_content_main',
            __('API Settings', 'ai-content-generator'),
            null,
            'ai-content-generator-settings'
        );

        add_settings_field(
            'api_key',
            __('DeepSeek API Key', 'ai-content-generator'),
            [__CLASS__, 'render_api_key_field'],
            'ai-content-generator-settings',
            'wc_ai_content_main'
        );

        add_settings_field(
            'default_temperature',
            __('Default Temperature', 'ai-content-generator'),
            [__CLASS__, 'render_temperature_field'],
            'ai-content-generator-settings',
            'wc_ai_content_main'
        );
    }

    public static function render_settings_page() {
        include AI_CONTENT_GENERATOR_PATH . 'admin/settings-page.php';
    }

    public static function render_api_key_field() {
        $api_key = get_option('wc_ai_content_generator_api_key');
        ?>
        <input type="password" 
               name="wc_ai_content_generator_api_key" 
               id="wc_ai_content_generator_api_key" 
               value="<?php echo esc_attr($api_key); ?>" 
               class="regular-text"
               autocomplete="new-password">
        <p class="description">
            <?php esc_html_e('Get your API key from DeepSeek AI platform', 'ai-content-generator'); ?>
        </p>
        <?php
    }

    public static function render_temperature_field() {
        $temperature = get_option('wc_ai_default_temperature', 0.7);
        ?>
        <input type="number" 
               name="wc_ai_default_temperature" 
               id="wc_ai_default_temperature" 
               value="<?php echo esc_attr($temperature); ?>" 
               min="0" 
               max="1" 
               step="0.1" 
               class="small-text">
        <p class="description">
            <?php esc_html_e('Control creativity level (0 = strict, 1 = creative)', 'ai-content-generator'); ?>
        </p>
        <?php
    }

    public static function enqueue_admin_assets($hook) {
        if($hook === 'woocommerce_page_ai-content-generator-settings' || $hook === 'post.php') {
            wp_enqueue_style(
                'ai-content-generator-admin',
                AI_CONTENT_GENERATOR_URL . 'assets/css/admin-style.css',
                [],
                AI_CONTENT_GENERATOR_VERSION
            );

            wp_enqueue_script(
                'ai-content-generator-admin',
                AI_CONTENT_GENERATOR_URL . 'assets/js/admin-script.js',
                ['jquery', 'wp-i18n'],
                AI_CONTENT_GENERATOR_VERSION,
                true
            );

            wp_set_script_translations('ai-content-generator-admin', 'ai-content-generator');

            wp_localize_script('ai-content-generator-admin', 'aiContentGenerator', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ai_content_nonce'),
                'i18n' => [
                    'generating' => __('Generating...', 'ai-content-generator'),
                    'error' => __('Error:', 'ai-content-generator')
                ]
            ]);
        }
    }

    public static function add_product_meta_box() {
        global $post;
        
        echo '<div class="options_group ai-content-generator-box">';
        echo '<h3>' . esc_html__('AI Content Generation', 'ai-content-generator') . '</h3>';
        
        woocommerce_wp_text_input([
            'id' => 'ai_content_prompt',
            'label' => __('Prompt for AI', 'ai-content-generator'),
            'description' => __('Enter your content generation prompt', 'ai-content-generator'),
            'desc_tip' => true,
            'type' => 'textarea',
            'class' => 'short',
            'style' => 'height: 80px;'
        ]);

        echo '<button type="button" class="button generate-ai-content" data-target="#_product_description">';
        echo esc_html__('Generate Description', 'ai-content-generator');
        echo '</button>';

        echo '</div>';
    }
}