/**
 * @module mod_qaa/reply
 */
define([], function() {
    return {
        moveReplyBox: function(targetId) {
            var box = document.getElementById('qaa-reply-box');
            if (!box) { return; }
            var selector = (targetId && Number(targetId) > 0)
                ? '[data-reply-target="' + targetId + '"]'
                : '[data-reply-target]';
            var target = document.querySelector(selector);
            if (target) {
                target.appendChild(box);
                if (box.scrollIntoView) {
                    try { box.scrollIntoView({behavior:'smooth', block:'center'}); } catch (e) {}
                }
            }
        }
    };
});
