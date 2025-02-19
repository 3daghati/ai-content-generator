jQuery(document).ready(function($) {
    $('.generate-ai-content').on('click', function(e) {
        e.preventDefault();
        const button = $(this);
        const target = button.data('target');
        const prompt = $('#ai_content_prompt').val();
        const temperature = $('input[name="wc_ai_default_temperature"]').val() || 0.7;

        button.addClass('ai-loading');

        $.ajax({
            url: aiContentGenerator.ajaxurl,
            type: 'POST',
            data: {
                action: 'generate_ai_content',
                security: aiContentGenerator.nonce,
                prompt: prompt,
                temperature: temperature
            },
            success: function(response) {
                if(response.success) {
                    $(target).val(response.data.content);
                } else {
                    alert(response.data);
                }
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseText);
            },
            complete: function() {
                button.removeClass('ai-loading');
            }
        });
    });
});