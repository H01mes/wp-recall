<?php

function rcl_group_migrate_old_data(){
    global $wpdb;

    $groups = $wpdb->get_results("SELECT terms.term_id "
            . "FROM wp_terms AS terms "
            . "INNER JOIN wp_term_taxonomy AS term_taxonomy ON terms.term_id=term_taxonomy.term_id "
            . "WHERE term_taxonomy.taxonomy = 'groups' AND term_taxonomy.parent = '0' ");

    $group_users = $wpdb->get_results("SELECT user_id,meta_value FROM $wpdb->usermeta WHERE meta_key LIKE 'user_group_%'");
    $group_options = $wpdb->get_results("SELECT group_id,option_value FROM ".RCL_PREF."groups_options");

    $options = array();
    foreach($group_options as $option){
        $options[$option->group_id] = unserialize($option->option_value);
    }

    //print_r($options);exit;

    $users = array();
    foreach($group_users as $user){
        $users[$user->meta_value][] = $user->user_id;
    }

    $grdata = array();
    foreach($groups as $group){

        $grdata[$group->term_id]['users'] = ($users[$group->term_id])? $users[$group->term_id]: 0;
        $grdata[$group->term_id]['admin_id'] = ($options[$group->term_id]['admin'])? $options[$group->term_id]['admin']: '';
        $grdata[$group->term_id]['avatar_id'] = ($options[$group->term_id]['avatar'])? $options[$group->term_id]['avatar']: '';
        $grdata[$group->term_id]['category'] = ($options[$group->term_id]['tags'])? $options[$group->term_id]['tags']: '';
        $grdata[$group->term_id]['status'] = ($options[$group->term_id]['private'])? 'closed': 'open';
    }

    //print_r($grdata);exit;

    $date = current_time('mysql');

    foreach($grdata as $group_id=>$data){

        $cnt_users = ($data['users'])? count($data['users']): 0;

        $wpdb->insert(
            RCL_PREF.'groups',
            array(
                'ID'=>$group_id,
                'admin_id'=>$data['admin_id'],
                'group_users'=>$cnt_users,
                'group_status'=>$data['status'],
                'group_date'=>$date
            )
        );

        if($data['avatar_id']){
            $wpdb->insert(
                RCL_PREF.'groups_options',
                array(
                    'group_id'=>$group_id,
                    'option_key'=>'avatar_id',
                    'option_value'=>$data['avatar_id']
                )
            );
        }

        if($data['category']){
            $wpdb->insert(
                RCL_PREF.'groups_options',
                array(
                    'group_id'=>$group_id,
                    'option_key'=>'category',
                    'option_value'=>serialize($data['category'])
                )
            );
        }

        $wpdb->insert(
                RCL_PREF.'groups_options',
                array(
                    'group_id'=>$group_id,
                    'option_key'=>'can_register',
                    'option_value'=>1
                )
            );

        $wpdb->insert(
                RCL_PREF.'groups_options',
                array(
                    'group_id'=>$group_id,
                    'option_key'=>'default_role',
                    'option_value'=>'author'
                )
            );

        if($data['users']){
            $sql = array();
            foreach($data['users'] as $user_id){
                $sql[] = "($group_id, $user_id, 'author', '$date', 0)";
            }

            $wpdb->query("INSERT INTO ".RCL_PREF."groups_users (group_id, user_id, user_role, user_date, status_time) VALUES ".implode(',',$sql));
        }

    }
}

