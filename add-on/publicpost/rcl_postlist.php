<?php


class Rcl_Postlist {

    public $id;
    public $name;
    public $posttype;

    /**
     * @param $id
     * @param $posttype
     * @param $name
     * @param array $args
     */
    function __construct( $id, $posttype, $name, $args = array() ){

        $this->id = $id;
        $this->posttype = $posttype;
        $this->name = $name;

        $order = ( isset( $args['order'] ) && ! empty( $args['order'] ) ) ? $args['order'] : 10;
        $this->class = ( isset( $args['class'] ) && ! empty( $args['class'] ) ) ? $args['class'] : 'fa-list';

        add_filter( 'posts_button_rcl', array( $this, 'add_postlist_button' ), $order, 2 );
        add_filter( 'posts_block_rcl', array( $this, 'add_postlist_block' ), $order, 2 );
    }
    
    function add_postlist_button( $button ){
            $status = ! $button ? 'active' : '';
            $button .= ' <a href="#" id="posts_'.$this->id.'" class="child_block_button '.$status.'"><i class="fa '.$this->class.'"></i>'.$this->name.'</a> ';
            return $button;
    }
    
    function add_postlist_block($posts_block,$author_lk){
            if(!$posts_block) $status = 'active';
            else $status = '';
            $posts_block .= '<div class="posts_'.$this->id.'_block recall_child_content_block '.$status.'">';	
            $posts_block .= $this->get_postslist($author_lk);
            $posts_block .= '</div>';
            return $posts_block;
    }

    function get_postslist( $author_lk ){
            //echo $author_lk;
            global $wpdb;
            $table = $wpdb->prefix . 'posts';
            $rayt = array();
            
            $posts = $wpdb->get_results("SELECT * FROM $table WHERE post_author='$author_lk' AND post_type='$this->posttype' AND post_status NOT IN ('draft','auto-draft') ORDER BY post_date DESC LIMIT 20");
            //print_r($posts);
            
            $posts_block = '';
            
            if($posts){
                $p_list = '';
                    $rayting = false;
                    if(function_exists('get_rayting_block_rcl')){
                            $b=0;
                            foreach((array)$posts as $p){if(++$b>1) $p_list .= ',';$p_list .= $p->ID;}	
                            $rayt_p = $wpdb->get_results("SELECT * FROM ".RCL_PREF."total_rayting_posts WHERE post_id IN ($p_list)");		
                            foreach((array)$rayt_p as $r){
                                if(!isset($r->post_id)) continue;
                                $rayt[$r->post_id] = $r->total;                               
                            }
                            $rayting = true;
                    }
               
                    $posts_block .= '
                    <h3>'.__('Опубликованные').' '.$this->name.'</h3>';
                    $posts_block .= get_pagenavi_ajax_rcl($author_lk,$this->posttype);
                    $posts_block .= '<table class="publics-table-rcl">
                    <tr>
                        <td>'.__('Дата').'</td>
                        <td>'.__('Заголовок').'</td>
                        <td>'.__('Статус').'</td>';
                            //if($user_ID==$author_lk) $posts_block .= '<td>Ред.</td>';
                            $posts_block .= '</tr>';
                    foreach($posts as $post){
                            if($post->post_status=='pending') $status = '<span class="pending">'.__('на утв.').'</span>';
                            elseif($post->post_status=='trash') $status = '<span class="pending">'.__('удален').'</span>';
                            else $status = '<span class="publish">'.__('опубл.').'</span>';
                            $posts_block .= '<tr>
                            <td width="50">'.mysql2date('d.m.y', $post->post_date).'</td>'
                                    . '<td>';
                            $content = '<a target="_blank" href="'.$post->guid.'">'.$post->post_title.'</a>';
                            if($rayting) {
                                $rtng = (isset($rayt[$post->ID]))? $rayt[$post->ID]: 0;
                                $content .= ' '.get_rayting_block_rcl($rtng);                              
                            }
                            $content = apply_filters('content_postslist',$content);
                            $posts_block .= $content;
                            $posts_block .= '</td>'
                                    . '<td>'.$status.'</td>';
                            //if($user_ID==$author_lk) $posts_block .= '<td><a target="_blank" href="'.get_permalink($rcl_options['public_form_page_rcl']).'?rcl-post-edit='.$post->ID.'">Ред.</a></td>';
                            $posts_block .= '</tr>';
                    }
                    $posts_block .= '</table>';
            }else{
                    $posts_block .= '<h3>'.$this->name.' '.__('еще не публиковались').'</h3>';
            }

            return $posts_block;
    }
}
