<?php
$fonts = array(
    'Arial'     => 'Arial',
    'Georgia'   => 'Georgia',
    'Helvetica' => 'Helvetica',
    'Verdana'   => 'Verdana'
);

if( ! empty( $field['google_font'] ) ){
    $google_fonts = LPR_Certificate_Helper::google_fonts();
}else{
    $google_fonts = null;
}
?>
<select name="<?php echo $field['name'];?>">
    <?php if( $google_fonts ){?>
    <optgroup label="<?php _e( 'System fonts', 'lpr_certificate' );?>">
    <?php }?>
    <?php foreach( $fonts as $name => $text ){?>
        <option value="<?php echo $name;?>" <?php selected( ! empty( $field['std'] ) && $field['std'] == $name ? 1 : 0, 1 );?>><?php echo $text;?></option>
    <?php }?>
    <?php if( $google_fonts ){?>
    </optgroup>
    <?php }?>

    <?php if( $google_fonts ){?>
    <optgroup label="<?php _e( 'Google fonts', 'lpr_certificate' );?>">
        <?php foreach( $google_fonts as $name => $text ){?>
            <option value="::<?php echo $text;?>" <?php selected( ! empty( $field['std'] ) && $field['std'] == $text ? 1 : 0, 1 );?>><?php echo $text;?></option>
        <?php }?>

    </optgroup>
    <?php }?>
</select>