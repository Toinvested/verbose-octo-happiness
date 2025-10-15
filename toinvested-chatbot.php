<?php
/**
 * Plugin Name: ToInvested Chatbot
 * Description: AI-powered real estate investment chatbot for ToInvested.com
 * Version: 1.0.0
 * Author: ToInvested
 * Text Domain: toinvested-chatbot
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ToInvestedChatbot {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'add_chatbot_html'));
    }
    
    public function enqueue_scripts() {
        // Enqueue CSS
        wp_enqueue_style(
            'toinvested-chatbot-style',
            plugin_dir_url(__FILE__) . 'chatbot-style.css',
            array(),
            '1.0.0'
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'toinvested-chatbot-script',
            plugin_dir_url(__FILE__) . 'chatbot-widget.js',
            array('jquery'),
            '1.0.0',
            true
        );
    }
    
    public function add_chatbot_html() {
        echo '<div id="toinvested-chatbot-widget"></div>';
    }
}

// Initialize the plugin
new ToInvestedChatbot();
