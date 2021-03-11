<?php $fields =  array(
	'author' => '<div class="form-input"><input type="text" name="author" value="" id="comment_name" aria-required="true" placeholder="'.esc_attr__('Your Name',"discy").'">'.(isset($its_question) && 'question' == $its_question?'<i class="icon-user"></i>':'').'</div>',
	'email'  => '<div class="form-input form-input-last"><input type="email" name="email" value="" id="comment_email" aria-required="true" placeholder="'.esc_attr__('Email',"discy").'">'.(isset($its_question) && 'question' == $its_question?'<i class="icon-mail"></i>':'').'</div>',
	'url'    => '<div class="form-input form-input-full"><input type="url" name="url" value="" id="comment_url" placeholder="'.esc_attr__('URL',"discy").'">'.(isset($its_question) && 'question' == $its_question?'<i class="icon-link"></i>':'').'</div>',
);

if (isset($comment_editor) && $comment_editor == "on") {
	$settings = array("textarea_name" => "comment","media_buttons" => true,"textarea_rows" => 10);
	$settings = apply_filters('wpqa_comment_editor_setting',$settings);
	
	ob_start();
	wp_editor("","comment",$settings);
	$comment_contents = ob_get_clean();
}else {
	$comment_contents = '<textarea id="comment" name="comment" aria-required="true" placeholder="'.apply_filters("discy_filter_textarea_comment".(isset($its_question) && 'question' == $its_question?"_question":""),(isset($its_question) && 'question' == $its_question?esc_html__("Answer","discy"):esc_html__("Comment","discy"))).'"></textarea>'.(isset($its_question) && 'question' == $its_question?'<i class="icon-pencil"></i>':'');
}

$comments_args = array(
	'must_log_in'          => (isset($its_question) && 'question' == $its_question?'<div class="alert-message warning"><i class="icon-flag"></i><p>'.esc_html__("You must login to can add an answer.","discy").'</p></div>'.do_shortcode("[wpqa_login]"):'<p class="no-login-comment">'.sprintf(esc_html__('You must %1$s login %2$s or %3$s register %4$s to add a new comment.','discy'),'<a href="'.(has_wpqa()?wpqa_login_permalink():"#").'" class="login-panel '.apply_filters('wpqa_pop_up_class','').apply_filters('wpqa_pop_up_class_login','').'">','</a>','<a href="'.(has_wpqa()?wpqa_signup_permalink():"#").'" class="signup-panel '.apply_filters('wpqa_pop_up_class','').apply_filters('wpqa_pop_up_class_signup','').'">','</a>').'</p>'),
	'logged_in_as'         =>  '<p class="comment-login">'.esc_html__('Logged in as',"discy").'<a class="comment-login-login" href="'.esc_url(get_author_posts_url($user_id)).'"><i class="icon-user"></i>'.esc_attr($user_identity).'</a><a class="comment-login-logout" href="'.wp_logout_url(get_permalink()).'" title="'.esc_attr__("Log out of this account","discy").'"><i class="icon-logout"></i>'.esc_html__('Log out',"discy").'</a></p>',
	'title_reply'          => (isset($its_question) && 'question' == $its_question?esc_html__("Leave an answer","discy"):esc_html__("Leave a comment","discy")),
	'title_reply_to'       => (isset($its_question) && 'question' == $its_question?esc_html__("Leave an reply to %s","discy"):esc_html__("Leave a comment to %s","discy")),
	'title_reply_before'   => (isset($its_question) && 'question' == $its_question && !is_user_logged_in() && !get_option('comment_registration')?'<div class="button-default show-answer-form">'.esc_html__("Leave an answer","discy").'</div>':'').'<h3 class="section-title'.(isset($its_question) && 'question' == $its_question && !is_user_logged_in()?' comment-form-hide':'').'">',
	'title_reply_after'    => '</h3>',
	'class_form'           => 'post-section comment-form'.(isset($its_question) && 'question' == $its_question && !is_user_logged_in()?' comment-form-hide':'').(isset($its_question) && 'question' == $its_question?' answers-form':''),
	'comment_notes_after'  => '',
	'comment_notes_before' => '',
	'comment_field'        => '<div class="form-input form-textarea'.(isset($comment_editor) && $comment_editor == "on"?" form-comment-editor":" form-comment-normal").'">'.$comment_contents.'</div>',
	'fields'               => apply_filters('comment_form_default_fields',$fields),
	'label_submit'         => esc_html__("Submit","discy"),
	'class_submit'         => 'button-default button-hide-click',
	'cancel_reply_before'  => '<div class="cancel-comment-reply">',
	'cancel_reply_after'   => '</div>',
	'format'               => 'html5'
);
comment_form(apply_filters("discy_filter_comment_form",$comments_args,$post->post_type),$post->ID);?>