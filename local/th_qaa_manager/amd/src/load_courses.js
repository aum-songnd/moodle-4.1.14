define(['jquery', 'core/ajax', 'core/str'], function($, Ajax, Str) {
    return {
        transport: function(selector, query, success, failure) {
            const maxcourses = 100;
            const request = Ajax.call([{
                methodname: 'local_th_qaa_manager_load_courses',
                args: {
                    term: query
                }
            }]);

            request[0].then(function(response) {
                if (!response || !response.length) {
                    success([]);
                    return;
                }

                if (response.length > maxcourses) {
                    Str.get_string('toomanycoursetoshow', 'report_th_gradebook_export', '>' + maxcourses).then(function(msg) {
                        success([{
                            value: null,
                            label: '<div style="pointer-events:none;">' + msg + '</div>'
                        }]);
                    }).catch(function() {
                        success(response);
                    });
                } else {
                    success(response);
                }
            }).fail(failure);
        },

        processResults: function(selector, results) {
            const courses = [];
            if (Array.isArray(results)) {
                results.forEach(function(course) {
                    courses.push(course);
                });
            }
            return courses;
        }
    };
});
