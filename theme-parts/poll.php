<div class='all_signle_post_content poll-area<?php echo ($pending_questions?" discy_hide":"")?>'>
	<?php $question_poll_yes = false;
	$question_poll_num = discy_post_meta("question_poll_num","",false);
	$asks = discy_post_meta("ask","",false);
	$wpqa_polls = discy_post_meta("wpqa_poll","",false);
	$wpqa_polls = (isset($wpqa_polls) && is_array($wpqa_polls) && !empty($wpqa_polls)?$wpqa_polls:array());
	$wpqa_question_poll = discy_post_meta("wpqa_question_poll","",false);
	$wpqa_question_poll = (isset($wpqa_question_poll) && is_array($wpqa_question_poll) && !empty($wpqa_question_poll)?$wpqa_question_poll:array());
	if (isset($asks) && is_array($asks)) {
		foreach ($asks as $key_ask => $value_ask) {
			$wpqa_polls[$key_ask] = array(
				"id"       => $asks[$key_ask]["id"],
				"title"    => $asks[$key_ask]["title"],
				"value"    => (isset($asks[$key_ask]["value"]) && $asks[$key_ask]["value"] != ""?$asks[$key_ask]["value"]:(isset($wpqa_polls[$key_ask]["value"]) && $wpqa_polls[$key_ask]["value"] != ""?$wpqa_polls[$key_ask]["value"]:0)),
				"user_ids" => (isset($asks[$key_ask]["user_ids"]) && $asks[$key_ask]["user_ids"] != ""?$asks[$key_ask]["user_ids"]:(isset($wpqa_polls[$key_ask]["user_ids"]) && $wpqa_polls[$key_ask]["user_ids"] != ""?$wpqa_polls[$key_ask]["user_ids"]:array()))
			);
		}
	}
	
	if (isset($asks) && is_array($asks)) {
		if ((is_user_logged_in() && in_array($user_id,$wpqa_question_poll)) || (!is_user_logged_in() && isset($_COOKIE[discy_options("uniqid_cookie").'wpqa_question_poll'.$post_data->ID]))) {
			$question_poll_yes = true;
		}
		$poll_1 = '<div class="poll_1'.($question_poll_yes == false?" discy_hide":"").'">
			<h3><i class="icon-help"></i>'.esc_html__("Poll Results","discy").'</h3>';
			if ($poll_user_only == "on" && !is_user_logged_in()) {
				$poll_1 .= '<p class="still-not-votes">'.esc_html__("Please login to vote and see the results.","discy").'</p>';
			}else {
				if ($question_poll_num > 0) {
					$poll_1 .= '<div class="progressbar-wrap">';
						foreach($asks as $v_ask):
							$poll_voters = (int)$wpqa_polls[$v_ask['id']]['value'];
							if ($question_poll_num != "" || $question_poll_num != 0) {
								$value_poll = round(($poll_voters/$question_poll_num)*100,2);
							}
							if ($poll_image == "on" && isset($v_ask['image']) && esc_html(discy_image_url_id($v_ask['image'])) != "") {
								$poll_1 .= '<div class="wpqa_radio_p'.($poll_image == "on" && isset($v_ask['image']) && esc_html(discy_image_url_id($v_ask['image'])) != ""?" wpqa_result_poll_image":"").'">
									<span>';
										if ($poll_image == "on" && isset($v_ask['image']) && esc_html(discy_image_url_id($v_ask['image'])) != "") {
											$poll_1 .= discy_get_aq_resize_img(203,160,"",esc_html($v_ask['image']['id']),"",(isset($v_ask['title']) && $v_ask['title'] != ''?esc_html($v_ask['title']):''));
										}
									$poll_1 .= '</span>
									<span class="progressbar-title">
										'."<span>".($question_poll_num == 0?0:$value_poll)."%</span>".(isset($v_ask['title']) && $v_ask['title'] != ''?esc_html($v_ask['title']):'')." ".($poll_voters != ""?"( ".discy_count_number($poll_voters)." "._n("voter","voters",$poll_voters,"discy")." )":"").'
									</span>';
							}else {
								$poll_1 .= '<span class="progressbar-title">
									'."<span>".($question_poll_num == 0?0:$value_poll)."%</span>".(isset($v_ask['title']) && $v_ask['title'] != ''?esc_html($v_ask['title']):'')." ".($poll_voters != ""?"( ".discy_count_number($poll_voters)." "._n("voter","voters",$poll_voters,"discy")." )":"").'
								</span>';
							}
							$poll_1 .= '<div class="progressbar">
							    <div class="progressbar-percent '.($poll_voters == 0?"poll-result":"").'" attr-percent="'.($poll_voters == 0?100:$value_poll).'"></div>
							</div>';
							if ($poll_image == "on" && isset($v_ask['image']) && esc_html(discy_image_url_id($v_ask['image'])) != "") {
								$poll_1 .= '</div>';
							}
						endforeach;
					$poll_1 .= '</div><!-- End progressbar-wrap -->
					<div class="poll-num">'.esc_html__("Based On","discy")." <span>".($question_poll_num > 0?discy_count_number($question_poll_num):0)." "._n("Vote","Votes",$question_poll_num,"discy")."</span>".'</div>';
				}else {
					$poll_1 .= '<p class="still-not-votes">'.esc_html__("No votes. Be the first one to vote.","discy").'</p>';
				}
			}
			if ($question_poll_yes == false) {
				$poll_1 .= '<input type="submit" class="ed_button poll_polls" value="'.esc_attr__("Voting","discy").'"">';
			}
		$poll_1 .= '</div>';
		echo apply_filters("discy_show_poll",$poll_1,$poll_user_only,$user_id,$question_poll_yes,$question_poll_num,$asks,$wpqa_polls,$poll_image);?>
		<div class="clear"></div>
		<?php if ($question_poll_yes == false) {
			$question_poll_title = discy_post_meta("question_poll_title");?>
			<div class="poll_2">
				<h3><i class="icon-help"></i><?php echo ($question_poll_title != ""?$question_poll_title:esc_html__("Participate in Poll, Choose Your Answer.","discy"))?></h3>
				<form class="wpqa_form">
					<div class="form-inputs clearfix<?php echo ($poll_image == "on"?" form-input-polls":"")?>">
						<?php foreach($asks as $v_ask):?>
							<p class="wpqa_radio_p<?php echo ($poll_image == "on" && isset($v_ask['image']) && esc_html(discy_image_url_id($v_ask['image'])) != ""?" wpqa_poll_image":"")?>">
								<span class="wpqa_radio">
									<input class="required-item" id="ask-<?php echo esc_attr($v_ask['id'])?>-title-<?php echo esc_attr($post_data->ID)?>" name="ask_radio" type="radio" value="poll_<?php echo (int)$v_ask['id']?>"<?php echo (isset($v_ask['title']) && $v_ask['title'] != ''?' data-rel="poll_'.esc_html($v_ask['title']).'"':'')?>>
									<?php if ($poll_image == "on" && isset($v_ask['image']) && esc_html(discy_image_url_id($v_ask['image'])) != "") {
										echo discy_get_aq_resize_img(212,160,"",esc_html($v_ask['image']['id']),"",(isset($v_ask['title']) && $v_ask['title'] != ''?esc_html($v_ask['title']):''));
									}?>
								</span>
								<label for="ask-<?php echo esc_attr($v_ask['id'])?>-title-<?php echo esc_attr($post_data->ID)?>"><?php echo (isset($v_ask['title']) && $v_ask['title'] != ''?esc_html($v_ask['title']):'')?></label>
							</p>
						<?php endforeach;?>
					</div>
					<?php if ($question_poll_yes == false) {?>
						<div class="load_span"><span class="loader_2"></span></div>
						<input type='submit' class="ed_button poll-submit button-default" value='<?php esc_attr_e("Submit","discy")?>'>
						<input type='submit' class='ed_button poll_results' value='<?php esc_attr_e("Results","discy")?>'>
					<?php }?>
				</form>
			</div>
		<?php }
	}?>
</div><!-- End poll-area -->