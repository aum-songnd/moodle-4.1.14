<?php
namespace mod_qaa\local\mapper;

defined('MOODLE_INTERNAL') || die();

class list_mapper {
    public static function map_rows(array $records, \context_module $ctx, int $cmid, $returnurl): array {
        global $OUTPUT;

        $rows = [];
        foreach ($records as $r) {
            $rootauthorid = (int)$r->rootauthorid;
            $latestauthorid = (int)($r->latestauthorid ?: null);

            $rootuser = \core_user::get_user($rootauthorid);
            $latestuser = \core_user::get_user($latestauthorid);

            $creatorDate = !empty($r->rootcreated)
                ? date('d/m/Y H:i:s', (int)$r->rootcreated)
                : '';
            $latestDate = !empty($r->latesttime)
                ? date('d/m/Y H:i:s', (int)$r->latesttime)
                : '';

            // Title rút gọn từ message (loại tag, cắt 120 ký tự)
            $plain = trim(strip_tags((string)$r->rootmessage));
            if (\core_text::strlen($plain) > 120) {
                $plain = \core_text::substr($plain, 0, 120) . '…';
            }

            // Nếu câu hỏi đang chờ phê duyệt thì thêm nhãn.
            $tag_status = '';
            if ((int)$r->status === 0) {
                $tag_status .= ' <span class="qaa-tag qaa-tag-pending">'
                    . s(get_string('tag_pending', 'mod_qaa'))
                    . '</span>';
            } else if ((int)$r->status === 2) {
                $tag_status .= ' <span class="qaa-tag qaa-tag-rejected">'
                    . s(get_string('rejected_tag', 'mod_qaa'))
                    . '</span>';
            }            
            

            $replycount = self::count_all_replies($r->rootid);

            if($latestuser) {
                $rows[] = [
                    'viewurl' => (new \moodle_url('/mod/qaa/view.php', ['id'=>$cmid,'questionid'=>$r->rootid,'embed'=>1, 'returnurl'=>$returnurl]))->out(false),
                    'title'   => format_string($plain) . $tag_status,
    
                    'creator' => [
                        'fullname'   => fullname($rootuser),
                        'date'       => $creatorDate,
                        'avatarhtml' => $OUTPUT->user_picture($rootuser, ['size'=>24, 'class'=>'qaa-avatar-img', 'context'=>$ctx, 'link'=>false])
                    ],
                    'latest'  => [
                        'fullname'   => fullname($latestuser),
                        'date'       => $latestDate,
                        'avatarhtml' => $OUTPUT->user_picture($latestuser, ['size'=>24, 'class'=>'qaa-avatar-img', 'context'=>$ctx, 'link'=>false])
                    ],
                    'replycount' => (int)$replycount
                ];
            } else {
                $rows[] = [
                    'viewurl' => (new \moodle_url('/mod/qaa/view.php', ['id'=>$cmid,'questionid'=>$r->rootid,'embed'=>1,'returnurl'=>$returnurl]))->out(false),
                    'title'   => format_string($plain) . $tag_status,
    
                    'creator' => [
                        'fullname'   => fullname($rootuser),
                        'date'       => $creatorDate,
                        'avatarhtml' => $OUTPUT->user_picture($rootuser, ['size'=>24, 'class'=>'qaa-avatar-img', 'context'=>$ctx, 'link'=>false])
                    ],
                    'latest'  => [
                        'fullname'   => '-',
                        'date'       => '',
                        'avatarhtml' => ''
                    ],
                    'replycount' => (int)$replycount
                ];
            }

            
        }
        return $rows;
    }

    private static function count_all_replies(int $rootid): int {
        global $DB;
    
        // Lấy tất cả reply trực tiếp của root.
        $children = $DB->get_records('qaapairs', ['parentid' => $rootid, 'status' => 1], '', 'id');
    
        $count = count($children);
        foreach ($children as $child) {
            // Đếm tiếp reply con của reply hiện tại.
            $count += self::count_all_replies($child->id);
        }
    
        return $count;
    }    
}
