<?php
namespace local_th_unanswered_questions\task;

defined('MOODLE_INTERNAL') || die();

class auto_send_unanswered_question extends \core\task\scheduled_task {
	public static function auto_send_unanswered_question() {
		global $CFG, $DB;
		mtrace("My task started");
		$title  = get_string('title', 'local_th_unanswered_questions');
		$content1 = get_string('content1', 'local_th_unanswered_questions');
		$content2 = get_string('content2', 'local_th_unanswered_questions');

		$sql = "
			SELECT DISTINCT
				ra.userid AS teacherid
			FROM {course} c
			JOIN {context} ctx
				ON ctx.instanceid = c.id
				AND ctx.contextlevel = :coursecontext
			JOIN {role_assignments} ra
				ON ra.contextid = ctx.id
			JOIN {role} r
				ON r.id = ra.roleid
				AND r.shortname = 'editingteacher'
			JOIN {qaa} qaa
				ON qaa.course = c.id
			JOIN {qaapairs} qp
				ON qp.qaaid = qaa.id
			WHERE c.startdate >= :twomonthsago
			AND qp.status = 0
		";

		$monthlimit = (int) get_config('local_th_unanswered_questions', 'monthlimit');
		if ($monthlimit <= 0) {
			$monthlimit = 2; // fallback an toàn
		}

		$params = [
			'coursecontext' => CONTEXT_COURSE,
			'twomonthsago'  => strtotime("-{$monthlimit} months"),
		];

		$teachers = $DB->get_records_sql($sql, $params);

		foreach($teachers as $teacherid){
			$id = $teacherid->teacherid;
			$url = "$CFG->wwwroot/local/th_unanswered_questions/index.php?id={$id}";
			$user_from = \core_user::get_noreply_user();
			$user_to = $DB->get_record('user', array('id' => $id));
			$result = email_to_user($user_to, $user_from, "{$title}", "$content1 $url . $content2");
		}

		if (!empty($result)) {
			mtrace("Run task success");
		} else {
			mtrace("Run task failed");
		}
	}

	public function get_name() {
		return get_string('taskname', 'local_th_unanswered_questions');
	}

	public function execute() {
		self::auto_send_unanswered_question();
	}
}
