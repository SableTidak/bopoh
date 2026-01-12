(function () {
    function initBopoh() {
        initAjaxComments();
        removeWidgetMenuDots();
        if (typeof hljs !== 'undefined') hljs.highlightAll();
    }

    // AJAX 评论提交
    function initAjaxComments() {
        const wrappers = document.querySelectorAll('.bopoh-comment-form-wrapper');
        if (!wrappers.length) return;

        wrappers.forEach(wrapper => {
            const form = wrapper.querySelector('form');
            if (!form) return;

            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const postId = wrapper.dataset.postId;
                const formData = new FormData(form);
                formData.append('action', 'bopoh_submit_comment');
                formData.append('comment_post_ID', postId);

                const submitBtn = form.querySelector('.submit-button');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = '提交中...';

                fetch(ajax_object.ajax_url, {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        form.reset();
                        const cancelLink = document.getElementById('cancel-comment-reply-link');
                        if (cancelLink) cancelLink.click();

                        const list = document.querySelector('.comment-list');
                        if (list) {
                            const temp = document.createElement('div');
                            temp.innerHTML = data.data.html.trim();
                            const newItem = temp.firstElementChild;
                            if (newItem) {
                                list.prepend(newItem);
                                newItem.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }
                        }

                        // 更新标题计数
                        const title = document.querySelector('.comments-title');
                        if (title) {
                            const num = parseInt(title.textContent.match(/\d+/)?.[0] || 0) || 0;
                            title.textContent = `${num + 1} 条评论`;
                        }
                    } else {
                        alert('⚠️ ' + (data.data?.message || '提交失败'));
                    }
                })
                .catch(() => alert('网络错误，请重试'))
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                });
            });
        });
    }

    // 移除小工具菜单的项目符号（• / ·）
    function removeWidgetMenuDots() {
        const selectors = [
            '.widget_nav_menu ul',
            '.widget_pages ul',
            '.widget_categories ul',
            '.widget_recent_entries ul',
            '.widget_archive ul'
        ].join(', ');

        document.querySelectorAll(selectors).forEach(menu => {
            menu.style.listStyle = 'none';
            menu.style.paddingLeft = '0';
            menu.style.margin = '0';
        });

        document.querySelectorAll(selectors + ' li').forEach(li => {
            li.style.listStyle = 'none';
            li.style.margin = '0.4rem 0';
            li.style.padding = '0';
        });
    }

    // 初始化
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBopoh);
    } else {
        initBopoh();
    }

    // PJAX 兼容（如果你用了 Pjax）
    if (typeof Pjax !== 'undefined') {
        document.addEventListener('pjax:success', initBopoh);
    }
})();