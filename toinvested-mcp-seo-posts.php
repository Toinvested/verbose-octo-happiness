<?php
/**
 * Plugin Name: ToInvested MCP — SEO Post Generator
 * Description: Exposes MCP tools to create SEO-optimized posts/pages and append sections via mwai_mcp_tools/mwai_mcp_callback.
 * Version: 1.2.0
 * Author: ToInvested (David J. Moore)
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) exit;

/**
 * Register MCP tools
 */
add_filter('mwai_mcp_tools', function ($tools) {
    if (!is_array($tools)) { $tools = []; }

    // 1) generate_seo_post
    $tools['generate_seo_post'] = [
        'title'       => 'Generate SEO Post',
        'description' => 'Create a post with SEO metadata, featured image, categories/tags, and optional JSON-LD.',
        'params'      => [
            ['name' => 'title',               'type' => 'string',  'required' => true],
            ['name' => 'slug',                'type' => 'string',  'required' => false],
            ['name' => 'content',             'type' => 'string',  'required' => true],
            ['name' => 'excerpt',             'type' => 'string',  'required' => false],
            ['name' => 'status',              'type' => 'string',  'required' => false],
            ['name' => 'author_email',        'type' => 'string',  'required' => false],
            ['name' => 'categories',          'type' => 'array',   'required' => false],
            ['name' => 'tags',                'type' => 'array',   'required' => false],
            ['name' => 'featured_image_url',  'type' => 'string',  'required' => false],
            ['name' => 'focus_keyword',       'type' => 'string',  'required' => false],
            ['name' => 'meta_description',    'type' => 'string',  'required' => false],
            ['name' => 'json_ld',             'type' => 'object',  'required' => false],
            ['name' => 'post_type',           'type' => 'string',  'required' => false],
        ],
        'returns'     => ['post_id','edit_link','view_link','status']
    ];

    // 2) generate_page
    $tools['generate_page'] = [
        'title'       => 'Generate Page',
        'description' => 'Create a WordPress page with optional template, parent, SEO fields and featured image.',
        'params'      => [
            ['name' => 'title',               'type' => 'string',  'required' => true],
            ['name' => 'slug',                'type' => 'string',  'required' => false],
            ['name' => 'content',             'type' => 'string',  'required' => true],
            ['name' => 'excerpt',             'type' => 'string',  'required' => false],
            ['name' => 'status',              'type' => 'string',  'required' => false],
            ['name' => 'author_email',        'type' => 'string',  'required' => false],
            ['name' => 'template',            'type' => 'string',  'required' => false],
            ['name' => 'parent_id',           'type' => 'number',  'required' => false],
            ['name' => 'featured_image_url',  'type' => 'string',  'required' => false],
            ['name' => 'focus_keyword',       'type' => 'string',  'required' => false],
            ['name' => 'meta_description',    'type' => 'string',  'required' => false],
            ['name' => 'json_ld',             'type' => 'object',  'required' => false]
        ],
        'returns'     => ['post_id','edit_link','view_link','status']
    ];

    // 3) append_section_to_post
    $tools['append_section_to_post'] = [
        'title'       => 'Append Section to Post',
        'description' => 'Append HTML/blocks to an existing post/page by ID or slug.',
        'params'      => [
            ['name' => 'post_id',             'type' => 'number',  'required' => false],
            ['name' => 'slug',                'type' => 'string',  'required' => false],
            ['name' => 'section_html',        'type' => 'string',  'required' => true],
            ['name' => 'separator',           'type' => 'string',  'required' => false],
            ['name' => 'status',              'type' => 'string',  'required' => false]
        ],
        'returns'     => ['post_id','edit_link','view_link','status']
    ];

    return $tools;
});

/**
 * MCP callback dispatcher
 */
add_filter('mwai_mcp_callback', function ($response, $tool, $args) {
    switch ($tool) {
        case 'generate_seo_post':
            return toinv_mcp_generate_seo_post($args);
        case 'generate_page':
            return toinv_mcp_generate_page($args);
        case 'append_section_to_post':
            return toinv_mcp_append_section($args);
        default:
            return $response;
    }
}, 10, 3);

/**
 * Tool: generate_seo_post
 */
function toinv_mcp_generate_seo_post($args) {
    $title   = isset($args['title']) ? wp_strip_all_tags($args['title']) : '';
    $content = isset($args['content']) ? $args['content'] : '';
    if (!$title || !$content) {
        return ['ok'=>false,'error'=>'Missing required fields: title, content','status'=>400];
    }
    $slug         = !empty($args['slug']) ? sanitize_title($args['slug']) : '';
    $excerpt      = !empty($args['excerpt']) ? wp_kses_post($args['excerpt']) : '';
    $status       = in_array(($args['status'] ?? 'draft'), ['publish','draft','private'], true) ? $args['status'] : 'draft';
    $post_type    = !empty($args['post_type']) ? sanitize_key($args['post_type']) : 'post';
    $categories   = is_array($args['categories'] ?? null) ? array_map('sanitize_text_field', $args['categories']) : [];
    $tags         = is_array($args['tags'] ?? null) ? array_map('sanitize_text_field', $args['tags']) : [];
    $feat_url     = !empty($args['featured_image_url']) ? esc_url_raw($args['featured_image_url']) : '';
    $focus_kw     = !empty($args['focus_keyword']) ? sanitize_text_field($args['focus_keyword']) : '';
    $meta_desc    = !empty($args['meta_description']) ? wp_strip_all_tags($args['meta_description']) : '';
    $json_ld      = isset($args['json_ld']) && is_array($args['json_ld']) ? $args['json_ld'] : null;

    $author_id = get_current_user_id();
    if (!empty($args['author_email']) && is_email($args['author_email'])) {
        $user = get_user_by('email', $args['author_email']); if ($user) $author_id = $user->ID;
    }

    $postarr = [
        'post_title'   => $title,
        'post_content' => wp_kses_post($content),
        'post_excerpt' => $excerpt,
        'post_status'  => $status,
        'post_author'  => $author_id,
        'post_type'    => $post_type
    ];
    if ($slug) $postarr['post_name'] = $slug;

    $post_id = wp_insert_post($postarr, true);
    if (is_wp_error($post_id)) return ['ok'=>false,'error'=>$post_id->get_error_message(),'status'=>500];

    if (!empty($categories) && $post_type === 'post') {
        $cat_ids = [];
        foreach ($categories as $cat_name) {
            $term = term_exists($cat_name, 'category');
            if (!$term) { $term = wp_insert_term($cat_name, 'category'); }
            if (!is_wp_error($term)) { $cat_ids[] = intval($term['term_id']); }
        }
        if ($cat_ids) wp_set_post_terms($post_id, $cat_ids, 'category', false);
    }
    if (!empty($tags) && taxonomy_exists('post_tag')) wp_set_post_terms($post_id, $tags, 'post_tag', false);

    if ($feat_url) {
        $attach_id = toinv_mcp_sideload_image_to_post($feat_url, $post_id);
        if ($attach_id && !is_wp_error($attach_id)) set_post_thumbnail($post_id, $attach_id);
    }

    if (defined('RANK_MATH_VERSION')) {
        if ($focus_kw) update_post_meta($post_id, 'rank_math_focus_keyword', $focus_kw);
        if ($meta_desc) update_post_meta($post_id, 'rank_math_description', $meta_desc);
    }
    if ($json_ld) update_post_meta($post_id, '_toinv_json_ld', wp_json_encode($json_ld, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));

    return ['ok'=>true,'post_id'=>$post_id,'edit_link'=>get_edit_post_link($post_id,''),'view_link'=>get_permalink($post_id),'status'=>get_post_status($post_id)];
}

/**
 * Tool: generate_page
 */
function toinv_mcp_generate_page($args) {
    $title   = isset($args['title']) ? wp_strip_all_tags($args['title']) : '';
    $content = isset($args['content']) ? $args['content'] : '';
    if (!$title || !$content) return ['ok'=>false,'error'=>'Missing required fields: title, content','status'=>400];

    $slug      = !empty($args['slug']) ? sanitize_title($args['slug']) : '';
    $excerpt   = !empty($args['excerpt']) ? wp_kses_post($args['excerpt']) : '';
    $status    = in_array(($args['status'] ?? 'draft'), ['publish','draft','private'], true) ? $args['status'] : 'draft';
    $template  = !empty($args['template']) ? sanitize_text_field($args['template']) : '';
    $parent_id = !empty($args['parent_id']) ? intval($args['parent_id']) : 0;
    $feat_url  = !empty($args['featured_image_url']) ? esc_url_raw($args['featured_image_url']) : '';
    $focus_kw  = !empty($args['focus_keyword']) ? sanitize_text_field($args['focus_keyword']) : '';
    $meta_desc = !empty($args['meta_description']) ? wp_strip_all_tags($args['meta_description']) : '';
    $json_ld   = isset($args['json_ld']) && is_array($args['json_ld']) ? $args['json_ld'] : null;

    $author_id = get_current_user_id();
    if (!empty($args['author_email']) && is_email($args['author_email'])) {
        $user = get_user_by('email', $args['author_email']); if ($user) $author_id = $user->ID;
    }

    $postarr = [
        'post_title'   => $title,
        'post_name'    => $slug ?: '',
        'post_content' => wp_kses_post($content),
        'post_excerpt' => $excerpt,
        'post_status'  => $status,
        'post_author'  => $author_id,
        'post_type'    => 'page',
        'post_parent'  => $parent_id,
        'meta_input'   => []
    ];

    $post_id = wp_insert_post($postarr, true);
    if (is_wp_error($post_id)) return ['ok'=>false,'error'=>$post_id->get_error_message(),'status'=>500];

    if ($template) update_post_meta($post_id, '_wp_page_template', $template);
    if ($feat_url) {
        $attach_id = toinv_mcp_sideload_image_to_post($feat_url, $post_id);
        if ($attach_id && !is_wp_error($attach_id)) set_post_thumbnail($post_id, $attach_id);
    }
    if (defined('RANK_MATH_VERSION')) {
        if ($focus_kw) update_post_meta($post_id, 'rank_math_focus_keyword', $focus_kw);
        if ($meta_desc) update_post_meta($post_id, 'rank_math_description', $meta_desc);
    }
    if ($json_ld) update_post_meta($post_id, '_toinv_json_ld', wp_json_encode($json_ld, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));

    return ['ok'=>true,'post_id'=>$post_id,'edit_link'=>get_edit_post_link($post_id,''),'view_link'=>get_permalink($post_id),'status'=>get_post_status($post_id)];
}

/**
 * Tool: append_section_to_post
 */
function toinv_mcp_append_section($args) {
    $post_id = !empty($args['post_id']) ? intval($args['post_id']) : 0;
    $slug    = !empty($args['slug']) ? sanitize_title($args['slug']) : '';
    $section = isset($args['section_html']) ? wp_kses_post($args['section_html']) : '';
    $status  = in_array(($args['status'] ?? ''), ['publish','draft','private'], true) ? $args['status'] : '';
    $separator = isset($args['separator']) ? sanitize_text_field($args['separator']) : "\n\n<!-- toinv-section -->\n\n";

    if (!$section) return ['ok'=>false,'error'=>'Missing required field: section_html','status'=>400];

    if (!$post_id && $slug) {
        $post_obj = get_page_by_path($slug, OBJECT, ['post','page']);
        if ($post_obj) $post_id = $post_obj->ID;
    }
    if (!$post_id) return ['ok'=>false,'error'=>'Target post not found (need post_id or slug)','status'=>404];

    $existing = get_post_field('post_content', $post_id);
    $new_content = $existing . $separator . $section;

    $update = [
        'ID'           => $post_id,
        'post_content' => $new_content
    ];
    if ($status) $update['post_status'] = $status;

    $res = wp_update_post($update, true);
    if (is_wp_error($res)) return ['ok'=>false,'error'=>$res->get_error_message(),'status'=>500];

    return ['ok'=>true,'post_id'=>$post_id,'edit_link'=>get_edit_post_link($post_id,''),'view_link'=>get_permalink($post_id),'status'=>get_post_status($post_id)];
}

/**
 * Front-end: render saved JSON-LD
 */
add_action('wp_head', function () {
    if (is_singular()) {
        $post_id = get_the_ID();
        $json = get_post_meta($post_id, '_toinv_json_ld', true);
        if ($json) {
            $decoded = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                echo "\n<script type=\"application/ld+json\">".wp_json_encode($decoded, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)."</script>\n";
            }
        }
    }
});

/**
 * Helper: sideload an image and attach to post
 */
function toinv_mcp_sideload_image_to_post($url, $post_id) {
    if (!function_exists('media_sideload_image')) {
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }
    $tmp = download_url($url);
    if (is_wp_error($tmp)) return $tmp;

    $file_array = [
        'name'     => wp_basename(parse_url($url, PHP_URL_PATH)),
        'tmp_name' => $tmp
    ];

    $file = wp_handle_sideload($file_array, ['test_form' => false]);
    if (isset($file['error'])) {
        @unlink($tmp);
        return new WP_Error('sideload_error', $file['error']);
    }

    $attachment = [
        'post_mime_type' => $file['type'],
        'post_title'     => sanitize_file_name($file_array['name']),
        'post_content'   => '',
        'post_status'    => 'inherit'
    ];

    $attach_id = wp_insert_attachment($attachment, $file['file'], $post_id);
    if (is_wp_error($attach_id)) return $attach_id;

    $attach_data = wp_generate_attachment_metadata($attach_id, $file['file']);
    wp_update_attachment_metadata($attach_id, $attach_data);

    return $attach_id;
}
