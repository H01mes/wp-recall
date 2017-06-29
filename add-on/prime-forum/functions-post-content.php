<?php

add_action('pfm_init','pfm_reset_oembed_filter');
add_action('pfm_pre_ajax_action','pfm_reset_oembed_filter');
function pfm_reset_oembed_filter(){
    remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );
}

add_filter('pfm_the_post_content','pfm_filter_tags_post_content',10);
function pfm_filter_tags_post_content($content){
    
    //$content = force_balance_tags($content);
    
    $content = esc_html($content);
    
    $content = str_replace(
        array(
            '&lt;blockquote&gt;',
            '&lt;/blockquote&gt;',
            '&lt;strong&gt;',
            '&lt;/strong&gt;',
            '&lt;b&gt;',
            '&lt;/b&gt;',
            '&lt;em&gt;',
            '&lt;/em&gt;',
            '&lt;s&gt;',
            '&lt;/s&gt;',
        ), 
        array(
            '<blockquote>',
            '</blockquote>',
            '<strong>',
            '</strong>',
            '<b>',
            '</b>',
            '<em>',
            '</em>',
            '<s>',
            '</s>',
        ), $content);
    
    $content = preg_replace('/&lt;pre[\s]class=&quot;(.+)&quot;&gt;(.+)&lt;\/pre&gt;/Uuis', '<pre>$2</pre>', $content);
    $content = preg_replace('/&lt;div[\s]class=&quot;sfcode&quot;&gt;(.+)&lt;\/div&gt;/Uuis', '<pre>$1</pre>', $content);

    $content = preg_replace('/&lt;code&gt;(.+)&lt;\/code&gt;/Uuis', '<code>$1</code>', $content);
    $content = preg_replace('/&lt;pre&gt;(.+)&lt;\/pre&gt;/Uuis', '<pre>$1</pre>', $content);
    
    $content = preg_replace('/&lt;del(.+)&gt;(.+)&lt;\/del&gt;/Uuis', '<del>$2</del>', $content);

    return $content;
}

add_filter('pfm_the_post_content','pfm_filter_content',11);
function pfm_filter_content($content){

    preg_match_all('/<pre>(.+)<\/pre>/Uuis', $content, $pres);
    
    if($pres){
        
        foreach( $pres[0] as $k=>$pre ){

            $content = str_replace($pre, '<!--pre'.$k.'-->', $content);

        }
    }
    
    $content = apply_filters('pfm_filter_content_without_pretags',$content);

    if($pres){
        
        foreach( $pres[0] as $k=>$pre ){

            $content = str_replace('<!--pre'.$k.'-->', $pre, $content);

        }
    }
    
    return $content;
}

add_filter('pfm_the_post_content','pfm_add_smilies_post_content',12);
function pfm_add_smilies_post_content($content){
    
    if(function_exists('convert_smilies')) 
        $content = str_replace( 'style="height: 1em; max-height: 1em;"', '', convert_smilies( $content ) );
    
    return $content;
}

add_filter('pfm_filter_content_without_pretags','pfm_filter_imgs',10);
function pfm_filter_imgs($content){

    preg_match_all('/&lt;img(.+)src=&quot;(.+)&quot;(.+)&gt;/Ui', $content, $imgs);

    if($imgs[0]){

        foreach( $imgs[2] as $k => $url ){
            
            $content = str_replace($imgs[0][$k], '<img src="'.trim($url,'&quot;').'">', $content);

        }
    
    }
    
    return $content;
    
}

add_filter('pfm_filter_content_without_pretags','pfm_filter_urls',11);
function pfm_filter_urls($content){

    preg_match_all("/(\s|^|>|])(https?:[_a-z0-9\/\.\-#?=&]+)/ui", $content, $urls);
    
    if($urls[0]){

        $oembedSupport = (pfm_get_option('support-oembed') && function_exists('wp_oembed_get'))? true: false;
        
        $sortStrings = $urls[2];
        
        usort($sortStrings, 'pfm_sort_array_by_string');

        foreach( $sortStrings as $k => $url ){
            
            if($oembedSupport){
                
                $oembed = wp_oembed_get($url,array('width'=>400,'height'=>400,'discover' => false));
                
                if($oembed){
                    $content = str_replace($url,$oembed,$content);
                    continue;
                }
            
            }
            
            if(pfm_is_can('post_create')){

                $replace = ' <a href="'.$url.'" target="_blank" rel="nofollow">'.$url.'</a>';

            }else{

                $replace = pfm_get_notice(__('Вы не можете просматривать опубликованные ссылки'),'warning');

            }

            $content = preg_replace('|[^">]'.$url.'|u', ' <a href="'.$url.'" target="_blank" rel="nofollow">'.$url.'</a>', $content);

        }
    
    }
    
    
    return $content;
}

function pfm_sort_array_by_string($a, $b){
    if (strlen($a) < strlen($b)) { return 1; } elseif (strlen($a) == strlen($b)) { return 0; } else { return -1; }
}

add_filter('pfm_filter_content_without_pretags','pfm_filter_links',12);
function pfm_filter_links($content){
    
    preg_match_all('/&lt;a(.+)href=([^"\s]+)(.+)&gt;(.+)&lt;\/a&gt;/iUus', $content, $links);
    
    if($links[0]){
        
        foreach( $links[0] as $k=>$link ){
            
            if(pfm_is_can('post_create')){
                
                $href = trim(str_replace(array('"','quot;'),'',$links[3][$k]),'&');
                
                $replace = '<a href="'.$href.'" target="_blank" rel="nofollow">'.$links[4][$k].'</a>';

            }else{

                $replace = pfm_get_notice(__('Вы не можете просматривать опубликованные ссылки'),'warning');

            }

            $content = str_replace($link, $replace, $content);

        }
    
    }
    
    return $content;
}

add_filter('pfm_filter_content_without_pretags','wpautop',14);
add_filter('pfm_filter_content_without_pretags','pfm_do_shortcode',15);

add_filter('pfm_the_post_content','pfm_add_topic_meta_box',20);
function pfm_add_topic_meta_box($content){
    global $PrimeTopic,$PrimePost;
    
    if($PrimePost->post_index != 1) return $content;
    
    $content = pfm_get_topic_meta_box($PrimeTopic->topic_id) . $content;
    
    return $content;
}

