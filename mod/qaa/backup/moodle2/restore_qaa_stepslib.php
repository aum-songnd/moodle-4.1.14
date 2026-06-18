<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * All the steps to restore mod_qa are defined here.
 *
 * @package     mod_qa
 * @category    restore
 * @copyright   pham le minh
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// For more information about the backup and restore process, please visit:
// https://docs.moodle.org/dev/Backup_2.0_for_developers
// https://docs.moodle.org/dev/Restore_2.0_for_developers

/**
 * Defines the structure step to restore one mod_qa activity.
 */
class restore_qaa_activity_structure_step extends restore_activity_structure_step {

	/**
	 * Defines the structure to be restored.
	 *
	 * @return restore_path_element[].
	 */
	protected function define_structure() {
		$paths = array();
		$userinfo = $this->get_setting_value('userinfo');

		$paths[] = new restore_path_element('qaa', '/activity/qaa');
		if ($userinfo) {
			$paths[] = new restore_path_element('qaapairs', '/activity/qaa/qaapairs');
		}

		return $this->prepare_activity_structure($paths);
	}

	/**
	 * Processes the Restore element name restore data.
	 *
	 * @param array $data Parsed element data.
	 */
	protected function process_qaa($data) {
		global $DB;

		$data = (object) $data;
		$data->course = $this->get_courseid();
		$data->timecreated = $this->apply_date_offset($data->timecreated);
		$data->timemodified = $this->apply_date_offset($data->timemodified);

		// Insert the qaa record.
		$newitemid = $DB->insert_record('qaa', $data);

		// Immediately after inserting record call this.
		$this->apply_activity_instance($newitemid);
	}

	/**
	 * Processes one <qaapairs> element from backup.
	 *
	 * @param array $data Parsed element data.
	 */
	protected function process_qaapairs($data) {
		global $DB;

		$data = (object)$data;

		// Lưu lại old id để set mapping sau khi insert.
		$oldid = isset($data->id) ? (int)$data->id : 0;
		unset($data->id);

		// Map parent activity (qaa) → instance mới của activity trong khóa học mới.
		$data->qaaid = $this->get_new_parentid('qaa');

		// Remap user ids nếu có.
		if (!empty($data->studentid)) {
			$mapped = $this->get_mappingid('user', $data->studentid);
			$data->studentid = $mapped ? $mapped : null;
		}
		if (!empty($data->teacherid)) {
			$mapped = $this->get_mappingid('user', $data->teacherid);
			$data->teacherid = $mapped ? $mapped : null;
		}

		// Remap parentid (quan trọng)
		if (!empty($data->parentid)) {
			$mappedparent = $this->get_mappingid('qaapairs', $data->parentid);
			// Nếu parent đã được insert trước đó, mappedparent sẽ có giá trị.
			// Nếu vì lý do nào đó chưa có mapping (trường hợp hiếm), tạm để NULL để tránh FK lỗi,
			// bạn có thể “sửa nối” ở after_execute (xem ghi chú bên dưới).
			$data->parentid = $mappedparent ? $mappedparent : null;
		} else {
			$data->parentid = null;
		}

		// Offsets thời gian (tùy schema của bạn có các cột nào)
		if (isset($data->timecreatedquestion)) {
			$data->timecreatedquestion = $this->apply_date_offset($data->timecreatedquestion);
		}
		if (isset($data->timemodifiedquestion)) {
			$data->timemodifiedquestion = $this->apply_date_offset($data->timemodifiedquestion);
		}
		if (isset($data->timecreatedanswer)) {
			$data->timecreatedanswer = $this->apply_date_offset($data->timecreatedanswer);
		}
		if (isset($data->timemodifiedanswer)) {
			$data->timemodifiedanswer = $this->apply_date_offset($data->timemodifiedanswer);
		}

		// Insert bản ghi mới.
		$newitemid = $DB->insert_record('qaapairs', $data);

		// Lưu mapping old→new cho các bản ghi kế tiếp (và cho fileareas).
		// Tham số thứ 4 = true để cho phép map file liên quan tới itemid này.
		$this->set_mapping('qaapairs', $oldid, $newitemid, true);
	}

	/**
	 * Defines post-execution actions.
	 */
	protected function after_execute() {
		// Add qaa related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_qaa', 'message', 'qaapairs');
        $this->add_related_files('mod_qaa', 'attachments', 'qaapairs');
	}
}
