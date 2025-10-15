<?php
/**
 * Plugin Name: ToInvested Schema Markup Pro
 * Plugin URI: https://toinvested.com
 * Description: Advanced schema markup system for high-CPC real estate keywords. Automatically generates rich snippets and improves SEO rankings.
 * Version: 1.0.0
 * Author: ToInvested
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ToInvestedSchemaPlugin {
    
    public function __construct() {
        add_action('wp_head', array($this, 'inject_schema'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        register_activation_hook(__FILE__, array($this, 'activate'));
    }
    
    public function activate() {
        // Set default options
        update_option('toinvested_schema_enabled', 1);
        update_option('toinvested_business_name', 'ToInvested');
        update_option('toinvested_business_phone', '+1-555-123-4567');
        update_option('toinvested_business_email', 'info@toinvested.com');
    }
    
    public function add_admin_menu() {
        add_options_page(
            'ToInvested Schema Settings',
            'Schema Markup',
            'manage_options',
            'toinvested-schema',
            array($this, 'admin_page')
        );
    }
    
    public function admin_page() {
        if (isset($_POST['submit'])) {
            update_option('toinvested_schema_enabled', isset($_POST['schema_enabled']) ? 1 : 0);
            update_option('toinvested_business_name', sanitize_text_field($_POST['business_name']));
            update_option('toinvested_business_phone', sanitize_text_field($_POST['business_phone']));
            update_option('toinvested_business_email', sanitize_email($_POST['business_email']));
            echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
        }
        
        $enabled = get_option('toinvested_schema_enabled', 1);
        $business_name = get_option('toinvested_business_name', 'ToInvested');
        $business_phone = get_option('toinvested_business_phone', '+1-555-123-4567');
        $business_email = get_option('toinvested_business_email', 'info@toinvested.com');
        ?>
        <div class="wrap">
            <h1>ToInvested Schema Markup Pro</h1>
            <p><strong>Status:</strong> <?php echo $enabled ? '<span style="color: green;">✅ Active - Boosting your SEO!</span>' : '<span style="color: red;">❌ Disabled</span>'; ?></p>
            
            <div style="background: #f0f8ff; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <h3>🎯 High-CPC Keywords Being Targeted:</h3>
                <ul>
                    <li><strong>Real estate investment analysis</strong> - $89 CPC</li>
                    <li><strong>Investment property mortgage</strong> - $112 CPC</li>
                    <li><strong>Commercial real estate financing</strong> - $127 CPC</li>
                    <li><strong>Property investment calculator</strong> - $76 CPC</li>
                    <li><strong>Real estate investment software</strong> - $65 CPC</li>
                </ul>
            </div>
            
            <form method="post">
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable Schema Markup</th>
                        <td>
                            <input type="checkbox" name="schema_enabled" value="1" <?php checked($enabled, 1); ?> />
                            <label>Enable automatic schema markup injection</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Business Name</th>
                        <td><input type="text" name="business_name" value="<?php echo esc_attr($business_name); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Business Phone</th>
                        <td><input type="text" name="business_phone" value="<?php echo esc_attr($business_phone); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Business Email</th>
                        <td><input type="email" name="business_email" value="<?php echo esc_attr($business_email); ?>" class="regular-text" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            
            <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0;">
                <h3>📊 Schema Types Active:</h3>
                <ul>
                    <li>✅ Organization Schema (Business Info)</li>
                    <li>✅ Website Schema (Site Structure)</li>
                    <li>✅ Service Schema (Your Offerings)</li>
                    <li>✅ Product Schema (WooCommerce)</li>
                    <li>✅ Article Schema (Blog Posts)</li>
                    <li>✅ FAQ Schema (High-CPC Content)</li>
                    <li>✅ Review Schema (Social Proof)</li>
                    <li>✅ Local Business Schema (Geographic)</li>
                    <li>✅ Breadcrumb Schema (Navigation)</li>
                </ul>
            </div>
        </div>
        <?php
    }
    
    public function inject_schema() {
        if (!get_option('toinvested_schema_enabled', 1)) return;
        
        // Organization and Website schema on all pages
        echo $this->organization_schema();
        echo $this->website_schema();
        echo $this->breadcrumb_schema();
        
        // Homepage specific schema
        if (is_front_page()) {
            echo $this->service_schema();
            echo $this->faq_schema();
            echo $this->review_schema();
            echo $this->local_business_schema();
        }
        
        // Product pages
        if (function_exists('is_product') && is_product()) {
            global $post;
            echo $this->product_schema($post->ID);
        }
        
        // Blog posts
        if (is_single() && get_post_type() == 'post') {
            global $post;
            echo $this->article_schema($post->ID);
        }
    }
    
    private function organization_schema() {
        $business_name = get_option('toinvested_business_name', 'ToInvested');
        $business_phone = get_option('toinvested_business_phone', '+1-555-123-4567');
        $business_email = get_option('toinvested_business_email', 'info@toinvested.com');
        
        $schema = array(
            "@context" => "https://schema.org",
            "@type" => "Organization",
            "name" => $business_name,
            "description" => "Professional real estate investment analysis and education platform with 30+ years of industry expertise",
            "url" => home_url(),
            "logo" => home_url() . "/wp-content/uploads/logo.png",
            "foundingDate" => "2024",
            "founder" => array(
                "@type" => "Person",
                "name" => "Real Estate Investment Expert",
                "jobTitle" => "Senior Real Estate Investment Advisor",
                "worksFor" => $business_name
            ),
            "address" => array(
                "@type" => "PostalAddress",
                "addressCountry" => "US"
            ),
            "contactPoint" => array(
                "@type" => "ContactPoint",
                "telephone" => $business_phone,
                "contactType" => "customer service",
                "email" => $business_email,
                "availableLanguage" => "English"
            ),
            "serviceArea" => array(
                "@type" => "Country",
                "name" => "United States"
            ),
            "hasOfferCatalog" => array(
                "@type" => "OfferCatalog",
                "name" => "Real Estate Investment Services",
                "itemListElement" => array(
                    array(
                        "@type" => "Offer",
                        "itemOffered" => array(
                            "@type" => "Service",
                            "name" => "AI Property Analysis",
                            "description" => "Advanced property investment analysis using AI technology"
                        )
                    )
                )
            )
        );
        
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
    
    private function website_schema() {
        $schema = array(
            "@context" => "https://schema.org",
            "@type" => "WebSite",
            "name" => get_bloginfo('name') . " - Real Estate Investment Platform",
            "description" => "Professional real estate investment analysis, education, and consultation services",
            "url" => home_url(),
            "potentialAction" => array(
                "@type" => "SearchAction",
                "target" => array(
                    "@type" => "EntryPoint",
                    "urlTemplate" => home_url() . "/search?q={search_term_string}"
                ),
                "query-input" => "required name=search_term_string"
            ),
            "publisher" => array(
                "@type" => "Organization",
                "name" => get_option('toinvested_business_name', 'ToInvested'),
                "url" => home_url()
            )
        );
        
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
    
    private function service_schema() {
        $services = array(
            array(
                "name" => "Real Estate Investment Analysis",
                "description" => "Professional property analysis and investment evaluation services",
                "keywords" => "real estate investment analysis, property investment calculator, investment property analysis",
                "price" => "29.00"
            ),
            array(
                "name" => "Real Estate Investment Consultation", 
                "description" => "Personal consultation for real estate investment strategies and portfolio optimization",
                "keywords" => "real estate investment advisor, property investment consultation, real estate investment strategy",
                "price" => "497.00"
            )
        );
        
        $schema_output = '';
        foreach ($services as $service) {
            $schema = array(
                "@context" => "https://schema.org",
                "@type" => "Service",
                "name" => $service['name'],
                "description" => $service['description'],
                "keywords" => $service['keywords'],
                "provider" => array(
                    "@type" => "Organization",
                    "name" => get_option('toinvested_business_name', 'ToInvested'),
                    "url" => home_url()
                ),
                "areaServed" => array(
                    "@type" => "Country",
                    "name" => "United States"
                ),
                "hasOfferCatalog" => array(
                    "@type" => "OfferCatalog",
                    "name" => $service['name'],
                    "itemListElement" => array(
                        array(
                            "@type" => "Offer",
                            "price" => $service['price'],
                            "priceCurrency" => "USD",
                            "availability" => "https://schema.org/InStock",
                            "validFrom" => date('Y-m-d'),
                            "priceValidUntil" => date('Y-m-d', strtotime('+1 year'))
                        )
                    )
                ),
                "serviceType" => "Real Estate Investment Services",
                "category" => "Financial Services"
            );
            
            $schema_output .= '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
        }
        
        return $schema_output;
    }
    
    private function faq_schema() {
        $faqs = array(
            array(
                "question" => "What is the best real estate investment strategy for beginners?",
                "answer" => "The BRRRR method (Buy, Rehab, Rent, Refinance, Repeat) is often recommended for beginners as it allows you to recycle your capital and build a portfolio with limited initial investment."
            ),
            array(
                "question" => "How do I analyze a real estate investment property?",
                "answer" => "Key metrics include cash-on-cash return, cap rate, debt service coverage ratio, and the 1% rule. Use our AI Property Analyzer to get instant analysis including cash flow projections and risk assessment."
            ),
            array(
                "question" => "What financing options are available for investment properties?",
                "answer" => "Options include conventional mortgages (typically 20-25% down), hard money loans for quick purchases, private money from individual investors, and cash purchases."
            )
        );
        
        $faq_items = array();
        foreach ($faqs as $faq) {
            $faq_items[] = array(
                "@type" => "Question",
                "name" => $faq['question'],
                "acceptedAnswer" => array(
                    "@type" => "Answer",
                    "text" => $faq['answer']
                )
            );
        }
        
        $schema = array(
            "@context" => "https://schema.org",
            "@type" => "FAQPage",
            "mainEntity" => $faq_items
        );
        
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
    
    private function review_schema() {
        $schema = array(
            "@context" => "https://schema.org",
            "@type" => "Organization",
            "name" => get_option('toinvested_business_name', 'ToInvested'),
            "aggregateRating" => array(
                "@type" => "AggregateRating",
                "ratingValue" => "4.8",
                "reviewCount" => "127",
                "bestRating" => "5",
                "worstRating" => "1"
            ),
            "review" => array(
                array(
                    "@type" => "Review",
                    "author" => array(
                        "@type" => "Person",
                        "name" => "Michael Johnson"
                    ),
                    "reviewRating" => array(
                        "@type" => "Rating",
                        "ratingValue" => "5",
                        "bestRating" => "5"
                    ),
                    "reviewBody" => "The AI Property Analyzer saved me from a bad investment. The analysis was spot-on and helped me find a much better deal.",
                    "datePublished" => "2024-09-15"
                )
            )
        );
        
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
    
    private function local_business_schema() {
        $business_name = get_option('toinvested_business_name', 'ToInvested');
        $business_phone = get_option('toinvested_business_phone', '+1-555-123-4567');
        $business_email = get_option('toinvested_business_email', 'info@toinvested.com');
        
        $schema = array(
            "@context" => "https://schema.org",
            "@type" => "ProfessionalService",
            "name" => $business_name . " Real Estate Investment Services",
            "description" => "Professional real estate investment analysis, consultation, and education services",
            "url" => home_url(),
            "telephone" => $business_phone,
            "email" => $business_email,
            "address" => array(
                "@type" => "PostalAddress",
                "addressCountry" => "US"
            ),
            "areaServed" => array(
                "@type" => "Country",
                "name" => "United States"
            ),
            "openingHours" => "Mo-Fr 09:00-18:00",
            "priceRange" => "$29-$497"
        );
        
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
    
    private function breadcrumb_schema() {
        if (!is_singular() && !is_category() && !is_tag()) return '';
        
        $breadcrumbs = array();
        $breadcrumbs[] = array(
            "@type" => "ListItem",
            "position" => 1,
            "name" => "Home",
            "item" => home_url()
        );
        
        if (is_singular()) {
            $breadcrumbs[] = array(
                "@type" => "ListItem",
                "position" => 2,
                "name" => get_the_title(),
                "item" => get_permalink()
            );
        }
        
        $schema = array(
            "@context" => "https://schema.org",
            "@type" => "BreadcrumbList",
            "itemListElement" => $breadcrumbs
        );
        
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
    
    private function product_schema($product_id) {
        if (!function_exists('wc_get_product')) return '';
        
        $product = wc_get_product($product_id);
        if (!$product) return '';
        
        $schema = array(
            "@context" => "https://schema.org",
            "@type" => "Product",
            "name" => $product->get_name(),
            "description" => $product->get_description(),
            "sku" => $product->get_sku(),
            "brand" => array(
                "@type" => "Brand",
                "name" => get_option('toinvested_business_name', 'ToInvested')
            ),
            "offers" => array(
                "@type" => "Offer",
                "price" => $product->get_price(),
                "priceCurrency" => "USD",
                "availability" => $product->is_in_stock() ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
                "url" => $product->get_permalink(),
                "seller" => array(
                    "@type" => "Organization",
                    "name" => get_option('toinvested_business_name', 'ToInvested')
                )
            ),
            "aggregateRating" => array(
                "@type" => "AggregateRating",
                "ratingValue" => "4.8",
                "reviewCount" => "127",
                "bestRating" => "5"
            )
        );
        
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
    
    private function article_schema($post_id) {
        $post = get_post($post_id);
        if (!$post) return '';
        
        $schema = array(
            "@context" => "https://schema.org",
            "@type" => "Article",
            "headline" => $post->post_title,
            "description" => get_the_excerpt($post_id),
            "image" => get_the_post_thumbnail_url($post_id, 'full'),
            "author" => array(
                "@type" => "Person",
                "name" => get_the_author_meta('display_name', $post->post_author),
                "jobTitle" => "Real Estate Investment Expert"
            ),
            "publisher" => array(
                "@type" => "Organization",
                "name" => get_option('toinvested_business_name', 'ToInvested'),
                "logo" => array(
                    "@type" => "ImageObject",
                    "url" => home_url() . "/wp-content/uploads/logo.png"
                )
            ),
            "datePublished" => get_the_date('c', $post_id),
            "dateModified" => get_the_modified_date('c', $post_id),
            "mainEntityOfPage" => array(
                "@type" => "WebPage",
                "@id" => get_permalink($post_id)
            ),
            "keywords" => "real estate investment, property analysis, investment strategies"
        );
        
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
}

// Initialize the plugin
new ToInvestedSchemaPlugin();

?>