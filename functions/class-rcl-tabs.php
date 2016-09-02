<?php

class Rcl_Tabs{
    
    public $id;//идентификатор вкладки
    public $name;//имя вкладки
    public $icon = 'fa-cog';
    public $public = 0;
    public $order = 10;
    public $first = false;
    public $counter = null;
    public $output = 'menu';
    public $supports = array();
    public $content = array();
    public $tab_active = 0;//указание активности вкладки
    public $tab_upload = 0;//указание загрузки содержимого вкладки
    public $use_cache;
    
    function __construct($args){
        global $rcl_options;
        
        $this->init_properties($args);
        
        $type_upload = (isset($rcl_options['tab_newpage']))? $rcl_options['tab_newpage']: 0;
        $this->tab_active = $this->is_view_tab();
        $this->tab_upload = (!$type_upload||$this->tab_active)? true: false;
        $this->use_cache = (isset($rcl_options['use_cache'])&&$rcl_options['use_cache'])? 1: 0;

        do_action('rcl_construct_'.$this->id.'_tab');

    }
    
    function init_properties($args){

        $properties = get_class_vars(get_class($this));

        foreach ($properties as $name=>$val){
            if(!isset($args[$name])) continue;
            $this->$name = $args[$name];
        }
    }

    function add_tab(){
        add_action('rcl_area_tabs',array($this,'print_tab'),$this->order);
        add_action('rcl_area_'.$this->output,array($this,'print_tab_button'),$this->order);
    }
    
    function print_tab(){
        global $user_LK;
        echo $this->get_tab($user_LK);
    }
    
    function print_tab_button(){
        global $user_LK;
        echo $this->get_tab_button($user_LK);
    }
    
    function is_view_tab(){
        
        $view = false;
 
        if(isset($_GET['tab'])){
            $view = ($_GET['tab']==$this->id)? true: false;
        }else{
            if($this->first){
                $view = true;
            }
        }
        
        return $view;
        
    }
    
    function get_tab_content($master_id){
        
        $subtabs = apply_filters('rcl_subtabs',$this->content,$this->id);
    
        include_once 'class-rcl-sub-tabs.php';

        $subtab = new Rcl_Sub_Tabs($subtabs,$this->id);

        if(count($this->content)>1)
            $content = $subtab->get_sub_content($master_id);
        else
            $content = $subtab->get_subtab($master_id);
        
        $content = apply_filters('rcl_tab_'.$this->id,$content);
        
        return $content;
    }
    
    function get_class_button(){
        global $rcl_options;

        $class = false;
        $tb = (isset($rcl_options['tab_newpage']))? $rcl_options['tab_newpage']:false;
        
        if(!$tb) $class = 'block_button';
        
        if(in_array('ajax',$this->supports)){
            if($tb==2){
                $class = 'rcl-ajax';
            }
        }
        
        if($this->tab_active) $class .= ' active';
        
        return $class;
    }
    
    function get_tab_button($master_id){
        global $user_ID;
        
        switch($this->public){
            case 0: if(!$user_ID||$user_ID!=$master_id) return false; break;
            case -1: if(!$user_ID||$user_ID==$master_id) return false; break;
            case -2: if($user_ID&&$user_ID==$master_id) return false; break;
        }

        $link = rcl_format_url(get_author_posts_url($master_id),$this->id);
        
        $datapost = array(
            'callback'=>'rcl_ajax_tab',
            'tab_id'=>$this->id,
            'user_LK'=>$master_id
        );
        
        $name = (isset($this->counter))? sprintf('%s <span class="rcl-menu-notice">%s</span>',$this->name,$this->counter): $this->name;
        
        $html_button = rcl_get_button(
                $name,$link,
                array(
                    'class'=>$this->get_class_button(),
                    'icon'=> ($this->icon)? $this->icon:'fa-cog',
                    'attr'=>'data-post='.rcl_encode_post($datapost)
                )
        );
        
	return sprintf('<span class="rcl-tab-button" data-tab="%s" id="tab-button-%s">%s</span>',$this->id,$this->id,$html_button);

    }
    
    function get_tab($master_id){
        global $user_ID,$rcl_options;
        
        switch($this->public){
            case 0: if(!$user_ID||$user_ID!=$master_id) return false; break;
            case -1: if(!$user_ID||$user_ID==$master_id) return false; break;
            case -2: if($user_ID&&$user_ID==$master_id) return false; break;
        }
        
        if(!$this->tab_upload) return false;
        
        $status = ($this->tab_active) ? 'active':'';
        
        $content = '';

        if($this->use_cache && in_array('cache',$this->supports)){
                                   
            $rcl_cache = new Rcl_Cache();
            
            $protocol  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://':  'https://';
            
            if(!$rcl_options['tab_newpage']){ //если загружаются все вкладки               
                $string = (isset($_GET['tab'])&&$_GET['tab']==$this->id)? $protocol.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']: rcl_format_url(get_author_posts_url($master_id),$this->id);               
            }else{
            
                if(defined( 'DOING_AJAX' ) && DOING_AJAX){
                    $string = rcl_format_url(get_author_posts_url($master_id),$this->id);
                }else{                   
                    $string = $protocol.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
                }
            
            }
            
            $file = $rcl_cache->get_file($string);

            if($file->need_update){

                $content = $this->get_tab_content($master_id);

                $rcl_cache->update_cache($content);
            
            }else{

                $content = $rcl_cache->get_cache();
            
            }

        }else{

            $content = $this->get_tab_content($master_id);
            
            if(!$content) return false;
        
        }
        
        return sprintf('<div id="tab-%s" class="%s_block recall_content_block %s">%s</div>',$this->id,$this->id,$status,$content);

    }

}