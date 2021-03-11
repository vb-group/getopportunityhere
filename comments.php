<?php if ($post->post_type == 'question') {
	$question_category = wp_get_post_terms($post->ID,'question-category',array("fields" => "all"));
	if (isset($question_category[0])) {
		$discy_new = discy_term_meta("new",$question_category[0]->term_id);
		$discy_special = discy_term_meta("special",$question_category[0]->term_id);
	}
	$closed_question = discy_post_meta("closed_question","",false);
	$custom_permission = discy_options("custom_permission");
	$add_answer = discy_options("add_answer");
	$its_question = "question";
}
$closed_post = apply_filters("discy_closed_post",false,$post);

$user_id = get_current_user_id();
if (is_user_logged_in()) {
	$user_is_login = get_userdata($user_id);
	$roles = $user_is_login->allcaps;
}

$wpqa_server = apply_filters('wpqa_server','SCRIPT_FILENAME');
if (!empty($wpqa_server) && 'comments.php' == basename($wpqa_server)) :
	die (esc_html__('Please do not load this page directly. Thanks!',"discy"));
endif;

if ( post_password_required() ) : ?>
    <p class="no-comments">
    	<?php if (isset($its_question) && 'question' == $its_question) {
    		esc_html_e("This question is password protected. Enter the password to view answers.","discy");
    	}else {
    		esc_html_e("This post is password protected. Enter the password to view comments.","discy");
    	}?>
    </p>
    <?php return;
endif;

if ( have_comments() ) :
	$k_ad = 1;?>
	<div id="comments" class="post-section">
		<div class="post-inner">
			<?php $filter_show_comments = apply_filters("discy_filter_show_comments",true,$post->post_type,$post->ID);
			if ($filter_show_comments == true) {
				if (isset($its_question) && $its_question == "question") {
					$custom_answer_tabs = discy_post_meta("custom_answer_tabs");
					if ($custom_answer_tabs == "on") {
						$answers_tabs = discy_post_meta('answers_tabs');
					}else {
						$answers_tabs = discy_options('answers_tabs');
					}
					$answers_tabs = apply_filters("wpqa_answers_tabs",$answers_tabs);
					$answers_tabs_keys = array_keys($answers_tabs);
					if (isset($answers_tabs) && is_array($answers_tabs)) {
						$a_count = 0;
						while ($a_count < count($answers_tabs)) {
							if (isset($answers_tabs[$answers_tabs_keys[$a_count]]["value"]) && $answers_tabs[$answers_tabs_keys[$a_count]]["value"] != "" && $answers_tabs[$answers_tabs_keys[$a_count]]["value"] != "0") {
								$first_one = $a_count;
								break;
							}
							$a_count++;
						}
						
						if (isset($first_one) && $first_one !== "") {
							$first_one = $answers_tabs[$answers_tabs_keys[$first_one]]["value"];
						}
						
						if (isset($_GET["show"]) && $_GET["show"] != "") {
							$first_one = $_GET["show"];
						}
					}
					if (isset($first_one) && $first_one !== "") {
						$wpqa_answers_tabs_foreach = apply_filters("wpqa_answers_tabs_foreach",true,$answers_tabs,$first_one);
					}
					if (isset($wpqa_answers_tabs_foreach) && $wpqa_answers_tabs_foreach == true && isset($its_question) && $its_question == "question" && isset($first_one) && $first_one !== "") {?>
						<div class="answers-tabs">
					<?php }
				}
					$count_post_all = (int)(has_wpqa()?wpqa_count_comments($post->ID):get_comments_number());?>
					<h3 class="section-title"><span><?php echo (isset($its_question) && 'question' == $its_question?sprintf(_n("%s Answer","%s Answers",$count_post_all,"discy"),$count_post_all):sprintf(_n("%s Comment","%s Comments",$count_post_all,"discy"),$count_post_all));?></h3>
				<?php if (isset($wpqa_answers_tabs_foreach) && $wpqa_answers_tabs_foreach == true && isset($its_question) && $its_question == "question" && isset($first_one) && $first_one !== "") {?>
						<div class="answers-tabs-inner">
							<ul>
								<?php foreach ($answers_tabs as $key => $value) {
									if ($key == "votes" && isset($answers_tabs["votes"]["value"]) && $answers_tabs["votes"]["value"] == "votes") {?>
										<li<?php echo ((isset($_GET["show"]) && $_GET["show"] === "votes") || $first_one === "votes"?" class='active-tab'":"")?>><a href="<?php echo esc_url_raw(add_query_arg(array("show" => "votes")))?>#comments"><?php esc_html_e("Voted","discy")?></a></li>
									<?php }else if ($key == "oldest" && isset($answers_tabs["oldest"]["value"]) && $answers_tabs["oldest"]["value"] == "oldest") {?>
										<li<?php echo ((isset($_GET["show"]) && $_GET["show"] === "oldest") || $first_one === "oldest"?" class='active-tab'":"")?>><a href="<?php echo esc_url_raw(add_query_arg(array("show" => "oldest")))?>#comments"><?php esc_html_e("Oldest","discy")?></a></li>
									<?php }else if ($key == "recent" && isset($answers_tabs["recent"]["value"]) && $answers_tabs["recent"]["value"] == "recent") {?>
										<li<?php echo ((isset($_GET["show"]) && $_GET["show"] === "recent") || $first_one === "recent"?" class='active-tab'":"")?>><a href="<?php echo esc_url_raw(add_query_arg(array("show" => "recent")))?>#comments"><?php esc_html_e("Recent","discy")?></a></li>
									<?php }else if ($key == "random" && isset($answers_tabs["random"]["value"]) && $answers_tabs["random"]["value"] == "random") {?>
										<li<?php echo ((isset($_GET["show"]) && $_GET["show"] === "random") || $first_one === "random"?" class='active-tab'":"")?>><a href="<?php echo esc_url_raw(add_query_arg(array("show" => "random")))?>#comments"><?php esc_html_e("Random","discy")?></a></li>
									<?php }
								}?>
							</ul>
						</div><!-- End answers-tabs-inner -->
						<div class="clearfix"></div>
					</div><!-- End answers-tabs -->
				<?php }
				if (isset($its_question) && $its_question == "question" && isset($first_one) && $first_one !== "" && isset($answers_tabs)) {
					do_action("wpqa_answers_after_tabs",$answers_tabs,$first_one);
				}
				$show_answer = discy_options("show_answer");
				if (!isset($its_question) || ((isset($its_question) && $its_question == "question") && (is_super_admin($user_id)) || $custom_permission != "on" || (is_user_logged_in() && $custom_permission == "on" && isset($roles["show_answer"]) && $roles["show_answer"] == 1) || (!is_user_logged_in() && $show_answer == "on"))) {
					if (isset($its_question) && $its_question == "question") {
						if (isset($first_one) && $first_one !== "") {
							if ($first_one == 'votes') {
								$comments_args = get_comments(array('post_id' => $post->ID,'status' => 'approve','orderby' => 'meta_value_num','meta_key' => 'comment_vote','order' => 'DESC'));
							}else if ($first_one == 'oldest') {
								$comments_args = get_comments(array('post_id' => $post->ID,'status' => 'approve','orderby' => 'comment_date','order' => 'ASC'));
							}else if ($first_one == 'recent') {
								$comments_args = get_comments(array('post_id' => $post->ID,'status' => 'approve','orderby' => 'comment_date','order' => 'DESC'));
							}else if ($first_one == 'random') {
								$comments_args = get_comments(array('post_id' => $post->ID,'status' => 'approve','orderby' => 'rand','order' => 'DESC'));
								shuffle($comments_args);
							}
						}
					}?>
					<ol class="commentlist clearfix">
					    <?php if (isset($its_question) && $its_question == "question" && isset($first_one) && $first_one !== "") {
					    	$comments_args = (isset($comments_args)?$comments_args:array());
						    $comments_args = apply_filters("wpqa_comments_args",$comments_args,$first_one,$post->ID);
						}
						$read_more_answer = discy_options("read_more_answer");
						$comment_read_more = (isset($its_question) && $its_question == "question" && $read_more_answer == "on"?array('comment_read_more' => true):array());
					    $list_comments_args = array_merge(array('callback' => 'discy_comment'),$comment_read_more);
					    if (isset($comments_args) && is_array($comments_args) && !empty($comments_args)) {
					    	$comment_order = get_option('comment_order');
					    	if ($comment_order == "desc") {
					    		$comments_args = array_reverse($comments_args);
					    	}
					    	wp_list_comments($list_comments_args,$comments_args);
					    }else {
					    	$wpqa_show_comments = apply_filters("wpqa_show_comments",true);
					    	if ($wpqa_show_comments == true) {
						    	wp_list_comments($list_comments_args);
						    }
					    }?>
					</ol><!-- End commentlist -->
				<?php }else {
					echo '<div class="alert-message error"><i class="icon-cancel"></i><p>'.esc_html__("Sorry, you do not have a permission to show this answers.","discy").' '.(has_wpqa()?wpqa_paid_subscriptions():'').'</p></div>';
				}
			}?>
			<div class="clearfix"></div>
		</div><!-- End post-inner -->
	</div><!-- End post -->
	
	<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
		<div class="pagination comments-pagination">
		    <?php paginate_comments_links(array('prev_text' => '<i class="icon-left-open"></i>', 'next_text' => '<i class="icon-right-open"></i>'))?>
		</div><!-- End comments-pagination -->
		<div class="clearfix"></div>
    <?php endif;
endif;

$comments_open = apply_filters("discy_comments_open",true);
if ( $comments_open == true && comments_open() ) {
	$anonymously_user = get_post_meta($post->ID,'anonymously_user',true);
	$yes_new = 1;
	if (have_comments()) {
		if (isset($question_category[0]) && $discy_new == "on") {
			$yes_new = 0;
			if ($user_id > 0 && $post->post_author != $user_id && $anonymously_user != $user_id) {
				$yes_new = 1;
			}
			if (is_super_admin($user_id)) {
				$yes_new = 0;
			}
		}else {
			$yes_new = 0;
		}
	}else {
		if (isset($question_category[0]) && $discy_new == "on") {
			if (isset($post->post_author) && $user_id > 0 && (($post->post_author == $user_id) || ($anonymously_user == $user_id))) {
				$yes_new = 1;
			}
			if (isset($post->ID) && $user_id > 0 && (($post->post_author == $user_id) || ($anonymously_user == $user_id))) {
				$yes_new = 1;
			}
		}else if (isset($question_category[0]) && $discy_new == 0) {
			$yes_new = 0;
		}
		
		if (empty($question_category[0]) || is_super_admin($user_id)) {
			$yes_new = 0;
		}
	}
	
	if (!isset($its_question) || (isset($its_question) && $its_question == "question" && $yes_new != 1)) {
		if (!isset($its_question) || ((isset($its_question) && $its_question == "question") && (is_super_admin($user_id)) || $custom_permission != "on" || (is_user_logged_in() && $custom_permission == "on" && isset($roles["add_answer"]) && $roles["add_answer"] == 1) || (!is_user_logged_in() && $add_answer == "on"))) {
			$yes_special = 1;
			if (have_comments()) {
				$yes_special = 0;
			}else {
				if (isset($question_category[0]) && $discy_special == "on") {
					if (isset($post->post_author) && $user_id > 0 && (($post->post_author == $user_id) || ($anonymously_user == $user_id))) {
						$yes_special = 1;
					}
				}else if (isset($question_category[0]) && $discy_special == 0) {
					$yes_special = 0;
				}
				
				if (!isset($question_category[0]) || is_super_admin($user_id)) {
					$yes_special = 0;
				}
			}
			
			if (isset($its_question) && $its_question == "question" && $yes_special == 1) {
				echo '<div class="alert-message warning"><i class="icon-flag"></i><p>'.esc_html__("Sorry this question is a special, The admin must answer first.","discy").'</p></div>';
			}else {
				$answer_per_question = discy_options("answer_per_question");
				if ($answer_per_question == "on" && !is_super_admin($user_id) && $user_id > 0) {
					$answers_question = get_comments(array('post_id' => $post->ID,'user_id' => $user_id,'parent' => 0));
				}
				if (isset($its_question) && $its_question == "question" && !is_super_admin($user_id) && $answer_per_question == "on" && $user_id > 0 && isset($answers_question) && is_array($answers_question) && !empty($answers_question)) {
					$edit_delete = '';
					if (has_wpqa() && isset($answers_question[0]) && isset($answers_question[0]->comment_approved) && $answers_question[0]->comment_approved == 1) {
						$can_edit_comment = discy_options("can_edit_comment");
						$can_edit_comment_after = discy_options("can_edit_comment_after");
						$can_edit_comment_after = (int)(isset($can_edit_comment_after) && $can_edit_comment_after > 0?$can_edit_comment_after:0);
						if (version_compare(phpversion(), '5.3.0', '>')) {
							$time_now = strtotime(current_time('mysql'),date_create_from_format('Y-m-d H:i',current_time('mysql')));
						}else {
							list($year, $month, $day, $hour, $minute, $second) = sscanf(current_time('mysql'),'%04d-%02d-%02d %02d:%02d:%02d');
							$datetime = new DateTime("$year-$month-$day $hour:$minute:$second");
							$time_now = strtotime($datetime->format('r'));
						}
						$time_edit_comment = strtotime('+'.$can_edit_comment_after.' hour',strtotime($comment->comment_date));
						$time_end = ($time_now-$time_edit_comment)/60/60;
						$can_delete_comment = discy_options("can_delete_comment");
						$comment_id = $answers_question[0]->comment_ID;
						$comment_user_id = $answers_question[0]->user_id;
						if (($can_edit_comment == "on" && $comment_user_id == $user_id && $comment_user_id != 0 && $user_id != 0 && ($can_edit_comment_after == 0 || $time_end <= $can_edit_comment_after))) {
	                	    	$edit_delete .= "<a class='comment-edit-link edit-comment' href='".wpqa_edit_permalink($comment_id,"comment")."'>".esc_html__("Edit your answer.","discy")."</a>";
	                	    }
	                	    if ($can_delete_comment == "on" && $comment_user_id == $user_id && $comment_user_id > 0 && $user_id > 0) {
	                	    	$edit_delete .= "<a class='delete-comment delete-answer' href='".esc_url_raw(add_query_arg(array('delete_comment' => $comment_id,"wpqa_delete_nonce" => wp_create_nonce("wpqa_delete_nonce")),get_permalink($answers_question[0]->comment_post_ID)))."'>".esc_html__("Delete your answer.","discy")."</a>";
	                	    }
	                }
					echo '<div class="alert-message warning alert-answer-question"><i class="icon-flag"></i><p>'.esc_html__("You have already answered this question.","discy").' '.$edit_delete.'</p></div>';
				}
				if (isset($closed_post) && $closed_post == 1) {
					do_action("discy_closed_post_text");
				}else if (isset($its_question) && $its_question == "question" && isset($closed_question) && $closed_question == 1) {
					echo '<div class="alert-message warning alert-close-question"><i class="icon-flag"></i><p>'.esc_html__("Sorry this question is closed.","discy").'</p></div>';
				}else {
					echo '<div id="respond-all"'.(isset($edit_delete)?' class="respond-edit-delete discy_hide"':'').'>';
						$comment_editor = discy_options((isset($its_question) && 'question' == $its_question?'answer_editor':'comment_editor'));
						include locate_template("theme-parts/comment-form.php");
					echo '</div>';
				}
			}
		}else {
			if (!is_user_logged_in()) {
				echo '<div id="respond"><div class="alert-message warning"><i class="icon-flag"></i><p>'.esc_html__("You must login to can add an answer.","discy").'</p></div>'.do_shortcode("[wpqa_login]").'</div>';
			}else {
				echo '<div class="alert-message error"><i class="icon-cancel"></i><p>'.esc_html__("Sorry, you do not have a permission to answer to this question.","discy").' '.(has_wpqa()?wpqa_paid_subscriptions():'').'</p></div>';
			}
		}
	}else {
		echo '<div class="alert-message error"><i class="icon-cancel"></i><p>'.esc_html__("Sorry, you do not have a permission to answer to this question.","discy").' '.(has_wpqa()?wpqa_paid_subscriptions():'').'</p></div>';
	}
}else {
	do_action("discy_action_if_comments_closed");
}?>