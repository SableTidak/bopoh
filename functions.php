<?php
/**
 * Theme Name: Bopoh
 */

function bopoh_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list']);
    add_theme_support('customize-selective-refresh-widgets');
}
add_action('after_setup_theme', 'bopoh_setup');

function bopoh_widgets_init() {
    register_sidebar([
        'name' => '左侧边栏',
        'id' => 'sidebar-left',
        'before_widget' => '<div class="widget">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ]);
    register_sidebar([
        'name' => '右侧边栏',
        'id' => 'sidebar-right',
        'before_widget' => '<div class="widget">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ]);
}
add_action('widgets_init', 'bopoh_widgets_init');

function bopoh_enqueue_scripts() {
    wp_enqueue_style('remix-icon', 'https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.min.css');
    wp_enqueue_style('bopoh-style', get_stylesheet_uri());

    wp_enqueue_script('bopoh-main', get_template_directory_uri() . '/assets/js/main.js', [], '1.0', true);

    // 如果原 Wing 有 PJAX，确保加载；此处假设已存在 pjax.min.js
    if (!is_admin()) {
        wp_enqueue_script('pjax', 'https://cdn.jsdelivr.net/npm/pjax@0.2.8/pjax.min.js', [], '0.2.8', true);
    }
}
add_action('wp_enqueue_scripts', 'bopoh_enqueue_scripts');
/**
 * =============== Bopoh 主题增强：评论 + 小工具优化 ===============
 */

// 确保脚本已注册并传递 AJAX URL
function bopoh_theme_enhancements() {
    wp_enqueue_script('bopoh-main', get_template_directory_uri() . '/assets/js/main.js', ['jquery'], '1.0', true);
    wp_localize_script('bopoh-main', 'ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php')
    ]);
}
add_action('wp_enqueue_scripts', 'bopoh_theme_enhancements', 20);

/**
 * AJAX 提交评论（兼容 WordPress 原生机制）
 */
function bopoh_ajax_submit_comment() {
    require_once ABSPATH . 'wp-admin/includes/comment.php';

    $comment_data = [
        'comment_post_ID'      => intval($_POST['comment_post_ID']),
        'comment_author'       => sanitize_text_field($_POST['author']),
        'comment_author_email' => sanitize_email($_POST['email']),
        'comment_author_url'   => esc_url_raw($_POST['url']),
        'comment_content'      => trim(wp_kses_post($_POST['comment'])),
        'comment_type'         => '',
        'comment_parent'       => isset($_POST['comment_parent']) ? absint($_POST['comment_parent']) : 0,
        'user_ID'              => get_current_user_id(),
        'comment_author_IP'    => $_SERVER['REMOTE_ADDR'] ?? '',
        'comment_agent'        => $_SERVER['HTTP_USER_AGENT'] ?? '',
    ];

    // 验证必填项
    if (empty($comment_data['comment_content'])) {
        wp_send_json_error(['message' => '评论内容不能为空']);
    }
    if (get_option('require_name_email') && (empty($comment_data['comment_author']) || empty($comment_data['comment_author_email']))) {
        wp_send_json_error(['message' => '请填写昵称和邮箱']);
    }
    if (!is_email($comment_data['comment_author_email'])) {
        wp_send_json_error(['message' => '邮箱格式不正确']);
    }

    // 敏感词检查
    if (wp_check_comment_disallowed_list(
        $comment_data['comment_author'],
        $comment_data['comment_author_email'],
        $comment_data['comment_author_url'],
        $comment_data['comment_content'],
        $comment_data['comment_author_IP'],
        $comment_data['comment_agent']
    )) {
        wp_send_json_error(['message' => '评论包含敏感内容']);
    }

    // 插入评论
    $comment_id = wp_new_comment($comment_data, true); // true = 不触发通知

    if (is_wp_error($comment_id)) {
        wp_send_json_error(['message' => $comment_id->get_error_message()]);
    }

    // 获取新评论 HTML
    ob_start();
    ?>
    <li <?php comment_class('comment-item'); ?> id="comment-<?php echo $comment_id; ?>">
        <div class="comment-body">
            <div class="comment-avatar"><?php echo get_avatar($comment_id, 50); ?></div>
            <div class="comment-content">
                <div class="comment-author">
                    <strong><?php echo get_comment_author($comment_id); ?></strong>
                    <span class="comment-date"><?php echo get_comment_date('', $comment_id) . ' ' . get_comment_time('', $comment_id); ?></span>
                </div>
                <div class="comment-text"><?php echo get_comment_text($comment_id); ?></div>
                <div class="comment-reply">
                    <?php
                    comment_reply_link([
                        'depth' => 1,
                        'max_depth' => get_option('thread_comments_depth'),
                        'reply_text' => '回复',
                        'before' => '',
                        'after' => ''
                    ], $comment_id);
                    ?>
                </div>
            </div>
        </div>
    </li>
    <?php
    $html = ob_get_clean();

    wp_send_json_success([
        'html' => $html,
        'comment_id' => $comment_id
    ]);
}
add_action('wp_ajax_bopoh_submit_comment', 'bopoh_ajax_submit_comment');
add_action('wp_ajax_nopriv_bopoh_submit_comment', 'bopoh_ajax_submit_comment');