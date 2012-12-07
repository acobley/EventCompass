<?php

class EventsCustomContent{
   
   public static $prefix = 'event_content_';
   
   public static $content_types_array = array('Event_Compass');
   
   private static function _get_active_content_types() {
         
         return self::$content_types_array;
      }
      
    public static function remove_default_custom_fields( $type, $context,$post ) {
         error_log(" remove_default_custom_fields",0);
         $content_types_array = self::_get_active_content_types();
         foreach ( array( 'normal', 'advanced', 'side' ) as $context ) {
            foreach ( $content_types_array as $content_type ) {
               remove_meta_box( 'postcustom', $content_type, $context );
			} 
		}
    }

    public static function create_meta_box() {
         $content_types_array = self::_get_active_content_types();
         foreach ( $content_types_array as $content_type ) {
            add_meta_box( 'my-custom-fields'
               , 'Custom Fields'
               , 'EventsCustomContent::print_custom_fields'
	           , $content_type
               , 'normal'
               , 'high'
               , $content_type
	        ); 
         }
    }
    
    
    public static $custom_fields_for_posts =	array(
		array(
			'name'			=> 'location', 
			'title'			=> 'Location',
			'description'	=> '',	
			'type'			=> 'text',
		),
		array(
			'name'			=> 'short_description',
			'title'			=> 'Short Description',
			'description'	=> '',
			'type'			=> 'textarea',
		),
		array(
			'name'			=> 'event_date',
			'title'			=> 'Event Date',
			'description'	=> '',
			'type'			=> 'date',
		)
		
		
	);

	
	private static function _get_custom_fields($content_type) {
      return self::$custom_fields_for_posts;
    }
    
	public static function print_custom_fields($post, $callback_args='') {
		$content_type = $callback_args['args']; // the 7th arg from add_meta_box()
		$custom_fields = self::_get_custom_fields($content_type);
		$output = '';		
		
		foreach ( $custom_fields as $field ) {

			$output_this_field = '';			
			
			$field['value'] = htmlspecialchars( get_post_meta( $post->ID, $field['name'], true ) );
			$field['name'] = self::$prefix . $field['name']; // this ensures unique keys in $_POST
			
			switch ( $field['type'] ) 
			{
				case 'checkbox':
					$output_this_field .= self::_get_checkbox_element($field);
					//$output_this_field .= "<p>I'm a checkbox!</p>";
					break;
				case 'dropdown':
					$output_this_field .= self::_get_dropdown_element($field);
					//$output_this_field .= "<p>I'm a dropdown!</p>";
					break;
				case 'textarea':
					$output_this_field .= self::_get_textarea_element($field);
					//$output_this_field .= "<p>I'm a textarea!</p>";
					break;
				case 'wysiwyg':
					$output_this_field .= self::_get_wysiwyg_element($field);
					//$output_this_field .= "<p>I'm a WISWIG!</p>";
					break;
				case 'text':
				default:
					//$output_this_field .= "<p>I'm a text input!</p>";
					$output_this_field .= self::_get_text_element($field);
					break;
			}
			// optionally add description
			if ( $field['description'] ) 
			{
				$output_this_field .= '<p>'.$field['description'].'</p>';
			}
			
			$output .= '<div class="form-field form-required">'.$output_this_field.'</div>';
		}
 		// Print the form
 		print '<div class="form-wrap">';
	 	wp_nonce_field('update_custom_content_fields','custom_content_fields_nonce');
	 	print $output;
	 	print '</div>';
 
	}
	
	public static function parse($tpl, $hash) {
	
	    foreach ($hash as $key => $value) {
	        $tpl = str_replace('[+'.$key.'+]', $value, $tpl);
	    }
	    return $tpl;
	}
	
	private static function _get_text_element($data)
	{
		$tpl = '<label for="[+name+]"><strong>[+title+]</strong></label><br/>
				<input type="text" name="[+name+]" id="[+name+]" value="[+value+]" /><br/>';
		return self::parse($tpl, $data);
	}
	
	private static function _get_wysiwyg_element($data)
	{
		$tpl = '<label for="[+name+]"><strong>[+title+]</strong></label>
			<textarea name="[+name+]" id="[+name+]" columns="30" rows="3">[+value+]</textarea>
			<script type="text/javascript">
				jQuery( document ).ready( function() {
					jQuery( "[+name+]" ).addClass( "mceEditor" );
					if ( typeof( tinyMCE ) == "object" && typeof( tinyMCE.execCommand ) == "function" ) {
						tinyMCE.execCommand( "mceAddControl", false, "[+name+]" );
					}
				});
			</script>
			';	
		return self::parse($tpl, $data);
	}
	
		private static function _get_textarea_element($data)
	{
		$tpl = '<label for="[+name+]"><strong>[+title+]</strong></label><br/>
			<textarea name="[+name+]" id="[+name+]" columns="30" rows="3">[+value+]</textarea>';
		return self::parse($tpl, $data);	
	}

private static function _get_dropdown_element($data)
	{
		// Some error messaging.
		if ( !isset($data['options']) || !is_array($data['options']) )
		{
			return '<p><strong>Custom Content Error:</strong> No options supplied for '.$data['name'].'</p>';
		}
		$tpl = '<label for="[+name+]"><strong>[+title+]</strong></label><br/>
			<select name="[+name+]" id="[+name+]">
			[+options+]  
			</select>';

		$option_str = '<option value="">Pick One</option>';
		foreach ( $data['options'] as $option )
		{
			$option = htmlspecialchars($option); // Filter the values
			$is_selected = '';
			if ( $data['value'] == $option )
			{
				$is_selected = 'selected="selected"';
			}
			$option_str .= '<option value="'.$option.'" '.$is_selected.'>'.$option.'</option>';
		}
		
		unset($data['options']); // the parse function req's a simple hash.
		$data['options'] = $option_str; // prep for parsing
		
		return self::parse($tpl, $data);
		
	}
	private static function _get_checkbox_element($data)
	{
		$tpl ='<input type="checkbox" name="[+name+]" id="[+name+]" value="yes" [+is_checked+] style="width: auto;"/> 
		<label for="[+name+]" style="display:inline;"><strong>[+title+]</strong></label>';
		// Special handling to see if the box is checked.
		if ( $data['value'] == "yes" )
		{
			$data['is_checked'] = 'checked="checked"';
		}
		else
		{
			$data['is_checked'] = '';
		}
	
		return self::parse($tpl, $data);
	}
public static function save_custom_fields( $post_id, $post ) {
		// The 2nd arg here is important because there are multiple nonces on the page
		if ( !empty($_POST) && check_admin_referer('update_custom_content_fields','custom_content_fields_nonce') )
		{			
			$custom_fields = self::_get_custom_fields($post->post_type);
			
			foreach ( $custom_fields as $field ) {
				if ( isset( $_POST[ self::$prefix . $field['name'] ] ) )
				{
					$value = trim($_POST[ self::$prefix . $field['name'] ]);
					// Auto-paragraphs for any WYSIWYG
					if ( $field['type'] == 'wysiwyg' ) 
					{
						$value = wpautop( $value );
					}
					update_post_meta( $post_id, $field[ 'name' ], $value );
				}
				// if not set, then it's an unchecked checkbox, so blank out the value.
				else 
				{
					update_post_meta( $post_id, $field[ 'name' ], '' );
				}
			}
			
		}
	}

    
}




?>
