<?php


class block_plugin02 extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_plugin02');
    }

   
    public function get_content() {
        global $OUTPUT,$CFG;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        for($i=1;$i <=5;$i++){
            $this->content->text .= "<a href='$CFG->wwwroot/blocks/plugin02/view_plugin02/view$i.php?id=$i'>Link $i</a> <br>" ;
        }
       
        $this->content->footer = 'end';

        $data = ['YOUR DATA GOES HERE'];

        return $this->content;
    }
    
   
    public function applicable_formats() {
        return [
            'admin' => false,
            'site-index' => true,
            'course-view' => true,
            'mod' => false,
            'my' => true,
        ];
    }
}