<?phpglobal $rcl_options;$rcl_options['info_author_recall']=1;$rcl_options['moderation_public_post']=1;$rcl_options['media_downloader_recall']='';$rcl_options['id_parent_category']='';$rcl_options['user_public_access_recall']=0;if(!isset($rcl_options['public_form_page_rcl'])){	$rcl_options['public_form_page_rcl'] = wp_insert_post(array('post_title'=>'Форма публикации','post_content'=>'[public-form]','post_status'=>'publish','post_author'=>1,'post_type'=>'page','post_name'=>'rcl-postedit'));	$rcl_options['publics_block_rcl'] = 1;	$rcl_options['view_publics_block_rcl'] = 1;	$rcl_options['type_text_editor'] = 0;	$rcl_options['output_public_form_rcl'] = 1;	$rcl_options['user_public_access_recall'] = 2;	$rcl_options['rcl_editor_buttons'] = array('header','text','image','html');    $rcl_options['front_editing'] = array(2);}update_option('primary-rcl-options',$rcl_options);?>