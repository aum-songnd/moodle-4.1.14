/**
 * @module mod_qaa/reply_parent
 */
define([], function() {
    return {
        moveReplyBox: function() {
            var box = document.getElementById('qaa-reply-box');
            if (!box) { return; }

            try {
                box.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            } catch (e) {
                // fallback nếu browser không hỗ trợ
                box.scrollIntoView(true);
            }
        }
    };
});
