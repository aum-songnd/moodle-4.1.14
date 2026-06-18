define(['jquery', 'core/ajax'], function($, Ajax) {
    return {
        transport: function(selector, query, success, failure) {
            const courseid = $('#id_courseid').val(); // hoặc thay bằng logic lấy từ context
            if (!courseid) {
                success([]);
                return;
            }

            Ajax.call([{
                methodname: 'local_th_online_exam_manager_load_quizzes',
                args: {
                    term: query,
                    courseid: parseInt(courseid)
                },
                done: function(results) {
                    success(results); // results = [{ value, label }]
                },
                fail: failure
            }]);
        },

        processResults: function(selector, results) {
            return results; // Đã đúng dạng rồi
        }
    };
});
