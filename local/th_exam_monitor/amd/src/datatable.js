define([
    'jquery',
    'local_thlib/jszip',
    'local_thlib/jquery.dataTables',
    'local_thlib/dataTables.buttons',
    'local_thlib/buttons.html5',
    'local_thlib/buttons.print'
], function(
    $,
    jszip
) {
    window.JSZip = jszip;

    /**
     * Init datatable.
     *
     * @param {String} selector
     * @param {String} title
     * @param {String} language
     * @param {String} summary_detail
     * @returns {void}
     */
    const init = function(selector, title, language, summary_detail) {
        let linkToCss = $(
            'link[href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css"]'
        );

        if (linkToCss.length === 0) {
            linkToCss = $('#my_custom_datatable_css');
        }

        if (linkToCss.length === 0) {
            const csslink =
                '<link id="my_custom_datatable_css" ' +
                'rel="stylesheet" type="text/css" ' +
                'href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">';
            $('head').first().append(csslink);
            $(
                'link[href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css"]'
            ).remove();
        }

        $(function() {
            const configure = {
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All']
                ],
                pageLength: 25,
                dom: 'lBfrtip',
                buttons: [
                    {
                        extend: 'copy',
                        text: function(dt) {
                            return dt.i18n('buttons.mcopy', 'Copy');
                        },
                        title: title
                    },
                    {
                        extend: 'csv',
                        text: 'csv',
                        title: title
                    }
                ],
                aaSorting: []
            };

            if (summary_detail) {
                configure.buttons[0].messageTop = summary_detail;
                configure.buttons[1].messageTop = summary_detail;
            }

            configure.buttons.push({
                text: 'Excel',
                className: 'btn-export-excel',
                action: function(e, dt) {
                    require(
                        ['local_th_exam_monitor/export_excel'],
                        function(mod) {
                            const tableNode = dt.table().node();
                            const courseid = tableNode.dataset.courseid || 0;

                            if (!$(tableNode).hasClass('th_feedback_report_table')) {
                                $(tableNode).addClass('th_feedback_report_table');
                            }

                            mod.exportExcel(dt, courseid);
                        }
                    );
                }
            });

            if (language === 'vi') {
                configure.language = {
                    url:
                        '//cdn.datatables.net/plug-ins/1.10.21/i18n/Vietnamese.json',
                    buttons: {
                        mcopy: 'sao chép'
                    },
                    decimal: ','
                };
            }

            const table = $(selector);

            if (!table.hasClass('th_feedback_report_table')) {
                table.addClass('th_feedback_report_table');
            }

            table.DataTable(configure);
        });
    };

    return {
        init: init
    };
});