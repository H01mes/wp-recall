<?php

class PrimeUser extends PrimeRoles{
    
    public $user_id;
    public $user_role;
    public $default_role = 'member';
    public $user_capabilities;
    
    function __construct($args = false) {
        global $user_ID;
        
        if(!isset($args['user_id']))
            $args['user_id'] = $user_ID;
        
        $this->init_properties($args);
        
        parent::__construct();

        $this->user_role = $this->user_id? $this->get_user_role($this->user_id): 'guest';
        
        $this->user_capabilities = $this->get_capabilities($this->user_role);
        
    }
    
    function init_properties($args){
        
        $properties = get_class_vars(get_class($this));

        foreach ($properties as $name=>$val){
            if(isset($args[$name])) $this->$name = $args[$name];
        }
        
    }
    
    function get_user_role($user_id){
        if(!$user_id) return 'guest';
        return ($role = get_user_meta($user_id, 'pfm_role', 1))? $role: $this->default_role;
    }
    
    function is_can($action){
        
        if(!isset($this->user_capabilities[$action])) return false;
        
        return apply_filters('pfm_is_can',$this->user_capabilities[$action],$action,$this->user_id);
        
    }
    
    function get_user_rolename($user_id){
        $roleID = $this->get_user_role($user_id);
        $role = $this->get_role($roleID);
        return $role['name'];
    }
    
    function is_role($roleName){
        
        if(is_array($roleName)){
            if(in_array($this->user_role, $roleName)) return true;
        }else{
            if($roleName = $this->user_role) return true;
        }
        
        return false;
        
    }
    
}

