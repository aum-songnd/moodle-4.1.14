<?php
// Language strings for mod_faceattend
defined('MOODLE_INTERNAL') || die();

// Plugin name (bắt buộc)
$string['pluginname']           = 'Face Attendance';
$string['modulename']           = 'Face Attendance';
$string['modulenameplural']     = 'Face Attendances';
$string['pluginadministration'] = 'Face Attendance administration';

// Activity settings
$string['faceattendname']       = 'Activity name';
$string['faceattendname_help']  = 'Nhập tên cho buổi điểm danh này';
$string['intro']                = 'Description';
$string['session_date']         = 'Session date';
$string['session_date_help']    = 'Ngày tổ chức buổi học';
$string['confidence_threshold'] = 'Confidence threshold (%)';
$string['confidence_threshold_help'] = 'Ngưỡng độ chính xác tối thiểu để xác nhận điểm danh (khuyến nghị: 80%)';
$string['api_url']              = 'Face Recognition API URL';
$string['api_url_help']         = 'Địa chỉ máy chủ xử lý nhận diện khuôn mặt, ví dụ: http://localhost:8000';
$string['max_attempts']         = 'Max attempts';
$string['max_attempts_help']    = 'Số lần thử tối đa nếu nhận diện thất bại';

// Camera UI strings
$string['start_camera']         = 'Bật Camera';
$string['capture_face']         = 'Chụp & Điểm danh';
$string['retake']               = 'Chụp lại';
$string['attending']            = 'Đang xử lý...';
$string['attend_success']       = '✅ Điểm danh thành công!';
$string['attend_failed']        = '❌ Không nhận diện được khuôn mặt. Vui lòng thử lại.';
$string['already_attended']     = '✅ Bạn đã điểm danh buổi học này rồi.';
$string['camera_error']         = 'Không thể truy cập camera. Hãy cấp quyền camera cho trình duyệt.';
$string['no_face_detected']     = 'Không phát hiện khuôn mặt trong ảnh. Hãy nhìn thẳng vào camera.';
$string['session_closed']       = 'Buổi điểm danh đã kết thúc.';
$string['not_registered']       = '⚠️ Khuôn mặt chưa được đăng ký. Liên hệ giảng viên.';

// Teacher view
$string['attendance_report']    = 'Báo cáo điểm danh';
$string['student_name']         = 'Tên sinh viên';
$string['attend_time']          = 'Thời gian điểm danh';
$string['confidence_score']     = 'Độ chính xác';
$string['status']               = 'Trạng thái';
$string['status_present']       = 'Có mặt';
$string['status_absent']        = 'Vắng mặt';
$string['total_present']        = 'Tổng có mặt';
$string['total_absent']         = 'Tổng vắng mặt';
$string['export_csv']           = 'Xuất CSV';

// Admin
$string['faceattend:addinstance']   = 'Add a face attendance activity';
$string['faceattend:view']          = 'View face attendance';
$string['faceattend:attend']        = 'Attend face attendance';
$string['faceattend:viewreport']    = 'View attendance report';
$string['faceattend:manage']        = 'Manage face attendance settings';
