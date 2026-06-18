<?php

defined('MOODLE_INTERNAL') || die();

function th_register_parse_course($list_time)
{
    if (empty($list_time)) {
        return array();
    } else {
        $rawlines = explode(PHP_EOL, $list_time);
        $result   = array();
        foreach ($rawlines as $rawline) {
            $result[] = trim($rawline);
        }
        return $result;
    }
}

function th_register_get_content($contents)
{
    $max = count($contents) - 1;
    for ($i = 1; $i < $max; ++$i) {
        $listcourses_new[] = $contents[$i];
    }
    return $listcourses_new;
}

function th_register_get_user_fullname($userid)
{
    global $DB, $CFG;
    $user = $DB->get_record('user', ['id' => $userid]);
    return html_writer::link($CFG->wwwroot . '/user/profile.php?id=' . $userid, fullname($user));
}
