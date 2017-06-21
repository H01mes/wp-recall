<?php

add_shortcode('prime-forum','pfm_get_forum_content');
function pfm_get_forum_content(){
    global $active_addons;
    
    $content = pfm_get_template_content();
    
    return $content;
    
}

function pfm_get_template_content(){

    $ThemeID = get_option('rcl_pforum_template');
    
    $theme = rcl_get_addon($ThemeID);
    
    if(!$theme) return false;
    
    rcl_dialog_scripts();
    
    $content = '<div id="prime-forum" class="'.pfm_class_forum().'">';
    
    $content .= pfm_get_primary_manager();
    
    $content .= rcl_get_include_template('pfm-header.php',$theme['path']);
    
    if(pfm_is_search()){
        
        $content .= rcl_get_include_template('pfm-search.php',$theme['path']);
        
    }else if(pfm_is_home()){
        
        $content .= rcl_get_include_template('pfm-home.php',$theme['path']);
        
    }else if(pfm_is_group()){
        
        $content .= rcl_get_include_template('pfm-group.php',$theme['path']);
        
    }else if(pfm_is_forum()){
        
        $content .= rcl_get_include_template('pfm-forum.php',$theme['path']);
        
    }else if(pfm_is_topic()){
        
        $content .= rcl_get_include_template('pfm-topic.php',$theme['path']);
        
    }
    
    $content .= rcl_get_include_template('pfm-footer.php',$theme['path']);
    
    $content .= '</div>';
    
    return $content;
}

function pfm_class_forum(){
    global $user_ID,$PrimeQuery;
    
    $classes = array();
    
    if(pfm_is_search()){
        
        $classes[] = 'prime-search-page';
        
    }else if(pfm_is_home()){
        
        $classes[] = 'prime-home-page';
        
    }else if(pfm_is_group()){
        
        $classes[] = 'prime-group-page prime-group-'.$PrimeQuery->object->group_id;
        
    }else if(pfm_is_forum()){
        
        $classes[] = 'prime-forum-page prime-forum-'.$PrimeQuery->object->forum_id;
        
        if($PrimeQuery->object->forum_closed){
            $classes[] = 'forum-closed';
        }else{
            $classes[] = 'forum-opened';
        }
        
    }else if(pfm_is_topic()){
        
        $classes[] = 'prime-topic-page prime-topic-'.$PrimeQuery->object->topic_id;
        
        if($PrimeQuery->object->topic_closed){
            $classes[] = 'topic-closed';
        }else{
            $classes[] = 'topic-opened';
        }
        
        if($PrimeQuery->object->topic_fix){
            $classes[] = 'topic-fixed';
        }
        
    }
    
    if($user_ID){

        $classes[] = 'prime-user-view';
        
    }else{
        
        $classes[] = 'prime-guest-view';
        
    }
    
    return implode(' ',$classes);
} 

function pfm_the_template($name){
    
    $ThemeID = get_option('rcl_pforum_template');
    
    $theme = rcl_get_addon($ThemeID);
    
    rcl_include_template($name.'.php',$theme['path']);
}

function pfm_the_notices(){
    global $PrimeQuery;
    
    if($PrimeQuery->errors){
        
        foreach($PrimeQuery->errors as $type => $notices){
            foreach($notices as $notice){
                echo pfm_get_notice($notice, $type);
            }
        }
        
    }else{
        
        if($PrimeQuery->is_search){
            
            echo pfm_get_notice(__('Ничего не найдено'));
            
        }else if($PrimeQuery->is_frontpage){
            
            echo pfm_get_notice(__('Группы не были найдены'));

        }else if($PrimeQuery->is_group){
            
            if(!$PrimeQuery->object){
                echo pfm_get_notice(__('Группа не найдена'));
                return;
            }
            
            echo pfm_get_notice(__('Форумы не были найдены'));
            
        }else if($PrimeQuery->is_forum){
            
            if(!$PrimeQuery->object){
                echo pfm_get_notice(__('Форум не найден'));
                return;
            }
            
            echo pfm_get_notice(__('Пока не создано ни одной темы'));
            
        }else if($PrimeQuery->is_topic){
            
            if(!$PrimeQuery->object){
                echo pfm_get_notice(__('Тема не найдена'));
                return;
            }
            
            echo pfm_get_notice(__('Тема не содержит ни одного сообщения'));
            
        }
        
    }
}

function pfm_get_notice($notice, $type = 'notice'){
    
    $content = '<div class="prime-notice">';    
        $content .= '<div class="notice-box type-'.$type.'">';
            $content .= $notice;
        $content .= '</div>';
    $content .= '</div>';
    
    return $content;
    
}

function pfm_the_vititors(){
    global $PrimeQuery;
    
    $visitors = pfm_get_visitors();
    
    if(!$visitors) return false;

    $visits = array();
    foreach($visitors as $visitor){
        $visits[] = '<a href="'.get_author_posts_url($visitor->user_id).'">'.$visitor->display_name.'</a>';
    }
    
    $content = '<div class="prime-visitors">';
    
        if($PrimeQuery->is_group){
            $content .= __('Группу просматривают');
        }else if($PrimeQuery->is_forum){
            $content .= __('Форум просматривают');
        }else if($PrimeQuery->is_topic){
            $content .= __('Тему просматривают');
        }else{
            $content .= __('Сейчас на форуме');
        }
        
        $content .= ': ';

        if($visits)
            $content .= implode(', ',$visits);
        else
            $content .= __('Никого нет');

    $content .= '</div>';
    
    echo $content;
    
}

function pfm_the_search_form(){ 
    global $PrimeQuery;?>

    <form action="<?php echo pfm_get_home_url() ?>">
        <input name="fs" value="<?php echo ($PrimeQuery->vars['search_vars'])? $PrimeQuery->vars['search_vars']: 'Поиск по форуму'; ?>" onblur="if (this.value == '') {this.value = 'Поиск по форуму';}" onfocus="if (this.value == 'Поиск по форуму') {this.value = '';}" type="text">
        <?php if(pfm_is_search()): ?>
        
            <?php if($PrimeQuery->vars['pfm-group']): ?>
            
                <input type="hidden" name="pfm-group" value="<?php echo $PrimeQuery->vars['pfm-group']; ?>">

             <?php elseif($PrimeQuery->vars['pfm-forum']): ?>

                <input type="hidden" name="pfm-forum" value="<?php echo $PrimeQuery->vars['pfm-forum']; ?>">

             <?php endif; ?>
        
        <?php else: ?>
        
            <?php if($PrimeQuery->is_group && $PrimeQuery->object->group_id){ ?>
                
                <input type="hidden" name="pfm-group" value="<?php echo $PrimeQuery->object->group_id; ?>">
            
            <?php }else if($PrimeQuery->is_forum || $PrimeQuery->is_topic && $PrimeQuery->object->forum_id){ ?>
            
                <input type="hidden" name="pfm-forum" value="<?php echo $PrimeQuery->object->forum_id; ?>">
            
            <?php } ?>
        
        <?php endif; ?>
        <button id="search-image" class="prime-search-button" type="submit" value="">
            <i class="fa fa-search" aria-hidden="true"></i>
        </button>
    </form>

<?php }

function pfm_the_breadcrumbs(){
    global $PrimeQuery;
    
    $object = $PrimeQuery->object; ?>

    <div class="prime-breadcrumbs">
        
        <?php if(pfm_is_home()): ?>
        
            <span>Главная</span>
            
        <?php else: ?>

            <?php $homeUrl = pfm_get_home_url(); ?>

            <span><a href="<?php echo $homeUrl; ?>">Главная</a></span>

            <?php if(pfm_is_search()): ?>
            
                <?php if($PrimeQuery->vars['pfm-group']): ?>
            
                    <span>
                        <a href="<?php echo pfm_get_group_permalink($PrimeQuery->vars['pfm-group']); ?>">
                            <?php  echo pfm_get_group_field($PrimeQuery->vars['pfm-group'],'group_name'); ?>
                        </a>
                    </span>
            
                 <?php elseif($PrimeQuery->vars['pfm-forum']): ?>
            
                    <span>
                        <a href="<?php echo pfm_get_forum_permalink($PrimeQuery->vars['pfm-forum']); ?>">
                            <?php  echo pfm_get_forum_field($PrimeQuery->vars['pfm-forum'],'forum_name'); ?>
                        </a>
                    </span>
            
                 <?php endif; ?>

                <span>Поиск: <?php echo $PrimeQuery->vars['search_vars'] ?></span>

            <?php else: ?>
                
                <?php if($object && $object->group_id): ?>

                <?php if(pfm_is_group()): ?>

                    <span><?php echo $object->group_name; ?></span>

                <?php else: ?>

                    <span><a href="<?php echo pfm_get_group_permalink($object->group_id); ?>"><?php echo $object->group_name; ?></a></span>

                    <?php if(pfm_is_forum()): ?>

                        <span><?php echo $object->forum_name; ?></span>

                    <?php else: ?>

                        <span><a href="<?php echo pfm_get_forum_permalink($object->forum_id); ?>"><?php echo $object->forum_name; ?></a></span>

                        <?php if(pfm_is_topic()): ?>

                            <span><?php echo $object->topic_name; ?></span>

                        <?php else: ?>

                            <span><a href="<?php echo pfm_get_topic_permalink($object->topic_id); ?>"><?php echo $object->topic_name; ?></a></span>

                        <?php endif; ?>

                    <?php endif; ?>

                <?php endif; ?>
                            
                <?php endif; ?>
                        
            <?php endif; ?>
            
        <?php endif; ?>

    </div>

    <?php
}

function pfm_page_navi(){
    
    $Navi = new PrimePageNavi();
        
    echo $Navi->pagenavi();
 
}

