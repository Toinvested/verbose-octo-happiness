jQuery(document).ready(function($) {
    // Create the chatbot widget HTML
    var chatbot_html = `
        <div class="chatbot-header">
            <span>🤖 AI Property Expert</span>
            <button class="chatbot-toggle" style="float: right; background: none; border: none; color: white; font-size: 18px; cursor: pointer;">−</button>
        </div>
        <div class="chatbot-conversation" style="display: none;">
            <div class="chatbot-message bot">
                <div class="message-content">
                    Hi! I'm your AI Property Investment Expert. I can help you analyze properties, understand market trends, and guide you through real estate investments. What would you like to know?
                </div>
            </div>
        </div>
        <div class="chatbot-input" style="display: none;">
            <input type="text" placeholder="Ask about property analysis, market trends, or investment strategies...">
            <button>➤</button>
        </div>
    `;

    // Initially hide the entire widget
    $('#toinvested-chatbot-widget').html(chatbot_html).hide();

    // Show the widget after a delay (15 seconds)
    setTimeout(function() {
        $('#toinvested-chatbot-widget').fadeIn(500);
    }, 15000);

    // Also show if user scrolls down (engaged with content)
    var hasShown = false;
    $(window).scroll(function() {
        if (!hasShown && $(window).scrollTop() > 300) {
            hasShown = true;
            $('#toinvested-chatbot-widget').fadeIn(500);
        }
    });

    // Toggle chatbot conversation visibility
    $('.chatbot-header').on('click', function() {
        var conversation = $('.chatbot-conversation');
        var input = $('.chatbot-input');
        var toggle = $('.chatbot-toggle');
        
        if (conversation.is(':visible')) {
            conversation.slideUp(300);
            input.slideUp(300);
            toggle.text('+');
        } else {
            conversation.slideDown(300);
            input.slideDown(300);
            toggle.text('−');
        }
    });

    // Generate a simple session ID
    var sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

    // Handle sending a message
    function sendMessage() {
        var message_text = $('.chatbot-input input').val().trim();
        if (message_text === '') {
            return;
        }

        // Auto-expand chat if it's collapsed
        if (!$('.chatbot-conversation').is(':visible')) {
            $('.chatbot-conversation').slideDown(300);
            $('.chatbot-input').slideDown(300);
            $('.chatbot-toggle').text('−');
        }

        // Add the user's message to the conversation
        $('.chatbot-conversation').append('<div class="chatbot-message user"><div class="message-content">' + message_text + '</div></div>');
        $('.chatbot-input input').val('');

        // Add typing indicator
        $('.chatbot-conversation').append('<div class="chatbot-message bot typing"><div class="message-content">Thinking...</div></div>');
        $('.chatbot-conversation').scrollTop($('.chatbot-conversation')[0].scrollHeight);

        // Send the message to your live API
        $.ajax({
            url: 'http://89.116.50.58:5000/send_message',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ 
                message: message_text,
                session_id: sessionId
            }),
            success: function(response) {
                // Remove typing indicator
                $('.chatbot-message.typing').remove();
                
                // Add the bot's reply to the conversation
                $('.chatbot-conversation').append('<div class="chatbot-message bot"><div class="message-content">' + response.reply + '</div></div>');
                
                // Scroll to the bottom of the conversation
                $('.chatbot-conversation').scrollTop($('.chatbot-conversation')[0].scrollHeight);
            },
            error: function(xhr, status, error) {
                // Remove typing indicator
                $('.chatbot-message.typing').remove();
                
                console.error('Chatbot API Error:', error);
                $('.chatbot-conversation').append('<div class="chatbot-message bot"><div class="message-content">I apologize, but I\'m having trouble connecting right now. Please try again in a moment, or feel free to contact us directly for assistance.</div></div>');
                
                // Scroll to the bottom of the conversation
                $('.chatbot-conversation').scrollTop($('.chatbot-conversation')[0].scrollHeight);
            },
            timeout: 10000 // 10 second timeout
        });
    }

    // Send message on button click
    $(document).on('click', '.chatbot-input button', sendMessage);

    // Send message on Enter key press
    $(document).on('keypress', '.chatbot-input input', function(e) {
        if (e.which === 13) {
            sendMessage();
        }
    });

    // Focus on input when chat is expanded
    $(document).on('click', '.chatbot-header', function() {
        setTimeout(function() {
            if ($('.chatbot-input').is(':visible')) {
                $('.chatbot-input input').focus();
            }
        }, 350);
    });
});
