<?php

/**
 * Created by PhpStorm.
 * User: Tu
 * Date: 27/03/2015
 * Time: 11:42 SA
 * Modified 03 Apr 2015
 */
class LPR_Question_Type_Single_Choice extends LPR_Question_Type {
	function __construct( $type = null, $options = null ) {
		parent::__construct( $type, $options );
	}

	function submit_answer( $quiz_id, $answer ) {
		$questions = learn_press_get_question_answers( null, $quiz_id );
		if ( !is_array( $questions ) ) $questions = array();
		$questions[$quiz_id][$this->get( 'ID' )] = is_array( $answer ) ? reset( $answer ) : $answer;
		learn_press_save_question_answer( null, $quiz_id, $this->get( 'ID' ), is_array( $answer ) ? reset( $answer ) : $answer );
		//print_r($answer);
	}

	function admin_script() {
		parent::admin_script();
		?>

		<?php

	}

	/**
	 * @param bool $enqueue
	 */
	private function _admin_enqueue_script( $enqueue = true ) {
		ob_start();
		$key = 'question_' . $this->get( 'ID' );
		?>
		<script type="text/javascript">
			(function ($) {
				var $form = $('#post');
				$form.unbind('learn_press_question_before_update.<?php echo $key;?>').on('learn_press_question_before_update.<?php echo $key;?>', function () {
					var $question = $('.lpr-question-single-choice[data-id="<?php echo $this->get('ID');?>"]');
					if ($question.length) {
						var $input = $('.lpr-is-true-answer input[type="radio"]:checked', $question);
						if (0 == $input.length) {
							var message = $('.lpr-question-title input', $question).val();
							message += ": " + '<?php _e( 'No answer added to question or you must set an answer is correct!', 'learn_press' );?>'
							window.learn_press_before_update_quiz_message.push(message);
							return false;
						}
					}
				});
			})(jQuery);
		</script>
		<?php
		$script = ob_get_clean();
		if ( $enqueue ) {
			$script = preg_replace( '!</?script.*>!', '', $script );
			learn_press_enqueue_script( $script );
		} else {
			echo $script;
		}
	}

	function admin_interface( $args = array() ) {
		$uid     = uniqid( 'lpr_question_answer' );
		$post_id = $this->get( 'ID' );
		$this->admin_interface_head( $args );
		?>
		<table class="lpr-question-option lpr-question-answer lpr-sortable">
			<thead>
			<th width="20"></th>
			<th width="100"><?php _e( 'Is Correct?', 'learn_press' ); ?></th>
			<th><?php _e( 'Answer Text', 'learn_press' ); ?></th>
			<th width="40"></th>
			</thead>
			<tbody>
			<?php if ( $answers = $this->get( 'options.answer' ) ): foreach ( $answers as $i => $ans ): ?>
				<?php $value = $this->_get_option_value( $this->get( 'options.answer.' . $i . '.value' ) );
				$image = $this->_get_option_value( $this->get( 'options.answer.' . $i . '.image' ) ); ?>
				<tr>
					<td class="lpr-sortable-handle">
						<i class="dashicons dashicons-sort"></i>
					</td>
					<td class="lpr-is-true-answer">
						<input type="radio" name="lpr_question[<?php echo $post_id; ?>][checked]" value="<?php echo $value;?>" <?php checked( $this->get( 'options.answer.' . $i . '.is_true', 0 ) ? 1 : 0 ); ?> />
					</td>
					<td>
						<p>	
							<label>Audio / Video / Image URL</label>
					        <input type="text" name="lpr_question[<?php echo $post_id; ?>][answer][image][]" class="lpr-answer-image meta-image" value="<?php echo $image; ?>" />
					        <input type="button" class="button meta-image-button" value="Choose or Upload an Image" />
					    </p>
					    <label>Answer Text (optional)</label>
						<input type="text" class="lpr-answer-text" name="lpr_question[<?php echo $post_id; ?>][answer][text][]" value="<?php echo esc_attr( $this->get( 'options.answer.' . $i . '.text', '' ) ); ?>" />
						<input type="hidden" name="lpr_question[<?php echo $post_id; ?>][answer][value][]" value="<?php echo $value; ?>" />
					</td>
					<td align="center" class="lpr-remove-answer"><i class="dashicons dashicons-trash"></td>
				</tr>
			<?php endforeach;endif; ?>
			<tr class="lpr-disabled">
				<?php $value = $this->_get_option_value();?>
				<td class="lpr-sortable-handle">
					<i class="dashicons dashicons-sort"></i>
				</td>
				<td class="lpr-is-true-answer">
					<input type="radio" name="lpr_question[<?php echo $post_id; ?>][checked]" value="<?php echo $value;?>" value="<?php echo $value;?>" />
				</td>
				<td>
					<p>
						<label>Audio / Video / Image URL</label>
				        <input type="text" name="lpr_question[<?php echo $post_id; ?>][answer][image][]" class="lpr-answer-image meta-image" value="" />
				        <input type="button" class="button meta-image-button" value="Choose or Upload an Image" />
				    </p>
				    <label>Answer Text (optional)</label>
					<input type="text" class="lpr-answer-text" name="lpr_question[<?php echo $post_id; ?>][answer][text][]" value="" />
					<input type="hidden" name="lpr_question[<?php echo $post_id; ?>][answer][value][]" value="<?php echo $value; ?>" />
				</td>
				<td align="center" class="lpr-remove-answer"><i class="dashicons dashicons-trash"></td>
			</tr>
			</tbody>
		</table>
		<input type="hidden" name="lpr_question[<?php echo $post_id; ?>][type]" value="<?php echo $this->get_type(); ?>">
		<p>
			<button type="button" class="button lpr-button-add-answer"><?php _e( 'Add answer', 'learn_press' ); ?></button>
		</p>

		<!--
		<label><?php _e( 'Question Explanation', 'learn_press' ) ?></label>
		<?php if ( $explaination = $this->get( 'options.explaination' ) ) {
			echo '<textarea rows="4" name="lpr_question[' . $post_id . '][explaination]">' . $explaination . '</textarea>';
		} else {
			echo '<textarea rows="4" name="lpr_question[' . $post_id . '][explaination]"></textarea>';
		} ?>
		-->
		<script type="text/javascript">
			(function ($) {
				var $form = $('#post');				

			    // Runs when the image button is clicked.
			    $(document).on('click', '.meta-image-button', function(e){
			    	e.preventDefault();
			    	var clicked = $(this);
					var custom_uploader = wp.media({
				        title: 'Select Images',
				        button: {
				            text: 'Select'
				        },
				        multiple: false  // Set this to true to allow multiple files to be selected
				    })
				    .on('select', function() {
				        var attachment = custom_uploader.state().get('selection').first().toJSON();
				        //console.log(attachment);
				        clicked.siblings('.meta-image').val(attachment.url);
			            updateAnswersImg(clicked.closest('.lpr-question'));
				    })
				    .open();
				});

				function updateAnswersImg($element) {
					$rows = $('.lpr-question-option tbody', $element).children();
					if ($rows.length == 1) {
						$rows.addClass('lpr-disabled');
					} else {
						$rows.filter(function () {
							if ( ( $('.lpr-answer-image', this).val() + '' ).length == 0) {
								$(this).addClass('lpr-disabled');
							} else {
								$(this).removeClass('lpr-disabled');
							}
						})
					}
					$element.trigger('lpr_update_item_index')
				}
			})(jQuery);			
		</script>
		<?php
		$this->admin_interface_foot( $args );
		$this->_admin_enqueue_script( false );
	}


	function save_post_action() {
		$screen = get_current_screen();
		//echo '('.$this->id.')';
		if ( $post_id = $this->id ) {
			$post_data    = isset( $_POST['lpr_question'] ) ? $_POST['lpr_question'] : array();
			$post_answers = array();
			if ( isset( $post_data[$post_id] ) && $post_data = $post_data[$post_id] ) {
				$checked = $post_data['checked'];
				wp_update_post(
						array(
								'ID'         => $post_id,
								'post_title' => $screen->id == 'lpr_question' ? $_POST['post_title'] : $post_data['text'],
								'post_type'  => 'lpr_question'
						)
				);
				$index = 0;
				foreach ( $post_data['answer']['text'] as $k => $txt ) {
					$img = $post_data['answer']['image'][$k];

					if ( !$txt && !$img) continue;
					$option_value = $post_data['answer']['value'][$k];
					$option_img = $post_data['answer']['image'][$k];
					$post_answers[$index ++] = array(
							'text'    => $txt,
							'is_true' => $checked == $option_value ? 1 : 0,
							'value' => $option_value,
							'image' => $option_img
					);
				}
			}
			$post_data['answer']       = $post_answers;
			$post_data['type']         = $this->get_type();
			//$post_data['explaination'] = $post_explain;
			update_post_meta( $post_id, '_lpr_question', $post_data );
		}
		return $post_id;
	}

	function render( $args = null ) {
		$unique_name = uniqid( 'lp_question_answer_' . $this->get( 'ID' ) . '_' );
		$answer      = '';
		$load_user_answer = false;
		is_array( $args ) && extract( $args );
		if ( !is_null( $answer ) )
			$answer = (array) $answer;
		else if( $load_user_answer ) {
		}else{
			$answer = array();
		}
		?>
		<div class="lp-question-wrap lp-single-choise-question question-<?php echo $this->get( 'ID' ); ?>">
			<?php do_action( 'learn_press_before_question_title', $this->get( 'ID' ) ); ?>
			<h4 class="question-title"><?php echo get_the_title( $this->get( 'ID' ) ); ?></h4>
			<?php do_action( 'learn_press_after_question_title', $this->get( 'ID' ) ); ?>
			<div class="question-content">
				<?php ob_start();?>
				<ul>
					<?php if ( $answers = $this->get( 'options.answer' ) ) foreach ( $answers as $k => $ans ): ?>
						<li>
							<?php settype($k, 'string');?>
							<label>
								<input type="radio" name="<?php echo $unique_name; ?>" <?php checked( in_array( $k, $answer, true ) ? 1 : 0 ); ?> value="<?php echo $k; ?>">
								<?php echo $this->get( "options.answer.{$k}.text" ); ?>
							</label>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php echo apply_filters( 'learn_press_question_content', ob_get_clean(), $answer, $this );?>
			</div>
			<?php do_action( 'learn_press_after_question_content', $this->get( 'ID' ) ); ?>
		</div>
		<?php
	}

	function check( $args = false ) {
		$answer = false;
		is_array( $args ) && extract( $args );
		$return = array(
				'correct' => false,
				'mark'    => 0
		);

		if ( is_numeric( $answer ) ) {
			if ( $this->get( 'options.answer.' . $answer . '.is_true' ) ) {
				$return['correct'] = true;
				$return['mark']    = intval( get_post_meta( $this->get( 'ID' ), '_lpr_question_mark', true ) );
			}
		}
		return $return;
	}

	public static function admin_js_template(){
		?>
		<script type="text/html" id="tmpl-single-choice-question-answer">
			<tr class="lpr-disabled">
				<td class="lpr-sortable-handle">
					<i class="dashicons dashicons-sort"></i>
				</td>
				<td class="lpr-is-true-answer">
					<input type="radio" name="lpr_question[{{data.question_id}}][checked]" value="{{data.option_value}}" />
				</td>
				<td>
					<p>
						<label>Audio / Video / Image URL</label>
				        <input type="text" name="lpr_question[{{data.question_id}}][answer][image][]" class="lpr-answer-image meta-image" value="" />
				        <input type="button" class="button meta-image-button" value="Choose or Upload a File" />
				    </p>
				    <label>Answer Text (optional)</label>
					<input type="text" class="lpr-answer-text" name="lpr_question[{{data.question_id}}][answer][text][]" value="" />
					<input type="hidden" name="lpr_question[{{data.question_id}}][answer][value][]" value="{{data.option_value}}" />
				</td>
				<td align="center" class="lpr-remove-answer"><i class="dashicons dashicons-trash"></td>
			</tr>
		</script>
		<?php
	}
}
///