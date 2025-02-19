<?php
class AI_Content_Generator_API {
    private static $api_endpoint = 'https://api.deepseek.com/v1/chat/completions';

    public static function init() {
        add_action('wp_ajax_generate_ai_content', [__CLASS__, 'handle_content_generation']);
    }

    public static function generate_content($prompt, $temperature = 0.7) {
        $api_key = get_option('wc_ai_content_generator_api_key');
        
        if(empty($api_key)) {
            return new WP_Error('missing_api_key', __('API key is missing', 'ai-content-generator'));
        }

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        ];

        $body = [
            'model' => get_option('wc_ai_content_model', 'deepseek-chat'),
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => (float)$temperature,
            'max_tokens' => 1000
        ];

        $response = wp_remote_post(self::$api_endpoint, [
            'headers' => $headers,
            'body' => json_encode($body),
            'timeout' => 30
        ]);

        if(is_wp_error($response)) {
            return $response;
        }

        $response_body = json_decode(wp_remote_retrieve_body($response), true);

        return $response_body['choices'][0]['message']['content'] ?? '';
    }

    public static function handle_content_generation() {
        check_ajax_referer('ai_content_nonce', 'security');

        if(!current_user_can('edit_products')) {
            wp_send_json_error(__('Permission denied', 'ai-content-generator'));
        }

        $prompt = sanitize_text_field($_POST['prompt']);
        $temperature = isset($_POST['temperature']) ? (float)$_POST['temperature'] : 0.7;

        $generated_content = self::generate_content($prompt, $temperature);

        if(is_wp_error($generated_content)) {
            wp_send_json_error($generated_content->get_error_message());
        }

        wp_send_json_success([
            'content' => wp_kses_post($generated_content),
            'temperature' => $temperature
        ]);
    }
}