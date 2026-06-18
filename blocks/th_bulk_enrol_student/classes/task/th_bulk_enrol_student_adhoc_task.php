<?php

namespace block_th_bulk_enrol_student\task;

require_once $CFG->libdir . '/datalib.php';
require_once $CFG->dirroot . '/blocks/th_bulk_enrol_student/blocklib.php';
require_once $CFG->dirroot . '/enrol/manual/locallib.php';

use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Task for updating RSS feeds for rss client block
 *
 * @package   block_recent_activity
 * @author    Farhan Karmali <farhan6318@gmail.com>
 * @copyright Farhan Karmali 2018
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class th_bulk_enrol_student_adhoc_task extends \core\task\adhoc_task {

	/**
	 * Remove old entries from table block_recent_activity
	 */
	public function execute() {
		global $DB, $USER;

		mtrace("My ad-hoc task started");

		$list_data_enrol = $this->get_custom_data();

		$option = $list_data_enrol->option;
		$list_data = $list_data_enrol->data;
		$ma_ghi_danh = $list_data_enrol->ma_ghi_danh;

		if (!$enrol_manual = enrol_get_plugin('manual')) {
			throw new coding_exception('Can not instantiate enrol_manual');
		}

		mtrace($ma_ghi_danh);

		$setting_ma_hoc_vien = get_config('local_thlib', 'studentcodeshortname');
		if($option == 0){
			if(!empty($setting_ma_hoc_vien)){
				$arr_mahv = explode(',', $setting_ma_hoc_vien);

				$ma_hoc_vien1 = $arr_mahv[0];
				$ma_hoc_vien2 = $arr_mahv[1];
			} else {
				$log_data = new stdClass();
				$log_data->notifications = "Chưa có setting mã học viên trong THlib. Vui lòng kiểm tra lại";
				$log_data->ma_ghi_danh = $ma_ghi_danh;
				$log_data->nguoi_tao = $list_data_enrol->userid;
				$log_data->timecreated = time();
				$DB->insert_record('th_log_bulk_enrol_student', $log_data, false);
				exit;
			}
		}

		// mtrace('option:' . json_encode($option));

		foreach($list_data as $shortname => $list_data1){

			foreach($list_data1 as $data) {
				$tt_user = trim($data[0]);
				$line = trim($data[1]);
				$sql = "SELECT id FROM {course} WHERE shortname = '$shortname'";
				$course = $DB->get_record_sql($sql);

				if ($course) {
					$courseid = $course->id;
					$roleid = 5;

					if($tt_user) {
						if($option == 0){
							$userrecord = $DB->get_record_sql("SELECT u.* FROM {user_info_field} as i, {user_info_data} as d, {user} as u WHERE (i.shortname = '$ma_hoc_vien1' OR i.shortname = '$ma_hoc_vien2') AND i.id = d.fieldid AND d.data = '$tt_user' AND d.userid = u.id");
						} else {
							$userrecord = $DB->get_record_sql("SELECT * FROM {user} WHERE email = '$tt_user'");
						}
						if ($userrecord) {
							$userisenrolled = th_bulkenrol_csv_check_email($userrecord->id, $courseid, $roleid);
							if ($userisenrolled) {
								$log_data = new stdClass();
								$log_data->user_id = $userrecord->id;
								$log_data->course_id = $courseid;
								$log_data->notifications = "Người dùng ($tt_user) đã gán trong khóa học có shortname ($shortname). Kiểm tra lại hàng $line của sheet ($shortname).</br>Hàng này sẽ bị bỏ qua.";
								$log_data->ma_ghi_danh = $ma_ghi_danh;
								$log_data->nguoi_tao = $list_data_enrol->userid;
								$log_data->timecreated = time();
								$DB->insert_record('th_log_bulk_enrol_student', $log_data, false);
							} else {
	
								if($userrecord->suspended == 0 && $userrecord->deleted == 0){
									$instance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
									$timestart = time();
									$timeend = 0;
									$enrol_manual->enrol_user($instance, $userrecord->id, $roleid, $timestart, $timeend);
	
									$log_data = new stdClass();
									$log_data->user_id = $userrecord->id;
									$log_data->course_id = $courseid;
									$log_data->status = 1;
									$log_data->notifications = "Người dùng ($tt_user) đã gán thành công vào khóa học có shortname ($shortname)";
									$log_data->ma_ghi_danh = $ma_ghi_danh;
									$log_data->nguoi_tao = $list_data_enrol->userid;
									$log_data->timecreated = time();
									$DB->insert_record('th_log_bulk_enrol_student', $log_data, false);
								} else {
									$log_data = new stdClass();
									$log_data->user_id = $userrecord->id;
									$log_data->course_id = $courseid;
									$log_data->notifications = "Người dùng ($tt_user) hiện tại đang bị suspended hoặc hoặc deleted . Kiểm tra lại hàng $line của sheet ($shortname).</br>Hàng này sẽ bị bỏ qua.";
									$log_data->ma_ghi_danh = $ma_ghi_danh;
									$log_data->nguoi_tao = $list_data_enrol->userid;
									$log_data->timecreated = time();
									$DB->insert_record('th_log_bulk_enrol_student', $log_data, false);
								}
								
							}
						} else {
							$log_data = new stdClass();
							$log_data->course_id = $courseid;
							$log_data->notifications = "Không có tài khoản người dùng ($tt_user) hiện có trong hàng $line của sheet ($shortname).<br />Hàng này sẽ bị bỏ qua.";
							$log_data->ma_ghi_danh = $ma_ghi_danh;
							$log_data->nguoi_tao = $list_data_enrol->userid;
							$log_data->timecreated = time();
							$DB->insert_record('th_log_bulk_enrol_student', $log_data, false);
						}
					} else {
						$log_data = new stdClass();
						$log_data->course_id = $courseid;
						$log_data->notifications = "Không có tài khoản người dùng ($tt_user) hiện có trong hàng $line của sheet ($shortname).<br />Hàng này sẽ bị bỏ qua.";
						$log_data->ma_ghi_danh = $ma_ghi_danh;
						$log_data->nguoi_tao = $list_data_enrol->userid;
						$log_data->timecreated = time();
						$DB->insert_record('th_log_bulk_enrol_student', $log_data, false);
					}
					
				} else {
					$log_data = new stdClass();
					$log_data->notifications = "Không tìm thấy tên viết tắt khóa học ($shortname) của sheet ($shortname). Sheet này sẽ bị bỏ qua.";
					$log_data->ma_ghi_danh = $ma_ghi_danh;
					$log_data->nguoi_tao = $list_data_enrol->userid;
					$log_data->timecreated = time();
					$DB->insert_record('th_log_bulk_enrol_student', $log_data, false);
					break;
				}
			}
			// mtrace($shortname . ': ' . json_encode($data));
		}

		mtrace("My ad-hoc task started");
	}
}

?>