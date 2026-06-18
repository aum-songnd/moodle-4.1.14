<?php

class block_th_user_access_course extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_th_user_access_course');
    }

    public function get_content() {
        global $OUTPUT,$CFG;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer='';
        for($i=1 ; $i<=2;$i++){
            $this->content->footer .= "<a href='$CFG->wwwroot/blocks/th_user_access_course/view$i.php?id=$i'>view $i</a><br>";
        }
       
   
        $data = ['YOUR DATA GOES HERE'];

        

        return $this->content;
    }

   
    
}