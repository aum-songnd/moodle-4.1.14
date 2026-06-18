<?php

/**
 * Thu phẳng toàn bộ descendants thành 1 list (bỏ qua node đang xét).
 * @param array $children
 * @param bool $skiprejected Bỏ qua các bản ghi status = 2 (rejected) nếu true.
 * @return array
 */
function qaa_flatten_children_list(array $children): array {
    $out = [];
    foreach ($children as $ch) {
        if (!is_object($ch)) {
            continue;
        }

        if($ch->teacherid && $ch->status == 2) {
            continue;
        } else {
            $out[] = $ch;
        }

        if (!empty($ch->children) && is_array($ch->children)) {
            $out = array_merge($out, qaa_flatten_children_list($ch->children));
        }
    }
    return $out;
}

/**
 * Sắp xếp danh sách phẳng theo dispcreated (tăng dần). Nếu cần mới nhất lên trên, đảo lại comparator.
 * @param array $items
 * @return void
 */
function qaa_sort_flat_by_dispcreated(array &$items): void {
    usort($items, function($a, $b) {
        $ta = (int)($a->dispcreated ?? 0);
        $tb = (int)($b->dispcreated ?? 0);
        if ($ta === $tb) {
            return ((int)$a->id <=> (int)$b->id);
        }
        return $ta <=> $tb; // đổi thành $tb <=> $ta nếu muốn mới nhất lên trước
    });
}

function qaa_map_record_to_comment(\stdClass $n,
                                   context_module $ctx,
                                   int $cmid,
                                   int $rootid,
                                   bool $canmake,
                                   bool $canapproval,
                                   int $rootownerid,
                                   \moodle_database $db): \mod_qaa\output\comment {
    global $OUTPUT, $USER, $DB, $CFG;

    // Author
    $authorid  = !empty($n->studentid) ? (int)$n->studentid : (int)$n->teacherid;
    $user      = core_user::get_user($authorid);
    $avatar    = $OUTPUT->user_picture($user, ['size' => 28, 'class' => 'qaa-avatar-img', 'link' => true, 'context' => $ctx]);
    $authorurl = (new moodle_url('/user/view.php', ['id' => $authorid]))->out(false);

    // Date
    $ts     = (int)($n->dispcreated ?? 0);
    $datestr = $ts > 0 ? date('d/m/Y H:i:s', $ts) : '';

    // Badge (tuỳ cần, bạn có thể set dựa theo role/owner)
    $badge       = '';
    $badge_class = '';

    // Content (rewrite + format)
    $raw = (string)($n->message ?? '');
    $raw = file_rewrite_pluginfile_urls($raw, 'pluginfile.php', $ctx->id, 'mod_qaa', 'message', (int)$n->id);
    $content = format_text($raw, FORMAT_HTML, ['context' => $ctx]);

    // Attachments
    $fs    = get_file_storage();
    $files = $fs->get_area_files($ctx->id, 'mod_qaa', 'attachments', $n->id);
    foreach ($files as $file) {
        if ($file->get_filename() === '.') {
            continue;
        }
        $path = '/' . $ctx->id . '/mod_qaa/attachments/' . $file->get_itemid() . $file->get_filepath() . $file->get_filename();
        $url  = moodle_url::make_file_url('/pluginfile.php', $path, 0);
        $content .= html_writer::link(
            $url,
            $OUTPUT->pix_icon(file_file_icon($file), get_mimetype_description($file)) . $file->get_filename(),
            ['target' => '_blank']
        );
    }

    // Nếu bản ghi đang ở trạng thái rejected (2) thì hiển thị reason (nếu có bản ghi child GV status=2)
    if (isset($n->status) && (int)$n->status === 2) {
        $rejectchild = $DB->get_record_sql("
            SELECT message
              FROM {qaapairs}
             WHERE parentid = ?
               AND teacherid IS NOT NULL
               AND status = 2
          ORDER BY id DESC
             LIMIT 1
        ", [$n->id]);

        if ($rejectchild && !empty($rejectchild->message)) {
            $reasonhtml = format_text((string)$rejectchild->message, FORMAT_HTML, ['context' => $ctx]);
            $content .= html_writer::div(
                html_writer::tag('strong', get_string('rejectreason', 'mod_qaa') . ': ') . $reasonhtml,
                'qaa-reject-reason alert alert-warning mt-1'
            );
        }
    }

    // Quyền trả lời (recompute local, không dùng tham số truyền vào)
    $isApproved = ((int)$n->status === 1);
    $isOwner    = ((int)$rootownerid === (int)$USER->id);
    $canmakeFlag = 0;

    if ($isApproved) {
        if ($canapproval || $isOwner) {
            // GV: trả lời mọi bài đã duyệt; Chủ bài: được trả lời bài của mình
            $canmakeFlag = 1;
        } elseif (!$canapproval) {
            // HV: chỉ trả lời bài đã duyệt và KHÔNG phải bài của mình? (theo yêu cầu cũ)
            $canmakeFlag = $isOwner ? 1 : 0;
        }
    }

    // Actions
    $replyurl = null;
    $editurl = null;
    $deleteurl = null;
    $is_edit = qaa_is_edit($n, $rootid, $cmid, $USER->id);
    if ($is_edit) {
        if($n->id == $rootid) {
            $editurl = (new moodle_url('/mod/qaa/view.php', [
                'id'         => $cmid,
                'questionid' => $n->id,
                'action'     => 'edit_question',
                'embed'      => 1,
            ]))->out(false);
        } else {
            $editurl = (new moodle_url('/mod/qaa/view.php', [
                'id'         => $cmid,
                'questionid' => $n->id,
                'action'     => 'edit_answer',
                'embed'      => 1,
            ]))->out(false);
        }
    }

    $is_delete = qaa_is_delete($n, $rootid, $cmid, $USER->id);
    if ($is_delete) {
        $deleteurl = (new moodle_url('/mod/qaa/delete_message.php', [
            'id' => $cmid,
            'mid' => $n->id,
            'rootid' => $rootid,
            'sesskey' => sesskey()
        ]))->out(false);
    }
    
    $isroot = is_null($n->parentid);

    // =========================
    // 2-TẦNG: CHỈ ROOT có children; replies không có children nữa
    // =========================
    $children = [];
    if ($isroot && (int)$n->status !== 2) {
        $flat = (!empty($n->children) && is_array($n->children))
            ? qaa_flatten_children_list($n->children)
            : [];

        qaa_sort_flat_by_dispcreated($flat); // tăng dần; đổi comparator nếu muốn mới nhất lên đầu

        foreach ($flat as $c) {
            // Ngăn đệ quy sâu hơn: clone và xóa children
            $c2 = clone $c;
            $c2->children = [];
            $children[] = qaa_map_record_to_comment($c2, $ctx, $cmid, $rootid, $canmakeFlag, $canapproval, $rootownerid, $db);
        }
    }
    // Nếu không phải root: $children = [] (giữ nguyên → đảm bảo chỉ 2 tầng)

    return new \mod_qaa\output\comment(
        (int)$n->id,
        fullname($user),
        $authorurl,
        $avatar,
        $datestr,
        $badge,
        $badge_class,
        $content,
        ((int)$n->status === 0),
        $canapproval,
        $replyurl,
        $editurl,
        $deleteurl,
        $children
    );
}

/**
 * Tìm root node chứa target id trong cây $roots.
 * Trả về: [ 'root' => <root obj>, 'foundChildId' => <id con nếu là con, null nếu chính root> ] hoặc null nếu không thấy.
 */
function qaa_find_root_for_id(array $roots, int $targetid): ?array {
    foreach ($roots as $root) {
        if ((int)$root->id === $targetid) {
            return ['root' => $root, 'foundChildId' => null];
        }
        if (!empty($root->children) && qaa_tree_contains($root->children, $targetid)) {
            return ['root' => $root, 'foundChildId' => $targetid];
        }
    }
    return null;
}

/** Kiểm tra cây con có chứa target id không (DFS). */
function qaa_tree_contains(array $nodes, int $targetid): bool {
    foreach ($nodes as $n) {
        if ((int)$n->id === $targetid) {
            return true;
        }
        if (!empty($n->children) && qaa_tree_contains($n->children, $targetid)) {
            return true;
        }
    }
    return false;
}

?>