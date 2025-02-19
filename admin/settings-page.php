<div class="wrap ai-content-settings">
    <h1 class="ai-settings-title">
        <span class="ai-icon">🤖</span>
        <?php esc_html_e('AI Content Generator Settings', 'ai-content-generator'); ?>
    </h1>
    
    <div class="ai-settings-container">
        <form method="post" action="options.php" class="ai-settings-form">
            <?php
            settings_fields('wc_ai_content_generator');
            do_settings_sections('ai-content-generator-settings');
            submit_button();
            ?>
        </form>
    </div>
</div>