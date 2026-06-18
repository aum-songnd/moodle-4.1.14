<?php

class block_th_activityrating extends block_base {
    public function init() {
        $this->title = get_string('th_activityrating', 'block_th_activityrating');
    }

    public function applicable_formats() {
      return [
        'admin' => false,
        'site-index' => false,
        'course-view' => false,
        'mod' => false,
        'my' => false,
        'mod-*' => true
      ];
    }
    function instance_allow_multiple() {
        return false;
    }

    public function has_config() {
        return true;
    }

    public function get_content() {
        global $CFG, $COURSE, $DB, $PAGE;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;

        if(!empty($this->config->title)){$this->content->title =  format_text($this->config->title, FORMAT_HTML, array('filter' => true));} else {$this->content->title = get_string('pluginname', 'block_th_activityrating');}

        $courseid = $COURSE->id;
        $context = context_course::instance($courseid);

        $canRate = has_capability('block/th_activityrating:rate', $context);
        if($canRate == 1) {
          $canRateClass = 'ccn-can-rate';
        } else {
          $canRateClass = 'ccn-cannot-rate';
        }
        
        $ccnSubmitRating = $this->submit_rating();
        $this->content->text = '';
        $parentcontextid = $this->instance->parentcontextid;
        $cmid = $DB->get_field('context', 'instanceid', array('id' => $parentcontextid, 'contextlevel' => CONTEXT_MODULE));
        $ccnRating = number_format($this->overall_rating($COURSE->id, $cmid), 1);

        $major = moodle_major_version();
        list($version) = explode('.', $major);
        if ($version == 3) {
          $ccnStar      = '<li class="list-inline-item"><i class="fas fa-star"></i></li>';
          $ccnStarHalf  = '<li class="list-inline-item"><i class="fas fa-star-half"></i></li>';
          $ccnStarVoid  = '<li class="list-inline-item"><i class="far fa-star"></i></li>';
        } else {
          $ccnStar      = '<li class="list-inline-item"><i class="fa fa-star"></i></li>';
          $ccnStarHalf  = '<li class="list-inline-item"><i class="fa fa-star-half-o"></i></li>';
          $ccnStarVoid  = '<li class="list-inline-item"><i class="fa-regular fa-star"></i></li>';
        }

        if($ccnRating == 5) {
          $ccnStars = str_repeat($ccnStar, 5);
        } elseif($ccnRating == 4.5) {
          $ccnStars = str_repeat($ccnStar, 4) . str_repeat($ccnStarHalf, 1);
        } elseif($ccnRating == 4) {
          $ccnStars = str_repeat($ccnStar, 4) . str_repeat($ccnStarVoid, 1);
        } elseif($ccnRating == 3.5) {
          $ccnStars = str_repeat($ccnStar, 3) . str_repeat($ccnStarHalf, 1) . str_repeat($ccnStarVoid, 1);
        } elseif($ccnRating == 3) {
          $ccnStars = str_repeat($ccnStar, 3) . str_repeat($ccnStarVoid, 2);
        } elseif($ccnRating == 2.5) {
          $ccnStars = str_repeat($ccnStar, 2) . str_repeat($ccnStarHalf, 1)  . str_repeat($ccnStarVoid, 2);
        } elseif($ccnRating == 2) {
          $ccnStars = str_repeat($ccnStar, 2) . str_repeat($ccnStarVoid, 3);
        } elseif($ccnRating == 1.5) {
          $ccnStars = str_repeat($ccnStar, 1) . str_repeat($ccnStarHalf, 1)  . str_repeat($ccnStarVoid, 3);
        } elseif($ccnRating == 0.5) {
          $ccnStars = str_repeat($ccnStarHalf, 1) . str_repeat($ccnStarVoid, 4);
        } else {
          $ccnStars = str_repeat($ccnStarVoid, 5);
        }

        $ccnFive = $this->get_specific_average($COURSE->id, 5, $cmid);
        $ccnFour = $this->get_specific_average($COURSE->id, 4, $cmid);
        $ccnThree = $this->get_specific_average($COURSE->id, 3, $cmid);
        $ccnTwo = $this->get_specific_average($COURSE->id, 2, $cmid);
        $ccnOne = $this->get_specific_average($COURSE->id, 1, $cmid);

        $this->content->text .= '
          <div class="cs_row_five" id="th_activityrating">
                  <div class="student_feedback_container">
                    <h4 data-ccn="title" class="aii_title"></h4>
                    <div class="s_feeback_content">
                          <ul class="skills">
                            <li class="list-inline-item">'.get_string('stars_5', 'block_th_activityrating').'</li>
                              <li class="list-inline-item progressbar1" data-width="'.$ccnFive.'" data-target="100">'.$ccnFive.'%</li>
                          </ul>
                          <ul class="skills">
                            <li class="list-inline-item">'.get_string('stars_4', 'block_th_activityrating').'</li>
                              <li class="list-inline-item progressbar2" data-width="'.$ccnFour.'" data-target="100">'.$ccnFour.'%</li>
                          </ul>
                          <ul class="skills">
                            <li class="list-inline-item">'.get_string('stars_3', 'block_th_activityrating').'</li>
                              <li class="list-inline-item progressbar3" data-width="'.$ccnThree.'" data-target="100">'.$ccnThree.'%</li>
                          </ul>
                          <ul class="skills">
                            <li class="list-inline-item">'.get_string('stars_2', 'block_th_activityrating').'</li>
                              <li class="list-inline-item progressbar4" data-width="'.$ccnTwo.'" data-target="100">'.$ccnTwo.'%</li>
                          </ul>
                          <ul class="skills">
                            <li class="list-inline-item">'.get_string('stars_1', 'block_th_activityrating').'</li>
                              <li class="list-inline-item progressbar5" data-width="'.$ccnOne.'" data-target="100">'.$ccnOne.'%</li>
                          </ul>
                    </div>
                    <div class="aii_average_review text-center '.$canRateClass.'">
                      <div class="av_content">
                        <h2>'.$ccnRating.'</h2>
                        <ul class="aii_rive_list mb0">
                          '.$ccnStars.'
                        </ul>
                        <p>'.$this->count_ratings($COURSE->id, $cmid).'</p>';
                        if($canRate == 1){
                          $this->content->text .= $ccnSubmitRating;
                        }
                      $this->content->text .='
                      </div>
                    </div>
                  </div>
                </div>';
        $PAGE->requires->js_call_amd('block_th_activityrating/scripts', 'init');
        return $this->content;

    }

    public function overall_rating($courseID, $cmid) {
      global $DB;
      $sql = "SELECT AVG(rating) AS average
                FROM {block_th_activityrating}
               WHERE course = $courseID AND cmid = $cmid";
      $params = [$courseID, $cmid];
      $avg = $DB->get_record_sql($sql, $params);
      return $avg ? round($avg->average * 2) / 2 : 0;
    }

    public function count_ratings($courseID, $cmid) {
        global $CFG, $DB;
        $countRecords = $DB->count_records('block_th_activityrating', array('course'=>$courseID, 'cmid' => $cmid));
        $countRatings = '';
        if ($countRecords > 0) {
            $countRatings = get_string ('rated_by', 'block_th_activityrating', $countRecords);
        } else {
            $countRatings = get_string ('rated_by_none', 'block_th_activityrating');
        }
        return $countRatings;
    }
    public function count_ratings_external($courseID, $cmid) {
        global $CFG, $DB;
        $countRecords = $DB->count_records('block_th_activityrating', array('course'=>$courseID, 'cmid' => $cmid));
        return $countRecords;
    }

    public function get_specific_average($courseID, $rating, $cmid) {
        global $CFG, $DB;
        $countOnlyRating        = $DB->count_records('block_th_activityrating', array('course'=>$courseID, 'rating'=>$rating, 'cmid' => $cmid));
        $countExcludingRating   = $DB->count_records_sql(
        "   SELECT COUNT(*)
            FROM {block_th_activityrating}
            WHERE course = $courseID AND cmid = $cmid
            AND rating <> $rating
        ");

        $countTotal = $DB->count_records('block_th_activityrating', array('course'=>$courseID, 'cmid' => $cmid));

        if($countTotal == 0) {
          $result = '0';
        } else {
          $number = $countOnlyRating / $countTotal * 100;
          $result =  round($number, 2);
        }
        return $result;

    }

    public function submit_rating() {

      global $CFG, $COURSE, $DB;

      $courseid = $COURSE->id;
      $context = context_course::instance($courseid);

      $parentcontextid = $this->instance->parentcontextid;
      $cmid = $DB->get_field('context', 'instanceid', array('id' => $parentcontextid, 'contextlevel' => CONTEXT_MODULE));

      $ccnStar =  '<span class="far fa-star"></span>';
      $return =   '<form id="ccn-star-rate" method="post" action="'.$CFG->wwwroot.'/blocks/th_activityrating/rate.php">
                    <input name="id" type="hidden" value="'.$courseid.'" />
                    <input name="cmid" type="hidden" value="'.$cmid.'" />
                    <div class="ccn-star-rate-inner">';
                      for ($i = 5; $i >= 1; $i--) {
                        $printCcnStar = str_repeat($ccnStar, $i);
                        $return .='    <input required type="radio" id="stars-'.$i.'" name="rating" value="'.$i.'" /><label for="stars-'.$i.'"></label>';
                      }
      $return .= '  </div>
                    <button class="btn btn-primary" type="submit">'.get_string('rate_course', 'block_th_activityrating').'</button>
                  </form>';
      return $return;
    }

    // public function external_star_rating($courseID, $cmid) {

    //   $ccnStar      = '<li class="list-inline-item"><i class="fa fa-star"></i></li>';
    //   $ccnStarHalf  = '<li class="list-inline-item"><i class="fa fa-star-half-o"></i></li>';
    //   $ccnStarVoid  = '<li class="list-inline-item"><i class="fa fa-star-o"></i></li>';
    //   $ccnRating    = $this->overall_rating($courseID);

    //   if($ccnRating == 5) {
    //     $ccnStars = str_repeat($ccnStar, 5);
    //   } elseif($ccnRating == 4.5) {
    //     $ccnStars = str_repeat($ccnStar, 4) . str_repeat($ccnStarHalf, 1);
    //   } elseif($ccnRating == 4) {
    //     $ccnStars = str_repeat($ccnStar, 4) . str_repeat($ccnStarVoid, 1);
    //   } elseif($ccnRating == 3.5) {
    //     $ccnStars = str_repeat($ccnStar, 3) . str_repeat($ccnStarHalf, 1) . str_repeat($ccnStarVoid, 1);
    //   } elseif($ccnRating == 3) {
    //     $ccnStars = str_repeat($ccnStar, 3) . str_repeat($ccnStarVoid, 2);
    //   } elseif($ccnRating == 2.5) {
    //     $ccnStars = str_repeat($ccnStar, 2) . str_repeat($ccnStarHalf, 1)  . str_repeat($ccnStarVoid, 2);
    //   } elseif($ccnRating == 2) {
    //     $ccnStars = str_repeat($ccnStar, 2) . str_repeat($ccnStarVoid, 3);
    //   } elseif($ccnRating == 1.5) {
    //     $ccnStars = str_repeat($ccnStar, 1) . str_repeat($ccnStarHalf, 1)  . str_repeat($ccnStarVoid, 3);
    //   } elseif($ccnRating == 0.5) {
    //     $ccnStars = str_repeat($ccnStarHalf, 1) . str_repeat($ccnStarVoid, 4);
    //   } else {
    //     $ccnStars = str_repeat($ccnStarVoid, 5);
    //   }

    //   $return = '<div class="ccn-external-stars">'.$ccnStars.'<li class="list-inline-item"><span>('.$this->count_ratings_external($courseID, $cmid).')</span></li></div>';
    //   return $return;

    // }
}
