/**
 * @module mod_qaa/edit
 */
define([], function() {
    return {
        moveEditBox: function(targetId) {
            var box = document.getElementById('qaa-edit-box');
            console.log(box);
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
