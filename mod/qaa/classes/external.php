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
 * qaa module external API
 *
 * @package    mod_qaa
 * @category   external
 * @copyright  2016 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/mod/qaa/classes/local/repository/qaapairs_repository.php');
use mod_qaa\local\repository\qaapairs_repository;

/**
 * qaa module external functions
 *
 * @package    mod_qaa
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_qaa_external extends external_api {

	/**
	 * Describes the parameters for get_qaas_by_courses.
	 *
	 * @return external_function_parameters
	 */
	public static function detailqaa_parameters() {

		return new external_function_parameters(
			[
				'courseid' => new external_value(PARAM_INT, 'course id'),
				'userid' => new external_value(PARAM_INT, 'user id'),
				'cmid' => new external_value(PARAM_INT, 'course module id'),
				'questionid' => new external_value(PARAM_INT, 'question id'),
			]
		);
	}

	public static function detailqaa($courseid, $userid, $cmid, $questionid) {
		global $DB, $CFG, $USER;
		require_once $CFG->dirroot . '/mod/qaa/lib.php';
		require_once $CFG->dirroot . '/local/thlib/lib.php';
		require_once $CFG->dirroot . '/user/lib.php';   // user_get_user_details()
		require_once $CFG->libdir . '/weblib.php';      // format_time()
	
		// Lấy bản ghi xuất phát (có thể là root hoặc 1 reply bất kỳ) bằng tham số ràng buộc (chống SQL injection).
		$sql = "SELECT qp.*
				  FROM {qaa} q
				  JOIN {qaapairs} qp ON qp.qaaid = q.id
				  JOIN {course_modules} cm ON cm.course = q.course AND cm.instance = q.id
				 WHERE cm.course = :courseid
				   AND cm.id     = :cmid
				   AND qp.id     = :qid";
		$start = $DB->get_record_sql($sql, ['courseid' => $courseid, 'cmid' => $cmid, 'qid' => $questionid], IGNORE_MISSING);
		if (!$start) {
			// Không tìm thấy bản ghi phù hợp trong activity này.
			throw new invalid_parameter_exception('Invalid question id / course-module context');
		}
	
		// Tìm ROOT của luồng (leo lên tới khi parentid IS NULL).
		$root = $start;
		while (!empty($root->parentid)) {
			$root = $DB->get_record('qaapairs', ['id' => (int)$root->parentid], '*', IGNORE_MISSING);
			if (!$root) break;
		}
		if (!$root) {
			// Nếu vì lý do gì không lần ra root, lấy chính start làm root.
			$root = $start;
		}
		$rootid = (int)$root->id;
	
		// Quyền trong module.
		$modulecontext = context_module::instance($cmid);
		$canmake       = has_capability('mod/qaa:makequest',   $modulecontext, $userid);
		$canapproval   = has_capability('mod/qaa:approval',    $modulecontext, $userid);
	
		// Helper chọn mốc thời gian hiển thị cho 1 post (ưu tiên answer).
		$pick_latest_ts = static function($r): int {
			if (!$r) return 0;
			return (int)(
				($r->timemodifiedanswer ?? 0)
				?: ($r->timecreatedanswer ?? 0)
				?: ($r->timemodifiedquestion ?? 0)
				?: ($r->timecreatedquestion ?? 0)
				?: 0
			);
		};
	
		// Helper build item hiển thị: message URL rewrite, tác giả, avatar, time, ago, weektitle.
		$build_item = function ($post) use ($modulecontext, $DB, $pick_latest_ts) {
			// Rewrite file URLs trong message nếu có
			if (property_exists($post, 'message')) {
				$post->message = file_rewrite_pluginfile_urls(
					(string)$post->message,
					'pluginfile.php',
					$modulecontext->id,
					'mod_qaa',
					'message',
					$post->id
				);
			}
	
			// Xác định tác giả: ưu tiên teacherid nếu có, ngược lại studentid
			$byrole = !empty($post->teacherid) ? 'teacher' : 'student';
			$byid   = !empty($post->teacherid) ? (int)$post->teacherid : ((int)$post->studentid ?: 0);
			$byname = '';
			$avatar = '';
	
			if ($byid) {
				$u = $DB->get_record('user', ['id' => $byid]);
				if ($u) {
					global $PAGE;
					$byname  = fullname($u);
					$up = new \user_picture($u);
					$up->size = 100;
					$avatar = $up->get_url($PAGE)->out(false);
				}
			}
	
			$t = $pick_latest_ts($post);
			$ago = $t ? format_time(max(0, time() - $t)) : null;
	
			// weektitle gợi ý (Question/Answer + người + thời gian)
			$isroot = empty($post->parentid);
			$label  = $isroot ? get_string('question') : get_string('answer');
			$weektitle = $label . ($byname ? (' (' . $byname . ' - ' . get_datetime($t, get_string('strftimedaydatetime', 'langconfig')) . ')') : '');
	
			return (object)[
				'id'        => (int)$post->id,
				'parentid'  => $post->parentid ? (int)$post->parentid : null,
				'status'    => isset($post->status) ? (int)$post->status : null,
	
				// Nội dung (ưu tiên field 'message' hiện đại; nếu schema cũ dùng 'question'/'answer' thì app vẫn đọc 'message')
				'message'   => property_exists($post, 'message')
								? (string)$post->message
								: ((string)($post->answer ?? $post->question ?? '')),
	
				// Tác giả
				'byid'      => $byid ?: null,
				'byname'    => $byname,
				'byrole'    => $byrole,
				'avatar'    => $avatar,
	
				// Thời gian
				'time'      => $t ?: null,
				'ago'       => $ago,
				'weektitle' => $weektitle,
	
				// (giữ lại các mốc cũ nếu cần)
				'timecreatedquestion'   => isset($post->timecreatedquestion) ? (int)$post->timecreatedquestion : null,
				'timemodifiedquestion'  => isset($post->timemodifiedquestion) ? (int)$post->timemodifiedquestion : null,
				'timecreatedanswer'     => isset($post->timecreatedanswer) ? (int)$post->timecreatedanswer : null,
				'timemodifiedanswer'    => isset($post->timemodifiedanswer) ? (int)$post->timemodifiedanswer : null,
			];
		};
	
		// Lọc hiển thị theo rule: nếu post chưa duyệt -> chỉ hiển thị cho người có quyền approval hoặc owner của post.
		$can_view_post = function($post) use ($userid, $canapproval) {
			$isapproved = isset($post->status) && ((int)$post->status === 1);
			$isowner = $userid && (
				$userid == (int)($post->studentid ?? 0) ||
				$userid == (int)($post->teacherid ?? 0)
			);
			return $isapproved || $canapproval || $isowner;
		};
	
		// === Thu thập toàn bộ luồng ===
		$thread   = [];
		$rootitem = null;

		// KHÔNG push root vào thread nữa.
		// Thay vào đó build riêng 1 object 'root' nếu người dùng được phép xem.
		if ($can_view_post($root)) {
			$rootitem = $build_item($root);

			if($rootitem->status == 2) {
				$rejectchild = $DB->get_record_sql("
					SELECT message
					FROM {qaapairs}
					WHERE parentid = ?
					AND teacherid IS NOT NULL
					AND status = 2
				ORDER BY id DESC LIMIT 1
				", [$rootitem->id]);

				if ($rejectchild) {
					$rootitem->reasonreject = $rejectchild->message;
				} else {
					$rootitem->reasonreject = null;
				}
			} else {
				$rootitem->reasonreject = null;
			}

			if(($rootitem->byrole == 'student' && (int)$rootitem->byid != (int)$userid && !$canapproval) || $rootitem->status != 1) {
				$canmake = 0;
			}
		
			$isreply = 0;
			if ($canmake) {
				$context_course = context_course::instance($courseid);
				$studentrole = $DB->get_record('role', ['shortname' => 'student'], 'id', IGNORE_MISSING);
				$cm = get_coursemodule_from_id('qaa', $cmid, 0, false, MUST_EXIST);
				$moduleinstance = $DB->get_record('qaa', ['id' => $cm->instance], '*', MUST_EXIST);
				if (user_has_role_assignment($USER->id, $studentrole->id, $context_course->id)) {
					if ($moduleinstance->question_close_time > time() || $moduleinstance->question_close_time == 0) {
						$isreply = 1;
					} else {
						$isreply = 0;
					}
				} else {
					$isreply = 1;
				}
			}

			$isedit = qaa_is_edit($rootitem, $rootitem->id, $cmid, $userid);
			$isdelete = qaa_is_delete($rootitem, $rootitem->id, $cmid, $userid);

			$rootitem->isreply = $isreply;
			$rootitem->isedit = $isedit;
			$rootitem->isdelete = $isdelete;
		}

		// BFS lấy toàn bộ con/cháu, CHỈ lấy các bản ghi có parentid trong queue (=> không dính root).
		$queue = [$rootid];

		while (!empty($queue)) {
			list($insql, $inparams) = $DB->get_in_or_equal(array_map('intval', $queue), SQL_PARAMS_QM, 'p');
			$queue = [];

			$children = $DB->get_records_sql(
				"SELECT *
				FROM {qaapairs}
				WHERE parentid $insql
			ORDER BY COALESCE(timecreatedanswer, timemodifiedquestion, timecreatedquestion, id) ASC",
				$inparams
			);

			foreach ($children as $c) {
				if ($can_view_post($c)) {
					$thread_child = $build_item($c);

					$fs = get_file_storage();
					$modulecontext = context_module::instance($cmid);
					$files = $fs->get_area_files($modulecontext->id, 'mod_qaa', 'attachments', $thread_child->id);
					
					global $CFG;
					foreach ($files as $key => $file) {
						if ($file->get_filename() == '.') {
							continue;
						}

						$path = '/' . $modulecontext->id . '/mod_qaa/attachments/' . $file->get_itemid() . $file->get_filepath() . $file->get_filename();
						$url = moodle_url::make_file_url('/pluginfile.php', $path, 0);
						global $OUTPUT;
						$thread_child->message = $thread_child->message . html_writer::link($url, $OUTPUT->pix_icon(file_file_icon($file), get_mimetype_description($file)).$file->get_filename(), array('target' => '_blank', 'class' => 'qaa-attachments'));
					}

					if($thread_child->status == 2) {
						$rejectchild = $DB->get_record_sql("
							SELECT message
							FROM {qaapairs}
							WHERE parentid = ?
							AND teacherid IS NOT NULL
							AND status = 2
						ORDER BY id DESC LIMIT 1
						", [$thread_child->id]);
		
						if ($rejectchild) {
							$thread_child->reasonreject = $rejectchild->message;
						} else {
							$thread_child->reasonreject = null;
						}
					} else {
						$thread_child->reasonreject = null;
					}

					$isreply = 0;
					$isedit = qaa_is_edit($thread_child, $rootitem->id, $cmid, $userid);
					$isdelete = qaa_is_delete($thread_child, $rootitem->id, $cmid, $userid);

					$thread_child->isreply = $isreply;
					$thread_child->isedit = $isedit;
					$thread_child->isdelete = $isdelete;

					if ($thread_child->status == 2 || $thread_child->status == 0) {
						if(($canapproval || $userid == $thread_child->byid) && $thread_child->byrole != 'teacher') {
							$thread[] = $thread_child;  // chỉ con/cháu
						}
					} else {
						$thread[] = $thread_child;
					}
				}
				$queue[] = (int)$c->id;
			}
		}

		uasort($thread, function ($a, $b) {
			$ta = (int)($a->timecreatedanswer ?? 0);
			$tb = (int)($b->timecreatedanswer ?? 0);
			if ($ta === $tb) {
				return (int)($a->id ?? 0) <=> (int)($b->id ?? 0);
			}
			return $ta <=> $tb;
		});

		$res = (object)[
			'makequest'   => $canmake,
			'canapproval' => $canapproval,
			// các field cũ theo ROOT (giữ tương thích)
			'id'                   => (int)$root->id,
			'parentid'             => $root->parentid ? (int)$root->parentid : null,
			'qaaid'                => (int)$root->qaaid,
			'question'             => property_exists($root, 'question') ? (string)$root->question : null,
			'studentid'            => isset($root->studentid) ? (int)$root->studentid : null,
			'answer'               => property_exists($root, 'answer') ? (string)$root->answer : null,
			'teacherid'            => isset($root->teacherid) ? (int)$root->teacherid : null,
			'teacheridmodified'    => isset($root->teacheridmodified) ? (int)$root->teacheridmodified : null,
			'timecreatedquestion'  => isset($root->timecreatedquestion) ? (int)$root->timecreatedquestion : null,
			'timemodifiedquestion' => isset($root->timemodifiedquestion) ? (int)$root->timemodifiedquestion : null,
			'timecreatedanswer'    => isset($root->timecreatedanswer) ? (int)$root->timecreatedanswer : null,
			'timemodifiedanswer'   => isset($root->timemodifiedanswer) ? (int)$root->timemodifiedanswer : null,

			'rootid' => $rootid,
			'root'   => $rootitem,          // 👈 root nằm ngoài thread
			'thread' => array_values($thread), // chỉ con/cháu
		];

		return $res;
	}
	

	public static function detailqaa_returns() {
		$post_item = new external_single_structure([
			'id'        => new external_value(PARAM_INT,   'post id', VALUE_OPTIONAL),
			'parentid'  => new external_value(PARAM_INT,   'parent id', VALUE_OPTIONAL),
			'status'    => new external_value(PARAM_INT,   'approval status', VALUE_OPTIONAL),
	
			'message'   => new external_value(PARAM_RAW,   'post content (HTML)', VALUE_OPTIONAL),
	
			'byid'      => new external_value(PARAM_INT,   'author user id', VALUE_OPTIONAL),
			'byname'    => new external_value(PARAM_RAW,   'author full name', VALUE_OPTIONAL),
			'byrole'    => new external_value(PARAM_ALPHA, 'author role: teacher|student', VALUE_OPTIONAL),
			'avatar'    => new external_value(PARAM_RAW,   'author avatar url', VALUE_OPTIONAL),
	
			'time'      => new external_value(PARAM_INT,   'unified timestamp', VALUE_OPTIONAL),
			'ago'       => new external_value(PARAM_RAW,   'humanized elapsed time', VALUE_OPTIONAL),
			'weektitle' => new external_value(PARAM_RAW,   'Question/Answer + name + datetime', VALUE_OPTIONAL),
			'timecreatedquestion'  => new external_value(PARAM_INT, 'timecreatedquestion', VALUE_OPTIONAL),
			'timemodifiedquestion' => new external_value(PARAM_INT, 'timemodifiedquestion', VALUE_OPTIONAL),
			'timecreatedanswer'    => new external_value(PARAM_INT, 'timecreatedanswer', VALUE_OPTIONAL),
			'timemodifiedanswer'   => new external_value(PARAM_INT, 'timemodifiedanswer', VALUE_OPTIONAL),
			'reasonreject'   => new external_value(PARAM_RAW, 'reasonreject', VALUE_OPTIONAL),
			'isreply'   => new external_value(PARAM_INT, 'isreply', VALUE_OPTIONAL),
			'isedit'   => new external_value(PARAM_INT, 'isedit', VALUE_OPTIONAL),
			'isdelete'   => new external_value(PARAM_INT, 'isdelete', VALUE_OPTIONAL),
		]);
	
		return new external_single_structure([
			// Quyền
			'makequest'   => new external_value(PARAM_BOOL, 'can make question', VALUE_OPTIONAL),
			'answerquest' => new external_value(PARAM_BOOL, 'can answer question', VALUE_OPTIONAL),
			'canapproval' => new external_value(PARAM_BOOL, 'can approval question', VALUE_OPTIONAL),
	
			// Trường legacy của ROOT (giữ tương thích)
			'id'                   => new external_value(PARAM_INT,  'root qaap id', VALUE_OPTIONAL),
			'parentid'             => new external_value(PARAM_INT,  'root parentid', VALUE_OPTIONAL),
			'qaaid'                => new external_value(PARAM_INT,  'qaa id', VALUE_OPTIONAL),
			'question'             => new external_value(PARAM_RAW,  'root question (legacy)', VALUE_OPTIONAL),
			'studentid'            => new external_value(PARAM_INT,  'root studentid', VALUE_OPTIONAL),
			'answer'               => new external_value(PARAM_RAW,  'root answer (legacy)', VALUE_OPTIONAL),
			'teacherid'            => new external_value(PARAM_INT,  'root teacherid', VALUE_OPTIONAL),
			'teacheridmodified'    => new external_value(PARAM_INT,  'root teacheridmodified', VALUE_OPTIONAL),
			'timecreatedquestion'  => new external_value(PARAM_INT,  'root timecreatedquestion', VALUE_OPTIONAL),
			'timemodifiedquestion' => new external_value(PARAM_INT,  'root timemodifiedquestion', VALUE_OPTIONAL),
			'timecreatedanswer'    => new external_value(PARAM_INT,  'root timecreatedanswer', VALUE_OPTIONAL),
			'timemodifiedanswer'   => new external_value(PARAM_INT,  'root timemodifiedanswer', VALUE_OPTIONAL),
	
			// NEW
			'rootid' => new external_value(PARAM_INT, 'root id of the thread', VALUE_OPTIONAL),
	
			// 👇 Thêm 'root' tách biệt khỏi thread
			'root' => $post_item,
	
			// 👇 'thread' chỉ chứa con/cháu (không gồm root)
			'thread' => new external_multiple_structure(
				$post_item,
				'children of the root (excluding root)'
			),
		]);
	}

	/**
	 * Describes the parameters for get_qaas_by_courses.
	 *
	 * @return external_function_parameters
	 */
	public static function getparentquestion_parameters() {
		$qaa = array(
			'userid' => new external_value(PARAM_INT, 'userid'),
			'courseid' => new external_value(PARAM_INT, 'courseid'),
			'cmid' => new external_value(PARAM_INT, 'Course module id'),
			'page' => new external_value(PARAM_INT, 'page'),
			'perpage' => new external_value(PARAM_INT, 'perpage')
		);
		return new external_function_parameters(
			[
				'qaa' => new external_single_structure($qaa),
			]
		);
	}

	/**
	 * Returns a list of qaas in a provided list of courses,
	 * if no list is provided all qaas that the user can view will be returned.
	 *
	 * @param array $courseids the course ids
	 * @return array the qaa details
	 */
	// public static function getparentquestion($qaa) {
	// 	global $USER, $DB, $CFG, $PAGE;
	
	// 	require_once $CFG->dirroot . '/lib/grade/grade_item.php';
	// 	require_once $CFG->dirroot . '/local/thlib/lib.php';
	// 	require_once $CFG->dirroot . '/mod/qaa/lib.php';
	// 	require_once $CFG->dirroot . '/user/lib.php'; // user_get_user_details()
	
	// 	$cmid    = $qaa['cmid'];
	// 	$page    = $qaa['page'];      // 0, 1, 2, ...
	// 	$perpage = $qaa['perpage'];   // số bản ghi mỗi trang

	// 	// Lấy các cặp Q/A (root) theo cmid
	// 	$sql = "SELECT qp.*
	// 			FROM {qaa} q
	// 			JOIN {qaapairs} qp ON qp.qaaid = q.id
	// 			JOIN {course_modules} cm ON cm.course = q.course AND q.id = cm.instance
	// 			WHERE cm.id = :cmid
	// 			AND qp.parentid IS NULL
	// 			ORDER BY qp.timecreatedquestion DESC";

	// 	$params = ['cmid' => $cmid];
		
	// 	// Tính offset
	// 	$offset = $page * $perpage;

	// 	// Lấy theo page / perpage
	// 	$qaaps = $DB->get_records_sql($sql, $params);
	
	// 	$list_question = [];
	
	// 	if (!empty($qaaps)) {
	// 		$modulecontext = context_module::instance($cmid);
	// 		$userid        = $USER->id;
	
	// 		// Quyền
	// 		$canapproval   = has_capability('mod/qaa:approval',  $modulecontext, $userid);
	
	// 		// Định nghĩa trạng thái đã duyệt
	// 		define('APPROVED_STATUS', 1);
	
	// 		foreach ($qaaps as $qaap) {
	
	// 			// --- Lọc theo phê duyệt ---
	// 			$isapproved = (int)$qaap->status === APPROVED_STATUS;
	// 			$isowner    = ($userid && ($userid == (int)$qaap->studentid || $userid == (int)$qaap->teacherid));
	
	// 			if (!$isapproved && !$canapproval && !$isowner) {
	// 				// Chưa được duyệt -> chỉ trả nếu có quyền approval hoặc là chính chủ
	// 				continue;
	// 			}
	
	// 			// --- Rewrite file URLs trong message ---
	// 			$qaap->message = format_string($qaap->message);
	
	// 			// --- Lấy thông tin user, tên + ảnh ---
	// 			$student = $qaap->studentid ? $DB->get_record('user', ['id' => $qaap->studentid]) : null;
	// 			$teacher = $qaap->teacherid ? $DB->get_record('user', ['id' => $qaap->teacherid]) : null;
	
	// 			// Tên hiển thị (giữ nguyên cấu trúc cũ: gán vào field studentid/teacherid)
	// 			if ($student) {
	// 				$qaap->studentid = fullname($student);
	// 			}
	// 			if ($teacher) {
	// 				$qaap->teacherid = fullname($teacher);
	// 			}
	
	// 			// Ảnh đại diện (URL có token)
	// 			$studentavatar = '';
	// 			$teacheravatar = '';
	
	// 			if ($student) {
	// 				$up = new user_picture($student);
	// 				$studentavatar = $up->get_url($PAGE)->out(false);
	// 			}
	
	// 			// Thông tin weekquestion / weekanswer
	// 			if ($student) {
	// 				$qaap->weekquestion = get_string('question') . ' (' . fullname($student) . ' - ' .
	// 					get_datetime($qaap->timecreatedquestion, get_string('strftimedaydatetime', 'langconfig')) . ')';
	// 			} else {
	// 				$qaap->weekquestion = get_string('question');
	// 			}
	// 			// Gắn thêm 2 field avatar vào object trả về
	// 			$qaap->studentavatar = $studentavatar;

	// 			// --- Lấy câu trả lời gần nhất (child mới nhất); nếu không có thì lấy mốc của chính $qaap ---
	// 			$childsql = "SELECT c.*
	// 			FROM {qaapairs} c
	// 			WHERE c.parentid = :parentid
	// 			ORDER BY COALESCE(c.timemodifiedanswer, c.timecreatedanswer, c.timemodifiedquestion, c.timecreatedquestion) DESC";
	// 			$children = $DB->get_records_sql($childsql, ['parentid' => $qaap->id], 0, 1);

	// 			$approvedstatus = 1; // hoặc dùng biến đã có ở trên
	// 			$qaap->approvedanswercount = (function() use ($DB, $qaap, $approvedstatus) {
	// 				$count  = 0;
	// 				$queue  = [(int)$qaap->id]; // bắt đầu từ root, sẽ lấy con của các id này

	// 				while (!empty($queue)) {
	// 					list($insql, $inparams) = $DB->get_in_or_equal($queue, SQL_PARAMS_QM, 'd');
	// 					// Lấy tất cả con trực tiếp của các node trong queue
	// 					$children = $DB->get_records_sql(
	// 						"SELECT id, status
	// 						FROM {qaapairs}
	// 						WHERE parentid $insql",
	// 						$inparams
	// 					);

	// 					// Chuẩn bị lớp tiếp theo
	// 					$queue = [];
	// 					foreach ($children as $c) {
	// 						if ((int)$c->status === (int)$approvedstatus) {
	// 							$count++;
	// 						}
	// 						$queue[] = (int)$c->id; // tiếp tục duyệt cháu, chắt...
	// 					}
	// 				}
	// 				return $count;
	// 			})();

	// 			$qaap->latestanswerago = null;

	// 			// helper chọn mốc thời gian mới nhất từ một bản ghi qaap/child
	// 			$pick_latest_ts = static function($r): int {
	// 			if (!$r) return 0;
	// 			return (int)(
	// 			($r->timemodifiedanswer ?? 0)
	// 			?: ($r->timecreatedanswer ?? 0)
	// 			?: ($r->timemodifiedquestion ?? 0)
	// 			?: ($r->timecreatedquestion ?? 0)
	// 			?: 0
	// 			);
	// 			};

	// 			$latestTs = 0;

	// 			if (!empty($children)) {
	// 			$ans = reset($children);
	// 			$latestTs = $pick_latest_ts($ans);
	// 			}

	// 			// Fallback: không có child (hoặc child không có mốc hợp lệ) thì lấy mốc của chính root $qaap
	// 			if (empty($children) || $latestTs === 0) {
	// 			$latestTs = $pick_latest_ts($qaap);
	// 			}

	// 			if ($latestTs > 0) {
	// 			$diff = max(0, time() - $latestTs);
	// 			$qaap->latestanswerago = self::th_humanize_duration_vi($diff); // ví dụ: "1 giờ 12 phút"
	// 			}
	
	// 			$list_question[] = $qaap;
	// 		}
	// 	}
	
	// 	// Trả về quyền để App quyết định UI
	// 	$context = context_module::instance($cmid);

	// 	$context_course = context_course::instance($qaa['courseid']);
	// 	$studentrole = $DB->get_record('role', ['shortname' => 'student'], 'id', IGNORE_MISSING);
	// 	$teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher'], 'id', IGNORE_MISSING);
	// 	$cm = get_coursemodule_from_id('qaa', $cmid, 0, false, MUST_EXIST);
	// 	$moduleinstance = $DB->get_record('qaa', ['id' => $cm->instance], '*', MUST_EXIST);
	// 	if (user_has_role_assignment($USER->id, $studentrole->id, $context_course->id)) {
	// 		if ($moduleinstance->question_close_time > time() || $moduleinstance->question_close_time == 0) {
	// 			$makequest = has_capability('mod/qaa:makequest', $context, $USER->id);
	// 		} else {
	// 			$makequest = false;
	// 		}
	// 	} else if (user_has_role_assignment($USER->id, $teacherrole->id, $context->id)) {
	// 		$makequest = false;
	// 	} else {
	// 		$makequest = has_capability('mod/qaa:makequest', $context, $USER->id);
	// 	}
	
	// 	return [
	// 		'makequest'     => $makequest,
	// 		'list_question' => $list_question,
	// 	];
	// }

	public static function getparentquestion($qaa) {
		global $USER, $DB, $CFG, $PAGE;
	
		require_once $CFG->dirroot . '/lib/grade/grade_item.php';
		require_once $CFG->dirroot . '/local/thlib/lib.php';
		require_once $CFG->dirroot . '/mod/qaa/lib.php';
		require_once $CFG->dirroot . '/user/lib.php'; // user_get_user_details()
	
		$cmid    = $qaa['cmid'];
		$page    = $qaa['page'];      // 0, 1, 2, ...
		$perpage = $qaa['perpage'];   // số bản ghi mỗi trang
	
		// Lấy cm và qaaid
		$cm    = get_coursemodule_from_id('qaa', $cmid, 0, false, MUST_EXIST);
		$qaaid = $cm->instance;
	
		// Context & quyền
		$modulecontext = context_module::instance($cmid);
		$userid        = $USER->id;
		$canapproval   = has_capability('mod/qaa:approval', $modulecontext, $userid);
		$approvedstatus = 1;
	
		// Tính offset phân trang (page 0,1,2,...)
		$offset = $page * $perpage;
	
		// Base where
		$where  = "qp.qaaid = :qaaid AND qp.parentid IS NULL";
		$params = ['qaaid' => $qaaid];
	
		// Nếu KHÔNG có quyền duyệt => chỉ xem được:
		// - câu đã duyệt, hoặc
		// - câu của chính mình (studentid/teacherid)
		if (!$canapproval) {
			$where .= " AND (qp.status = :approvedstatus
						  OR qp.studentid = :userid1
						  OR qp.teacherid = :userid2)";
			$params['approvedstatus'] = $approvedstatus;
			$params['userid1']        = $userid;
			$params['userid2']        = $userid;
		}
	
		// Query root (parentid IS NULL), đã lọc theo quyền, có phân trang
		$sql = "SELECT qp.*
				FROM {qaapairs} qp
				WHERE $where
				ORDER BY qp.timecreatedquestion DESC, qp.id DESC";
	
		$qaaps = $DB->get_records_sql($sql, $params, $offset, $perpage);
	
		$list_question = [];
	
		if (!empty($qaaps)) {
	
			foreach ($qaaps as $qaap) {
	
				// (Có thể bỏ check này vì SQL đã lọc; giữ lại cho an toàn)
				$isapproved = (int)$qaap->status === $approvedstatus;
				$isowner    = ($userid && ($userid == (int)$qaap->studentid || $userid == (int)$qaap->teacherid));
	
				if (!$isapproved && !$canapproval && !$isowner) {
					continue;
				}
	
				// --- Xử lý message ---
				$qaap->message = format_string($qaap->message);
	
				// --- Lấy thông tin user, tên + ảnh ---
				$student = $qaap->studentid ? $DB->get_record('user', ['id' => $qaap->studentid]) : null;
				$teacher = $qaap->teacherid ? $DB->get_record('user', ['id' => $qaap->teacherid]) : null;
	
				// Tên hiển thị (ghi đè lên field studentid/teacherid)
				if ($student) {
					$qaap->studentid = fullname($student);
				}
				if ($teacher) {
					$qaap->teacherid = fullname($teacher);
				}
	
				// Ảnh đại diện
				$studentavatar = '';
				if ($student) {
					$up = new user_picture($student);
					$studentavatar = $up->get_url($PAGE)->out(false);
				}
	
				// Thông tin weekquestion
				if ($student) {
					$qaap->weekquestion = get_string('question') . ' (' . fullname($student) . ' - ' .
						get_datetime($qaap->timecreatedquestion, get_string('strftimedaydatetime', 'langconfig')) . ')';
				} else {
					$qaap->weekquestion = get_string('question');
				}

				if ($student) {
					$qaap->weekanswer = get_string('answer') . ' (' . fullname($student) . ' - ' .
					get_datetime($qaap->timecreatedanswer, get_string('strftimedaydatetime', 'langconfig')) . ')';
				} else {
					$qaap->weekanswer = get_string('answer') . ' (' . fullname($teacher) . ' - ' .
					get_datetime($qaap->timecreatedanswer, get_string('strftimedaydatetime', 'langconfig')) . ')';
				}
	
				$qaap->studentavatar = $studentavatar;
	
				// --- Lấy câu trả lời gần nhất (1 child mới nhất) ---
				$childsql = "SELECT c.*
							 FROM {qaapairs} c
							 WHERE c.parentid = :parentid
							 ORDER BY COALESCE(c.timemodifiedanswer, c.timecreatedanswer,
											   c.timemodifiedquestion, c.timecreatedquestion) DESC";
				$children = $DB->get_records_sql($childsql, ['parentid' => $qaap->id], 0, 1);
	
				// Đếm số câu trả lời đã duyệt trong toàn bộ cây con của root này
				$qaap->approvedanswercount = (function() use ($DB, $qaap, $approvedstatus) {
					$count = 0;
					$queue = [(int)$qaap->id]; // bắt đầu từ root
	
					while (!empty($queue)) {
						list($insql, $inparams) = $DB->get_in_or_equal($queue, SQL_PARAMS_QM, 'd');
						$children = $DB->get_records_sql(
							"SELECT id, status
							   FROM {qaapairs}
							  WHERE parentid $insql",
							$inparams
						);
	
						$queue = [];
						foreach ($children as $c) {
							if ((int)$c->status === (int)$approvedstatus) {
								$count++;
							}
							$queue[] = (int)$c->id;
						}
					}
					return $count;
				})();
	
				$qaap->latestanswerago = null;
	
				// Helper chọn mốc thời gian mới nhất trong một record
				$pick_latest_ts = static function($r): int {
					if (!$r) {
						return 0;
					}
					return (int)(
						($r->timemodifiedanswer ?? 0)
						?: ($r->timecreatedanswer ?? 0)
						?: ($r->timemodifiedquestion ?? 0)
						?: ($r->timecreatedquestion ?? 0)
						?: 0
					);
				};
	
				$latestTs = 0;
	
				if (!empty($children)) {
					$ans = reset($children);
					$latestTs = $pick_latest_ts($ans);
				}
	
				// Nếu không có child hoặc không có mốc hợp lệ -> lấy mốc của chính root
				if (empty($children) || $latestTs === 0) {
					$latestTs = $pick_latest_ts($qaap);
				}
	
				if ($latestTs > 0) {
					$diff = max(0, time() - $latestTs);
					$qaap->latestanswerago = self::th_humanize_duration_vi($diff); // Ví dụ: "1 giờ 12 phút"
				}
	
				$list_question[] = $qaap;
			}
		}
	
		// Trả về quyền để App quyết định UI
		$context        = $modulecontext;
		$context_course = context_course::instance($qaa['courseid']);
	
		$studentrole = $DB->get_record('role', ['shortname' => 'student'], 'id', IGNORE_MISSING);
		$teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher'], 'id', IGNORE_MISSING);
	
		$moduleinstance = $DB->get_record('qaa', ['id' => $cm->instance], '*', MUST_EXIST);
	
		if ($studentrole && user_has_role_assignment($USER->id, $studentrole->id, $context_course->id)) {
			if ($moduleinstance->question_close_time > time() || $moduleinstance->question_close_time == 0) {
				$makequest = has_capability('mod/qaa:makequest', $context, $USER->id);
			} else {
				$makequest = false;
			}
		} else if ($teacherrole && user_has_role_assignment($USER->id, $teacherrole->id, $context->id)) {
			$makequest = false;
		} else {
			$makequest = has_capability('mod/qaa:makequest', $context, $USER->id);
		}
	
		return [
			'makequest'     => $makequest,
			'list_question' => $list_question,
		];
	}	

	private static function th_humanize_duration_vi(int $seconds): string {
		$seconds = max(0, $seconds);
		$parts = [];
	
		$d = intdiv($seconds, DAYSECS);   $seconds %= DAYSECS;
		$h = intdiv($seconds, HOURSECS);  $seconds %= HOURSECS;
		$m = intdiv($seconds, MINSECS);
	
		if ($d > 0) $parts[] = $d . ' ngày';
		if ($h > 0) $parts[] = $h . ' giờ';
		if (!$parts && $m > 0) $parts[] = $m . ' phút';
		if (!$parts) $parts[] = '0 giây';
	
		return implode(' ', array_slice($parts, 0, 2));
	}

	public static function getparentquestion_returns() {
		return new external_single_structure([
			'makequest' => new external_value(PARAM_BOOL, 'makequest', VALUE_OPTIONAL),
			'answerquest' => new external_value(PARAM_BOOL, 'answerquest', VALUE_OPTIONAL),
			'list_question' => new external_multiple_structure(
				new external_single_structure([
					'id' => new external_value(PARAM_INT, 'qaap id', VALUE_OPTIONAL),
					'parentid' => new external_value(PARAM_INT, 'parentid', VALUE_OPTIONAL),
					'qaaid' => new external_value(PARAM_INT, 'qaa id', VALUE_OPTIONAL),
	
					// Lưu ý: bạn đang dùng studentid/teacherid để chứa "tên".
					'studentid' => new external_value(PARAM_RAW, 'student name', VALUE_OPTIONAL),
					'teacherid' => new external_value(PARAM_RAW, 'teacher name', VALUE_OPTIONAL),
	
					'teacheridmodified' => new external_value(PARAM_RAW, 'teacher modified', VALUE_OPTIONAL),
					'message' => new external_value(PARAM_RAW, 'message content', VALUE_OPTIONAL),
					'status' => new external_value(PARAM_INT, 'status', VALUE_OPTIONAL),
					'rejectreason' => new external_value(PARAM_TEXT, 'rejectreason', VALUE_OPTIONAL),
					'timecreatedquestion' => new external_value(PARAM_INT, 'created question', VALUE_OPTIONAL),
					'timemodifiedquestion' => new external_value(PARAM_INT, 'modified question', VALUE_OPTIONAL),
					'timecreatedanswer' => new external_value(PARAM_INT, 'created answer', VALUE_OPTIONAL),
					'timemodifiedanswer' => new external_value(PARAM_INT, 'modified answer', VALUE_OPTIONAL),
					'weekquestion' => new external_value(PARAM_RAW, 'week question info', VALUE_OPTIONAL),
					'weekanswer' => new external_value(PARAM_RAW, 'week answer info', VALUE_OPTIONAL),
					'studentavatar' => new external_value(PARAM_URL, 'student avatar url', VALUE_OPTIONAL),
					'latestanswerago' => new external_value(PARAM_RAW, 'human-readable elapsed time since latest answer', VALUE_OPTIONAL),
					'approvedanswercount' => new external_value(PARAM_INT, 'number of approved answers', VALUE_OPTIONAL),
				]),
				'list_question',
				VALUE_OPTIONAL
			),
		]);
	}


	/**
	 * Describes the parameters for get_qaas_by_courses.
	 *
	 * @return external_function_parameters
	 */
	public static function makequestion_parameters() {
		return new external_function_parameters(
			array(
				'cmid' => new external_value(PARAM_INT, 'Course module id'),
				'courseid' => new external_value(PARAM_INT, 'Course module id'),
				'question' => new external_value(PARAM_RAW, 'Course module id'),
				'parentid' => new external_value(PARAM_INT, 'Parent id'),
			)
		);
	}

	/**
	 * Returns a list of qaas in a provided list of courses,
	 * if no list is provided all qaas that the user can view will be returned.
	 *
	 * @param array $courseids the course ids
	 * @return array the qaa details
	 */
	public static function makequestion($cmid, $courseid, $question, $parentid) {

		// require __DIR__ . '/../../../config.php';
		// require_once __DIR__ . '/../lib.php';
		global $USER, $DB, $CFG;

		require_once $CFG->dirroot . '/lib/grade/grade_item.php';
		require_once $CFG->dirroot . '/local/thlib/lib.php';
		require_once $CFG->dirroot . '/mod/qaa/lib.php';

		$question = trim($question);
		$question_length = strip_tags(trim($question)); // Bỏ các thẻ HTML
		$config = get_config('mod_qaa');
		$min_character = $config->min_question_character;

		if (strlen($question_length) < $min_character) {
			throw new Exception(get_string('err_questionform', 'mod_qaa', $min_character));
		}

		$cm = get_coursemodule_from_id('qaa', $cmid);
		$qaaid = $cm->instance;

		$course = $DB->get_record('course', array('id' => $courseid));
		$modulecontext = context_module::instance($cm->id);
		$moduleinstance = $DB->get_record('qaa', array('id' => $cm->instance), '*', MUST_EXIST);

		require_capability('mod/qaa:makequest', $modulecontext);

        $now = time();

        $transaction = $DB->start_delegated_transaction();
		// Tạo bản ghi root (câu hỏi gốc từ HV).

		if(!$parentid) {
			$isTeacher = has_capability('mod/qaa:approval', $modulecontext);
			$rec = (object)[
				'qaaid'                 => $cm->instance,
				'parentid'              => null,
				'studentid'             => $USER->id,
				'teacherid'             => null,
				'message'               => $question,
				'status'                => $isTeacher ? 1 : 0,    // pending duyệt (tuỳ policy có thể auto=1)
				'timecreatedquestion'   => $now,
				'timemodifiedquestion'  => $now,
			];
	
			$id = $DB->insert_record('qaapairs', $rec, true);
	
			// Event (nếu có event "created"; nếu chưa định nghĩa có thể bỏ).
			if (class_exists('\\mod_qaa\\event\\qaapairs_created')) {
				$event = \mod_qaa\event\qaapairs_created::create(array(
					'objectid' => $rootid,
					'context' => $modulecontext
				));
				$event->add_record_snapshot('course', $course);
				$event->add_record_snapshot('qaa', $moduleinstance);
				$event->trigger();
			}
	
			$transaction->allow_commit();
		} else {
			$isTeacher = has_capability('mod/qaa:approval', $modulecontext);
			$authorStudentId = $isTeacher ? null : $USER->id;
			$authorTeacherId = $isTeacher ? $USER->id : null;
			$rec = (object)[
				'qaaid'                 => $cm->instance,
				'parentid'              => $parentid,
				'studentid'             => $authorStudentId,
				'teacherid'             => $authorTeacherId,
				'message'               => $question,
				'status'                => $isTeacher ? 1 : 0,
				'timecreatedanswer'   => $now,
				'timemodifiedanswer'  => $now,
			];
	
			$id = $DB->insert_record('qaapairs', $rec, true);

			// Event
			if (class_exists('\\mod_qaa\\event\\qaapairs_answered')) {
				$event = \mod_qaa\event\qaapairs_answered::create([
					'objectid' => $id,
					'context'  => $modulecontext
				]);
				$event->add_record_snapshot('course', $course);
				$event->add_record_snapshot('qaa', $moduleinstance);
				$event->trigger();
			}
	
			$transaction->allow_commit();
		}
		

		return ['id' => $id];
	}

	/**
	 * Describes the get_qaas_by_courses return value.
	 *
	 * @return external_single_structure
	 */
	public static function makequestion_returns() {

		return new external_single_structure(
			array(
				'id' => new external_value(PARAM_INT, 'qaa id'),
			)
		);
	}

	/**
	 * Describes the parameters for get_qaas_by_courses.
	 *
	 * @return external_function_parameters
	 */
	public static function decidequestion_parameters() {
		return new external_function_parameters(
			array(
				'cmid' => new external_value(PARAM_INT, 'Course module id'),
				'courseid' => new external_value(PARAM_INT, 'Course module id'),
				'question' => new external_value(PARAM_RAW, 'Course module id'),
				'parentid' => new external_value(PARAM_INT, 'Parent id'),
				'status' => new external_value(PARAM_INT, 'Status')
			)
		);
	}

	/**
	 * Returns a list of qaas in a provided list of courses,
	 * if no list is provided all qaas that the user can view will be returned.
	 *
	 * @param array $courseids the course ids
	 * @return array the qaa details
	 */
	public static function decidequestion($cmid, $courseid, $question, $parentid, $status) {
		global $USER, $DB, $CFG;

		require_once($CFG->libdir . '/gradelib.php');
		require_once $CFG->dirroot . '/lib/grade/grade_item.php';
		require_once $CFG->dirroot . '/local/thlib/lib.php';
		require_once $CFG->dirroot . '/mod/qaa/lib.php';

		$question = trim($question);
		$question_length = strip_tags(trim($question)); // Bỏ các thẻ HTML
		$config = get_config('mod_qaa');
		$min_character = $config->min_question_character;
		
		if (strlen($question_length) < $min_character) {
			throw new Exception(get_string('err_questionform', 'mod_qaa', $min_character));
		}

		if ((int)$status !== 1 && (int)$status !== 2) {
			throw new Exception('Trạng thái không hợp lệ');
		}		

		$cm = get_coursemodule_from_id('qaa', $cmid);
		$qaaid = $cm->instance;

		$course = $DB->get_record('course', array('id' => $courseid));
		$modulecontext = context_module::instance($cm->id);
		$moduleinstance = $DB->get_record('qaa', array('id' => $cm->instance), '*', MUST_EXIST);

		require_capability('mod/qaa:approval', $modulecontext);

        $now = time();

		if($parentid) {
			$DB->update_record('qaapairs', (object)[
				'id' => $parentid,
				'status' => $status
			]);

			$transaction = $DB->start_delegated_transaction();
			// Tạo bản ghi root (câu hỏi gốc từ HV).
			$authorTeacherId = $USER->id;
			$rec = (object)[
				'qaaid'                 => $cm->instance,
				'parentid'              => $parentid,
				'studentid'             => null,
				'teacherid'             => $authorTeacherId,
				'message'               => $question,
				'status'                => $status,
				'timecreatedanswer'   => $now,
				'timemodifiedanswer'  => $now,
			];
	
			$id = $DB->insert_record('qaapairs', $rec, true);

			// Event
			if($status == 2) {
				if (class_exists('\\mod_qaa\\event\\qaapairs_reject')) {
					$event = \mod_qaa\event\qaapairs_reject::create(array(
						'objectid' => $id,
						'context' => $modulecontext
					));
					$event->add_record_snapshot('course', $course);
					$event->add_record_snapshot('qaa', $moduleinstance);
					$event->trigger();
				}
			} else if ($status == 1) {
				// Cập nhật điểm (đếm số câu hỏi theo luồng của HV).
				$qaa = $DB->get_record('qaapairs', ['id' => $parentid], '*', MUST_EXIST);
				if(!empty($qaa->studentid)) {
					$authorid = $qaa->studentid;
					$gradeitem = grade_item::fetch([
						'itemtype'    => 'mod',
						'itemmodule'  => $cm->modname,
						'iteminstance'=> $cm->instance,
						'itemnumber'  => 0,
						'courseid'    => $course->id
					]);
					if ($gradeitem) {
						$count = $DB->count_records('qaapairs', [
							'qaaid' => $cm->instance, 'studentid' => $authorid, 'status' => 1
						]);
	
						$rawgrade = min($count, (float)$gradeitem->grademax);
						$grade = (object)[
							'userid'        => $authorid,
							'rawgrade'      => $rawgrade,
							'dategraded'    => time(),
							'datesubmitted' => time()
						];
						// Hàm từ lib.php của plugin.
						if (function_exists('qaa_grade_item_update')) {
							qaa_grade_item_update($moduleinstance, [$authorid => $grade]);
						}
					}
				}

				if (class_exists('\\mod_qaa\\event\\qaapairs_answered')) {
					$event = \mod_qaa\event\qaapairs_answered::create([
						'objectid' => $id,
						'context'  => $modulecontext
					]);
					$event->add_record_snapshot('course', $course);
					$event->add_record_snapshot('qaa', $moduleinstance);
					$event->trigger();
				}
			}
			
			$transaction->allow_commit();
		} else {
			throw new Exception('Không tìm thấy câu hỏi cần phê duyệt');
		}
		

		return ['id' => $id];
	}

	/**
	 * Describes the get_qaas_by_courses return value.
	 *
	 * @return external_single_structure
	 */
	public static function decidequestion_returns() {

		return new external_single_structure(
			array(
				'id' => new external_value(PARAM_INT, 'qaa id'),
			)
		);
	}

	public static function updatequestion_parameters() {
		return new external_function_parameters(
			array(
				'cmid' => new external_value(PARAM_INT, 'Course module id'),
				'courseid' => new external_value(PARAM_INT, 'Course module id'),
				'questionid' => new external_value(PARAM_INT, 'Course module id'),
				'message' => new external_value(PARAM_RAW, 'The newanswer submitted', VALUE_DEFAULT, ''),
			)
		);
	}

	/**
	 * Returns a list of qaas in a provided list of courses,
	 * if no list is provided all qaas that the user can view will be returned.
	 *
	 * @param array $courseids the course ids
	 * @return array the qaa details
	 */
	public static function updatequestion($cmid, $courseid, $questionid, $message) {
		global $DB, $CFG;

		require_once $CFG->dirroot . '/lib/grade/grade_item.php';
		require_once $CFG->dirroot . '/local/thlib/lib.php';
		require_once $CFG->dirroot . '/mod/qaa/lib.php';

		$cm = get_coursemodule_from_id('qaa', $cmid);
		$course = $DB->get_record('course', array('id' => $courseid));
		$modulecontext = context_module::instance($cm->id);
		$moduleinstance = $DB->get_record('qaa', array('id' => $cm->instance), '*', MUST_EXIST);

		if ($qaap = $DB->get_record('qaapairs', array('id' => $questionid))) {
			$newmessage = trim($message);
			$newmessage_length = strip_tags(trim($newmessage)); // Bỏ các thẻ HTML
			$config = get_config('mod_qaa');
			$min_character = $config->min_question_character;

			if (strlen($newmessage_length) < $min_character) {
				throw new Exception(get_string('err_questionform', 'mod_qaa', $min_character));
			}

			if ($newmessage) {
				$qaap->message = $newmessage;

				if($qaap->parentid) {
					$qaap->timemodifiedanswer = time();
				} else {
					$qaap->timemodifiedquestion = time();
				}

				if ($DB->update_record('qaapairs', $qaap)) {

					$event = \mod_qaa\event\qaapairs_question_edited::create(array(
						'objectid' => $questionid,
						'context' => $modulecontext,
					));
					$event->add_record_snapshot('course', $course);
					$event->add_record_snapshot('qaa', $moduleinstance);
					$event->trigger();
				};
			}
		}

		return ['id' => $questionid];
	}

	/**
	 * Describes the get_qaas_by_courses return value.
	 *
	 * @return external_single_structure
	 */
	public static function updatequestion_returns() {

		return new external_single_structure(
			array(
				'id' => new external_value(PARAM_INT, 'qaa id'),
			)
		);
	}

	public static function deletequestion_parameters() {
		return new external_function_parameters(
			array(
				'cmid' => new external_value(PARAM_INT, 'Course module id'),
				'courseid' => new external_value(PARAM_INT, 'Course module id'),
				'questionid' => new external_value(PARAM_INT, 'Course module id')
			)
		);
	}

	/**
	 * Returns a list of qaas in a provided list of courses,
	 * if no list is provided all qaas that the user can view will be returned.
	 *
	 * @param array $courseids the course ids
	 * @return array the qaa details
	 */
	public static function deletequestion($cmid, $courseid, $questionid) {
		global $DB, $CFG;
		require_once($CFG->libdir . '/gradelib.php');
		require_once $CFG->dirroot . '/lib/grade/grade_item.php';
		require_once $CFG->dirroot . '/local/thlib/lib.php';
		require_once $CFG->dirroot . '/mod/qaa/lib.php';

		$cm = get_coursemodule_from_id('qaa', $cmid);
		$instance= $DB->get_record('qaa',    ['id' => $cm->instance], '*', MUST_EXIST);
		if ($qaap = $DB->get_record('qaapairs', array('id' => $questionid))) {

			// Permissions
			$context = context_module::instance($cm->id);
			$canmanage   = has_capability('mod/qaa:approval', $context); // teacher/manager
			$candelete   = has_capability('mod/qaa:deletequest', $context);

			if(!empty($msg->studentid)) {
				$authorid = $msg->studentid;
				$is_update_grade = true;
			} else {
				$authorid = $msg->teacherid;
				$is_update_grade = false;
			}
			$isowner     = ((int)$authorid === (int)$USER->id);
			$haschildren = $DB->record_exists('qaapairs', ['parentid' => $questionid]);

			// Author can only delete leaf non-root; managers can delete anything.
			if (!$canmanage) {
				if ($haschildren || !$isowner || $qaap->status != 0 || !$candelete) {
					throw new Exception(get_string('nopermissions', 'error', 'delete message'));
				}
			}

			// Delete helpers (cascade)
			function qaa_delete_message_subtree(int $mid, context_module $context): void {
				global $DB;
				// Xoá con trước
				$children = $DB->get_records('qaapairs', ['parentid' => $mid], 'id ASC', 'id');
				foreach ($children as $c) {
					qaa_delete_message_subtree((int)$c->id, $context);
				}
				// Xoá file đính kèm ở filearea 'message'
				$fs = get_file_storage();
				$fs->delete_area_files($context->id, 'mod_qaa', 'message', $mid);
				// Xoá bản ghi chính
				$DB->delete_records('qaapairs', ['id' => $mid]);
			}

			// Managers: xoá cả cây; Author: đã kiểm tra chỉ xoá lá (không cần cascade, vẫn gọi cho thống nhất)
			qaa_delete_message_subtree($mid, $context);

			// Cập nhật điểm (đếm số câu hỏi theo luồng của HV).
			$gradeitem = grade_item::fetch([
				'itemtype'    => 'mod',
				'itemmodule'  => $cm->modname,
				'iteminstance'=> $cm->instance,
				'itemnumber'  => 0,
				'courseid'    => $courseid
			]);
			if ($gradeitem && $is_update_grade) {
				$count = $DB->count_records('qaapairs', [
					'qaaid' => $cm->instance, 'studentid' => $authorid, 'status' => 1
				]);

				$rawgrade = min($count, (float)$gradeitem->grademax);
				$grade = (object)[
					'userid'        => $authorid,
					'rawgrade'      => $rawgrade,
					'dategraded'    => time(),
					'datesubmitted' => time()
				];
				// Hàm từ lib.php của plugin.
				if (function_exists('qaa_grade_item_update')) {
					qaa_grade_item_update($instance, [$authorid => $grade]);
				}
			}
		}

		return ['status' => true];
	}

	/**
	 * Describes the get_qaas_by_courses return value.
	 *
	 * @return external_single_structure
	 */
	public static function deletequestion_returns() {

		return new external_single_structure(
			array(
				'status' => new external_value(PARAM_BOOL, 'status: true if success'),
			)
		);
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function view_qaa_parameters() {
		return new external_function_parameters(
			array(
				'qaaid' => new external_value(PARAM_INT, 'qaa instance id'),
			)
		);
	}

	/**
	 * Trigger the course module viewed event and update the module completion status.
	 *
	 * @param int $qaaid the qaa instance id
	 * @return array of warnings and status result
	 * @throws moodle_exception
	 */
	public static function view_qaa($qaaid) {
		global $DB;

		$params = self::validate_parameters(self::view_qaa_parameters(),
			array(
				'qaaid' => $qaaid,
			)
		);
		$warnings = array();

		// Request and permission validation.
		$qaa = $DB->get_record('qaa', array('id' => $params['qaaid']), '*', MUST_EXIST);
		list($course, $cm) = get_course_and_cm_from_instance($qaa, 'qaa');

		$context = context_module::instance($cm->id);
		self::validate_context($context);
		require_capability('mod/qaa:view', $context);

		$event = \mod_qaa\event\course_module_viewed::create(array(
			'objectid' => $qaa->id,
			'context' => $context,
		));
		$event->add_record_snapshot('course', $course);
		$event->add_record_snapshot('qaa', $qaa);
		$event->trigger();

		$completion = new completion_info($course);
		$completion->set_module_viewed($cm);

		$result = array();
		$result['status'] = true;
		$result['warnings'] = $warnings;
		return $result;
	}

	/**
	 * Returns description of method result value
	 *
	 * @return external_description
	 */
	public static function view_qaa_returns() {
		return new external_single_structure(
			array(
				'status' => new external_value(PARAM_BOOL, 'status: true if success'),
				'warnings' => new external_warnings(),
			)
		);
	}

	/**
	 * Check if the user can issue qaas.
	 *
	 * @param  int $qaaid qaa instance id
	 * @return array array containing context related data
	 */
	private static function check_can_issue($qaaid) {
		global $DB;

		$qaa = $DB->get_record('qaa', array('id' => $qaaid), '*', MUST_EXIST);
		list($course, $cm) = get_course_and_cm_from_instance($qaa, 'qaa');

		$context = context_module::instance($cm->id);
		self::validate_context($context);
		require_capability('mod/qaa:view', $context);

		// Check if the user can view the qaa.
		if ($qaa->requiredtime && !has_capability('mod/qaa:manage', $context)) {
			if (qaa_get_course_time($course->id) < ($qaa->requiredtime * 60)) {
				$a = new stdClass();
				$a->requiredtime = $qaa->requiredtime;
				throw new moodle_exception('requiredtimenotmet', 'qaa', '', $a);
			}
		}
		return array($qaa, $course, $cm, $context);
	}

	/**
	 * Returns a issued qaad structure
	 *
	 * @return external_single_structure External single structure
	 */
	private static function issued_structure() {
		return new external_single_structure(
			array(
				'id' => new external_value(PARAM_INT, 'Issue id'),
				'userid' => new external_value(PARAM_INT, 'User id'),
				'qaaid' => new external_value(PARAM_INT, 'qaa id'),
				'code' => new external_value(PARAM_RAW, 'qaa code'),
				'timecreated' => new external_value(PARAM_INT, 'Time created'),
				'filename' => new external_value(PARAM_FILE, 'Time created'),
				'fileurl' => new external_value(PARAM_URL, 'Time created'),
				'mimetype' => new external_value(PARAM_RAW, 'mime type'),
				'grade' => new external_value(PARAM_NOTAGS, 'qaa grade', VALUE_OPTIONAL),
			)
		);
	}

	/**
	 * Add extra required information to the issued qaa
	 *
	 * @param stdClass $issue       issue object
	 * @param stdClass $qaa qaa object
	 * @param stdClass $course      course object
	 * @param stdClass $cm          course module object
	 * @param stdClass $context     context object
	 */
	private static function add_extra_issue_data($issue, $qaa, $course, $cm, $context) {
		global $CFG;

		// Grade data.
		if ($qaa->printgrade) {
			$issue->grade = qaa_get_grade($qaa, $course);
		}

		// File data.
		$issue->mimetype = 'application/pdf';
		$issue->filename = qaa_get_qaa_filename($qaa, $cm, $course) . '.pdf';
		// We need to use a special file area to be able to download qaas (in most cases are not stored in the site).
		$issue->fileurl = moodle_url::make_webservice_pluginfile_url(
			$context->id, 'mod_qaa', 'onthefly', $issue->id, '/', $issue->filename)->out(false);
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function issue_qaa_parameters() {
		return new external_function_parameters(
			array(
				'qaaid' => new external_value(PARAM_INT, 'qaa instance id'),
			)
		);
	}

	/**
	 * Create new qaa record, or return existing record.
	 *
	 * @param int $qaaid the qaa instance id
	 * @return array of warnings and status result
	 * @throws moodle_exception
	 */
	public static function issue_qaa($qaaid) {
		global $USER;

		$params = self::validate_parameters(self::issue_qaa_parameters(),
			array(
				'qaaid' => $qaaid,
			)
		);
		$warnings = array();

		// Request and permission validation.
		list($qaa, $course, $cm, $context) = self::check_can_issue($params['qaaid']);

		$issue = qaa_get_issue($course, $USER, $qaa, $cm);
		self::add_extra_issue_data($issue, $qaa, $course, $cm, $context);

		$result = array();
		$result['issue'] = $issue;
		$result['warnings'] = $warnings;
		return $result;
	}

	/**
	 * Returns description of method result value
	 *
	 * @return external_description
	 */
	public static function issue_qaa_returns() {
		return new external_single_structure(
			array(
				'issue' => self::issued_structure(),
				'warnings' => new external_warnings(),
			)
		);
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function get_issued_qaas_parameters() {
		return new external_function_parameters(
			array(
				'qaaid' => new external_value(PARAM_INT, 'qaa instance id'),
			)
		);
	}

	/**
	 * Get the list of issued qaas for the current user.
	 *
	 * @param int $qaaid the qaa instance id
	 * @return array of warnings and status result
	 * @throws moodle_exception
	 */
	public static function get_issued_qaas($qaaid) {

		$params = self::validate_parameters(self::get_issued_qaas_parameters(),
			array(
				'qaaid' => $qaaid,
			)
		);
		$warnings = array();

		// Request and permission validation.
		list($qaa, $course, $cm, $context) = self::check_can_issue($params['qaaid']);

		$issues = qaa_get_attempts($qaa->id);

		if ($issues !== false) {
			foreach ($issues as $issue) {
				self::add_extra_issue_data($issue, $qaa, $course, $cm, $context);

			}
		} else {
			$issues = array();
		}

		$result = array();
		$result['issues'] = $issues;
		$result['warnings'] = $warnings;
		return $result;
	}

	/**
	 * Returns description of method result value
	 *
	 * @return external_description
	 */
	public static function get_issued_qaas_returns() {
		return new external_single_structure(
			array(
				'issues' => new external_multiple_structure(self::issued_structure()),
				'warnings' => new external_warnings(),
			)
		);
	}

}