<?PHP
/**
 * @package Events_Compass
 * @version 1.6
 */
/*
Plugin Name: Events Compass Post Type
Plugin URI: 
Description: Events Handler
Author: Andy C
Version: 0.1
Author URI: http://ac31004.blogspot.com
*/
add_action( 'init', 'create_post_type' );
include_once('includes/EventsCustomContent.php');
add_action('do_meta_boxes','EventsCustomContent::remove_default_custom_fields',10,3);
add_action( 'admin_menu',  'EventsCustomContent::create_meta_box' );
add_action( 'save_post', 'EventsCustomContent::save_custom_fields', 1, 2 );
add_action( 'widgets_init', 'CompassEvent_register_widgets' );

register_activation_hook(__FILE__, 'add_options');

function create_post_type() {
	register_post_type( 'Event_Compass',
		array(
			'labels' => array(
				'name' => __( 'Events' ),
				'singular_name' => __( 'Event' )
			),
		'public' => true,
		'has_archive' => true,
		'rewrite' => array('slug' => 'events'),
		'supports' => array('title', 'editor','custom-fields','comments')

		)
	);
}




function List_Events_Compass() {
	
	$args = array( 'post_type' => 'Event_Compass', 'posts_per_page' => 5 );
    $loop = new WP_Query( $args );
	
    echo "<div id='CompassEvents'>";
    if ($loop->have_posts()){
       echo "<ul>";
    }
    while ( $loop->have_posts() ): 
       $loop->the_post();
       echo "<li class='Event'>";
       echo "<a href='";
       the_permalink();
       echo "'>";
       the_title();
       echo"</a>"; 
	  
	   echo "</li>";
    endwhile;
    
    
	if ($loop->have_posts()){
       echo "</ul>";
    }
	echo "</div>";

}




class CompassEventWidget extends WP_Widget {

	function CompassEventWidget() {
		// Instantiate the parent object
		parent::__construct( false, 'Compass Events' );
	}

	function widget( $args, $instance ) {
		// Widget output
		
		echo "<h3 class='widget-title'>Compass Events</h3>";
		 List_Events_Compass();
	}

	function update( $new_instance, $old_instance ) {
		// Save widget options
	}

	function form( $instance ) {
		// Output admin widget options form
	}
}

function CompassEvent_register_widgets() {
	
	register_widget( 'CompassEventWidget' );
}



function add_options(){

		$template['page_template'] 			= '<p><strong>%title%</strong>, %event% '.__('on', 'wpevents').' %startdate% %starttime%<br />%countdown%<br />'.__('Duration', 'wpevents').': %duration%<br />%link%</p>';
		$template['page_h_template'] 		= '<h2>%category%</h2>';
		$template['page_title_default']		= __('Important events', 'wpevents');
		$template['page_f_template'] 		= '';
		$template['archive_template'] 		= '<p><strong>%title%</strong>, %after% '.__('on', 'wpevents').' %startdate% %starttime%<br />%countup%<br />%enddate% %endtime%<br />%link%</p>';
		$template['archive_h_template'] 	= '<h2>%category%</h2>';
		$template['archive_title_default']	= __('Archive', 'wpevents');
		$template['archive_f_template'] 	= '';
		$template['daily_template'] 		= '<p>%title% %event% - %countdown% %link%</p>';
		$template['daily_h_template'] 		= '<h2>%category%</h2>';
		$template['daily_title_default']	= __('Today\'s events', 'wpevents');
		$template['daily_f_template'] 		= '';
		$template['calendar_template'] 		= '<p><strong>%title%</strong>, %event% '.__('on', 'wpevents').' %startdate% %starttime%<br />%countdown%<br />'.__('Duration', 'wpevents').': %duration%<br />%link%</p>';
		$template['calendar_h_template'] 	= '<h2>'.__('Highlighted events', 'wpevents').'</h2>';
		$template['calendar_f_template'] 	= '';
		$template['location_seperator']		= __('@', 'wpevents').' ';
		update_option('CompassEvents_template', $template);

$events_template = get_option('CompassEvents_template');

foreach ($events_template as $event){
	echo $event;
	}

}
?>