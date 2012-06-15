<?php

class compression {
    
    private $js_dirs, $css_dirs, $merged_dir, $home_dir;
    
    public function __construct($js_dirs, $css_dirs, $merged_dir, $home_dir) {

        $this->home_dir     = $home_dir;
        $this->js_dirs      = $js_dirs;
        $this->css_dirs     = $css_dirs;
        $this->mdir         = $merged_dir;

        require_once dirname(__FILE__) . '/JSmin.php';

    }

    static public function compress($js_dirs = array(), $css_dirs = array(), $merged_dir = null, $home_dir = null) {
        
        $c = new compression($js_dirs, $css_dirs, $merged_dir, $home_dir);
        $c->all();
        
    }

    public function all() {
        
        $this->precheking();
        
        $sections = array('js' => array(),'css' => array());
        
        foreach($this->js_dirs as $js_dirs){
            foreach(scandir($this->home_dir.$js_dirs) as $file) {
                if(preg_match('/\.js$/s',$file)) {
                    $sections['js'][$this->mdir.'all.js'][] = $js_dirs.$file;
                }
            }
        }
        
        
        foreach($this->css_dirs as $css_dirs){
            foreach(scandir($this->home_dir.$css_dirs) as $file) {
                if(preg_match('/\.css$/',$file)) {
                    if(!preg_match('/ie(\d)?/',$file) && !preg_match('/print/',$file)) {
                        $sections['css'][$this->mdir.'all.css'][] = $css_dirs.$file;
                    }
                }
            }
        }
        
		if(isset($_COOKIE['debug'])){
			echo '<pre>';print_r($sections);echo '</pre>';
            $this->error('DEBUG');
		}
		
        foreach($sections as $section => $data) {
            foreach($data as $target => $files) {
                if(($modified = @filemtime($this->home_dir.$target)) !== false) {
                    foreach($files as $file) {
                        if(is_file($file) && (int)@filemtime($this->home_dir.$file) > $modified) {
                            if($section === 'css') {
                                $this->css($target,$files);
                            } else {
                                $this->js($target,$files);
                            }

                            break;
                        }
                    }
                } else {
                    if($section === 'css') {
                        $this->css($target,$files);
                    } else {
                        $this->js($target,$files);
                    }
                }
            }
        }
    }

    private function js($target,$files) {
        $merged = $this->merge($files);

        try {
            $this->write($target,JSmin::minify($merged));
        }
        catch (JSMinException $e) {
            $this->write($target,$merged);
        }
    }


    private function css($target,$files) {
        $merged = $this->merge($files);

        $merged = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!','',$merged);
        $merged = str_replace(array("\r","\n","\t",'  ','   '),'',$merged);

        $this->write($target,$merged);
    }

    private function merge($files) {
        $merged = '';

        foreach($files as $file) {
            if(($content = @file_get_contents($this->home_dir.$file)) !== false) {
                $merged .= $content."\n";
            } else {
                $this->error(sprintf('Cannot read file: %s', $this->home_dir.$file));
            }
        }

        return $merged;
    }

    private function write($target,$content) {
        if(@file_put_contents($this->home_dir.$target,$content) !== false) {
            @chmod($target,0666);
        } else {
            $this->error(sprintf('Cannot write file: %s', $this->home_dir.$target));
        }
    }
    
    private function precheking(){
        
        if(!is_null($this->home_dir) && !file_exists($this->home_dir)){
            $this->error(sprintf('The <strong>%s</strong> does not exist.', $this->home_dir));
        }
        
        if(!is_null($this->merged_dir) && !file_exists($this->home_dir.$this->merged_dir)){
            $this->error(sprintf('The <strong>%s</strong> does not exist.', $this->home_dir.$this->merged_dir));
        }
        
        if(!empty($this->js_dirs) && is_array($this->js_dirs)){
            foreach($this->js_dirs as $js_dir){
                if(!file_exists($this->home_dir.$js_dir)){
                    $this->error(sprintf('The <strong>%s</strong> does not exist.', $this->home_dir.$js_dir));
                }
            }
        }else{
            $this->error('Var <strong>$js_dirs</strong> not array');
        }
        
        if(!empty($this->css_dirs) && is_array($this->css_dirs)){
            foreach($this->css_dirs as $css_dir){
                if(!file_exists($this->home_dir.$css_dir)){
                    $this->error(sprintf('The <strong>%s</strong> does not exist.', $this->home_dir.$css_dir));
                }
            }
        }else{
            $this->error('Var <strong>$css_dirs</strong> not array');
        }
    }
    
    private function error($msg){
        die(__CLASS__ . " error: <br />" . $msg);
    }

}

?>