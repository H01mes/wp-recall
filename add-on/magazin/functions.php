<?php
include_once 'functions/core.php';
include_once 'functions/init.php';

function get_button_cart_rcl($product_id){
    if(get_post_meta($product_id, 'outsale', 1)) return false;

    if(get_post_meta($product_id, 'availability_product', 1)=='empty'){ //если товар цифровой
        $button = '<div class="cart-button">'.get_button_rcl('В корзину','#',array('icon'=>false,'class'=>'add_basket add_to_cart','attr'=>'data-product='.$product_id)).'</div>';
    }else{
        if($rmag_options['products_warehouse_recall']==1){
            $amount = get_post_meta($product_id, 'amount_product', 1);
            if($amount>0||$amount==false){
                $button = '<div class="cart-button">'.get_button_rcl('В корзину','#',array('icon'=>false,'class'=>'add_basket add_to_cart','attr'=>'data-product='.$product_id)).'</div>';
            }
        }else{
            $button = '<div class="cart-button">'.get_button_rcl('В корзину','#',array('icon'=>false,'class'=>'add_basket add_to_cart','attr'=>'data-product='.$product_id)).'</div>';
        }
    }

    $button = apply_filters('cart_button_rcl',$button,$product_id);

    return $button;
}

add_filter('the_content','add_block_related_products',70);
function add_block_related_products($content){
	global $rmag_options,$post;
	if($rmag_options['sistem_related_products']!=1) return $content;
	if($post->post_type!='products')return $content;
	$related_prodcat = get_post_meta($post->ID,'related_products_recall',1);
	if(!$related_prodcat) return $content;

	$args = array(
		'numberposts'     => $rmag_options['size_related_products'],
		'orderby'         => 'rand',
		'post_type'       => 'products',
		'tax_query' 	  => array(
			array(
				'taxonomy'=>'prodcat',
				'field'=>'id',
				'terms'=> $related_prodcat
				)
			)
	);

	$related_products = get_posts($args);

	if(!$related_products) return $content;

	$content .= '<div class="related-products prodlist">';
	$title_related = $rmag_options['title_related_products_recall'];
	if($title_related) $content .= '<h3>'.$title_related.'</h3>';

	foreach($related_products as $post){ setup_postdata($post);
		$content .= get_include_template_rcl('product-slab.php',__FILE__);
	}
        wp_reset_query();

	$content .= '</div>';

	return $content;
}

add_filter('the_content','add_gallery_product_recall',1);
function add_gallery_product_recall($content){
global $post;
	if(get_post_type($post->ID)=='products'){
		if(get_post_meta($post->ID, 'recall_slider', 1)!=1||!is_single()) return $content;

		if(!class_exists( 'Attachments' )){
		$postmeta = get_post_meta($post->ID, 'children_prodimage', 1);

			if($postmeta){
                            add_bxslider_scripts();
				$values = explode(',',$postmeta);

				$gallery = '<ul class="rcl-gallery">';
				foreach((array) $values as $children ){
					$large = wp_get_attachment_image_src( $children, 'large' );
					$gallery .= '<li><a class="fancybox" href="'.$large[0].'"><img src="'.$large[0].'"></a></li>';
					$thumbs[] = $large[0];
				}
				$gallery .= '</ul>';
				if(count($thumbs)>1){
					$gallery .= '<div id="bx-pager">';
						foreach($thumbs as $k=>$src ){
							$gallery .= '<a data-slide-index="'.$k.'" href=""><img src="'.$src.'" /></a>';
						}
					$gallery .= '</div>';
				}
			}
			return $gallery.$content;
		}else{
			$attachments = new Attachments( 'attachments_products' );

			if( $attachments->exist() ) :
                            add_bxslider_scripts();
				$num=0;
				$gallery = '<ul class="rcl-gallery">';
			while( $attachments->get() ) :
				$num++;

				$large = wp_get_attachment_image_src( $children, 'large' );
				$gallery .= '<li><a class="fancybox" href="'.$attachments->src( 'full' ).'"><img src="'.$attachments->src( 'thumbnail' ).'"></a></li>';
				$thumbs[] = $large[0];

			endwhile;
				$gallery .= '</ul>';

				$gallery .= '<div id="bx-pager">';
					foreach($thumbs as $k=>$src ){
						$gallery .= '<a data-slide-index="'.$k.'" href=""><img src="'.$src.'" /></a>';
					}
				$gallery .= '</div>';
			endif;

			return $gallery.$content;
		}
	} else {
		return $content;
	}
}

//Выводим кнопку корзины в кратком содержании
add_filter('the_excerpt', 'excerpt_product_basket');
function excerpt_product_basket($excerpt){
    global $post;
    if($post->post_type=='products') $excerpt .= get_button_cart_rcl($post->ID);
    return $excerpt;
}

//Выводим категорию товара
function add_wpm_product_meta($content){
	global $post;
	if($post->post_type!='products') return $content;
	$product_cat = get_product_category($post->ID);
	return $product_cat.$content;
}
add_filter('the_content','add_wpm_product_meta',10);

//снимаем товар заказа с резерва
function remove_reserve_product($order_id,$st=0){
	global $rmag_options,$wpdb;
	if($rmag_options['products_warehouse_recall']!=1) return false;

	$orders = get_order($order_id);
        foreach((array)$orders as $sumproduct){
                $reserve = get_post_meta($sumproduct->product,'reserve_product',1);
                if($reserve){ //если резев имеется
                        $reserve = $reserve - "$sumproduct->count";//уменьшаем резерв
                        update_post_meta($sumproduct->product, 'reserve_product', $reserve);
                        if($st){
                                $amount = get_post_meta($sumproduct->product, 'amount_product', 1);
                                $amount = $amount + "$sumproduct->count";//увеличиваем наличие
                                update_post_meta($sumproduct->product, 'amount_product', $amount);
                        }
                }
        }

}

//формируем таблицу содержимого заказа для письма
/*function get_email_table_order_rcl($order_data,$inv_id,$sumprise){

        $n = 1;

        $header = array('№ п/п','Наименование товара','Цена','Количество','Сумма','Статус');

	$table_order .= '<table class="order-form">'
                . '<tr>';
                foreach($header as $name){
                    $table_order .= '<td><b>'.$name.'</b></td>';
                }
        $table_order .= '</tr>';

	foreach((array)$order_data as $order){
            if($order->inv_id==$inv_id){

				$price = apply_filters('cart_price_product',$order->price,$order->product);

                $table_order .= '<tr align="center">'
                        . '<td>'.$n++.'</td>'
                        . '<td>'.get_the_title($order->product).'</td>'
                        . '<td>'.$price.'</td>'
                        . '<td>'.$order->count.'</td>'
                        . '<td>'.$order->price*$order->count.'</td>'
                        . '<td>'.get_status_name($order->status).'</td>'
                    . '</tr>';
            }
	}
	$table_order .= '<tr>'
                    . '<td colspan="4">Сумма заказа</td>'
                    . '<td colspan="2">'.$sumprise.'</td>'
                . '</tr>'
                . '</table>';

	return $table_order;
}*/