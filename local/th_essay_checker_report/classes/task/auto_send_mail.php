<?php

namespace local_th_essay_checker_report\task;
require_once $CFG->libdir . '/datalib.php';
use html_writer;
use html_table;
use moodle_url;

class auto_send_mail extends \core\task\scheduled_task {
	
	public static function auto_send_mail() {
		global $CFG, $DB, $USER;

		mtrace(get_string('task_started', 'local_th_essay_checker_report'));

		$sql = "SELECT DISTINCT
                    qat.id, qat.responsesummary, q.name AS quizname, c.fullname AS coursename, c.shortname,
                    CONCAT(u_grader.firstname, ' ', u_grader.lastname) AS grader_name,
                    CONCAT(u_attempt.firstname, ' ', u_attempt.lastname) AS attempt_name,
                    qas.fraction, ques.name AS questionname, qa.state AS attempt_state, qa.id as attemptid, qat.questionusageid, qat.slot
                FROM {question_attempts} AS qat
                JOIN {question_usages} AS qu ON qu.id = qat.questionusageid
                JOIN {quiz_attempts} AS qa ON qa.uniqueid = qu.id
                JOIN {context} AS ctx ON ctx.id = qu.contextid
                JOIN {course_modules} AS cm ON cm.id = ctx.instanceid
                JOIN {course} AS c ON c.id = cm.course
                JOIN {quiz} AS q ON q.id = cm.instance
                JOIN (
                    SELECT MAX(id) AS max_id, questionattemptid
                    FROM {question_attempt_steps}
                    GROUP BY questionattemptid
                ) AS max_qas ON max_qas.questionattemptid = qat.id
                JOIN {question_attempt_steps} AS qas ON qas.id = max_qas.max_id
                JOIN {question_attempt_step_data} AS qad ON qad.attemptstepid = qas.id
                JOIN {user} AS u_grader ON u_grader.id = qas.userid
                JOIN {user} AS u_attempt ON u_attempt.id = qa.userid
                JOIN {question} AS ques ON ques.id = qat.questionid
                WHERE ques.qtype = 'essay'
                AND (qat.responsesummary IS NULL OR qat.responsesummary = '' OR qat.responsesummary LIKE 'Any text entered here will be displayed in the response input box when a new attempt at the question starts.%')
                AND qas.fraction IS NOT NULL
                AND qas.fraction > 0
                AND qat.behaviour = 'manualgraded'
                AND qa.state = 'finished'
                ORDER BY qat.questionusageid, qat.slot";
        
        $results = $DB->get_records_sql($sql);
        $table = new html_table();
        // Định nghĩa tiêu đề của bảng
        $table->head = [
            get_string('stt', 'local_th_essay_checker_report'),
            get_string('attemptname', 'local_th_essay_checker_report'),
            get_string('shortname', 'local_th_essay_checker_report'),
            get_string('coursename', 'local_th_essay_checker_report'),
            get_string('quizname', 'local_th_essay_checker_report'),
            get_string('grade', 'local_th_essay_checker_report'),
            get_string('gradername', 'local_th_essay_checker_report')
        ];

        if($results){
            $stt = 0;
            // Thêm các hàng dữ liệu vào bảng
            foreach ($results as $result) {
                if (trim($result->responsesummary) == null || iconv('UTF-8', 'ASCII//TRANSLIT', trim($result->responsesummary)) == 'Any text entered here will be displayed in the response input box when a new attempt at the question starts.') {
                    $stt++;

                    // Chuyển đổi sang thang điểm 10
                    $score = $result->fraction * 10;

                    // Định dạng kết quả nếu cần (ví dụ: làm tròn đến 2 chữ số thập phân)
                    $score = round($score, 2);

                    // Tạo một liên kết đến trang review
                    $gradeLink = html_writer::link(
                        new moodle_url('/mod/quiz/review.php', ['attempt' => $result->attemptid]) . '#question-' . $result->questionusageid . '-' . $result->slot,
                        $score
                    );

                    // Thêm dữ liệu của hàng vào bảng
                    $table->data[] = [
                        $stt,
                        $result->attempt_name,
                        $result->shortname,
                        $result->coursename,
                        $result->quizname,
                        $gradeLink,
                        $result->grader_name
                    ];
                }
            }

            $table->attributes = array('class' => 'th_essay_checker_report', 'border' => '1');
            $table->attributes['style'] = "width: 100%; text-align:left; border-collapse: collapse; border: 1px solid black;";
            // Hiển thị bảng
            $table_html = html_writer::table($table);

            // Giả sử bạn đã có đối tượng $table (mảng dữ liệu) và tạo bảng HTML từ đó.
            $table_html = html_writer::table($table);

            // Định nghĩa tiêu đề email và nội dung HTML (bao gồm tiêu đề dạng heading và bảng HTML)
            $subject = get_string('email_subject', 'local_th_essay_checker_report'); 
            $html_message = html_writer::tag('div', get_string('email_body', 'local_th_essay_checker_report', $table_html));

            // Tạo phiên bản text của email bằng cách loại bỏ các thẻ HTML
            $plain_message = strip_tags($html_message);

            // Lấy thông tin người gửi: sử dụng user hỗ trợ của Moodle
            $from = \core_user::get_support_user();

            // Lấy danh sách email từ cấu hình (các email ngăn cách nhau bởi dấu phẩy)
            $listuser = get_config('local_th_essay_checker_report', 'email');

            if (empty($listuser)) {
                mtrace(get_string('no_email_configured', 'local_th_essay_checker_report'));
                return;
            }

            // Tách chuỗi thành mảng các email
            $emails = explode(',', $listuser);
            // Gửi email cho từng địa chỉ
            foreach ($emails as $email) {
                $email = trim($email);
                if (!empty($email)) {
                    // Tạo đối tượng người nhận (user) với thông tin tối thiểu
                    $recipient = $DB->get_record('user', array('email' => $email));

                    if ($recipient) {
                        // Gửi email cho người nhận hiện tại
                        $sent = email_to_user($recipient, $from, $subject, $plain_message, $html_message);
                    
                        if (!$sent) {
                            mtrace(get_string('email_send_failed', 'local_th_essay_checker_report', $email));
                        } else {
                            mtrace(get_string('email_send_success', 'local_th_essay_checker_report'));
                        }
                    } else {
                        mtrace(get_string('email_user_not_found', 'local_th_essay_checker_report', $email));
                    }
                }
            }
        }

		mtrace(get_string('task_finished', 'local_th_essay_checker_report'));
	}

	/**
	 * Name for this task.
	 *
	 * @return string
	 */
	public function get_name() {
		return get_string('taskname', 'local_th_essay_checker_report');
	}

	/**
	 * Remove old entries from table block_recent_activity
	 */
	public function execute() {
		self::auto_send_mail();
	}
}

?>