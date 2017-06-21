<div class="prime-forum-content">
    
    <?php if(pfm_have_posts()): ?>
    
        <?php pfm_the_topic_manager(); ?>
    
        <div class="prime-topics-header">
            <span class="topic_count"><?php pfm_the_post_count(); ?> ответ(a) в теме</span>
            <?php pfm_page_navi(); ?>
        </div>
    
        <?php do_action('pfm_topic_loop_before'); ?>
    
        <div class="prime-posts">
    
        <?php while ( pfm_get_next('post') ) : ?>
            
            <div id="topic-post-<?php pfm_post_field('post_id'); ?>" class="<?php pfm_the_post_classes(); ?>">
                <div class="prime-topic-left">
                    
                    <?php pfm_the_author_manager(); ?>
                    
                    <div class="prime-author-avatar">
                        <a href="<?php echo get_author_posts_url(pfm_post_field('user_id',0)); ?>" title="В кабинет"><?php pfm_author_avatar(); ?></a>
                    </div>
                    <div class="prime-author-metabox">
                        <div class="prime-author-meta prime-author-name"><?php pfm_the_author_name(); ?></div>
                        <?php do_action('pfm_post_author_metabox'); ?>
                    </div>
                </div>
                <div class="prime-topic-right">
                    <div class="prime-post-top">
                        <div class="prime-count">
                            <span><?php pfm_post_field('post_index'); ?></span>
                            <a href="#topic-post-<?php pfm_post_field('post_id'); ?>" title="Ссылка на сообщение">
                                <i class="fa fa-link" aria-hidden="true"></i>
                            </a>
                        </div>
                        <div class="prime-date">
                            <span class="post-date"><?php echo mysql2date('F j Y', pfm_post_field('post_date',0)) ?></span>
                            <span class="post-time"><?php echo mysql2date('H:i', pfm_post_field('post_date',0)) ?></span>
                        </div>
                        
                        <?php pfm_the_post_manager(); ?>

                    </div>
                    <div class="prime-post-content">
                        <?php pfm_the_post_content(); ?>
                    </div>
                    <div class="prime-post-bottom">
                        <?php pfm_the_post_bottom(); ?>
                    </div>
                </div>
            </div>

        <?php endwhile; ?>
            
        </div>
            
        <?php pfm_page_navi(); ?>
    
        <?php pfm_the_topic_manager(); ?>

    <?php else: ?>
    
        <?php pfm_the_notices(); ?>
        
    <?php endif; ?>

    <?php pfm_the_post_form(); ?>
        
</div>

