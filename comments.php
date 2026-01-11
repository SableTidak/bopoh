<?php
if (post_password_required()) return;
?>

<div id="comments" class="widget">
    <h3 class="comments-title">
        <?php
        $num = get_comments_number();
        echo $num ? sprintf('%d 条评论', $num) : '暂无评论';
        ?>
    </h3>

    <?php if (have_comments()) : ?>
        <ol class="comment-list">
            <?php wp_list_comments(['style' => 'ol', 'short_ping' => true, 'avatar_size' => 50]); ?>
        </ol>
    <?php endif; ?>

    <?php if (!comments_open() && get_comments_number()) : ?>
        <p class="no-comments">评论已关闭。</p>
    <?php else : ?>

        <div class="bopoh-comment-form-wrapper" data-post-id="<?php the_ID(); ?>">
            <?php
            comment_form([
                'title_reply'          => '',
                'comment_notes_before' => '',
                'comment_notes_after'  => '',
                'fields' => [
                    'author' => '<div class="comment-form-field"><input id="author" name="author" type="text" placeholder="昵称（必填）" value="' . esc_attr($commenter['comment_author']) . '" required /></div>',
                    'email'  => '<div class="comment-form-field"><input id="email" name="email" type="email" placeholder="邮箱（必填）" value="' . esc_attr($commenter['comment_author_email']) . '" required /></div>',
                    'url'    => '<div class="comment-form-field"><input id="url" name="url" type="url" placeholder="网站（选填）" value="' . esc_attr($commenter['comment_author_url']) . '" /></div>',
                ],
                'comment_field' => '<div class="comment-form-field"><textarea id="comment" name="comment" placeholder="写下你的想法..." required></textarea></div>',
                'submit_field'  => '<button type="submit" class="submit-button">提交评论</button>',
            ]);
            ?>
        </div>

    <?php endif; ?>
</div>