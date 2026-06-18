<?php

class theme_th_lambda_st_core_renderer extends theme_lambda_core_renderer {
	 
	protected function render_custom_menu(custom_menu $menu) {
        global $CFG;
		
		$hasdisplaymycourses = theme_lambda_get_setting('mycourses_dropdown');
		
        if (isloggedin() && !isguestuser()) {
            $branchlabel = get_string('mycourses') ;
            $branchurl   = new moodle_url('/my/courses.php');
            $branchtitle = $branchlabel;
            $branchsort  = 10000; 
            $branch = $menu->add($branchlabel, $branchurl, $branchtitle, $branchsort);

            if (!$sortorder = $CFG->navsortmycoursessort) {
                $sortorder = 'sortorder';
            }
            $courses_limit = $CFG->navcourselimit;
 			if ($mycourses = enrol_get_my_courses(NULL, 'visible DESC, '.$sortorder.' ASC', $courses_limit)) {
				foreach ($mycourses as $mycourse) {
					if ($CFG->navshowfullcoursenames) {
						$current_menu_item = $mycourse->fullname;
					} else {
						$current_menu_item = $mycourse->shortname;
					}
					$current_menu_item = format_string($current_menu_item, true, ['context' => context_course::instance(SITEID), "escape" => false]);
					$current_menu_item_title = format_string($mycourse->fullname, true, ['context' => context_course::instance(SITEID), "escape" => false]);
                	$branch->add($current_menu_item, new moodle_url('/course/view.php', array('id' => $mycourse->id)), $current_menu_item_title);
                    
            	}
                $branch->add(
                    get_string('viewallcourses'), 
                    new moodle_url('/my/courses.php'), 
                    get_string('viewallcourses'), 
                    9999,
                    ['class' => 'th-view-all-courses-btn']
                );
			}
			else {
            	$branch->add(get_string('myhome'), new moodle_url('/my/index.php'), get_string('myhome'));
			}
        }

        $langs = get_string_manager()->get_list_of_translations();
        $haslangmenu = $this->lang_menu() != '';

        if (!$menu->has_children() && !$haslangmenu) {
            return '';
        }

        if ($haslangmenu) {
            $strlang =  get_string('language');
            $currentlang = current_language();
            if (isset($langs[$currentlang])) {
                $currentlang = $langs[$currentlang];
            } else {
                $currentlang = $strlang;
            }
            $this->language = $menu->add($currentlang, new moodle_url(''), $strlang, 10000);
            foreach ($langs as $langtype => $langname) {
                $this->language->add($langname, new moodle_url($this->page->url, array('lang' => $langtype)), $langname);
            }
        }

        $content = '<ul class="nav">';
        
        foreach ($menu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item, 1);
        }

        return $content.'</ul>';
    }

}