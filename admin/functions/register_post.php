<?php
/* discy_field_is_visible */
function discy_field_is_visible( $condition, $operator, $fields, $values ) {
	
	if ( ! is_string( $condition ) || empty( $condition ) ) {
		return true;
	}

	if ( ! is_array( $fields ) ) {
		$fields = array();
	}

	if ( ! is_array( $values ) ) {
		$values = array();
	}
	
	$field_values = array();
	foreach ( $fields as $v ) {
		if (isset($v['id'])) {
			$field_values[ $v['id'] ] = array_key_exists( $v['id'], $values ) ? $values[ $v['id'] ] : ( array_key_exists( 'std', $v ) ? $v['std'] : '' );
		}
	}
	
	$bool_arr = array();
	$cond_arr = array_map( function($v) { $l = substr($v, -1); if ( $l != ')' ) { $v .= ')'; } return $v; }, explode( '),', $condition ) );
	
	foreach ( $cond_arr as $v ) {

		$bool = false;

		preg_match( '#^([a-z0-9_]+)\:(not|is|has|has_not)\(([a-z0-9-_\,]+)\)$#', trim( $v ), $match );

		if ( ! empty( $match ) ) {

			$id = $match[1];
			$op = $match[2];
			$val = $match[3];

			if ( in_array( $op, array( 'is', 'not' ) ) ) {
				if ($val == "empty" && ($op == 'is' || $op == 'not')) {
					if ($op == 'not') {
						$bool = ( $field_values[$id] != "" );
					}else {
						$bool = ( $field_values[$id] == "" );
					}
				}else {
					$bool = ( array_key_exists( $id, $field_values ) && $field_values[$id] == $val );
					
					if ( $op == 'not' ) {
						$bool = ( ! $bool );
					}
				}
			}else if ( in_array( $op, array( 'has', 'has_not' ) ) ) {
				if ( ! array_key_exists( $id, $field_values ) ) {
					$field_values[$id] = array();
				}

				if ( is_string( $field_values[$id] ) ) {
					$field_values[$id] = array_filter( explode( ',', $field_values[$id] ), function( $mv ) { return trim( $mv ); } );
				}

				if ( ! is_array( $field_values[$id] ) ) {
					$field_values[$id] = array();
				}
				
				if (isset($field_values[$id][$val])) {
					$bool = ((isset($field_values[$id][$val]["value"]) && $field_values[$id][$val]["value"] == $val) || (isset($field_values[$id][$val]) && $field_values[$id][$val] == 1) || (isset($field_values[$id][$val]) && $field_values[$id][$val] == $val));
				}else {
					$val = array_filter( explode( ',', $val ), function( $mv ) { return trim( $mv ); } );
					$bool = ((array_intersect($val,$field_values[$id]) == $val) || (count($field_values[$id]) == 1 && end($field_values[$id]) == 'all'));
				}
				
				if ( $op == 'has_not' ) {
					$bool = ( ! $bool );
				}
			}

		}

		$bool_arr[] = $bool;
	}

	if ( $operator == 'or' ) {
		return in_array( true, $bool_arr, true );
	}else {
		return ( ! in_array( false, $bool_arr, true ) );
	}
}
/* Style taxonomy */
$args = apply_filters("discy_term_options",array('category','question-category'));
discy_term_options($args);
function discy_term_options( $args ) {
	if (is_array($args) && !empty($args)) {
		foreach ($args as $taxonomy) {
			add_action( $taxonomy .'_add_form_fields', 'discy_term_add_form_fields', 1 );
			add_action( $taxonomy .'_edit_form_fields', 'discy_term_edit_form_fields', 1 );
			add_action( 'edited_'. $taxonomy, 'discy_save_term', 10 );
			add_action( 'create_'. $taxonomy, 'discy_save_term', 10 );
		}
	}
}
function discy_term_edit_form_fields( $tag ) {?>
	<tr class="form-terms">
		<th colspan="2" scope="row" valign="top">
			<div class="discy_framework">
				<?php discy_admin_fields_class::discy_admin_fields("term_edit",discy_terms,"terms",$tag->term_id,discy_admin_terms($tag->taxonomy,$tag->term_id));?>
			</div>
		</th>
	</tr>
	<?php
}
function discy_term_add_form_fields( $tag ) {?>
	<div class="form-terms">
		<div class="discy_framework">
			<?php discy_admin_fields_class::discy_admin_fields("term_add",discy_terms,"terms",null,discy_admin_terms($tag));?>
		</div>
	</div>
	<?php 
}
function discy_save_term( $term_id ) {
	$term = get_term($term_id);
	$options = discy_admin_terms($term->taxonomy,$term_id);
	foreach ($options as $value) {
		if ($value['type'] != 'heading' && $value['type'] != "heading-2" && $value['type'] != "heading-3" && $value['type'] != 'info' && $value['type'] != 'content') {
			$val = '';
			if (isset($value['std'])) {
				$val = $value['std'];
			}
			
			$field_name = $value['id'];
			
			if (isset($_POST[$field_name])) {
				$val = $_POST[$field_name];
			}
			
			if (!isset($_POST[$field_name]) && $value['type'] == "checkbox") {
				$val = 0;
			}
			
			if ('' === $val || array() === $val) {
				delete_term_meta($term_id,$field_name);
			}else {
				update_term_meta($term_id,$field_name,$val);
			}
		}
	}
}
/* Admin columns for post types */
add_filter('manage_post_posts_columns' , 'discy_post_columns');
function discy_post_columns($columns) {
	unset($columns["author"]);
	$new_columns = array(
		'author-p' => esc_html__("Author","discy"),
		'formats'  => esc_html__("Format","discy"),
	);
	$active_post_stats = discy_options("active_post_stats");
	$view = ($active_post_stats == "on"?array('view' => '<span class="dashicons dashicons-visibility dashicons-before"></span>'):array());
	return array_merge($columns,$new_columns,$view);
}
add_action('manage_post_posts_custom_column','discy_post_custom_columns');
function discy_post_custom_columns($column) {
	global $post;
	switch ( $column ) {
		case 'formats' :
			$what_post = discy_post_meta("what_post","",false);
			if (is_sticky()) {
				$formats = 'standard';
			}else if ($what_post == "google") {
				$formats = 'aside';
			}else if ($what_post == "audio") {
				$formats = 'audio';
			}else if ($what_post == "video") {
				$formats = 'video';
			}else if ($what_post == "slideshow") {
				$formats = 'gallery';
			}else if ($what_post == "quote") {
				$formats = 'quote';
			}else if ($what_post == "link") {
				$formats = 'link';
			}else if ($what_post == "soundcloud" || $what_post == "twitter" || $what_post == "facebook" || $what_post == "instagram") {
				$formats = 'chat';
			}else {
				if (has_post_thumbnail()) {
					$formats = 'image';
				}else {
					$formats = 'standard';
				}
			}
			echo '<span class="post-format-icon discy-format-icon post-format-'.$formats.'"></span>';
		break;
		case 'view' :
			$post_meta_stats = discy_options("post_meta_stats");
			$cache_post_stats = discy_options("cache_post_stats");
			$post_meta_stats = ($post_meta_stats != ""?$post_meta_stats:"post_stats");
			if ($cache_post_stats == "on") {
				$post_stats = get_transient($post_meta_stats.$post->ID);
				$post_stats = ($post_stats !== false?$post_stats:discy_post_meta($post_meta_stats,"",false));
			}else {
				$post_stats = discy_post_meta($post_meta_stats,"",false);
			}
			echo ($post_stats != ""?$post_stats:0)." ".esc_html__("views","discy");
		break;
		case 'author-p' :
			$display_name = get_the_author_meta('display_name',$post->post_author);
			if (isset($display_name) && $display_name != "") {?>
				<a href="<?php echo admin_url('edit.php?post_author='.$post->post_author.'&post_type=post');?>"><?php echo esc_attr($display_name)?></a>
			<?php }else {
				$post_username = discy_post_meta("post_username","",false);
				echo esc_attr($post_username);
			}
		break;
	}
}
add_action('current_screen','discy_posts_exclude',10,2);
if (!function_exists('discy_posts_exclude')) :
	function discy_posts_exclude($screen) {
		if ($screen->id != 'edit-post')
			return;
		$get_author = (int)((isset($_GET['post_author']))?esc_attr($_GET['post_author']):0);
		if ($get_author > 0) {
			add_filter('parse_query','discy_list_posts_author');
		}
	}
endif;
if (!function_exists('discy_list_posts_author')) :
	function discy_list_posts_author($clauses) {
		$get_author = (int)((isset($_GET['post_author']))?esc_attr($_GET['post_author']):0);
		if ($get_author > 0) {
			$clauses->query_vars['author'] = $get_author;
		}
	}
endif;?>