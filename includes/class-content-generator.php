<?php
/**
 * Handles AI content generation operations
 */

class AI_Content_Generator {

    /**
     * Generate product content based on parameters
     *
     * @param array $args Generation arguments
     * @return array|WP_Error
     */
    public static function generate_product_content($args) {
        $defaults = [
            'product_id'    => 0,
            'content_type'  => 'description',
            'temperature'  => 0.7,
            'max_length'    => 1000,
            'language'     => get_locale(),
            'extra_prompt' => ''
        ];

        $args = wp_parse_args($args, $defaults);

        try {
            // Validate input
            if(!self::validate_generation_args($args)) {
                throw new Exception(__('Invalid generation parameters', 'ai-content-generator'));
            }

            // Build the AI prompt
            $prompt = self::build_prompt($args);

            // Get API response
            $api_response = AI_Content_Generator_API::generate_content(
                $prompt,
                $args['temperature'],
                $args['max_length']
            );

            if(is_wp_error($api_response)) {
                return $api_response;
            }

            // Process and sanitize output
            return self::process_output($api_response, $args['content_type']);

        } catch (Exception $e) {
            return new WP_Error('generation_failed', $e->getMessage());
        }
    }

    /**
     * Build AI prompt based on product data
     */
    private static function build_prompt($args) {
        $product = wc_get_product($args['product_id']);

        $base_prompt = sprintf(
            /* translators: %1$s: Product name, %2$s: Content type */
            __('Generate a product %2$s for "%1$s" in %3$s language. Focus on:', 'ai-content-generator'),
            $product->get_name(),
            self::get_content_type_label($args['content_type']),
            self::get_language_name($args['language'])
        );

        $structured_data = [
            'product_name' => $product->get_name(),
            'short_description' => $product->get_short_description(),
            'attributes' => $product->get_attributes(),
            'category' => wp_strip_all_tags(wc_get_product_category_list($args['product_id']))
        ];

        return implode("\n", [
            $base_prompt,
            $args['extra_prompt'],
            __('Structured product data:', 'ai-content-generator'),
            json_encode($structured_data, JSON_PRETTY_PRINT)
        ]);
    }

    /**
     * Process and sanitize API output
     */
    private static function process_output($content, $content_type) {
        $sanitized = wp_kses_post($content);

        switch($content_type) {
            case 'short_description':
                return wp_trim_words($sanitized, 55);
                
            case 'meta_description':
                return wp_trim_words($sanitized, 25);
                
            case 'meta_title':
                return wp_trim_words($sanitized, 8);
                
            default:
                return $sanitized;
        }
    }

    /**
     * Validate generation arguments
     */
    private static function validate_generation_args($args) {
        if(!in_array($args['content_type'], ['description', 'short_description', 'meta_title', 'meta_description'])) {
            return false;
        }

        if($args['temperature'] < 0 || $args['temperature'] > 1) {
            return false;
        }

        if(!get_post($args['product_id'])) {
            return false;
        }

        return true;
    }

    /**
     * Get content type labels
     */
    private static function get_content_type_label($type) {
        $labels = [
            'description'        => __('Full Description', 'ai-content-generator'),
            'short_description' => __('Short Description', 'ai-content-generator'),
            'meta_title'        => __('Meta Title', 'ai-content-generator'),
            'meta_description'   => __('Meta Description', 'ai-content-generator')
        ];

        return $labels[$type] ?? '';
    }

    /**
     * Get language name from locale
     */
    private static function get_language_name($locale) {
        require_once ABSPATH . 'wp-admin/includes/translation-install.php';
        $translations = wp_get_available_translations();
        
        return $translations[$locale]['native_name'] ?? __('Unknown language', 'ai-content-generator');
    }
}