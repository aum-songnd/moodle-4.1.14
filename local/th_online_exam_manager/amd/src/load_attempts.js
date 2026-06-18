define(['jquery', 'core/ajax'], function($, Ajax) {
    return {
        transport: function(selector, query, success, failure) {
            const quizid = $('#id_quizid').val();
            if (!quizid) {
                success([]);
                return;
            }

            Ajax.call([{
                methodname: 'local_th_online_exam_manager_load_attempts',
                args: {
                    term: query,
                    quizid: parseInt(quizid)
                },
                done: success,
                fail: failure
            }]);
        },

        processResults: function(selector, results) {
            return results;
        }
    };
});
