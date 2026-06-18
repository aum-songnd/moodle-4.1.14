<?php

class block_plugin01 extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_plugin01');
    }

    public function get_content() {
        global $OUTPUT,$CFG;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer='';
        $this->content->text='';
        
        for ($i = 1; $i <= 8; $i++) {
            $this->content->footer .= "<a href='$CFG->wwwroot/blocks/plugin01/view$i.php?id=$i'>Link $i</a><br>";
        }
      
        if (isset($this->config->text)){
            $this->content->text =get_string('defaultvalue', 'block_plugin01')." ".$this->config->text;
        $this->content->text .= ' ' . get_string('defaultvalue', 'block_plugin01')." ".$this->config->text;
        }else {
            $this->content->text =get_string('defaultvalue', 'block_plugin01');
        $this->content->text .= ' ' . get_string('defaultvalue', 'block_plugin01');
        }

        
       
       
        $data = ['YOUR DATA GOES HERE'];

        

        return $this->content;
    }

   
    
}