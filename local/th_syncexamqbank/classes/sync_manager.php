<?php
namespace local_th_syncexamqbank;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/bank.php');
require_once($CFG->dirroot . '/lib/accesslib.php');

use question_bank;
use question_edit_contexts;
use context;

class sync_manager {

    /**
     * Ghi log đồng bộ 1 lần vào bảng local_th_syncexamqbank_log.
     *
     * @param int   $userid
     * @param int   $sourcecourseid  0 nếu không có / không xác định.
     * @param int   $targetcourseid
     * @param int   $syncedcount
     * @param int   $coursestartdate
     * @param array $logs            Mảng message, sẽ implode("\n", $logs).
     */
    protected static function write_log(int $userid, int $sourcecourseid, int $targetcourseid,
                                        int $syncedcount, int $coursestartdate, array $logs): void {
        global $DB;

        $record = (object)[
            'userid'         => $userid,
            'sourcecourseid' => $sourcecourseid,
            'targetcourseid' => $targetcourseid,
            'syncedcount'    => $syncedcount,
            'timecreated'    => time(),
            'coursestartdate'=> $coursestartdate,
            'logdetail'      => implode("\n", $logs),
        ];

        $DB->insert_record('local_th_syncexamqbank_log', $record);
    }

    /**
     * Đồng bộ theo ngày startdate.
     */
    public static function sync_by_startdate($startdate) {
        global $DB,$USER;

        $logs = [];

        $thi_idnumber  = get_config('local_th_syncexamqbank', 'category_thi_idnumber') ?? 'THI';
        $nhkt_idnumber = get_config('local_th_syncexamqbank', 'category_nhkt_idnumber') ?? 'NHKT';

        $catthi = $DB->get_record('course_categories', ['idnumber' => $thi_idnumber]);
        $catkho = $DB->get_record('course_categories', ['idnumber' => $nhkt_idnumber]);
        if (!$catthi || !$catkho) {
            $logs[] = '❌ Không tìm thấy danh mục THI hoặc NHKT.';
        
            // GHI LOG Ở ĐÂY
            self::write_log(
                isloggedin() ? (int)$USER->id : 0,
                0,
                0,
                0,
                (int)$startdate,
                $logs
            );
        
            return $logs;
        }
        

        //$courses = $DB->get_records('course', ['category' => $catthi->id, 'startdate' => $startdate]);
        // Lấy tất cả course thuộc THI và các danh mục con, có startdate đúng
        $sql = "SELECT c.*
        FROM {course} c
        JOIN {course_categories} cc ON cc.id = c.category
        WHERE (cc.id = :catid OR cc.path LIKE :pathlike)
        AND c.startdate = :startdate";
        $params = [
        'catid'     => (int)$catthi->id,
        'pathlike'  => $catthi->path . '/%',
        'startdate' => (int)$startdate,
        ];

        $courses = $DB->get_records_sql($sql, $params);
        if (empty($courses)) {
            $logs[] = '⚠️ Không tìm thấy khóa học nào trong danh mục THI...';
        
            self::write_log(
                isloggedin() ? (int)$USER->id : 0,
                0,
                0,
                0,
                (int)$startdate,
                $logs
            );
        
            return $logs;
        }
        

        foreach ($courses as $course) {
            // Mỗi course sẽ tự ghi log bằng sync_course().
            $logs = array_merge($logs, self::sync_course($course, $catkho));
        }

        return $logs;
    }

    /**
     * Đồng bộ một khóa THI cụ thể.
     * Luôn ghi log 1 dòng cho mỗi lần gọi, bất kể thành công hay thất bại.
     */
    public static function sync_course($examcourse, $catkho = null) {
        global $DB, $USER;

        $nhkt_idnumber  = get_config('local_th_syncexamqbank', 'category_nhkt_idnumber') ?? 'NHKT';
        $logs           = [];
        $copied         = 0;    // Mặc định 0, kể cả khi có lỗi.
        $sourcecourseid = 0;    // Chưa có course nguồn.
        $hasfatal       = false;
        $transaction    = null;

        try {
            // 1. Lấy category NHKT nếu chưa có.
            if (!$catkho) {
                $catkho = $DB->get_record('course_categories', ['idnumber' => $nhkt_idnumber]);
                if (!$catkho) {
                    $logs[]   = '❌ Không tìm thấy danh mục NHKT.';
                    $hasfatal = true;
                }
            }

            // 2. Lấy mã môn học của khóa THI.
            $coursecode = null;
            if (!$hasfatal) {
                $sql = "SELECT cd.value
                          FROM {customfield_data} cd
                          JOIN {customfield_field} cf ON cf.id = cd.fieldid
                         WHERE cd.instanceid = :courseid AND cf.shortname = 'th_course_code'";
                $coursecode = $DB->get_field_sql($sql, ['courseid' => $examcourse->id]);

                if (empty($coursecode)) {
                    $logs[]   = "⚠️ Khóa học '{$examcourse->fullname}' không có mã môn (th_course_code), bỏ qua.";
                    $hasfatal = true;
                }
            }

            // 3. Tìm khóa NHKT tương ứng.
            $source = null;
            if (!$hasfatal) {
                $sql = "SELECT c.*
                          FROM {course} c
                          JOIN {customfield_data} cd ON cd.instanceid = c.id
                          JOIN {customfield_field} cf ON cf.id = cd.fieldid
                         WHERE c.category = :catid
                           AND cf.shortname = 'th_course_code'
                           AND cd.value = :coursecode";
                $source = $DB->get_record_sql($sql, [
                    'catid'      => $catkho->id,
                    'coursecode' => $coursecode
                ]);

                if (!$source) {
                    $logs[]   = "⚠️ Không tìm thấy khóa NHKT có mã môn '{$coursecode}' cho '{$examcourse->fullname}'.";
                    $hasfatal = true;
                } else {
                    $sourcecourseid = (int)$source->id;
                }
            }

            // 4. Nếu không có lỗi chặn, tiến hành copy bên trong transaction.
            if (!$hasfatal) {
                $transaction = $DB->start_delegated_transaction();
                $destctx   = \context_course::instance($examcourse->id);
                $sourcectx = \context_course::instance($source->id);

                $destcats   = $DB->get_records('question_categories', ['contextid' => $destctx->id]);
                $sourcecats = $DB->get_records('question_categories', ['contextid' => $sourcectx->id]);

                $destmap = [];
                foreach ($destcats as $dc) {
                    if (!empty($dc->idnumber)) {
                        $destmap[trim($dc->idnumber)] = $dc;
                    }
                }

                $sourcemap = [];
                foreach ($sourcecats as $sc) {
                    if (!empty($sc->idnumber)) {
                        $sourcemap[trim($sc->idnumber)] = $sc;
                    }
                }
                // Lấy tất cả idnumber trùng giữa source và dest
                $valididnumbers = array_intersect(
                    array_keys($sourcemap),
                    array_keys($destmap)
                );

                if (empty($valididnumbers)) {
                    $logs[] = "⛔ Không có danh mục trùng idnumber giữa nguồn và đích.";
                    $copied = 0;
                } 
                else if (self::course_has_questions($examcourse->id)) {
                    $logs[] = "⛔ Khóa '{$examcourse->fullname}' đã có câu hỏi, bỏ qua.";
                    $copied = 0;
                } 
                else {
                    // Lấy idnumber của 2 bên
        $sourceids = [];
        foreach ($sourcecats as $sc) {
            if (!empty($sc->idnumber)) {
                $sourceids[] = trim($sc->idnumber);
            }
        }

        $destids = [];
        foreach ($destcats as $dc) {
            if (!empty($dc->idnumber)) {
                $destids[] = trim($dc->idnumber);
            }
        }

        sort($sourceids);
        sort($destids);

        // Nếu số lượng hoặc idnumber không khớp thì không sync
        if (count($sourceids) !== count($destids) || $sourceids !== $destids) {
            $logs[] = '❌ Không đồng bộ: Danh mục giữa 2 khóa không giống nhau';
            $copied = 0;
        } else {
            $copied = self::copy_questions_between_courses($source, $examcourse, $valididnumbers, $logs);
        }
                }
                $transaction->allow_commit();
            }

        } catch (\Throwable $e) {
            if ($transaction) {
                $transaction->rollback($e);
            }
            $logs[] = "❌ Lỗi khi đồng bộ: " . $e->getMessage();
        }

        if ($copied > 0 && !$hasfatal) {
            $logs = [
                "✅ Đồng bộ {$copied} câu hỏi từ khóa '{$source->fullname}' -> khóa '{$examcourse->fullname}'."
            ];
        } else {
            $logs = [
                "❌ Khóa '{$examcourse->fullname}' đồng bộ không thành công."
            ];
        }

        // 5. Luôn luôn ghi log 1 lần ở cuối, bất kể thành công hay thất bại.
        $userid = isloggedin() ? (int)$USER->id : 0;
        self::write_log(
            $userid,
            $sourcecourseid,
            (int)$examcourse->id,
            (int)$copied,
            (int)$examcourse->startdate,
            $logs
        );

        return $logs;
    }

    /**
     * Kiểm tra xem khóa học có câu hỏi nào chưa.
     */
    public static function course_has_questions($courseid): bool {
        global $DB;

        $context = \context_course::instance($courseid, IGNORE_MISSING);
        if (!$context) {
            return false;
        }

        $categories = $DB->get_records('question_categories', ['contextid' => $context->id]);
        if (empty($categories)) {
            return false;
        }

        list($catsql, $params) = $DB->get_in_or_equal(array_keys($categories), SQL_PARAMS_NAMED);

        $columns   = $DB->get_columns('question');
        $hashidden = array_key_exists('hidden', $columns);

        if ($DB->get_manager()->table_exists('question_bank_entries')) {
            // Moodle 4.x+
            $sql = "SELECT COUNT(1)
                      FROM {question_bank_entries} qbe
                      JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
                      JOIN {question} q ON q.id = qv.questionid
                     WHERE qbe.questioncategoryid {$catsql}
                       AND qv.status = 'ready'
                       AND q.qtype <> 'random'";
            if ($hashidden) {
                $sql .= " AND (q.hidden = 0 OR q.hidden IS NULL)";
            }
        } else {
            // Moodle 3.x
            $sql = "SELECT COUNT(1)
                      FROM {question}
                     WHERE category {$catsql}
                       AND qtype <> 'random'";
            if ($hashidden) {
                $sql .= " AND (hidden = 0 OR hidden IS NULL)";
            }
        }

        return ((int)$DB->count_records_sql($sql, $params)) > 0;
    }

    /**
     * Copy toàn bộ câu hỏi giữa hai khóa học (copy danh mục theo idnumber).
     * Tối ưu: cache stamp của category đích 1 lần, tránh query exists theo từng câu.
     * Moodle 4: chỉ lấy latest ready.
     */
    public static function copy_questions_between_courses($sourcecourse, $destcourse, array $valididnumbers, array &$logs){
        global $DB;

        $copied    = 0;
        $success   = [];
        $failed    = [];

        $sourcectx = \context_course::instance($sourcecourse->id, MUST_EXIST);
        $destctx   = \context_course::instance($destcourse->id, MUST_EXIST);

        $destcats = $DB->get_records('question_categories', ['contextid' => $destctx->id]);
        if (empty($destcats)) {
            return 0;
        }

        $destmap = [];
        foreach ($destcats as $dc) {
            if (!empty($dc->idnumber)) {
                $destmap[trim($dc->idnumber)] = $dc;
            }
        }

        $sourcecats = $DB->get_records('question_categories', ['contextid' => $sourcectx->id]);
        if (empty($sourcecats)) {
            return 0;
        }

        $sourcemap = [];
        foreach ($sourcecats as $sc) {
            if (!empty($sc->idnumber)) {
                $sourcemap[trim($sc->idnumber)] = $sc;
            }
        }

        $is_moodle4 = $DB->get_manager()->table_exists('question_bank_entries');
        $columns    = $DB->get_columns('question');
        $hashidden  = array_key_exists('hidden', $columns);

        $deststampscache = [];
        $questionidmap = [];
        $clusterpairs  = [];
        foreach ($valididnumbers as $idnumber) {
            $categorycopied = 0;
            if (empty($sourcemap[$idnumber]) || empty($destmap[$idnumber])) {
                continue;
            }

            $src       = $sourcemap[$idnumber];
            $destcatid = (int)$destmap[$idnumber]->id;

            // Cache stamps của category đích (1 lần/category).
            if (!isset($deststampscache[$destcatid])) {
                $deststampscache[$destcatid] = [];

                if ($is_moodle4) {
                    $sql = "SELECT DISTINCT q.stamp
                              FROM {question_bank_entries} qbe
                              JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
                              JOIN {question} q ON q.id = qv.questionid
                              JOIN (
                                    SELECT questionbankentryid, MAX(version) AS maxversion
                                      FROM {question_versions}
                                     WHERE status = 'ready'
                                  GROUP BY questionbankentryid
                              ) latest
                                ON latest.questionbankentryid = qv.questionbankentryid
                               AND latest.maxversion = qv.version
                             WHERE qbe.questioncategoryid = :catid
                               AND qv.status = 'ready'";
                    if ($hashidden) {
                        $sql .= " AND (q.hidden = 0 OR q.hidden IS NULL)";
                    }

                    $rows = $DB->get_records_sql($sql, ['catid' => $destcatid]);
                    foreach ($rows as $r) {
                        if (!empty($r->stamp)) {
                            $deststampscache[$destcatid][$r->stamp] = true;
                        }
                    }
                } else {
                    $rows = $DB->get_records('question', ['category' => $destcatid], '', 'id,stamp');
                    foreach ($rows as $r) {
                        if (!empty($r->stamp)) {
                            $deststampscache[$destcatid][$r->stamp] = true;
                        }
                    }
                }
            }

            // Lấy câu hỏi nguồn.
            if ($is_moodle4) {
                $sql = "SELECT q.id, q.qtype, q.stamp, q.name
                          FROM {question_bank_entries} qbe
                          JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
                          JOIN {question} q ON q.id = qv.questionid
                          JOIN (
                                SELECT questionbankentryid, MAX(version) AS maxversion
                                  FROM {question_versions}
                                 WHERE status = 'ready'
                              GROUP BY questionbankentryid
                          ) latest
                            ON latest.questionbankentryid = qv.questionbankentryid
                           AND latest.maxversion = qv.version
                         WHERE qbe.questioncategoryid = :catid
                           AND qv.status = 'ready'";
                if ($hashidden) {
                    $sql .= " AND (q.hidden = 0 OR q.hidden IS NULL)";
                }

                $questions = $DB->get_records_sql($sql, ['catid' => (int)$src->id]);
            } else {
                if ($hashidden) {
                    $questions = $DB->get_records('question', ['category' => (int)$src->id, 'hidden' => 0]);
                } else {
                    $questions = $DB->get_records('question', ['category' => (int)$src->id]);
                }
            }

            foreach ($questions as $q) {

                if (empty($q->stamp)) {
                    continue;
                }
            
                if (!empty($deststampscache[$destcatid][$q->stamp])) {
                    continue;
                }
            
                $newquestion = self::copy_question_to_category((int)$q->id, $destcatid, $destctx);

            if ($newquestion && !empty($newquestion->id)) {
                $copied++;
                $categorycopied++;

                $deststampscache[$destcatid][$q->stamp] = true;

                // Lưu map ID câu hỏi nguồn -> đích
                $questionidmap[(int)$q->id] = (int)$newquestion->id;

                // Nếu là cluster thì ghi nhớ cặp để xử lý JSON sau
                if ($q->qtype === 'thvstepcluster') {
                    $clusterpairs[] = [
                        'source' => (int)$q->id,
                        'dest'   => (int)$newquestion->id,
                    ];
                }
            }
            }
            if ($categorycopied > 0) {
                $success[] = $idnumber;
            } else {
                $failed[] = $idnumber;
            }  
        }

        // Sau khi copy xong hết, sửa lại JSON cho tất cả các cluster
        foreach ($clusterpairs as $pair) {
            self::fix_cluster_children($pair['source'], $pair['dest'], $questionidmap);
        }

        if (!empty($success)) {
            $logs[] = '✅ Đồng bộ thành công danh mục: "' . implode('", "', $success) . '"';
        }
        
        if (!empty($failed)) {
            $logs[] = '❌ Đồng bộ không thành công danh mục: "' . implode('", "', $failed) . '"';
        }
        
        return $copied;
    }

    /**
     * (Giữ nguyên) Copy_full_question - KHÔNG dùng trong Moodle 4 (nhưng bạn đang không gọi).
     */
    public static function copy_full_question($question, $targetcatid) {
        global $DB;

        $newq = clone $question;
        unset($newq->id);
        $newq->category = $targetcatid;
        $newq->contextid = $DB->get_field('question_categories', 'contextid', ['id' => $targetcatid]);
        $newq->version = 1;
        $newq->timecreated = time();
        $newq->timemodified = time();

        $newid = $DB->insert_record('question', $newq);

        $qtype = $question->qtype;
        $extratable = "question_{$qtype}";
        if ($DB->get_manager()->table_exists($extratable)) {
            $columns = $DB->get_columns($extratable);
            $linkfield = array_key_exists('questionid', $columns) ? 'questionid' : 'question';
            if ($srcq = $DB->get_record($extratable, [$linkfield => $question->id])) {
                unset($srcq->id);
                $srcq->{$linkfield} = $newid;
                $DB->insert_record($extratable, $srcq);
            }
        }

        $answers = $DB->get_records('question_answers', ['question' => $question->id]);
        foreach ($answers as $a) {
            $newa = clone $a;
            unset($newa->id);
            $newa->question = $newid;
            $DB->insert_record('question_answers', $newa);
        }

        $hints = $DB->get_records('question_hints', ['questionid' => $question->id]);
        foreach ($hints as $h) {
            $newh = clone $h;
            unset($newh->id);
            $newh->questionid = $newid;
            $DB->insert_record('question_hints', $newh);
        }

        return $newid;
    }

    /**
     * Copy 1 câu hỏi sang category đích bằng qtype->save_question.
     */
    public static function copy_question_to_category($questionid, $targetcategoryid, $context) {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/question/editlib.php');
        require_once($CFG->libdir . '/questionlib.php');

        $courseid = (int)$context->instanceid;
        $course   = get_course($courseid);

        // Lấy thông tin câu hỏi gốc.
        $question = $DB->get_record('question', ['id' => $questionid], '*', MUST_EXIST);
        get_question_options($question, true, [$course]);

        // Category đích.
        $category        = $DB->get_record('question_categories', ['id' => $targetcategoryid], '*', MUST_EXIST);
        $categorycontext = context::instance_by_id($category->contextid);
        $contexts        = new question_edit_contexts($categorycontext);

        // Chuẩn bị dữ liệu form.
        $toform                   = fullclone($question);
        $toform->category         = "{$category->id},{$category->contextid}";
        $toform->scrollpos        = 0;
        $toform->appendqnumstring = null;
        $toform->returnurl        = null;
        $toform->makecopy         = 1;
        $toform->inpopup          = 0;
        $toform->courseid         = $courseid;

        // Quyền thao tác (giữ logic như cũ).
        $addpermission = has_capability('moodle/question:add', $categorycontext);
        $question->formoptions = (object)[
            'canedit'        => question_has_capability_on($question, 'edit'),
            'canmove'        => $addpermission && question_has_capability_on($question, 'move'),
            'cansaveasnew'   => $addpermission && (question_has_capability_on($question, 'view') || question_has_capability_on($question, 'edit')),
            'repeatelements' => true
        ];
        $formeditable = $question->formoptions->canedit ||
                        $question->formoptions->cansaveasnew ||
                        $question->formoptions->canmove;

        // Tạo mform.
        $qtypeobj = question_bank::get_qtype($question->qtype);
        $mform    = $qtypeobj->create_editing_form('question.php', $question, $category, $contexts, $formeditable);
        $mform->set_data($toform);

        // Lấy data chuẩn.
        $formdata = $mform->get_data();
        if (!$formdata) {
            $formdata = $toform; // fallback
        }

        // Clone đúng cách để tạo bản mới.
        $clonedquestion = clone $question;
        unset($clonedquestion->id);
        $clonedquestion->contextid = $category->contextid;
        $clonedquestion->category  = $category->id;

        $newquestion = $qtypeobj->save_question($clonedquestion, $formdata);

        question_bank::notify_question_edited($newquestion->id);

        return $newquestion;
    }

    /**
     * Xử lý theo bước (gọi qua AJAX).
     */
    public static function sync_step($startdate, $step) {
        global $DB;

        $thi_idnumber = get_config('local_th_syncexamqbank', 'category_thi_idnumber') ?? 'THI';
        $catthi = $DB->get_record('course_categories', ['idnumber' => $thi_idnumber], 'id, path', IGNORE_MISSING);
        if (!$catthi) {
            return ['done' => true, 'progress' => 100, 'message' => 'Không tìm thấy danh mục "THI".'];
        }

        // Total để tính progress.
        $totalsql = "SELECT COUNT(1)
               FROM {course} c
               JOIN {course_categories} cc ON cc.id = c.category
              WHERE (cc.id = :catid OR cc.path LIKE :pathlike)
                AND c.startdate = :startdate";
        $total = (int)$DB->count_records_sql($totalsql, [
            'catid'     => (int)$catthi->id,
            'pathlike'  => $catthi->path . '/%',
            'startdate' => (int)$startdate,
        ]);

        if ($total <= 0) {
            return ['done' => true, 'progress' => 100, 'message' => '⚠️ Không có khóa học để đồng bộ.'];
        }

        // Lấy đúng 1 course theo step.
        $sql = "SELECT c.id, c.fullname, c.startdate, c.category
          FROM {course} c
          JOIN {course_categories} cc ON cc.id = c.category
         WHERE (cc.id = :catid OR cc.path LIKE :pathlike)
           AND c.startdate = :startdate
        ORDER BY c.id ASC";

        $courses = $DB->get_records_sql(
            $sql,
            [
                'catid'     => (int)$catthi->id,
                'pathlike'  => $catthi->path . '/%',
                'startdate' => (int)$startdate,
            ],
            (int)$step,
            1
        );

        if (empty($courses)) {
            return ['done' => true, 'progress' => 100, 'message' => '✅ Hoàn tất đồng bộ.'];
        }

        $destcourse = reset($courses);
        self::sync_course($destcourse);

        $progress = (int)round((($step + 1) / $total) * 100);

        return [
            'done'     => false,
            'progress' => $progress,
            'message'  => "Đang xử lý: <strong>{$destcourse->fullname}</strong> ({$progress}%)",
            'step'     => $step + 1
        ];
    }

    protected static function fix_cluster_children($sourceclusterid, $destclusterid, array $questionidmap) {
        global $DB;
    
        // Lấy config cluster nguồn
        $srcoptions = $DB->get_record('qtype_thvstepcluster',
            ['question' => $sourceclusterid], '*', IGNORE_MISSING);
        if (!$srcoptions || empty($srcoptions->question_entries_id)) {
            return;
        }
    
        $srcitems = json_decode($srcoptions->question_entries_id, true);
        if (!is_array($srcitems) || empty($srcitems['questionids']) || !is_array($srcitems['questionids'])) {
            return;
        }
    
        //  Chuẩn bị cấu trúc mới cho cluster đích
        $new_entry_items = [];   // list qbe.id mới
        $new_questionids = [];   // map qbe.id mới => [questionid, sequence]
    
        foreach ($srcitems['questionids'] as $oldentryid => $info) {
            $srcchildid = !empty($info['questionid']) ? (int)$info['questionid'] : 0;
            if (!$srcchildid) {
                continue;
            }
    
            // Không có trong map thì bỏ qua
            if (empty($questionidmap[$srcchildid])) {
                continue;
            }
    
            $destchildid = (int)$questionidmap[$srcchildid];
            $sequence    = isset($info['sequence']) ? (int)$info['sequence'] : 0;
    
            //  Lấy qbe.id tương ứng với câu con bên đích
            $destentryid = $DB->get_field_sql("
                SELECT qbe.id
                  FROM {question_bank_entries} qbe
                  JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
                 WHERE qv.questionid = :qid
                 ORDER BY qv.version DESC
                 LIMIT 1
            ", ['qid' => $destchildid]);
    
            if (!$destentryid) {
                continue;
            }
    
            $destentryid = (int)$destentryid;
    
            // 4. Build lại cấu trúc
            $new_entry_items[] = $destentryid;
            $new_questionids[$destentryid] = [
                'questionid' => $destchildid,
                'sequence'   => $sequence,
            ];
        }
    
        // Không remap được gì thì thôi, tránh ghi hỏng config
        if (empty($new_entry_items) || empty($new_questionids)) {
            return;
        }
    
        $destitems = [
            'question_entry_items' => $new_entry_items,
            'questionids'          => $new_questionids,
        ];
    
        $destjson = json_encode($destitems);
    
        //  Ghi JSON vào bản ghi cluster đích
        $destoptions = $DB->get_record('qtype_thvstepcluster',
            ['question' => $destclusterid], '*', IGNORE_MISSING);
    
        if ($destoptions) {
            $destoptions->question_entries_id = $destjson;
            $DB->update_record('qtype_thvstepcluster', $destoptions);
        } else {
            $destoptions = (object)[
                'question'            => $destclusterid,
                'question_entries_id' => $destjson,
            ];
            $DB->insert_record('qtype_thvstepcluster', $destoptions);
        }
    }
}
