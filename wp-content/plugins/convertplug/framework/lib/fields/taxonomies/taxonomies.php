<?php
// Add new input type "taxonomies"
if ( function_exists('smile_add_input_type'))
{
	smile_add_input_type('taxonomies' , 'taxonomies_settings_field' );
}

/**
* Function to handle new input type "taxonomies"
*
* @param $settings		- settings provided when using the input type "taxonomies"
* @param $value			- holds the default / updated value
* @return string/html 	- html output generated by the function
*/
function taxonomies_settings_field($name, $settings, $value)
{
	$input_name = $name;
	$type = isset($settings['type']) ? $settings['type'] : '';
	$class = isset($settings['class']) ? $settings['class'] : '';
	ob_start();
	?>
<select name="<?php echo esc_attr( $input_name ); ?>" id="smile_<?php echo esc_attr( $input_name ); ?>" class="select2-taxonomies-dropdown form-control smile-input <?php echo esc_attr( 'smile-'.$type.' '.$input_name.' '.$type.' '.$class ); ?>" multiple="multiple" style="width:260px;"> 
	<?php
	$args = array(
	   'public'   => true,
	   '_builtin' => false
	);
	
	$output = 'objects'; // names or objects, note names is the default
	$operator = 'and'; // 'and' or 'or'
    $taxonomies = get_taxonomies( $args, $output, $operator );
	
    foreach ( $taxonomies as $taxonomy ) {
		?>
        <optgroup label="<?php echo ucwords( $taxonomy->label ); ?>">
        <?php 
        $terms = get_terms( $taxonomy->name, array(
			'orderby'    => 'count',
			'hide_empty' => 0,
		 ) );
        
		foreach( $terms as $term ) { ?>
		<?php
			$val_arr = explode( ",", $value );
			$selected = ( in_array( $term->term_id, $val_arr) ) ? 'selected="selected"' : '';
		?>
			<option <?php echo $selected; ?> value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
		<?php
		}
	}
    ?>
    </optgroup>
</select>
<script type="text/javascript">
	jQuery('select.select2-taxonomies-dropdown').select2({
		 placeholder: "Select posts from - taxonomies",
	});
</script>
    <?php
	return ob_get_clean();
}