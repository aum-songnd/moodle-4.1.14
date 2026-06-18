define(['jquery', 'core/ajax'], function($, Ajax) {
    return {
        init: function() {
            $('.regrade-status-select').on('change', function() {
                const id = $(this).data('id');
                const status = $(this).val();
                Ajax.call([{
                    methodname: 'local_th_online_exam_manager_update_status',
                    args: { id: id, status: status },
                    done: function() {
                        alert('Status updated!');
                        location.reload();
                    },
                    fail: function(error) {
                        alert('Error: ' + error.message);
                    }
                }]);
            });
        }
    };
});
