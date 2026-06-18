<?php
defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . "/course/renderer.php";

class th_search_courses_core_course_renderer extends core_course_renderer {
    public function search_courses($searchcriteria) {
        global $CFG;
        $content = '';

        $search = '';
        if (!empty($searchcriteria['search'])) {
            $search = $searchcriteria['search'];
        }
        $content .= $this->course_search_form($search);

        if (!empty($searchcriteria)) {
            // print search results

            $displayoptions = array('sort' => array('displayname' => 1));
            // take the current page and number of results per page from query
            $perpage = optional_param('perpage', 0, PARAM_RAW);
            if ($perpage !== 'all') {
                $displayoptions['limit'] = ((int) $perpage <= 0) ? $CFG->coursesperpage : (int) $perpage;
                $page = optional_param('page', 0, PARAM_INT);
                $displayoptions['offset'] = $displayoptions['limit'] * $page;
            }
            // options 'paginationurl' and 'paginationallowall' are only used in method coursecat_courses()
            $displayoptions['paginationurl'] = new moodle_url('/local/th_search_courses/view.php', $searchcriteria);
            $displayoptions['paginationallowall'] = true; // allow adding link 'View all'

            $class = 'course-search-result';
            foreach ($searchcriteria as $key => $value) {
                if (!empty($value)) {
                    $class .= ' course-search-result-' . $key;
                }
            }

            $chelper = new coursecat_helper();
            $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED_WITH_CAT)->
                set_courses_display_options($displayoptions)->
                set_search_criteria($searchcriteria)->
                set_attributes(array('class' => $class));

            $courses = th_search_courses_core_course_category::search_courses($searchcriteria, $chelper->get_courses_display_options());
            $totalcount = th_search_courses_core_course_category::search_courses_count($searchcriteria);

            $courseslist = $this->coursecat_courses($chelper, $courses, $totalcount);
            if (!$totalcount) {
                if (!empty($searchcriteria['search'])) {
                    $content .= $this->heading(get_string('nocoursesfound', '', $searchcriteria['search']));
                } else {
                    $content .= $this->heading(get_string('novalidcourses'));
                }
            } else {
                $content .= $this->heading(get_string('searchresults') . ": $totalcount");
                $content .= $courseslist;
            }
        }
        return $content;
    }

    public function course_search_form($value = '') {
        global $CFG;

        $data = [
            'action' => $CFG->wwwroot . '/local/th_search_courses/view.php',
            'btnclass' => 'btn-primary',
            'inputname' => 'q',
            'searchstring' => get_string('searchcourses'),
            'hiddenfields' => (object) ['name' => 'areaids', 'value' => 'core_course-course'],
            'query' => $value,
        ];
        return $this->render_from_template('core/search_input', $data);
    }

    protected function coursecat_coursebox_content(coursecat_helper $chelper, $course) {
        global $CFG;
        if ($chelper->get_show_courses() < self::COURSECAT_SHOW_COURSES_EXPANDED) {
            return '';
        }
        if ($course instanceof stdClass) {
            $course = new core_course_list_element($course);
        }
        $content = '';

        // display course overview files
        $contentimages = $contentfiles = '';
        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                $file->get_filearea() . $file->get_filepath() . $file->get_filename(), !$isimage);
            if ($isimage) {
                $contentimages .= '<div class="courseimage" style="background-image: url(' . $url . ');"></div>';
            } else {
                $image = $this->output->pix_icon(file_file_icon($file, 24), $file->get_filename(), 'moodle');
                $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')) .
                html_writer::tag('span', $file->get_filename(), array('class' => 'fp-filename'));
                $contentfiles .= html_writer::tag('span',
                    html_writer::link($url, $filename),
                    array('class' => 'coursefile fp-filename-icon'));
            }
        }
        $content .= $contentimages . $contentfiles;

        // display course summary
        $content .= html_writer::start_tag('div', array('class' => $course->visible ? 'summary' : 'summary dimmed'));

        $coursename = $chelper->get_course_formatted_name($course);
        $coursenamelink = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)),
            $coursename, array('class' => $course->visible ? '' : 'dimmed'));
        $content .= html_writer::tag('h3', $coursenamelink, array('class' => 'coursename'));

        $content .= '<div>' . get_course_formatted_summary_nohighlight($course,
            array('overflowdiv' => true, 'noclean' => true, 'para' => false)) . '</div>';

        $content .= html_writer::end_tag('div'); // .summary

        // display course contacts. See course_in_list::get_course_contacts()
        if ($course->has_course_contacts()) {
            $content .= '<div class="teachers">';
            $current_role = '';
            $i = 0;
            $list_course_contacts = $course->get_course_contacts();

            foreach ($list_course_contacts as $userid => $coursecontact) {
                if ($i == 0) {
                    $current_role = $coursecontact['rolename'];
                    $content .= $current_role . ': ';
                    $name = html_writer::link(new moodle_url('/user/view.php', array('id' => $userid, 'course' => SITEID)), $coursecontact['username']);
                    $content .= $name;
                }
                if (($i > 0) AND ($coursecontact['rolename'] == $current_role)) {
                    $content .= ', ';
                    $name = html_writer::link(new moodle_url('/user/view.php', array('id' => $userid, 'course' => SITEID)), $coursecontact['username']);
                    $content .= $name;
                } else if ($i > 0) {
                    $content .= '</div>';
                    $content .= '<div class="teachers">';
                    $current_role = $coursecontact['rolename'];
                    $content .= $current_role . ': ';
                    $name = html_writer::link(new moodle_url('/user/view.php', array('id' => $userid, 'course' => SITEID)), $coursecontact['username']);
                    $content .= $name;
                }
                $i++;
            }
            $content .= '</div>'; // .teachers
        }

        // Display custom fields.
        if ($course->has_custom_fields()) {
            $content .= '<div class="custom_fields">';
            $handler = core_course\customfield\course_handler::create();
            $customfields = $handler->display_custom_fields_data($course->get_custom_fields());
            $content .= \html_writer::tag('div', $customfields, ['class' => 'customfields-container']);
            $content .= '</div>';
        }

        $content .= '<div class="course-btn"><p><a class="btn btn-primary" href="' . new moodle_url('/course/view.php', array('id' => $course->id)) . '">' . get_string('entercourse') . '</a></p></div>';

        // display course category if necessary (for example in search results)
        if ($chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_EXPANDED_WITH_CAT) {
            if ($CFG->version < 2018120300) {
                require_once $CFG->libdir . '/coursecatlib.php';
                if ($cat = coursecat::get($course->category, IGNORE_MISSING)) {
                    $content .= html_writer::start_tag('div', array('class' => 'coursecat'));
                    $content .= get_string('category') . ': ' .
                    html_writer::link(new moodle_url('/course/index.php', array('categoryid' => $cat->id)),
                        $cat->get_formatted_name(), array('class' => $cat->visible ? '' : 'dimmed'));
                    $content .= html_writer::end_tag('div'); // .coursecat
                }
            } else {
                if ($cat = core_course_category::get($course->category, IGNORE_MISSING)) {
                    $content .= html_writer::start_tag('div', array('class' => 'coursecat'));
                    $content .= get_string('category') . ': ' .
                    html_writer::link(new moodle_url('/course/index.php', array('categoryid' => $cat->id)),
                        $cat->get_formatted_name(), array('class' => $cat->visible ? '' : 'dimmed'));
                    $content .= html_writer::end_tag('div'); // .coursecat
                }
            }
        }

        return $content;
    }
}

class th_search_courses_core_course_category extends core_course_category {
    public static function search_courses($search, $options = array(), $requiredcapabilities = array()) {
        global $DB;

        $offset = !empty($options['offset']) ? $options['offset'] : 0;
        $limit = !empty($options['limit']) ? $options['limit'] : null;
        $sortfields = !empty($options['sort']) ? $options['sort'] : array('sortorder' => 1);

        $preloadcoursecontacts = !empty($options['coursecontacts']);
        unset($options['coursecontacts']);

        // Empty search string will return all results.
        if (!isset($search['search'])) {
            $search['search'] = '';
        }

        if (empty($search['blocklist']) && empty($search['modulelist']) && empty($search['tagid'])) {
            // Search courses that have specified words in their names/summaries.
            $searchterms = preg_split('|\s+|', trim($search['search']), 0, PREG_SPLIT_NO_EMPTY);
            $searchcond = $searchcondparams = [];
            if (!empty($search['onlywithcompletion'])) {
                $searchcond = ['c.enablecompletion = :p1'];
                $searchcondparams = ['p1' => 1];
            }
            $courselist = th_search_courses_get_courses_search_fullname(trim($search['search']), 'c.sortorder ASC', 0, 9999999, $totalcount,
                $requiredcapabilities, $searchcond, $searchcondparams);

            // self::sort_records($courselist, $sortfields);
            // $coursecatcache->set($cachekey, array_keys($courselist));
            // $coursecatcache->set($cntcachekey, $totalcount);
            $records = array_slice($courselist, $offset, $limit, true);
        } else {
            if (!empty($search['blocklist'])) {
                // Search courses that have block with specified id.
                $blockname = $DB->get_field('block', 'name', array('id' => $search['blocklist']));
                $where = 'ctx.id in (SELECT distinct bi.parentcontextid FROM {block_instances} bi
                    WHERE bi.blockname = :blockname)';
                $params = array('blockname' => $blockname);
            } else if (!empty($search['modulelist'])) {
                // Search courses that have module with specified name.
                $where = "c.id IN (SELECT DISTINCT module.course " .
                    "FROM {" . $search['modulelist'] . "} module)";
                $params = array();
            } else if (!empty($search['tagid'])) {
                // Search courses that are tagged with the specified tag.
                $where = "c.id IN (SELECT t.itemid " .
                    "FROM {tag_instance} t WHERE t.tagid = :tagid AND t.itemtype = :itemtype AND t.component = :component)";
                $params = array('tagid' => $search['tagid'], 'itemtype' => 'course', 'component' => 'core');
                if (!empty($search['ctx'])) {
                    $rec = isset($search['rec']) ? $search['rec'] : true;
                    $parentcontext = context::instance_by_id($search['ctx']);
                    if ($parentcontext->contextlevel == CONTEXT_SYSTEM && $rec) {
                        // Parent context is system context and recursive is set to yes.
                        // Nothing to filter - all courses fall into this condition.
                    } else if ($rec) {
                        // Filter all courses in the parent context at any level.
                        $where .= ' AND ctx.path LIKE :contextpath';
                        $params['contextpath'] = $parentcontext->path . '%';
                    } else if ($parentcontext->contextlevel == CONTEXT_COURSECAT) {
                        // All courses in the given course category.
                        $where .= ' AND c.category = :category';
                        $params['category'] = $parentcontext->instanceid;
                    } else {
                        // No courses will satisfy the context criterion, do not bother searching.
                        $where = '1=0';
                    }
                }
            } else {
                debugging('No criteria is specified while searching courses', DEBUG_DEVELOPER);
                return array();
            }
            $courselist = self::get_course_records($where, $params, $options, true);
            if (!empty($requiredcapabilities)) {
                foreach ($courselist as $key => $course) {
                    context_helper::preload_from_record($course);
                    $coursecontext = context_course::instance($course->id);
                    if (!has_all_capabilities($requiredcapabilities, $coursecontext)) {
                        unset($courselist[$key]);
                    }
                }
            }
            // self::sort_records($courselist, $sortfields);
            // $coursecatcache->set($cachekey, array_keys($courselist));
            // $coursecatcache->set($cntcachekey, count($courselist));
            $records = array_slice($courselist, $offset, $limit, true);
        }

        // Preload course contacts if necessary - saves DB queries later to do it for each course separately.
        if (!empty($preloadcoursecontacts)) {
            self::preload_course_contacts($records);
        }
        // Preload custom fields if necessary - saves DB queries later to do it for each course separately.
        if (!empty($options['customfields'])) {
            self::preload_custom_fields($records);
        }
        // If option 'idonly' is specified no further action is needed, just return list of ids.
        if (!empty($options['idonly'])) {
            return array_keys($records);
        }
        // Prepare the list of core_course_list_element objects.

        $courses = array();
        foreach ($records as $record) {
            $courses[$record->id] = new core_course_list_element($record);
        }

        return $courses;
    }
    public static function search_courses_count($search, $options = array(), $requiredcapabilities = array()) {

        $courses = self::search_courses($search, $options, $requiredcapabilities);
        $cnt = count($courses);

        return $cnt;
    }
}

/**
 * A list of courses that match a search
 *
 * @global object
 * @global object
 * @param array $searchterms An array of search criteria
 * @param string $sort A field and direction to sort by
 * @param int $page The page number to get
 * @param int $recordsperpage The number of records per page
 * @param int $totalcount Passed in by reference.
 * @param array $requiredcapabilities Extra list of capabilities used to filter courses
 * @param array $searchcond additional search conditions, for example ['c.enablecompletion = :p1']
 * @param array $params named parameters for additional search conditions, for example ['p1' => 1]
 * @return stdClass[] {@link $COURSE} records
 */
function th_search_courses_get_courses_search_fullname($searchterms, $sort, $page, $recordsperpage, &$totalcount,
    $requiredcapabilities = array(), $searchcond = [], $params = []) {
    global $CFG, $DB;

    $courses = array();
    $c = 0; // counts how many visible courses we've seen

    // Tiki pagination
    $limitfrom = $page * $recordsperpage;
    $limitto = $limitfrom + $recordsperpage;

    $sql = "SELECT * FROM {course}
    WHERE (LOWER(fullname) LIKE BINARY LOWER('%$searchterms%')
    OR shortname LIKE '%$searchterms%') AND id <> 1 ORDER BY startdate DESC";

    // $courses = $DB->get_records_sql($sql);

    $mycourses = enrol_get_my_courses();
    $rs = $DB->get_recordset_sql($sql, $params);

    foreach ($rs as $course) {
        // Preload contexts only for hidden courses or courses we need to return.
        context_helper::preload_from_record($course);
        $coursecontext = context_course::instance($course->id);
        if (!array_key_exists($course->id, $mycourses) && !th_search_courses_core_course_category::can_view_course_info($course)) {
            continue;
        }
        if (!empty($requiredcapabilities)) {
            if (!has_all_capabilities($requiredcapabilities, $coursecontext)) {
                continue;
            }
        }
        // Don't exit this loop till the end
        // we need to count all the visible courses
        // to update $totalcount
        if ($c >= $limitfrom && $c < $limitto) {
            $courses[$course->id] = $course;
        }
        $c++;
    }
    $rs->close();

    // our caller expects 2 bits of data - our return
    // array, and an updated $totalcount
    $totalcount = $c;
    return $courses;
}

/**
 * Returns given course's summary with proper embedded files urls and formatted
 *
 * @param core_course_list_element $course
 * @param array|stdClass $options additional formatting options
 * @return string
 */
function get_course_formatted_summary_nohighlight($course, $options = array()) {
    global $CFG;
    require_once $CFG->libdir . '/filelib.php';
    if (!$course->has_summary()) {
        return '';
    }
    $options = (array) $options;
    $context = context_course::instance($course->id);
    if (!isset($options['context'])) {
        // TODO see MDL-38521
        // option 1 (current), page context - no code required
        // option 2, system context
        // $options['context'] = context_system::instance();
        // option 3, course context:
        // $options['context'] = $context;
        // option 4, course category context:
        // $options['context'] = $context->get_parent_context();
    }
    $summary = file_rewrite_pluginfile_urls($course->summary, 'pluginfile.php', $context->id, 'course', 'summary', null);
    // print_object($summary);
    $summary = format_text($summary, $course->summaryformat, $options, $course->id);
    // if (!empty($this->searchcriteria['search'])) {
    //     $summary = highlight($this->searchcriteria['search'], $summary);
    // }
    return $summary;
}
