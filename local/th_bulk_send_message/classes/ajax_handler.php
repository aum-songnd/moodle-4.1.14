<?php

require_once '../../../config.php';
require_once $CFG->dirroot . '/local/thlib/classes/ssp.class.php';
  
// Thiết lập các cột cho DataTables
$columns = array(
    array('db' => 'stt', 'dt' => 0),
    array('db' => 'sender', 'dt' => 1),
    array('db' => 'receiver', 'dt' => 2),
    array('db' => 'subject','dt' => 3),
    array('db' => 'body', 'dt' => 4),
    array(
        'db' => 'date', 
        'dt' => 5,
        'formatter' => function( $d, $row ) {
            return date("d/m/Y H:i", $d);
        }
    ),
    array(
        'db' => 'status', 
        'dt' => 6,
        'formatter' => function( $d, $row ) {
            if($d == 1){
                return html_writer::tag(
                    'span',
                    'Gửi mail thành công',
                    array('class' => 'badge badge-success'));
            } else {
                return html_writer::tag(
                    'span',
                    'Gửi mail thất bại',
                    array('class' => 'badge badge-warning'));
            }
            
        }
    )
    
);

// Thiết lập thông tin bảng và khóa chính
$table = 'th_bulk_send_message';
$primaryKey = 'id';

// Xử lý phân trang trên phía máy chủ
echo json_encode(SSP::simple($_GET, $table, $primaryKey, $columns));
?>