<?php
namespace mod_qaa\local\service;

use context_module;
use mod_qaa\local\repository\qaapairs_repository;

defined('MOODLE_INTERNAL') || die();

class feed_service {
    /** @var qaapairs_repository */
    private $repo;

    public function __construct(qaapairs_repository $repo) {
        $this->repo = $repo;
    }

    /**
     * @return array
     */
    public function get_feed_tree(int $qaaid, context_module $context) {
        global $USER;
        $ismanager = has_capability('mod/qaa:approval', $context);
        $records = $ismanager
            ? $this->repo->get_all_for_manager($qaaid)
            : $this->repo->get_all_for_student($qaaid, $USER->id);

        // Build tree
        $byid = [];
        foreach ($records as $m) { $m->children = []; $byid[$m->id] = $m; }
        $roots = [];
        foreach ($byid as $m) {
            if (!empty($m->parentid) && isset($byid[$m->parentid])) {
                $byid[$m->parentid]->children[] = $m;
            } else {
                $roots[] = $m;
            }
        }
        return $roots;
    }

    public function rewrite_message(string $text, int $contextid, int $itemid): string {
        return file_rewrite_pluginfile_urls($text, 'pluginfile.php', $contextid, 'mod_qaa', 'message', $itemid);
    }
}
