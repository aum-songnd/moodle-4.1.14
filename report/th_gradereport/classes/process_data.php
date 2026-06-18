<?php
// Lấy dữ liệu từ Select Option 1
$option1Value = $_POST['id_userid'];

if(!empty($option1Value)) {
    foreach($option1Value as $value) {
        
    }
}

// Xử lý dữ liệu và tạo dữ liệu cho Select Option 2
// $options = array();
// if ($option1Value == 1) {
//     $options[] = array('value' => '1.1', 'text' => 'Option 2.1');
//     $options[] = array('value' => '1.2', 'text' => 'Option 2.2');
// } elseif ($option1Value == 2) {
//     $options[] = array('value' => '2.1', 'text' => 'Option 2.3');
//     $options[] = array('value' => '2.2', 'text' => 'Option 2.4');
// } elseif ($option1Value == 3) {
//     $options[] = array('value' => '3.1', 'text' => 'Option 2.5');
//     $options[] = array('value' => '3.2', 'text' => 'Option 2.6');
// }

// Trả về phản hồi dữ liệu dưới dạng JSON
header('Content-Type: application/json');
echo json_encode($option1Value);
?>