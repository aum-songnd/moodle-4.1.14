<?php

$selectedHour = get_config('local_th_essay_checker_report', 'hour');
// Định nghĩa task sử dụng cấu hình đã lấy
$tasks = [
    [
        'classname'  => 'local_th_essay_checker_report\task\auto_send_mail',
        'blocking'   => 0,
        'minute'     => '00',
        'hour'       => $selectedHour, // giờ lấy từ cấu hình
        'day'        => 15,  // ngày lấy từ cấu hình (đã điều chỉnh nếu vượt quá số ngày của tháng)
        'month'      => '*',
        'dayofweek'  => '*', // Để tránh mâu thuẫn với việc chọn ngày cụ thể, sử dụng '*' ở đây.
    ],
];
?>
