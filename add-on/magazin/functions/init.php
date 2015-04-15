<?php
if (is_admin()):
	add_action('admin_head','output_script_admin_recall');	
endif;

rcl_enqueue_style('rmag',__FILE__);

function output_script_admin_recall(){
	wp_enqueue_script( 'jquery' );		
	wp_enqueue_script( 'ajax_admin_magazine_recall', addon_url('js/admin.js', __FILE__) );		
}

function attachments_products( $attachments ){
	$args = array(
		'label' => 'Галлерея товара',
		'post_type' => array( 'products' ),
		'filetype' => null,
		'note' => null,
		'button_text' => __( 'Прикрепить изображение или загрузить его', 'attachments' ),
		'modal_text' => __( 'Прикрепить изображение или загрузить его', 'attachments' ),
		'fields' => array(
		) 
	);
	 
	$attachments->register( 'attachments_products', $args );
}
 
add_action( 'attachments_register', 'attachments_products' );

add_action( 'init', 'register_terms_rec_products' );
function register_terms_rec_products(){

    $labels = array( 
        'name' => _x( 'Каталог товаров', 'products' ),
        'singular_name' => _x( 'Каталог товаров', 'products' ),
        'add_new' => _x( 'Добавить товар', 'products' ),
        'add_new_item' => _x( 'Добавить новый товар', 'products' ),
        'edit_item' => _x( 'Редактировать', 'products' ),
        'new_item' => _x( 'Новое', 'products' ),
        'view_item' => _x( 'Просмотр', 'products' ),
        'search_items' => _x( 'Поиск', 'products' ),
        'not_found' => _x( 'Не найдено', 'products' ),
        'not_found_in_trash' => _x( 'Корзина пуста', 'products' ),
        'parent_item_colon' => _x( 'Родительский товар', 'products' ),
        'menu_name' => _x( 'Товары', 'products' )
    );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,        
        'supports' => array( 'title', 'editor','custom-fields','thumbnail','comments','excerpt'),
        'taxonomies' => array( 'prodcat' ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 10,
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );
    
    $args = apply_filters('register_data_products',$args);

    register_post_type( 'products', $args );
}

add_action( 'init', 'register_taxonomy_prodcat' );
function register_taxonomy_prodcat() {

    $labels = array( 
          'name' => _x( 'Категории', 'prodcat' ),
        'singular_name' => _x( 'Категории', 'prodcat' ),
        'search_items' => _x( 'Поиск', 'prodcat' ),
        'popular_items' => _x( 'Популярные категории', 'prodcat' ),
        'all_items' => _x( 'Все категории', 'prodcat' ),
        'parent_item' => _x( 'Родительская категория', 'prodcat' ),
        'parent_item_colon' => _x( 'Родительская категория:', 'prodcat' ),
        'edit_item' => _x( 'Редактировать категорию', 'prodcat' ),
        'update_item' => _x( 'Обновить категорию', 'prodcat' ),
        'add_new_item' => _x( 'Добавить новую категорию', 'prodcat' ),
        'new_item_name' => _x( 'Новая категория', 'prodcat' ),
        'separate_items_with_commas' => _x( 'Separate страна with commas', 'prodcat' ),
        'add_or_remove_items' => _x( 'Добавить или удалить категорию', 'prodcat' ),
        'choose_from_most_used' => _x( 'Выберите для использования', 'prodcat' ),
        'menu_name' => _x( 'Категории', 'prodcat' )
    );

    $args = array( 
        'labels' => $labels,
        'public' => true,
        'show_in_nav_menus' => true,
        'show_ui' => true,
        'show_tagcloud' => true,
        'hierarchical' => true,
        'rewrite' => true,
        'query_var' => true
    );

    register_taxonomy( 'prodcat', array('products'), $args );
}

// создаем колонку товарных категорий
add_filter('manage_edit-products_columns', 'add_prodcat_column', 10, 1);  
function add_prodcat_column( $columns ){  
    $columns['prodcat'] = 'Категория'; 
    return $columns;  
}  
  
// заполняем колонку данными  
add_filter('manage_products_posts_custom_column', 'fill_prodcat_column', 5, 2);
function fill_prodcat_column($column_name, $post_id) {  
    if( $column_name != 'prodcat' )  
        return;  
  
    $cur_terms = get_the_terms( $post_id, 'prodcat' );  
		foreach((array)$cur_terms as $cur_term){  
			echo '<a href="./edit.php?post_type=products&prodcat='. $cur_term->slug .'">'. $cur_term->name .'</a><br />'  ;
		}   
}
// добавляем возможность сортировать колонку  
add_filter('manage_edit-products_sortable_columns', 'add_price_sortable_column');  
function add_price_sortable_column($sortable_columns){  
        $sortable_columns['prodcat'] = 'prodcat_prodcat';  
      
        return $sortable_columns;  
}

// создаем колонку цены
add_filter('manage_edit-products_columns', 'add_price_column', 10, 1);  
function add_price_column( $columns ){   
	$out = array();  
    foreach((array)$columns as $col=>$name){  
        if(++$i==3)  
             $out['price'] = 'Цена';  
        $out[$col] = $name;  
    }   
    return $out;   
  
}  
  
// заполняем колонку цены  
add_filter('manage_products_posts_custom_column', 'fill_price_column', 5, 2); // wp-admin/includes/class-wp-posts-list-table.php  
function fill_price_column($column_name, $post_id) {  
    switch( $column_name ){
        case 'price':
			echo '<input type="text" id="price-product-'.$post_id.'" name="price-product" size="4" value="'.get_post_meta($post_id,'price-products',1).'"> '.get_current_type_currency($post_id).'
                <input type="button" class="recall-button edit-price-product" product="'.$post_id.'" id="product-'.$post_id.'" value="Ок">'; 
        break;
    }
}

add_filter('manage_products_posts_columns', 'delete_column_date_product', 10, 1);  
function delete_column_date_product( $columns ){
        unset($columns['date']);
        return $columns;   
	  
}  

// создаем колонку наличия товара
add_filter('manage_edit-products_columns', 'add_availability_column', 10, 1);  
function add_availability_column( $columns ){
	global $rmag_options;
	if($rmag_options['products_warehouse_recall']!=1) return $columns;
		$out = array();  
		foreach((array)$columns as $col=>$name){  
			if(++$i==3)  
				 $out['availability'] = 'Наличие';  
			$out[$col] = $name;  
		}   
		return $out;   
	  
}  
	  
// заполняем колонку наличия товара  
add_filter('manage_products_posts_custom_column', 'fill_availability_column', 5, 2); 
function fill_availability_column($column_name, $post_id) {
	global $rmag_options;
	if($rmag_options['products_warehouse_recall']!=1) return $column_name;
        
        if( $column_name != 'availability' ) return; 
        
         if(get_post_meta($post_id, 'availability_product', 1)=='empty'){ //если товар цифровой
             echo '<span>цифровой товар</span>';             
         }else{	
            if(!get_post_meta($post_id, 'outsale', 1)){
                    $amount = get_post_meta($post_id,'amount_product',1);
                    $reserve = get_post_meta($post_id,'reserve_product',1);

                    if($amount==0&&$amount!='') echo '<span style="color:red;">в наличии</span> ';
                    else echo '<span style="color:green;">в наличии</span> ';
                    
                    if($amount!='') $form_amount = '<input type="text" name="amountprod[]" size="3" value=""> шт.';
                            else $form_amount = false;
                            
                    if($amount!=false&&$amount>0) echo '<span style="color:green;">'.$amount.'</span> '.$form_amount;
                            else if($amount<=0) echo '<span style="color:red;">'.$amount.'</span> '.$form_amount;
                            
                    if($reserve) echo '<br /><span style="color:orange;">в резерве '.$reserve.'</span>';			
            }else{
                    echo '<span style="color:red;">снят с продажи</span>';
            }
         }
}


// создаем колонку миниатюр
add_filter('manage_edit-products_columns', 'add_thumb_column', 10, 1);  
function add_thumb_column( $columns ){   
	$out = array();  
    foreach((array)$columns as $col=>$name){  
        if(++$i==2)  
             $out['thumb'] = 'Миниатюра';  
        $out[$col] = $name;  
    }   
    return $out;   
  
}  

if (is_admin()) add_action('admin_init', 'recall_postmeta_products');
function recall_postmeta_products() {
    add_meta_box( 'recall_meta', __('Настройки Wp-Recall','rcl'), 'options_box_rcl', 'products', 'normal', 'high'  );
}
  
// заполняем колонку миниатюр  
add_filter('manage_products_posts_custom_column', 'fill_thumb_column', 5, 2); // wp-admin/includes/class-wp-posts-list-table.php  
function fill_thumb_column($column_name, $post_id) {  
    if( $column_name != 'thumb' )  
        return;     
    if(get_the_post_thumbnail($post_id,'thumbnail')) $img = get_the_post_thumbnail($post_id,array(70,70)) ;
    
    echo '<div class="thumbnail">'.$img.'</div>';
}
 

add_action('admin_init', 'recall_products_fields', 1);

function recall_products_fields() {
    add_meta_box( 'products_fields', 'Характеристики товара', 'recall_products_fields_box', 'products', 'normal', 'high'  );
}

function recall_products_fields_box( $post ){
	global $rmag_options; ?>
	
	<p>Цена товара:<br>
	<label><input type="number" name="wprecall[price-products]" value="<?php echo get_post_meta($post->ID,'price-products',1); ?>" style="width:70px" /> <?php the_type_currency_list($post->ID); ?></label></p>
	
	<?php if($rmag_options['multi_cur']){ ?>	
	<p>Курс доп.валюты для товара:<br>
	<label><input type="text" name="wprecall[curse_currency]" value="<?php echo get_post_meta($post->ID,'curse_currency',1); ?>" style="width:70px" /></label><br>
	<small>Текущий курс доп.валюты: <?php echo $rmag_options['curse_currency']; ?>.<br>
	Если для товара указан свой курс, то он будет приоритетным при расчете цены этого товара.</small></p>
	<?php } ?>
	
	<p>Наценка на товар (%):<br>
	<label><input type="number" name="wprecall[margin_product]" value="<?php echo get_post_meta($post->ID,'margin_product',1); ?>" style="width:70px" /></label><br>
	<small>Наценка на товар будет прибавляться к выводимой стоимости товара</small></p>
	
	<?php
	$customprice = unserialize(get_post_meta($post->ID, 'custom-price', 1));
	if($customprice){
		$cnt = count($customprice);
		for($a=0;$a<$cnt;$a++){
			$price .= '<p id="custom-price-'.$a.'">Заголовок: <input type="text" class="title-custom-price" name="title-custom-price[]" value="'.$customprice[$a]['title'].'"> 
			Цена: <input type="number" class="custom-price" name="custom-price[]" value="'.$customprice[$a]['price'].'"> 
			<a href="#" class="delete-price" id="'.$a.'">удалить</a></p>';
		}
	}	
	
	//echo '<div id="custom-price-list">'.$price.'</div>
	//<input type="button" id="add-custom-price" class="button-secondary" value="Добавить еще цену">'; ?>	

	<?php if($rmag_options['products_warehouse_recall']==1){ ?>	
		<h4>Наличие товара: <?php $mark_v = get_post_meta($post->ID, 'availability_product', 1); ?></h4>
		 <p><label><input type="radio" name="wprecall[availability_product]" value="" <?php checked( $mark_v, '' ); ?>/> в наличии</label> 
		 <input type="number" name="wprecall[amount_product]" value="<?php echo get_post_meta($post->ID, 'amount_product', 1); ?>" size="4"/> шт.</p>
		 <p><label><input type="radio" name="wprecall[availability_product]" value="empty" <?php checked( $mark_v, 'empty' ); ?> /> Цифровой товар</label></p>
	
        <?php }else{ ?>
            <p><label><input type="checkbox" name="wprecall[availability_product]" value="empty" <?php checked( get_post_meta($post->ID, 'availability_product', 1), 'empty' ); ?> /> Цифровой товар</label></p>
        <?php } ?>
		
                 
        <p><label><input type="checkbox" name="wprecall[outsale]" value="1" <?php checked( get_post_meta($post->ID, 'outsale', 1), 1 ); ?> /> Снять с продажи</label></p>
		
	<?php
	if($rmag_options['sistem_related_products']==1){ 
	echo '<h3>Похожие или рекомендуемые товары:</h3>';
	$args = array(  
		'show_option_all'    => '',  
		'show_option_none'   => '',  
		'orderby'            => 'ID',  
		'order'              => 'ASC',  
		'show_last_update'   => 0,  
		'show_count'         => 0,  
		'hide_empty'         => 0,  
		'child_of'           => 0,  
		'exclude'            => '',  
		'echo'               => 0,  
		'selected'           => get_post_meta($post->ID, 'related_products_recall', 1),  
		'hierarchical'       => 0,  
		'name'               => 'wprecall[related_products_recall]',  
		'id'                 => 'name',  
		'class'              => 'postform',  
		'depth'              => 0,  
		'tab_index'          => 0,  
		'taxonomy'           => 'prodcat',  
		'hide_if_empty'      => false );   
	  
	echo '<div style="margin:10px 0;">'.wp_dropdown_categories( $args ).' - выберите товарную категорию</div>';		
	}	
	
	if(!class_exists( 'Attachments' )){
	$args = array(  
    'numberposts' => -1,  
    'order'=> 'ASC',  
    'post_mime_type' => 'image',  
    'post_parent' => $post->ID,  
    'post_status' => null,  
    'post_type' => 'attachment'  
    );   
	  
	$childrens = get_children( $args ); 
	
	$postmeta = get_post_meta($post->ID, 'children_prodimage', 1);
	$value = explode(',',$postmeta);
	$count_value = count($value);
	$id_thumbnail = get_post_thumbnail_id( $post->ID );
	
	echo '
	<style>
	.image-prod-gallery{float: left; margin: 3px;} .prod-gallery{overflow:hidden;}
	</style>
	<h3>Изображения галереи</h3>
	
	<div class="prod-gallery">';
	if( $childrens ){
		$n=0;
		
		foreach((array) $childrens as $children ){ 
			
                    $n++;			

                    for($a=0;$a<=$count_value;$a++){
                            if($value[$a]==$children->ID) $selected = ' checked=checked';					
                    }
                    echo '<div class="image-prod-gallery"><label><img width="100" src="'.wp_get_attachment_thumb_url( $children->ID ).'" class="current">';
                    echo '<input style="position: absolute; margin-left: -15px; margin-top: 85px;" type="checkbox" id="imageprod-'.$children->ID.'" name="children_prodimage[]" value="'.$children->ID.'"'.$selected.'></label></div>';


                    $selected = '';

                    if($id_thumbnail==$children->ID) $thumb = true;
                    
		}
		if(!$thumb&&has_post_thumbnail($post->ID)){
			for($a=0;$a<=$count_value;$a++){
				if($value[$a]==$id_thumbnail) $selected = ' checked=checked';					
			}
			echo '<div class="image-prod-gallery"><label><img width="100" src="'.wp_get_attachment_thumb_url( $id_thumbnail ).'" class="current">';
			echo '<input style="position: absolute; margin-left: -15px; margin-top: 85px;" type="checkbox" id="imageprod-'.$id_thumbnail.'" name="children_prodimage[]" value="'.$id_thumbnail.'"'.$selected.'></label></div>';
			
			$selected = '';
		}
				
	}else{
		if(has_post_thumbnail($post->ID)){
			for($a=0;$a<=$count_value;$a++){
					if($value[$a]==$id_thumbnail) $selected = ' checked=checked';					
			}
			echo '<div class="image-prod-gallery"><label><img width="100" src="'.wp_get_attachment_thumb_url( $id_thumbnail ).'" class="current">';
			echo '<input style="position: absolute; margin-left: -15px; margin-top: 85px;" type="checkbox" id="imageprod-'.$id_thumbnail.'" name="children_prodimage[]" value="'.$id_thumbnail.'"'.$selected.'></label></div>';

			$selected = '';
		}
	}
	echo '</div>';
	}
	
?>
	
	<input type="hidden" name="wpm_fields_nonce" value="<?php echo wp_create_nonce(__FILE__); ?>" />
<?php
}

add_action('save_post', 'wpm_extra_fields_update');
function wpm_extra_fields_update( $post_id ){
    if(!isset($_POST['wpm_fields_nonce'])) return false;
    if ( !wp_verify_nonce($_POST['wpm_fields_nonce'], __FILE__) ) return false;
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE  ) return false;
	if ( !current_user_can('edit_post', $post_id) ) return false;

	if(!isset($_POST['wprecall']['outsale'])) delete_post_meta($post_id, 'outsale');
    if(!isset($_POST['wprecall']['availability_product'])) delete_post_meta($post_id, 'availability_product');
        
	/*if(isset($_POST['custom-price'])){
		$cnt = count($_POST['custom-price']);
		for($a=0;$a<$cnt;$a++){
			if($_POST['custom-price']){
				$customprice[$a]['title'] .= $_POST['title-custom-price'][$a];
				$customprice[$a]['price'] .= $_POST['custom-price'][$a];
			}
		}
		$customprice = serialize($customprice);
		update_post_meta($post_id, 'custom-price', $customprice);
	}else{
		delete_post_meta($post_id, 'custom-price');
	}*/
	
	if( $_POST['children_prodimage']=='' ){
		delete_post_meta($post_id, 'children_prodimage');	
	}else{
		$_POST['children_prodimage'] = array_map('trim', (array)$_POST['children_prodimage']);
		$n=0;
		foreach((array) $_POST['children_prodimage'] as $value ){
			$n++;	
			if($n==1) $children_prodimage = $value;	
				else $children_prodimage .= ','.$value;	
		}
		update_post_meta($post_id, 'children_prodimage', $children_prodimage);
	}

	return $post_id;
}
