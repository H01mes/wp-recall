<?php
include_once 'classes/rcl-group-widget.php';

add_action('init','rcl_group_add_primary_widget');
function rcl_group_add_primary_widget(){
    rcl_group_register_widget('Group_Primary_Widget');
}

class Group_Primary_Widget extends Rcl_Group_Widget {

    function Group_Primary_Widget() {
        parent::__construct( array(
            'widget_id'=>'group-primary-widget',
            'widget_place'=>'sidebar',
            'widget_title'=>__('Control panel','rcl')
            )
        );
    }

    function options($instance){

        $defaults = array('title' => __('Control panel','rcl'));
        $instance = wp_parse_args( (array) $instance, $defaults );

        echo '<label>'.__('Title','rcl').'</label>'
                . '<input type="text" name="'.$this->field_name('title').'" value="'.$instance['title'].'">';

    }

    function widget($args) {
        extract( $args );

        global $rcl_group,$user_ID;

        if(!$user_ID||rcl_is_group_can('admin')) return false;

        if($rcl_group->current_user=='banned') return false;

        if(rcl_is_group_can('reader')){

            echo $before;

                echo '<form method="post">'
                   . '<input type="submit" class="recall-button" name="group-submit" value="'.__('Leave group','rcl').'">'
                    . '<input type="hidden" name="group-action" value="leave">'
                    . wp_nonce_field( 'group-action-' . $user_ID,'_wpnonce',true,false )
               . '</form>';

            echo $after;

        }else{

            if(rcl_get_group_option($rcl_group->term_id,'can_register')){

                echo $before;

                if($rcl_group->group_status=='open'){
                    echo '<form method="post">'
                        . '<input type="submit" class="recall-button" name="group-submit" value="'.__('Join group','rcl').'">'
                        . '<input type="hidden" name="group-action" value="join">'
                        . wp_nonce_field( 'group-action-' . $user_ID,'_wpnonce',true,false )
                    . '</form>';
                }

                if($rcl_group->group_status=='closed'){

                    $requests = rcl_get_group_option($rcl_group->term_id,'requests_group_access');

                    if($requests&&false!==array_search($user_ID, $requests)){

                        echo '<h3 class="title-widget">'.__('The request for access sent','rcl').'</h3>';

                    }else{

                        echo '<form method="post">'
                            . '<input type="submit" class="recall-button" name="group-submit" value="'.__('Apply for membership','rcl').'">'
                            . '<input type="hidden" name="group-action" value="ask">'
                            . wp_nonce_field( 'group-action-' . $user_ID,'_wpnonce',true,false )
                        . '</form>';

                    }
                }

                echo $after;

            }
        }


    }

}

add_action('init','rcl_group_add_users_widget');
function rcl_group_add_users_widget(){
    rcl_group_register_widget('Group_Users_Widget');
}

class Group_Users_Widget extends Rcl_Group_Widget {

    function Group_Users_Widget() {
        parent::__construct( array(
            'widget_id'=>'group-users-widget',
            'widget_place'=>'sidebar',
            'widget_title'=>__('Users','rcl')
            )
        );
    }

    function widget($args,$instance) {

        global $rcl_group,$user_ID;

        extract( $args );

        $user_count = (isset($instance['count']))? $instance['count']: 12;

        echo $before;
        echo rcl_group_users($user_count);
        echo rcl_get_group_link('rcl_get_group_users',__('All users','rcl'));

        echo $after;
    }

    function options($instance){

        $defaults = array('title' => __('Users','rcl'),'count' => 12);
        $instance = wp_parse_args( (array) $instance, $defaults );

        echo '<label>'.__('Title','rcl').'</label>'
                . '<input type="text" name="'.$this->field_name('title').'" value="'.$instance['title'].'">';
        echo '<label>'.__('Amount','rcl').'</label>'
                . '<input type="number" name="'.$this->field_name('count').'" value="'.$instance['count'].'">';
    }

}

add_action('init','rcl_group_add_publicform_widget');
function rcl_group_add_publicform_widget(){
    rcl_group_register_widget('Group_PublicForm_Widget');
}

class Group_PublicForm_Widget extends Rcl_Group_Widget {

    function Group_PublicForm_Widget() {
        parent::__construct( array(
            'widget_id'=>'group-public-form-widget',
            'widget_title'=>__('Form of the publication','rcl'),
            'widget_place'=>'content',
            'widget_type'=>'hidden'
            )
        );
    }

    function widget($args,$instance) {

       if(!rcl_is_group_can('author')) return false;

        extract( $args );

        global $rcl_group;

        $typeform = (isset($instance['type_form']))? $instance['type_form']: 0;

        echo $before;

        echo do_shortcode('[public-form post_type="post-group" type_editor="'.$typeform.'" group_id="'.$rcl_group->term_id.'"]');

        echo $after;
    }

    function options($instance){

        $defaults = array('title' => __('Form of the publication','rcl'));
        $instance = wp_parse_args( (array) $instance, $defaults );

        echo '<label>'.__('Title','rcl').'</label>'
                . '<input type="text" name="'.$this->field_name('title').'" value="'.$instance['title'].'">';
        echo '<label>'.__('Type form','rcl').'</label>'
                . '<select name="'.$this->field_name('type_form').'">'
                . '<option value="0" '.selected(0,$instance['type_form'],false).'>WP-Recall</option>'
                . '<option value="1" '.selected(1,$instance['type_form'],false).'>WordPress</option>'
                . '</select>';

    }

}

add_action('init','rcl_group_add_categorylist_widget');
function rcl_group_add_categorylist_widget(){
    rcl_group_register_widget('Group_CategoryList_Widget');
}

class Group_CategoryList_Widget extends Rcl_Group_Widget {

    function Group_CategoryList_Widget() {
        parent::__construct( array(
            'widget_id'=>'group-category-list-widget',
            'widget_title'=>__('Categories Content Group','rcl'),
            'widget_place'=>'unuses'
            )
        );
    }

    function options($instance){

        $defaults = array('title' => __('Categories Content Group','rcl'));
        $instance = wp_parse_args( (array) $instance, $defaults );

        echo '<label>'.__('Title','rcl').'</label>'
                . '<input type="text" name="'.$this->field_name('title').'" value="'.$instance['title'].'">';

    }

    function widget($args) {

        extract( $args );

        global $rcl_group;

        $category = rcl_get_group_category_list();
        if(!$category) return false;

        echo $before;
        echo $category;
        echo $after;

    }

}

add_action('init','rcl_group_add_admins_widget');
function rcl_group_add_admins_widget(){
    rcl_group_register_widget('Group_Admins_Widget');
}

class Group_Admins_Widget extends Rcl_Group_Widget {

    function Group_Admins_Widget() {
        parent::__construct( array(
            'widget_id'=>'group-admins-widget',
            'widget_place'=>'sidebar',
            'widget_title'=>__('Management','rcl')
            )
        );
    }

    function widget($args,$instance) {

        global $rcl_group,$user_ID;

        extract( $args );

        $user_count = (isset($instance['count']))? $instance['count']: 12;

        echo $before;
        echo $this->get_group_administrators($user_count);
        echo $after;
    }

    function add_admins_query($query){
        global $rcl_group;
        $query['join'][] = "LEFT JOIN ".RCL_PREF."groups_users AS groups_users ON users.ID=groups_users.user_id";
        $query['where'][] = "(groups_users.user_role IN ('admin','moderator') AND groups_users.group_id='$rcl_group->term_id') OR (users.ID='$rcl_group->admin_id')";
        $query['group'] = "users.ID";

        return $query;
    }

    function get_group_administrators($number){
        global $rcl_group;
        if(!$rcl_group) return false;
        add_filter('rcl_users_query',array($this,'add_admins_query'));
        return rcl_get_userlist(array('number'=>$number,'template'=>'mini'));
    }

    function options($instance){

        $defaults = array('title' => __('Management','rcl'),'count' => 12);
        $instance = wp_parse_args( (array) $instance, $defaults );

        echo '<label>'.__('Title','rcl').'</label>'
                . '<input type="text" name="'.$this->field_name('title').'" value="'.$instance['title'].'">';

    }

}

add_action('init','rcl_group_add_posts_widget');
function rcl_group_add_posts_widget(){
    rcl_group_register_widget('Group_Posts_Widget');
}

class Group_Posts_Widget extends Rcl_Group_Widget {

    function Group_Posts_Widget() {
        parent::__construct( array(
            'widget_id'=>'group-posts-widget',
            'widget_place'=>'unuses',
            'widget_title'=>__('Group posts','rcl')
            )
        );
    }

    function widget($args,$instance) {

        global $rcl_group,$user_ID;

        extract( $args );

        echo $before;

        if(have_posts()){ ?>

            <?php while ( have_posts() ): the_post(); ?>
                <div class="post-group">
                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <?php if(!$instance['excerpt']) the_excerpt(); ?>
                </div>
            <?php endwhile; ?>

            <nav class="pagination group">
                <?php if ( function_exists('wp_pagenavi') ): ?>
                    <?php wp_pagenavi(); ?>
                <?php else: ?>
                    <ul class="group">
                        <li class="prev left"><?php previous_posts_link(); ?></li>
                        <li class="next right"><?php next_posts_link(); ?></li>
                    </ul>
                <?php endif; ?>
            </nav>

        <?php }else{ ?>
            <p><?php _e("Publications don't have","rcl"); ?></p>
        <?php }

        echo $after;
    }

    function options($instance){

        $defaults = array('title' => __('Group posts','rcl'),'count' => 12);
        $instance = wp_parse_args( (array) $instance, $defaults );

        echo '<label>'.__('Title','rcl').'</label>'
                . '<input type="text" name="'.$this->field_name('title').'" value="'.$instance['title'].'">';
        echo '<label>'.__('Краткое содержимое','rcl').'</label>'
                . '<select name="'.$this->field_name('excerpt').'">'
                . '<option value="0" '.selected(0,$instance['excerpt'],false).'>Выводить</option>'
                . '<option value="1" '.selected(1,$instance['excerpt'],false).'>Не выводить</option>'
                . '</select>';

    }

}

