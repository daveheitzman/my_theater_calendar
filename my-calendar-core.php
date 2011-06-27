<?php
function my_calendar_add_feed() {
	if ( get_option('mc_show_rss') == 'true' ) {
		add_feed( 'my-calendar-rss', 'my_calendar_rss' );
	}
	if ( get_option('mc_show_ical') == 'true' ) {
		add_feed( 'my-calendar-ics', 'my_calendar_ical' );
	}
}

if ( ! function_exists( 'is_ssl' ) ) {
	function is_ssl() {
		if ( isset($_SERVER['HTTPS']) ) {
		if ( 'on' == strtolower($_SERVER['HTTPS']) )
		 return true;
		if ( '1' == $_SERVER['HTTPS'] )
		 return true;
		} elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
		return true;
		}
	return false;
	}
}

function jd_calendar_plugin_action($links, $file) {
	if ($file == plugin_basename(dirname(__FILE__).'/my-calendar.php')) {
		$links[] = "<a href='admin.php?page=my-calendar-config'>" . __('Settings', 'my-calendar') . "</a>";
		$links[] = "<a href='admin.php?page=my-calendar-help'>" . __('Help', 'my-calendar') . "</a>";
	}
	return $links;
}

// Function to add the calendar style into the header
function my_calendar_wp_head() {
  global $wpdb, $wp_query;
  // If the calendar isn't installed or upgraded this won't work
  check_my_calendar();
  $styles = mc_get_style_path( get_option( 'my_calendar_css_file' ),'url' );
	if ( get_option('my_calendar_use_styles') != 'true' ) {
	
		$this_post = $wp_query->get_queried_object();
		if (is_object($this_post)) {
			$id = $this_post->ID;
		} 
		if ( get_option( 'my_calendar_show_css' ) != '' ) {
		$array = explode( ",",get_option( 'my_calendar_show_css' ) );
			if (!is_array($array)) {
				$array = array();
			}
		}
		if ( @in_array( $id, $array ) || get_option( 'my_calendar_show_css' ) == '' ) {
	// generate category colors
	$category_styles = '';
	$categories = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_CATEGORIES_TABLE . " ORDER BY category_id ASC");
	foreach ( $categories as $category ) {
			$class = "mc_".sanitize_title($category->category_name);
			$hex = (strpos($category->category_color,'#') !== 0)?'#':'';
			$color = $hex.$category->category_color;
		if ( get_option( 'mc_apply_color' ) == 'font' ) {
			$type = 'color';
		} else if ( get_option( 'mc_apply_color' ) == 'background' ) {
			$type = 'background';
		}
		if ( get_option( 'mc_apply_color' )  == 'font' || get_option( 'mc_apply_color' ) == 'background' ) {
		$category_styles .= "\n#jd-calendar .$class .event-title { $type: $color; }";
		}
	}
echo "
<link rel=\"stylesheet\" href=\"$styles\" type=\"text/css\" media=\"all\" />
<style type=\"text/css\">
<!--
.js #jd-calendar .details { display: none; }
/* Styles from My Calendar - Joseph C Dolson http://www.joedolson.com/ */
$category_styles
.mc-event-visible {
display: block!important;
}
-->
</style>";
		}
	}
}

// Function to deal with events posted by a user when that user is deleted
function mc_deal_with_deleted_user($id) {
  global $wpdb;
  check_my_calendar();
  // Do the query
  $wpdb->get_results( "UPDATE ".MY_CALENDAR_TABLE." SET event_author=".$wpdb->get_var("SELECT MIN(ID) FROM ".$wpdb->prefix."users",0,0)." WHERE event_author=".$id );
}

// Function to add the javascript to the admin header
function my_calendar_add_javascript() { 
global $wp_plugin_url;
	if ( isset($_GET['page']) && $_GET['page'] == 'my-calendar' ) {
		wp_enqueue_script('jquery.calendrical',$wp_plugin_url . '/my-calendar/js/jquery.calendrical.js', array('jquery') );
	}
}

function my_calendar_write_js() {
	if ( isset($_GET['page']) && $_GET['page']=='my-calendar') {
	?>
	<script type="text/javascript">
	//<![CDATA[
		var new_occurrences=0;
		function new_occ_html() {return '<p><label for="event_begin'+new_occurrences+'"><span>Date (required)</span></label> <input type="text" id="event_begin'+new_occurrences+'" name="event_begin'+new_occurrences+'" class="new_event_date_time" size="12" value="" /> <label for="event_time'+new_occurrences+'">Time (hh:mm am/pm)</label> <input type="text" id="event_time'+new_occurrences+'" name="event_time'+new_occurrences+'" size="12"	value="" /> </p>			<p>			<label for="event_end'+new_occurrences+'">End Date (YYYY-MM-DD)</label> <input type="text" name="event_end'+new_occurrences+'" id="event_end'+new_occurrences+'" size="12" value="" /> <label for="event_endtime'+new_occurrences+'">End Time (hh:mm am/pm)</label> <input type="text" id="event_endtime'+new_occurrences+'" name="event_endtime'+new_occurrences+'"  size="12" value="" /><div><label for="occ_text'+new_occurrences+'">Note</label> <input type="text" id="occ_text'+new_occurrences+'" name="occ_text'+new_occurrences+'" class="new_event_date_time" size="60" value=""></input><label>&nbsp;&nbspActive?</label><input type="checkbox" value="true" checked="true" id="occ_active'+new_occurrences+'" name="occ_active'+new_occurrences+'" ></div></p>';
		};
		
		function occ_row_html(num) {

			$begindate = "";
			$enddate = "";
			$begintime ="";
			$endtime = "";
			
			return '<tr><td style="width:20px;"></td><td> <input style="width:95%" type="text" id="new_occ_beg_date'+num+'" name="new_occ_beg_date'+num+'" size="12" value="'+$begindate+'"></input></td><td> <input style="width:95%" type="text" id="new_occ_beg_time'+num+'" name="new_occ_beg_time'+num+'" size="12"	value="" /></td><td><input  style="width:95%" type="text" name="new_occ_end_date'+num+'" id="new_occ_end_date'+num+'" size="12" value="'+$enddate+'" /></td><td><input style="width:95%" type="text" id="new_occ_end_time'+num+'" name="new_occ_end_time'+num+'"  size="12" value="'+$endtime+'" /></td><td><input style="width:95%" type="text" id="new_occ_text'+num+'" name="new_occ_text'+num+'" size="10" value=""></input></td><td><input type="checkbox" value="" checked="true" id="new_occ_active'+num+'" name="new_occ_active'+num+'" ></td></tr>';
		}
		
			function open_row_for_edit(row) {
				//make it only set up the calendar and time once 
					if (row.id !== "done") {
						jQuery('#up_occ_beg_date'+row+', #up_occ_beg_time'+row+', #up_occ_end_date'+row+', #up_occ_end_time'+row).calendricalDateTimeRange(); 
						row.id = "done";
					}
				}
		function confirm_occ_delete_html(){
			return "<span id=\"delete_checked_occurrences\"> Delete checked occurrences? &nbsp;&nbsp;&nbsp; <a class=\"delete\" id=\"occs_confirm_delete\">Delete</a><a class=\"button-primary\">Cancel</a></span> ";
			}
		jQuery(document).ready(function($) {
      /* jQuery( new_occ_html() ).insertBefore("#new_occurrence_accept"); */
			create_blank_occurrence();
			/* this is to add the drop-down time/date selectors for the multiple occurrence events: */
			jQuery("#delete_checked_occurrences").hide();
			jQuery("#delete_events_confirm").hide();
      //delete_checked_events();
			
      /*this is to add the drop-down time/date selectors regular 1-time and recurrence events: */
	    jQuery('#event_begin , #event_time, #event_end, #event_endtime').calendricalDateTimeRange();
	    set_occurrence_pane('');
	    jQuery('#up_occ_beg_date58, #up_occ_beg_time58, #up_occ_end_date58, #up_occ_end_time58').calendricalDateTimeRange();
	    
	    jQuery('.up_occ').each(function(ind,elem){
				jQuery('#up_occ_beg_date'+elem.id+', #up_occ_beg_time'+elem.id+', #up_occ_end_date'+elem.id+', #up_occ_end_time'+elem.id).calendricalDateTimeRange();
				});
	    
		});
		
		jQuery(document).ready(function($) {
			var id = 'event_desc';
			
			$('a.toggleVisual').click(
				function() {
					tinyMCE.execCommand('mceAddControl', false, id);
				}
			);
			$('a.toggleHTML').click(
				function() {
					tinyMCE.execCommand('mceRemoveControl', false, id);
				}
			);
		});
		function occurrence_edit(occ_id) {
				jQuery(edit_occ_html()).insertAfter('#'+occ_id);
	
			}

		function confirm_occ_delete(){
			var checked_occurrences = jQuery('.occ_del').filter(":checked");
			var wpnonce = jQuery('input[name~="_wpnonce"]').attr("value");
			var occs_list = '';
			var i = 0;
			if (checked_occurrences.length > 0) {
				var event_id = jQuery("#occurrences_table").attr("name");
				jQuery("#delete_checked_occurrences").hide();
				checked_occurrences.each(function(ind,occ) {
					occs_list +="&occ_del["+ind+"]="+this.id;//occ.attr("id");
					});
				jQuery("#occs_confirm_delete").attr("href","admin.php?page=my-calendar&event_id="+event_id+"&mode=occ_del&_wpnonce="+wpnonce+occs_list);
				jQuery("#delete_checked_occurrences").show(300);
				} 
		}		

		function create_blank_occurrence(){

			/* make sure the dates and times of the existing occurrences have been entered*/
			if ( (new_occurrences === 0 )	|| 
						(jQuery("#new_occ_beg_date"+new_occurrences).attr("value") != ""	
					&& jQuery("#new_occ_beg_time"+new_occurrences).attr("value") != ""
					&& jQuery("#new_occ_end_date"+new_occurrences).attr("value") != ""
					&& jQuery("#new_occ_end_time"+new_occurrences).attr("value") != "" )
			) {
			new_occurrences++;
			jQuery("#occurrences_table").append(occ_row_html(new_occurrences));
/*			jQuery(new_occ_html() ).insertBefore("#new_occurrence_accept"); */
			/* add the js datepicker dropdowns*/
	    jQuery('#new_occ_beg_date'+new_occurrences+', #new_occ_beg_time'+new_occurrences+',#new_occ_end_date'+new_occurrences+', #new_occ_end_time'+new_occurrences).calendricalDateTimeRange();
			jQuery("#new_occurrence_count").attr("value",new_occurrences+1);
			}
			
		};
		function set_occurrence_pane(va) {
			/*what is the status of the reoccurrence type dropdown? */
			var va = jQuery("#event_recur").attr("value");
			//jQuery(".inside").append("my-calendar-core.set_occurrence_pane():154 "+va);
			if (va == 'G') {
				jQuery("#occurrences_regular").slideUp('fast');
				jQuery("#occurrences_g").slideDown('fast');
				jQuery(".recurrence_explanation").hide();
				} else {
				jQuery("#occurrences_g").slideUp('fast');
				jQuery("#occurrences_regular").slideDown('fast');
				jQuery(".recurrence_explanation").show();
				}
		}; 
		function delete_checked_events() {
        var n=jQuery("#my-calendar-admin-table input").filter(":checked");
        if (n.length >0){
          jQuery("#delete_events_confirm").show(400);  
        }
				
		};
	//]]>
	</script>
	<?php
	$mc_input = get_option( 'mc_input_options' );
	if ( $mc_input['event_use_editor'] == 'on' ) {
		wp_tiny_mce( true , 
			array( "editor_selector" => "event_desc", 'theme'=>'advanced' )
		);
		add_action( 'admin_print_footer_scripts', 'wp_tiny_mce_preload_dialogs', 30 );	
	}	
	}
	if ( isset($_GET['page']) && $_GET['page']=='my-calendar-help') {
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('dd:even').css('background','#f6f6f6');
	});
	</script>
	<?php
	}
}

function my_calendar_add_display_javascript() {
	wp_enqueue_script('jquery');
}

function my_calendar_calendar_javascript() {
	if ( !mc_is_mobile() ) {
		global $wpdb, $wp_query, $wp_plugin_url;

		if ( get_option('calendar_javascript') != 1 || get_option('list_javascript') != 1 || get_option('mini_javascript') != 1 || get_option('ajax_javascript') != 1 ) {
		  
		$list_js = stripcslashes( get_option( 'my_calendar_listjs' ) );
		$cal_js = stripcslashes( get_option( 'my_calendar_caljs' ) );
		$mini_js = stripcslashes( get_option( 'my_calendar_minijs' ) );
		$ajax_js = stripcslashes( get_option( 'my_calendar_ajaxjs' ) );

			$this_post = $wp_query->get_queried_object();
			if (is_object($this_post)) {
				$id = $this_post->ID;
			} 
			if ( get_option( 'my_calendar_show_js' ) != '' ) {
			$array = explode( ",",get_option( 'my_calendar_show_js' ) );
				if (!is_array($array)) {
					$array = array();
				}
			}
			if ( @in_array( $id, $array ) || get_option( 'my_calendar_show_js' ) == '' ) {
				$scripting = "<script type='text/javascript'>\n";
				if ( get_option('calendar_javascript') != 1 ) {	$scripting .= "\n".$cal_js; }
				if ( get_option('list_javascript') != 1 ) {	$scripting .= "\n".$list_js; }
				if ( get_option('mini_javascript') != 1 ) {	$scripting .= "\n".$mini_js; }
				if ( get_option('ajax_javascript') != 1 ) { $scripting .= "\n".$ajax_js; }
				$scripting .= "</script>";
				echo $scripting;
			}
		}
	}
}

function my_calendar_add_styles() {
global $wp_plugin_url;
	if (  isset($_GET['page']) && ($_GET['page'] == 'my-calendar' || $_GET['page'] == 'my-calendar-categories' || $_GET['page'] == 'my-calendar-locations' || $_GET['page'] == 'my-calendar-config' || $_GET['page'] == 'my-calendar-styles' || $_GET['page'] == 'my-calendar-help' || $_GET['page'] == 'my-calendar-behaviors' ) ) {
		echo '<link type="text/css" rel="stylesheet" href="'.$wp_plugin_url.'/my-calendar/mc-styles.css" />';
	}
	if ( isset($_GET['page']) && $_GET['page'] == 'my-calendar') {
		echo '<link type="text/css" rel="stylesheet" href="'.$wp_plugin_url.'/my-calendar/js/calendrical.css" />';
	}
}

function mc_get_current_url() {
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["HTTP_HOST"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
		}
	return esc_url($pageURL);
}

function csv_to_array($csv, $delimiter = ',', $enclosure = '"', $escape = '\\', $terminator = "\n") {
    $r = array();
    $rows = explode($terminator,trim($csv));
    foreach ($rows as $row) {
        if (trim($row)) {
            $values = explode($delimiter,$row);
			$r[$values[0]] = $values[1];
        }
    }
    return $r;
}

// Function to check what version of My Calendar is installed and install or upgrade if needed
function check_my_calendar() {
	global $wpdb, $initial_listjs, $initial_caljs, $initial_minijs, $initial_ajaxjs,$mc_version;
	$current_version = get_option('my_calendar_version');
	
	// If current version matches, don't bother running this.
	//comment out the return true to get it to set up the db again.
	if ($current_version == $mc_version) {
		return true;
	}

  // Lets see if this is first run and create a table if it is!
  // Assume this is not a new install until we prove otherwise
  $new_install = false;
  
  $my_calendar_exists = false;
  $upgrade_path = array();
  
  // Determine the calendar version
  $tables = $wpdb->get_results("show tables;");
	foreach ( $tables as $table ) {
      foreach ( $table as $value )  {
		  if ( $value == MY_CALENDAR_TABLE ) {
		      $my_calendar_exists = true;
			  // check whether installed version matches most recent version, establish upgrade process.
		    } 
       }
    }
	
	if ( $my_calendar_exists == false ) {
      $new_install = true;
	// for each release requiring an upgrade path, add a version compare. Loop will run every relevant upgrade cycle.
    } else if ( version_compare( $current_version,"1.3.0","<" ) ) {
		$upgrade_path[] = "1.3.0";
	} else if ( version_compare( $current_version,"1.3.8","<" ) ) {
		$upgrade_path[] = "1.3.8";
	} else if ( version_compare( $current_version, "1.4.0", "<" ) ) {
		$upgrade_path[] = "1.4.0";
	} else if ( version_compare( $current_version, "1.4.7", "<" ) ) {
		$upgrade_path[] = "1.4.7";
	} else if ( version_compare( $current_version, "1.4.8", "<" ) ) {
		$upgrade_path[] = "1.4.8";
	} else if ( version_compare( $current_version, "1.5.0", "<" ) ) {
		$upgrade_path[] = "1.5.0";
	} else if ( version_compare( $current_version, "1.6.0", "<" ) ) {
		$upgrade_path[] = "1.6.0";
	} else if ( version_compare( $current_version, "1.6.2", "<" ) ) {
		$upgrade_path[] = "1.6.2";
	} else if ( version_compare( $current_version, "1.6.3", "<" ) ) {
		$upgrade_path[] = "1.6.3";
	} else if ( version_compare( $current_version, "1.7.0", "<" ) ) { 
		$upgrade_path[] = "1.7.0";
	} else if ( version_compare( $current_version, "1.7.1", "<" ) ) { 
		$upgrade_path[] = "1.7.1";
	} else if ( version_compare ( $current_version, "1.8.0", "<" ) ) {
		$upgrade_path[] = "1.8.0";
	}
	// having determined upgrade path, assign new version number
	update_option( 'my_calendar_version' , $mc_version );
	// Now we've determined what the current install is or isn't 

  
  

  if ( $new_install == true ) {
		  //add default settings
		mc_default_settings();
		$sql = "INSERT INTO " . MY_CALENDAR_CATEGORIES_TABLE . " SET category_id=1, category_name='General', category_color='#ffffff', category_icon='event.png'";
		$wpdb->query($sql);
    }		
	// switch for different upgrade paths
	foreach ($upgrade_path as $upgrade) {
		switch ($upgrade) {
			case '1.8.0':
				$mc_input = get_option( 'mc_input_options' );
				$mc_input['event_use_editor'] = 'off';
				update_option( 'mc_input_options',$mc_input );
				add_option( 'mc_show_weekends','true' );
				add_option( 'mc_uri','' );
				delete_option( 'my_calendar_stored_styles');
				upgrade_db();
			break;
			case '1.7.1':
				if (get_option('mc_location_type') == '') {
					update_option('mc_location_type','event_state');
				}
			break;				
			case '1.7.0': 
				update_option('mc_db_version','1.7.0');
				add_option('mc_show_rss','false');
				add_option('mc_show_ical','false');					
				add_option('mc_skip_holidays','false');	
				add_option('mc_event_edit_perms','manage_options');
				$original_styles = get_option('my_calendar_style');
				if ($original_styles != '') {
				$stylefile = mc_get_style_path('my-calendar.css');
					if ( mc_write_styles( $stylefile, $original_styles ) ) {
						delete_option('my_calendar_style');
					} else {
						add_option('my_calendar_file_permissions','false');
					}
				}
				update_option('my_calendar_css_file','my-calendar.css');				
				// convert old widget settings into new defaults
				$type = get_option('display_upcoming_type');
				if ($type == 'events') {
					$before = get_option('display_upcoming_events');
					$after = get_option('display_past_events');
				} else {
					$before = get_option('display_upcoming_days');
					$after = get_option('display_past_days');
				}
				$category = get_option('display_in_category');
				$today_template = get_option('my_calendar_today_template'); 
				$upcoming_template = get_option('my_calendar_upcoming_template');
				$today_title = get_option('my_calendar_today_title');
				$today_text = get_option('my_calendar_no_events_text');
				$upcoming_title = get_option('my_calendar_upcoming_title');

				$defaults = array(
					'upcoming'=>array(	
						'type'=>$type,
						'before'=>$before,
						'after'=>$after,
						'template'=>$upcoming_template,
						'category'=>$category,
						'text'=>'',
						'title'=>$upcoming_title
					),
					'today'=>array(
						'template'=>$today_template,
						'category'=>'',
						'title'=>$today_title,
						'text'=>$today_text
					)
				);
				add_option('my_calendar_widget_defaults',$defaults);
				delete_option('display_upcoming_type');
				delete_option('display_upcoming_events');
				delete_option('display_past_events');
				delete_option('display_upcoming_days');
				delete_option('display_todays','true');
				delete_option('display_upcoming','true');
				delete_option('display_upcoming_days',7);				
				delete_option('display_past_days');
				delete_option('display_in_category');
				delete_option('my_calendar_today_template'); 
				delete_option('my_calendar_upcoming_template');
				delete_option('my_calendar_today_title');
				delete_option('my_calendar_no_events_text');
				delete_option('my_calendar_upcoming_title');			
			break;		
			case '1.6.3':
				add_option( 'my_calendar_ajaxjs',$initial_ajaxjs );
				add_option( 'ajax_javascript', 1 );
				add_option( 'my_calendar_templates', array(
					'title'=>'{title}'
				));
			break;
						case '1.6.2':
				$mc_user_settings = array(
				'my_calendar_tz_default'=>array(
					'enabled'=>'off',
					'label'=>'My Calendar Default Timezone',
					'values'=>array(
							"-12" => "(GMT -12:00) Eniwetok, Kwajalein",
							"-11" => "(GMT -11:00) Midway Island, Samoa",
							"-10" => "(GMT -10:00) Hawaii",
							"-9.5" => "(GMT -9:30) Marquesas Islands",
							"-9" => "(GMT -9:00) Alaska",
							"-8" => "(GMT -8:00) Pacific Time (US &amp; Canada)",
							"-7" => "(GMT -7:00) Mountain Time (US &amp; Canada)",
							"-6" => "(GMT -6:00) Central Time (US &amp; Canada), Mexico City",
							"-5" => "(GMT -5:00) Eastern Time (US &amp; Canada), Bogota, Lima",
							"-4.5" => "(GMT -4:30) Venezuela",
							"-4" => "(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz",
							"-3.5" => "(GMT -3:30) Newfoundland",
							"-3" => "(GMT -3:00) Brazil, Buenos Aires, Georgetown",
							"-2" => "(GMT -2:00) Mid-Atlantic",
							"-1" => "(GMT -1:00 hour) Azores, Cape Verde Islands",
							"0" => "(GMT) Western Europe Time, London, Lisbon, Casablanca",
							"1" => "(GMT +1:00 hour) Brussels, Copenhagen, Madrid, Paris",
							"2" => "(GMT +2:00) Kaliningrad, South Africa",
							"3" => "(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg",
							"3.5" => "(GMT +3:30) Tehran",
							"4" => "(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi",
							"4.5" => "(GMT +4:30) Afghanistan",
							"5" => "(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent",
							"5.5" => "(GMT +5:30) Bombay, Calcutta, Madras, New Delhi",
							"5.75" => "(GMT +5:45) Nepal",
							"6" => "(GMT +6:00) Almaty, Dhaka, Colombo",
							"6.5" => "(GMT +6:30) Myanmar, Cocos Islands",
							"7" => "(GMT +7:00) Bangkok, Hanoi, Jakarta",
							"8" => "(GMT +8:00) Beijing, Perth, Singapore, Hong Kong",
							"9" => "(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk",
							"9.5" => "(GMT +9:30) Adelaide, Darwin",
							"10" => "(GMT +10:00) Eastern Australia, Guam, Vladivostok",
							"10.5" => "(GMT +10:30) Lord Howe Island",
							"11" => "(GMT +11:00) Magadan, Solomon Islands, New Caledonia",
							"11.5" => "(GMT +11:30) Norfolk Island",
							"12" => "(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka",
							"12.75" => "(GMT +12:45) Chatham Islands",
							"13" => "(GMT +13:00) Tonga",
							"14" => "(GMT +14:00) Line Islands"
							),
					),
				'my_calendar_location_default'=>array(
					'enabled'=>'off',
					'label'=>'My Calendar Default Location',
					'values'=>array(
								'AL'=>"Alabama",
								'AK'=>"Alaska", 
								'AZ'=>"Arizona", 
								'AR'=>"Arkansas", 
								'CA'=>"California", 
								'CO'=>"Colorado", 
								'CT'=>"Connecticut", 
								'DE'=>"Delaware", 
								'DC'=>"District Of Columbia", 
								'FL'=>"Florida", 
								'GA'=>"Georgia", 
								'HI'=>"Hawaii", 
								'ID'=>"Idaho", 
								'IL'=>"Illinois", 
								'IN'=>"Indiana", 
								'IA'=>"Iowa", 
								'KS'=>"Kansas", 
								'KY'=>"Kentucky", 
								'LA'=>"Louisiana", 
								'ME'=>"Maine", 
								'MD'=>"Maryland", 
								'MA'=>"Massachusetts", 
								'MI'=>"Michigan", 
								'MN'=>"Minnesota", 
								'MS'=>"Mississippi", 
								'MO'=>"Missouri", 
								'MT'=>"Montana",
								'NE'=>"Nebraska",
								'NV'=>"Nevada",
								'NH'=>"New Hampshire",
								'NJ'=>"New Jersey",
								'NM'=>"New Mexico",
								'NY'=>"New York",
								'NC'=>"North Carolina",
								'ND'=>"North Dakota",
								'OH'=>"Ohio", 
								'OK'=>"Oklahoma", 
								'OR'=>"Oregon", 
								'PA'=>"Pennsylvania", 
								'RI'=>"Rhode Island", 
								'SC'=>"South Carolina", 
								'SD'=>"South Dakota",
								'TN'=>"Tennessee", 
								'TX'=>"Texas", 
								'UT'=>"Utah", 
								'VT'=>"Vermont", 
								'VA'=>"Virginia", 
								'WA'=>"Washington", 
								'WV'=>"West Virginia", 
								'WI'=>"Wisconsin", 
								'WY'=>"Wyoming"),
					)
				);			
				update_option('mc_user_settings',$mc_user_settings);			
			break;
			case '1.6.0':
				add_option('mc_user_settings_enabled',false);
				add_option('mc_user_location_type','state');
				add_option('my_calendar_show_js',get_option('my_calendar_show_css') );   
				upgrade_db();
			break;
			case '1.5.0':
				add_option('mc_event_mail','false');
				add_option('mc_event_mail_subject','');
				add_option('mc_event_mail_to','');
				add_option('mc_event_mail_message','');
				add_option('mc_event_approve','false');		
				add_option('mc_event_approve_perms','manage_options');
				add_option('mc_no_fifth_week','true');				
				upgrade_db();
			break;
			case '1.4.8':
				add_option('mc_input_options',array('event_short'=>'on','event_desc'=>'on','event_category'=>'on','event_link'=>'on','event_recurs'=>'on','event_open'=>'on','event_location'=>'on','event_location_dropdown'=>'on') );	
				add_option('mc_input_options_administrators','false');
			break;
			case '1.4.7':
				add_option( 'mc_event_open', 'Registration is open' );
				add_option( 'mc_event_closed', 'Registration is closed' );
				add_option( 'mc_event_registration', 'false' );
				add_option( 'mc_short', 'false' );
				add_option( 'mc_desc', 'true' );
				upgrade_db();
			break;
			case '1.4.0':
			// change tables					
				add_option( 'mc_db_version', '1.4.0' );
				add_option( 'mc_event_link_expires','false' );
				add_option( 'mc_apply_color','default' );
				add_option( 'my_calendar_minijs', $initial_minijs);
				add_option( 'mini_javascript', 1);
				upgrade_db();
			break;
			case '1.3.8':
				update_option('my_calendar_show_css','');
			break;
			case '1.3.0':
				add_option('my_calendar_listjs',$initial_listjs);
				add_option('my_calendar_caljs',$initial_caljs);
				add_option('my_calendar_show_heading','true');  
			break;
			default:
			break;
		}
	}
	/* 
	if the user has fully uninstalled the plugin but kept the database of events, this will restore default 
	settings and upgrade db if needed.
	*/
	if ( get_option( 'my_calendar_uninstalled' ) == 'true' ) {
		mc_default_settings();	
		update_option( 'mc_db_version', '1.4.0' );
	}
}



function jd_cal_checkCheckbox( $theFieldname,$theValue,$theArray='' ){
	if (!is_array( get_option( $theFieldname ) ) ) {
	if( get_option( $theFieldname ) == $theValue ){
		echo 'checked="checked"';
	}
	} else {
		$theSetting = get_option( $theFieldname );
		if ( $theSetting[$theArray]['enabled'] == $theValue ) {
			echo 'checked="checked"';
		}
	}
}
function jd_cal_checkSelect( $theFieldname,$theValue,$theArray='' ){
	if (!is_array( get_option( $theFieldname ) ) ) {
	if( get_option( $theFieldname ) == $theValue ){
			echo 'selected="selected"';
	}
	} else {
		$theSetting = get_option( $theFieldname );
		if ( $theSetting[$theArray]['enabled'] == $theValue ) {
			echo 'selected="selected"';
		}
	}
}


// Function to return a prefix which will allow the correct placement of arguments into the query string.
function my_calendar_permalink_prefix() {
  // Get the permalink structure from WordPress
  $p_link = get_permalink();
  $real_link = mc_get_current_url();

  // Now use all of that to get the My Calendar link prefix
  if (strstr($p_link, '?') && $p_link == $real_link) {
      $link_part = $p_link.'&';
    } else if ($p_link == $real_link) {
      $link_part = $p_link.'?';
    } else if (strstr($real_link, '?')) {
	
		if ( isset($_GET['month']) || isset($_GET['yr']) || isset($_GET['week']) ) {
			$link_part = '';
			$new_base = split('\?', $real_link);
			if(count($new_base) > 1) {
				$new_tail = split('&', $new_base[1]);
				foreach ($new_tail as $item) {
					if ( isset($_GET['month']) && isset($_GET['yr']) ) {
						if (!strstr($item, 'month') && !strstr($item, 'yr')) {
							$link_part .= $item.'&';
						}
					} 
					if ( isset($_GET['week']) && isset($_GET['yr']) ) {
						if (!strstr($item, 'week') && !strstr($item, 'yr')) {
							$link_part .= $item.'&';
						}
					} 
				}
			}
			$link_part = $new_base[0] . ($link_part ? "?$link_part" : '?');
		} else {
			$link_part = $real_link.'&';
		}
		
		
    } else {
      $link_part = $real_link.'?';
    }
  return $link_part;
}

function my_calendar_fouc() {
global $wp_query;
	if ( get_option('calendar_javascript') != 1 || get_option('list_javascript') != 1 || get_option('mini_javascript') != 1 ) {
		$scripting = "\n<script type='text/javascript'>\n";
		$scripting .= "jQuery('html').addClass('js');\n";
		$scripting .= "jQuery(document).ready(function($) { \$('html').removeClass('js') });\n";
		$scripting .= "</script>\n";
		$this_post = $wp_query->get_queried_object();
		if ( is_object($this_post) ) {
			$id = $this_post->ID;
		} 
		if ( get_option( 'my_calendar_show_js' ) != '' ) {
		$array = explode( ",",get_option( 'my_calendar_show_js' ) );
			if ( !is_array( $array ) ) {
				$array = array();
			}
		}
		if ( @in_array( $id, $array ) || trim ( get_option( 'my_calendar_show_js' ) ) == '' ) {	
			echo $scripting;
		}
	}
}


function mc_month_comparison($month) {
	$offset = (60*60*get_option('gmt_offset'));
	$current_month = date("n", time()+($offset));
	if (isset($_GET['yr']) && isset($_GET['month'])) {
		if ($month == $_GET['month']) {
			return ' selected="selected"';
		  }
	} elseif ($month == $current_month) { 
		return ' selected="selected"'; 
	}
}

function mc_year_comparison($year) {
	$offset = (60*60*get_option('gmt_offset'));
		$current_year = date("Y", time()+($offset));
		if (isset($_GET['yr']) && isset($_GET['month'])) {
			if ($year == $_GET['yr']) {
				return ' selected="selected"';
			}
		} else if ($year == $current_year) {
			return ' selected="selected"';
		}
}


function my_calendar_is_odd( $int ) {
  return( $int & 1 );
}

function mc_can_edit_event($author_id) {
	global $user_ID;
	get_currentuserinfo();
	$user = get_userdata($user_ID);	
	
	if ( current_user_can( get_option('mc_event_edit_perms') ) ) {
			return true;
		} elseif ( $user_ID == $author_id ) {
			return true;
		} else {
			return false;
		}
}

function jd_option_selected($field,$value,$type='checkbox') {
	switch ($type) {
		case 'radio':		
		case 'checkbox':
		$result = ' checked="checked"';
		break;
		case 'option':
		$result = ' selected="selected"';
		break;
	}	
	if ($field == $value) {
		$output = $result;
	} else {
		$output = '';
	}
	return $output;
}

// compatibility of clone keyword between PHP 5 and 4
if (version_compare(phpversion(), '5.0') < 0) {
	eval('
	function clone($object) {
	  return $object;
	}
	');
}

// Mail functions by Roland
function my_calendar_send_email( $details ) {
$event = event_as_array($details);

	if ( get_option('mc_event_mail') == 'true' ) {	
		$to = get_option('mc_event_mail_to');
		$subject = get_option('mc_event_mail_subject');
		$message = jd_draw_template( $event, get_option('mc_event_mail_message') );
		$mail = wp_mail($to, $subject, $message);
	}
}
// checks submitted events against akismet, if available, otherwise just returns false 
function mc_akismet( $event_url='', $description='' ) {
	global $akismet_api_host, $akismet_api_port, $user;
	if ( current_user_can( 'edit_posts' ) ) { // is a privileged user
		return 0;
	} 
	$c = array();
	if ( ! function_exists( 'akismet_http_post' ) || ! ( get_option( 'wordpress_api_key' ) || $wpcom_api_key ) ) {
		return 0;
	}

	$c['blog'] = get_option( 'home' );
	$c['user_ip'] = preg_replace( '/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'] );
	$c['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
	$c['referrer'] = $_SERVER['HTTP_REFERER'];
	$c['comment_type'] = 'my_calendar_event';
	if ( $permalink = get_permalink() )
		$c['permalink'] = $permalink;
		
	if ( '' != $event_url )
		$c['comment_author_url'] = $event_url;
	if ( '' != $description )
		$c['comment_content'] = $description;

	$ignore = array( 'HTTP_COOKIE' );

	foreach ( $_SERVER as $key => $value )
		if ( ! in_array( $key, (array) $ignore ) )
			$c["$key"] = $value;

	$query_string = '';
	foreach ( $c as $key => $data )
		$query_string .= $key . '=' . urlencode( stripslashes( (string) $data ) ) . '&';

	$response = akismet_http_post( $query_string, $akismet_api_host,
		'/1.1/comment-check', $akismet_api_port );
	if ( 'true' == $response[1] )
		return 1;
	else
		return 0;
}

function check_akismet() {
	global $user; 
	if ( current_user_can( 'edit_plugins' ) ) {
		if ( get_option('can_manage_events') == 'read' && !function_exists( 'akismet_http_post' ) ) {
			echo "<div class='error'><p class='warning'>".__("You're currently allowing to subscribers to post events, but aren't using Akismet. My Calendar can use Akismet to check for spam in event submissions. <a href='https://akismet.com/signup/'>Get an Akismet API Key now.</a>",'my-calendar' )."</p></div>";
		}
	}
}

function mc_external_link( $link ) {
	$url = parse_url($link);
	$host = $url['host'];
	$site = parse_url( get_option( 'siteurl' ) );
	$known = $site['host'];
	if ( strpos( $host, $known ) === false ) {
		return "class='event-link external'";
	} else {
		return "class='event-link'";
	}
	return "class='bananas'";
}

// Adding button to the MCE toolbar (Visual Mode) 
add_action('init', 'mc_addbuttons');

// Add button hooks to the Tiny MCE 
function mc_addbuttons() {
	global $mc_version;
	if (!current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
		return;
	}
	if ( get_user_option('rich_editing') == 'true') {
		add_filter( 'tiny_mce_version', 'mc_tiny_mce_version', 0 );
		add_filter( 'mce_external_plugins', 'mc_plugin', 0 );
		add_filter( 'mce_buttons', 'mc_button', 0 );
	}
	// Register Hooks
	if (is_admin()) {	
		// Add Quicktag
		add_action( 'edit_form_advanced', 'mc_add_quicktags' );
		add_action( 'edit_page_form', 'mc_add_quicktags' );

		// Queue Embed JS
		add_action( 'admin_head', 'mc_admin_js_vars');
		wp_enqueue_script( 'mcqt', plugins_url('/my-calendar/button/mcb.js'), array(), $mc_version );
	}
}

// Break the browser cache of TinyMCE
function mc_tiny_mce_version( ) {
	global $mc_version;
	return 'mcb-' . $mc_version;
}

// Load the custom TinyMCE plugin
function mc_plugin( $plugins ) {
global $wp_plugin_url;
	$plugins['mcqt'] = $wp_plugin_url . '/my-calendar/button/tinymce3/editor_plugin.js';
	return $plugins;
}

// Add the buttons: separator, custom
function mc_button( $buttons ) {
	array_push( $buttons, 'separator', 'myCalendar' );
	return $buttons;
}

// Add a button to the quicktag view (HTML Mode) >>>
function mc_add_quicktags(){
?>
<script type="text/javascript" charset="utf-8">
// <![CDATA[
(function(){
	if (typeof jQuery === 'undefined') {
		return;
	}
	jQuery(document).ready(function(){
		// Add the buttons to the HTML view
		jQuery("#ed_toolbar").append('<input type="button" class="ed_button" onclick="myCalQT.Tag.embed.apply(myCalQT.Tag); return false;" title="Insert My Calendar" value="My Calendar" />');
	});
}());
// ]]>
</script>
<?php	
}

function mc_newline_replace($string) {
  return (string)str_replace(array("\r", "\r\n", "\n"), '', $string);
}

// Set URL for the generator page
function mc_admin_js_vars(){
global $wp_plugin_url;
?>
<script type="text/javascript" charset="utf-8">
// <![CDATA[
	if (typeof myCalQT !== 'undefined' && typeof myCalQT.Tag !== 'undefined') {
		myCalQT.Tag.configUrl = "<?php echo $wp_plugin_url . '/my-calendar/button/generator.php'; ?>";
	}
// ]]>	
</script>
<?php
}

function reverse_array($array, $boolean, $order) {
	if ( $order == 'desc' ) {
		return array_reverse($array, $boolean);
	} else {
		return $array;
	}
}

function mc_is_mobile() {
	$uagent = new uagent_info();
	if ( $uagent->DetectMobileQuick == $uagent->true ) {
		return true;
	} else {
		return false;
	}
}

?>
