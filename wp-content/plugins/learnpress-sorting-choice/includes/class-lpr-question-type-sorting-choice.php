<?php

/**
 * Class LPR_Question_Type_Sorting_Choice
 *
 * @extend LPR_Question_Type
 */
class LPR_Question_Type_Sorting_Choice extends LPR_Question_Type{
    /**
     * Constructor
     *
     * @param null $type
     * @param null $options
     */
    function __construct( $type = null, $options = null ){
        static $loaded = false;
        parent::__construct( $type, $options );

        if( $loaded ) return;
        add_action( 'learn_press_question_suggestion_sorting_choice', array( $this, 'suggestion' ), 5, 2 );
        add_filter( 'learn_press_question_meta_box_args', array( $this, 'admin_options' ) );
        add_action( 'rwmb_before_question_settings', array( $this, 'remove_settings_field' ) );
        $loaded = true;
    }

    /**
     * Add new options to question
     *
     * @param $args
     * @return mixed
     */
    function admin_options( $args ){
        $args['fields']['_lpr_sorting_choice_mark_result'] = array(
            'name'  => __( 'Mark result', 'learnpress_sorting_choice' ),
            'id'    => "_lpr_sorting_choice_mark_result",
            'type'  => 'radio',
            'clone' => false,
            'desc'  => 'Mark result for this question',
            'std'   => 'correct_blanks',
            'options' => array(
                'correct_blanks' => __( 'Mark is calculated by total of correct options', 'learnpress_sorting_choice' ),
                'correct_all' => __( 'Requires correct all options', 'learnpress_sorting_choice' )
            )
        );

        return $args;
    }

    /**
     * Save question user submitted
     *
     * @param $quiz_id
     * @param $answer
     */
    function submit_answer( $quiz_id, $answer ){
        $questions = learn_press_get_question_answers( null, $quiz_id );
        if( !is_array( $questions ) ) $questions = array();
        $questions[$quiz_id][$this->get('ID')] = is_array( $answer ) ? reset( $answer ) : $answer;
        learn_press_save_question_answer( null, $quiz_id, $this->get('ID'), is_array( $answer ) ? reset( $answer ) : $answer);
    }

    /**
     * Get answer text by unique id
     *
     * @param array
     * @param string
     * @return bool
     */
    private function get_answer_text( $arr, $uniqid ){
        foreach( $arr as $a ){
            if( $a['uniqid'] == $uniqid ) return $a['text'];
        }
        return false;
    }

    /**
     * Show answer suggestion if it is enabled
     *
     * @param $ques
     * @param $answered
     */
    function suggestion( $ques, $answered ){
        $options = $ques->get('options.answer');
    ?>
        <ul class="lpr-question-hint sorting-choice">
            <?php foreach( $options as $i => $option ):?>
            <?php
            $index = array_search( $option['uniqid'], $answered );
            $correct = ( $index !== false && $index === $i );
            ?>
            <li class="<?php echo $correct ? 'correct' : 'wrong';?>">
                <?php echo $option['text'];?>
                <?php if( ! $correct ){?>
                <div class="correct-answer">
                    <span class="correct-label"><?php _e( 'Correct:', 'learnpress_sorting_choice' );?></span>
                    <?php echo $this->get_answer_text( $options, ! empty( $answered[$i] ) ? $answered[$i] : '' );?>
                </div>
                <?php }?>
            </li>
            <?php endforeach;?>
        </ul>
    <?php
    }

    /**
     * Display admin interface
     *
     * @param array $args
     */
    function admin_interface( $args = array() ){
        $post_id = $this->get('ID');
        $this->admin_interface_head( $args );

        ?>
        <table class="lpr-question-option lpr-question-answer lpr-sortable">
            <thead>
            <th width="20"></th>
            <th><?php _e('Answer Text', 'learnpress');?></th>
            <th class="lpr-remove-answer" width="40"></th>
            </thead>
            <tbody>
            <?php if( $answers = $this->get('options.answer') ): $i = 0; foreach( $answers as $i => $ans ):?>
                <tr>
                    <td class="lpr-sortable-handle">
                        <i class="dashicons dashicons-sort"></i>
                    </td>
                    <td>
                        <input type="text" class="lpr-answer-text" name="lpr_question[<?php echo $post_id;?>][answer][text][__INDEX__<?php echo $i;?>]" value="<?php echo esc_attr( $this->get( 'options.answer.'.$i.'.text', __( '', 'learnpres' ) ) );?>" />
                        <input type="hidden" name="lpr_question[<?php echo $post_id;?>][answer][uniqid][__INDEX__<?php echo $i;?>]" value="<?php echo ! empty( $ans['uniqid'] ) ? $ans['uniqid'] : '';?>" />
                    </td>
                    <td align="center" class="lpr-remove-answer"><i class="dashicons dashicons-trash"></td>
                </tr>
            <?php endforeach; endif;?>
            <tr class="lpr-disabled">
                <td class="lpr-sortable-handle">
                    <i class="dashicons dashicons-sort"></i>
                </td>
                <td><input class="lpr-answer-text" type="text" name="lpr_question[<?php echo $post_id;?>][answer][text][__INDEX__]" value="" /></td>
                <td align="center" class="lpr-remove-answer"><span class=""><i class="dashicons dashicons-trash"></i></span> </td>
            </tr>
            </tbody>
        </table>
        <input type="hidden" name="lpr_question[<?php echo $post_id;?>][type]" value="<?php echo $this->get_type();?>">
        <p><button type="button" class="button lpr-button-add-answer"><?php _e('Add answer', 'learnpress');?></button> </p>
        <?php

        $this->admin_interface_foot( $args );
        $this->_admin_enqueue_script();
    }

    /**
     * Enqueue some script
     */
    private function _admin_enqueue_script(){
        ob_start();
        ?>
        <script type="text/javascript">
            (function() {
                $('.lpr-question-sorting-choice').lprSortingChoice();
            })();
        </script>
        <?php
        $script = ob_get_clean();
        $script = preg_replace( '!</?script.*>!', '', $script );
        learn_press_enqueue_script( $script );
    }

    /**
     * Create an unique id
     *
     * @return string
     */
    function generate_uniqid(){
        return md5( microtime() );
    }

    /**
     * Update question data when saving post
     *
     * @return int
     */
    function save_post_action(){
        if( $post_id = $this->ID ){
            $post_data = isset( $_POST['lpr_question'] ) ? $_POST['lpr_question'] : array();
            $post_answers = array();
            if( isset( $post_data[$post_id] ) && $post_data = $post_data[$post_id] ){
                $post_args = array(
                    'ID'            => $post_id,
                    'post_title'    => $post_data['text'],
                    'post_type'     => 'lpr_question'
                );
                wp_update_post( $post_args );
                $index = 0;
                foreach( $post_data['answer']['text'] as $k => $txt ){
                    if( !$txt ) continue;
                    $uniqid = ! empty( $post_data['answer']['uniqid'][ $k ] ) ?  $post_data['answer']['uniqid'][ $k ] : $this->generate_uniqid();
                    $post_answers[$index] = array(
                        'text'      => $txt,
                        'uniqid'    => $uniqid
                    );
                    $index++;
                }
            }
            $post_data['answer']    = $post_answers;
            $post_data['type']      = $this->get_type();
            update_post_meta( $post_id, '_lpr_question', $post_data );
        }

        //die();
        return intval( $post_id );
    }

    /**
     * Render question in front end
     *
     * @param null $args
     */
    function render( $args = null ){
        $unique_name = uniqid( 'lp_question_answer_' . $this->get('ID') . '_' );
        $answer = false;
        is_array( $args ) && extract( $args );

        $answers = $this->get('options.answer');

        shuffle( $answers );

        if( $answer ) settype( $answer, 'array' );
        else $answer = array();

        ?>
        <div class="lp-question-wrap question-<?php echo $this->get('ID');?> question-<?php echo $this->get_type();?>">
            <h4><?php echo get_the_title( $this->get('ID') );?></h4>
            <ul>
                <?php if( $answers ) foreach( $answers as $k => $ans ):?>
                    <li>
                        <label>
                            <input type="hidden" name="<?php echo $unique_name;?>[]" value="<?php echo ! empty( $ans['uniqid'] ) ? $ans['uniqid'] : '';?>" />
                            <?php echo $ans['text'];?>
                        </label>
                    </li>
                <?php endforeach;?>
            </ul>
            <script style="text/javascript">
                jQuery(function($){
                    $('.lp-question-wrap.question-<?php echo $this->get('ID');?> ul').sortable({
                        axis: 'y'
                    });
                })
            </script>
        </div>
    <?php
    }

    /**
     * Check the result of the question
     *
     * @param array $a
     * @return array
     */
    function check( $a = array() )
    {
        $question_id = $this->get('ID');
        $question_mark = learn_press_get_question_mark($question_id);
        $mark_result = get_post_meta( $question_id, '_lpr_sorting_choice_mark_result', true );

        $return = array(
            'fills' => array(),
            'correct' => false,
            'mark' => 0
        );

        $options = $this->get('options.answer');
        $total = sizeof( $options );
        $correct = 0;
        $answer = ! empty( $a['answer'] ) ? $a['answer'] : array();
        if ($options) foreach ($options as $i => $option){
            /**
             * Search position of a choice and compare it with the position of a corresponding choice of user
             * If two positions is equals so this position is correct
             */
            $index = array_search( $option['uniqid'], $answer );
            if($index !== false && $index === $i){
                $correct++;
            }
        }
        if( $mark_result != 'correct_all' ){
            $return['mark'] = ( $correct / $total ) * $question_mark;
        }elseif( $correct == $total ){
            $return['mark'] = $question_mark;
        }
        $return['correct'] = ( $correct == $total );
        return $return;
    }

    /**
     *
     */
    function remove_settings_field( $instance ){
        global $post;
        $question_settings = (get_post_meta($post->ID, '_lpr_question', true) );
        if( ! $question_settings || ( $question_settings && $question_settings['type'] != 'sorting_choice' ) ){
            if( ! empty( $instance->meta_box['fields']['_lpr_sorting_choice_mark_result'] ) ) {
                unset($instance->meta_box['fields']['_lpr_sorting_choice_mark_result']);
            }
        }
    }
}
new LPR_Question_Type_Sorting_Choice();