$(document).ready(function() {
    var table = $('.th_feedback_report_table').DataTable({
        dom: 'Blfrtip',
        buttons: [],
        pageLength: 100,
        lengthMenu: [[10, 100, 500, 1000, -1], [10, 100, 500, 1000, "All"]]
    });

    // Hàm helper để lấy timestamp
    function getTimestamp(dateString) {
        var date = new Date(dateString);
        return date.getTime() / 1000;
    }

    // Hàm helper để lấy khoảng thời gian
    function getTimeRange(value) {
        var now = new Date();
        var start, end;

        switch(value) {
            case 'today':
                start = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                end = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);
                break;
            case 'yesterday':
                start = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1);
                end = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                break;
            case 'thisweek':
                start = new Date(now.getFullYear(), now.getMonth(), now.getDate() - now.getDay());
                end = new Date(now.getFullYear(), now.getMonth(), now.getDate() + (7 - now.getDay()));
                break;
            case 'lastweek':
                start = new Date(now.getFullYear(), now.getMonth(), now.getDate() - now.getDay() - 7);
                end = new Date(now.getFullYear(), now.getMonth(), now.getDate() - now.getDay());
                break;
            case 'thismonth':
                start = new Date(now.getFullYear(), now.getMonth(), 1);
                end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                break;
            case 'lastmonth':
                start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                end = new Date(now.getFullYear(), now.getMonth(), 0);
                break;
            default:
                return null;
        }

        return {start: getTimestamp(start), end: getTimestamp(end)};
    }

    // Add custom filtering function
    $.fn.dataTable.ext.search.push(
        function( settings, data, dataIndex ) {
            var selectedStatus = $('#statusFilter').val();
            var selectedTime = $('#timeFilter').val();
            var row = table.row(dataIndex).node();
            var status = $(row).find('select[name^="status"]').val();
            var timeCreated = $(row).find('.c8').text();
            
            if (selectedStatus !== "" && status !== selectedStatus) {
                return false;
            }

            // Time filter
            if (selectedTime !== "") {
                var range = getTimeRange(selectedTime);
                // Convert timeCreated to a timestamp
                var timeParts = timeCreated.split(' ');
                var dateParts = timeParts[1].split('-');
                var time = timeParts[0].split(':');
                var timeCreatedDate = new Date(dateParts[2], dateParts[1] - 1, dateParts[0], time[0], time[1], time[2]);
                var timeCreatedTimestamp = Math.floor(timeCreatedDate.getTime() / 1000);
                if (range && (timeCreatedTimestamp < range.start || timeCreatedTimestamp >= range.end)) {
                    return false;
                }
            }
            
            return true;
        }
    );

    // Event listener for status filter
    $('#statusFilter, #timeFilter').change(function() {
        table.draw();
    });
});

function fnExcelReport_xls() {
    // Clone the table to avoid modifying the original table
    var clonedTable = $('.th_feedback_report_table').clone();

    // Remove elements with display: none
    clonedTable.find('td[style*="display: none"]').remove();

    // Replace select elements with their selected option text in column 9
    clonedTable.find('tr').each(function() {
        var statusCell = $(this).find('td:nth-child(10)');
        var selectedText = statusCell.find('select[name^="status"] option:selected').text();
        statusCell.html(selectedText);
    });

    var tab_text = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    tab_text += '<head><meta charset="UTF-8"><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
    tab_text += '<x:Name>Test Sheet</x:Name>';
    tab_text += '<x:WorksheetOptions><x:Panes></x:Panes></x:WorksheetOptions></x:ExcelWorksheet>';
    tab_text += '</x:ExcelWorksheets></x:ExcelWorkbook></xml></head><body>';
    tab_text += "<table>";
    tab_text += clonedTable.html();
    tab_text += '</table></body></html>';

    var data_type = 'data:application/vnd.ms-excel;charset=utf-8';

    var ua = window.navigator.userAgent;
    var msie = ua.indexOf("MSIE ");

    if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
        if (window.navigator.msSaveBlob) {
            var blob = new Blob([tab_text], {
                type: "application/csv;charset=utf-8;"
            });
            navigator.msSaveBlob(blob, 'Comment.xls');
        }
    } else {
        $('#th_export_xls').attr('href', data_type + ', ' + encodeURIComponent(tab_text));
        $('#th_export_xls').attr('download', 'Comment.xls');
    }
}

function updateStatus(id, status) {
    $.ajax({
        url: 'update_status.php',
        type: 'POST',
        data: {
            id: id,
            status: status
        },
        success: function(response) {
            alert(M.util.get_string('update_status_success', 'local_th_feedback_report'));
        },
        error: function() {
            alert(M.util.get_string('update_status_error', 'local_th_feedback_report'));
        }
    });
}


function showUpdateForm(id, selectHtml) {
    var form = '<form id="update-type-form" action="update_type.php" method="post">'+
                '<h2>' + M.util.get_string('error_classification', 'local_th_feedback_report') + '</h2>'+
                '<label for="typename">' + M.util.get_string('select_feedback_type', 'local_th_feedback_report') + ':</label>'+
                selectHtml+
                '<input type="hidden" name="id" value="' + id + '">'+
                '<input type="submit" value="' + M.util.get_string('update', 'local_th_feedback_report') + '">'+
                '</form>';

    var modal = '<div id="update-type-modal" style="display:none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000;">'+
                '<div style="background-color: #fefefe; padding: 20px; border: 1px solid #888; width: 80%; position: relative;">'+
                '<span style="position: absolute; top: 10px; right: 10px; cursor: pointer;" onclick="closeModal()">x</span>'+
                form+
                '</div>'+
                '</div>';
    if (!document.getElementById("update-type-modal")) {
        document.body.insertAdjacentHTML('beforeend', modal);
    }
    document.getElementById("update-type-modal").style.display = "block";
}

function closeModal() {
    var modal = document.getElementById("update-type-modal");
    if (modal) {
        modal.style.display = "none";
        modal.remove(); // Remove the modal from the DOM to prevent conflicts
    }
}