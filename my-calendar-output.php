<?php
// Used to draw multiple events
function my_calendar_draw_events($events, $type, $process_date) {
  // We need to sort arrays of objects by time
  usort($events, "my_calendar_time_cmp");
 $temp_array = array();
 $output_array = array();
  
	if ($type == "mini" && count($events) > 0) { $output .= "<div class='calendar-events'>"; }
	foreach($events as $event) { 
		if ( get_option('mc_skip_holidays') == 'false' ) {
		// if we're not skipping holidays, just add 'em all.
		$output_array[] = my_calendar_draw_event($event, $type, $process_date); 
		} else {
			$temp_array[] = $event;
		} 
	}
	// By default, skip no events.
	$skipping = false;
	foreach($temp_array as $event) {
		// if any event this date is in the holiday category, we are skipping
		if ( $event->event_category == get_option('mc_skip_holidays_category') ) {
			$skipping = true;
			break;
		}
	}
	// check each event, if we're skipping, only include the holiday events.
	foreach($temp_array as $event) {
		if ($skipping == true) {
			if ($event->event_category == get_option('mc_skip_holidays_category') ) {
				$output_array[] = my_calendar_draw_event($event, $type, $process_date);
			}
		} else {
			$output_array[] = my_calendar_draw_event($event, $type, $process_date);
		}
	}

	if ( is_array($output_array) ) {
		foreach ($output_array as $value) {
			$output .= $value;
		}
	}
	if ($type == "mini" && count($events) > 0) { $output .= "</div>"; }	
  return $output;
}
// Used to draw an event to the screen
function my_calendar_draw_event($event, $type="calendar", $process_date) {
	global $wpdb,$wp_plugin_url;
	// My Calendar must be updated to run this function
	check_my_calendar();						 
	$display_author = get_option('display_author');
	$display_map = get_option('my_calendar_show_map');
	$display_address = get_option('my_calendar_show_address');
	$display_details = get_option('mc_details');
	$this_category = $event->event_category; 
    // get user-specific data
	$tz = mc_user_timezone();
	$category = "mc_".sanitize_title( $event->category_name );
	if ( get_option('my_calendar_hide_icons')=='true' ) {
		$image = "";
	} else {
	    if ($event->category_icon != "") {
			if ( is_custom_icon() ) {
				$path = $wp_plugin_url . '/my-calendar-custom';
			} else {
				$path = $wp_plugin_url . '/my-calendar/icons';
			}
			$hex = (strpos($event->category_color,'#') !== 0)?'#':'';
		$image = '<img src="'.$path.'/'.$event->category_icon.'" alt="" class="category-icon" style="background:'.$hex.$event->category_color.';" />';
		} else {
			$image = "";
		}
	}
    $location_string = $event->event_street.$event->event_street2.$event->event_city.$event->event_state.$event->event_postcode.$event->event_country;
	// put together address information as vcard
	if (($display_address == 'true' || $display_map == 'true') && strlen($location_string) > 0 ) {
		$map_string = $event->event_street.' '.$event->event_street2.' '.$event->event_city.' '.$event->event_state.' '.$event->event_postcode.' '.$event->event_country;
		$address .= '<div class="address vcard">';
			if ($display_address == 'true') {
				$address .= "<div class=\"adr\">";
					if ($event->event_label != "") {
						$address .= "<strong class=\"org\">".stripslashes($event->event_label)."</strong><br />";
					}
					if ($event->event_street != "") {
						$address .= "<div class=\"street-address\">".stripslashes($event->event_street)."</div>";
					}
					if ($event->event_street2 != "") {
						$address .= "<div class=\"street-address\">".stripslashes($event->event_street2)."</div>";
					}
					if ($event->event_city != "") {
						$address .= "<span class=\"locality\">".stripslashes($event->event_city).",</span>";
					}
					if ($event->event_state != "") {
						$address .= " <span class=\"region\">".stripslashes($event->event_state)."</span> ";
					}
					if ($event->event_postcode != "") {
						$address .= " <span class=\"postal-code\">".stripslashes($event->event_postcode)."</span>";
					}	
					if ($event->event_country != "") {
						$address .= "<div class=\"country-name\">".stripslashes($event->event_country)."</div>";
					}
				$address .= "</div>";
			}
			if ($display_map == 'true' && (strlen($location_string) > 0 || ( $event->event_longitude != '0.000000' && $event->event_latitude != '0.000000' ) ) ) {
					$map_string = str_replace(" ","+",$map_string);
					if ($event->event_label != "") {
						$map_label = stripslashes($event->event_label);
					} else {
						$map_label = stripslashes($event->event_title);
					}
					$zoom = ($event->event_zoom != 0)?$event->event_zoom:'15';
					$map_string_label = urlencode($map_label);
					if ($event->event_longitude != '0.000000' && $event->event_latitude != '0.000000') {
						$map_string = "$event->event_latitude,$event->event_longitude+($map_string_label)";
					}
					$map_url = esc_url("http://maps.google.com/maps?f=q&amp;z=$zoom&amp;q=$map_string");
					$lead = __('Map to','my-calendar');
					$map = "<a href=\"$map_url\" class='external'>$lead $map_label</a>";
					$address .= "<div class=\"url map\">$map</div>";
			}
		$address .= "</div>";
	}
		$id_start = date('Y-m-d',$event->event_start_ts);
		$id_end = date('Y-m-d',$event->event_end_ts);
         $uid = 'mc_'.$id_start.'_'.$event->event_id;
    $header_details .=  "<div id='$uid' class='$type-event $category vevent'>\n";
	$array = event_as_array($event);
	$templates = get_option('my_calendar_templates');
	$title_template = ($templates['title'] == '' )?'{title}':$templates['title'];
	$mytitle = jd_draw_template($array,$title_template);
	$toggle = ($type == 'calendar')?" <a href='#' class='mc-toggle'><img src='".MY_CALENDAR_DIRECTORY."/images/event-details.png' alt='".__('Event Details','my-calendar')."' /></a>":'';
	$event_date = ($type == 'single')?date_i18n(get_option('my_calendar_date_format'),strtotime($process_date)).', ':'';

	if ($type != 'list') {
		$header_details .= "<h3 class='event-title summary'>$image".$mytitle."$toggle</h3>\n";
	}
	
	$header_details .= "	<div class='details'>"; 
	$header_details .= ($type == 'calendar')?"\n	<h3 class='close'><a href='#' class='mc-toggle mc-close'><img src='".MY_CALENDAR_DIRECTORY."/images/event-close.png' alt='".__('Close','my-calendar')."' /></a></h3>":'';
		if ( $event->event_time != "00:00:00" && $event->event_time != '' ) {
			$header_details .= "\n	<span class='event-time dtstart' title='".$id_start.'T'.$event->event_time."'>$event_date".date_i18n(get_option('time_format'), strtotime($event->event_time));
			if ($event->event_endtime != "00:00:00" && $event->event_endtime != '' ) {
				$header_details .= "<span class='time-separator'>&thinsp;&ndash;&thinsp;</span><span class='end-time dtend' title='".$id_end.'T'.$event->event_endtime."'>".date_i18n(get_option('time_format'), strtotime($event->event_endtime))."</span>";
			}
			$header_details .= "</span>\n";
			if ($tz != '') {
				$local_begin = date_i18n( get_option('time_format'), strtotime($event->event_time ."+$tz hours") );
				$header_details .= "<span class='local-time'>$local_begin ". __('in your time zone','my-calendar')."</span>";
			}
		} else {
			$header_details .= "<span class='event-time'>";
				if ( get_option('my_calendar_notime_text') == '' || get_option('my_calendar_notime_text') == "N/A" ) { 
				$header_details .= "<abbr title='".__('Not Applicable','my-calendar')."'>".__('N/A','my-calendar')."</abbr>\n"; 
				} else {
				$header_details .= get_option('my_calendar_notime_text');
				}
			$header_details .= "</span>";
		}
		$header_details .= "
		<div class='sub-details'>";
		if ($type == "list") {
			$header_details .= "<h3 class='event-title summary'>$image".$mytitle."</h3>\n";
		}
		if ($display_author == 'true') {
			$e = get_userdata($event->event_author);
			$header_details .= '<span class="event-author">'.__('Posted by', 'my-calendar').': <span class="author-name">'.$e->display_name."</span></span><br />\n";
		}	
	if (($display_address == 'true' || $display_map == 'true') && strlen($location_string) > 0 ) {
		$header_details .= $address;
	}
	if ($display_details == 'true' && !isset($_GET['mc_id']) ) {
		$dateid = date('Y-m-d',$event->event_start_ts);
		$id = $event->event_id;
		$header_details .= ( get_option( 'mc_uri' ) != '' )?"<p class='mc_details'><a href='".get_option( 'mc_uri' )."?mc_id=mc_".$dateid."_".$id."'><span>$event->event_title </span>".__('details','my-calendar')."</a></p>":'';
	}
  // handle link expiration
	if ( $event->event_link_expires == 0 ) {
		$event_link = esc_url($event->event_link);
	} else {
		if ( my_calendar_date_comp( $event->event_end,date_i18n('Y-m-d',time()+$offset ) ) ) {
			$event_link = '';
		} else {
			$event_link = esc_url($event->event_link);
		}
	}
	// this is cut for now; I'm concerned about security.
	/*
	if ( function_exists('my_calendar_generate_vcal') ) {
		$vcal_data = my_calendar_generate_vcal($event);
		$vcal = urlencode($vcal_data[0]);
		$file = urlencode($vcal_data[1]);
		$nonce = wp_create_nonce('my-calendar-nonce');
		$vcal_link = "<a class='ical' rel='nofollow' href='$wp_plugin_url" . "/my-calendar/my-calendar-export.php?vcal=$vcal&amp;file=$file&amp;_wpnonce=$nonce"."'>ical</a>";
		$header_details .= $vcal_link;
	}
	*/
	if ( get_option('mc_short') == 'true' ) {
		$short = "<div class='shortdesc'>".wpautop(stripcslashes($event->event_short),1)."</div>";	
	}
	if ( get_option('mc_desc') == 'true' ) {
		$description = "
		<div class='longdesc'>
		".wpautop(stripcslashes($event->event_desc),1)."
		</div>";
	}
	if ( get_option('mc_event_registration') == 'true' ) {
		switch ($event->event_open) {
			case '0':$status = get_option('mc_event_closed');
				break;
			case '1':$status = get_option('mc_event_open');
				break;
			case '2':$status = '';
				break;
			default:$status = '';
		}
	}
	// if the event is a member of a group of events, but not the first, note that.
	if ($event->event_group == 1 ) {
		$info = array();
		$info[] = $event->event_id;
		update_option( 'mc_event_groups' , $info );
	}
	if ( is_array( get_option( 'mc_event_groups' ) ) ) {
		if ( in_array ( $event->event_id , get_option( 'mc_event_groups') ) ) {
			if ( $process_date != $event->event_original_begin ) {
				$status = __("This class is part of a series. You must register for the first event in this series to attend.",'my-calendar');
			}
		}
	}
	$status = ($status != '')?"<p>$status</p>":'';
	$return = ($type == 'single')?"<p><a href='".get_option('mc_uri')."'>".__('View full calendar','my-calendar')."</a></p>":'';
	$is_external = mc_external_link( $event_link );
	if ($event_link != '') {
		$details = "\n". $header_details . $description . $short . $status."<p><a href='$event_link' $is_external>" . stripslashes($event->event_title) . '&raquo; </a></p>'.$return;
	} else {
		$details = "\n". $header_details . $description . $short . $status . $return;	
	}
	$details .= "
		</div>
	</div>
</div>\n";
	if ( get_option( 'mc_event_approve' ) == 'true' ) {
		if ( $event->event_approved == 1 ) {	
		  return $details;
		}
	} else {
		return $details;
	}
}

function mc_build_date_switcher() {
	$my_calendar_body = "";
	$my_calendar_body .= '<div class="my-calendar-date-switcher">
            <form method="get" action=""><div>';
	$qsa = array();
	parse_str($_SERVER['QUERY_STRING'],$qsa);
	foreach ($qsa as $name => $argument) {
	    if ($name != 'month' && $name != 'yr' && $name != 'week' ) {
			$my_calendar_body .= '<input type="hidden" name="'.$name.'" value="'.$argument.'" />';
	    }
	  }
	// We build the months in the switcher
	$my_calendar_body .= '
            <label for="my-calendar-month">'.__('Month','my-calendar').':</label> <select id="my-calendar-month" name="month">'."\n".'
            <option value="1"'.mc_month_comparison('1').'>'.__('January','my-calendar').'</option>'."\n".'
            <option value="2"'.mc_month_comparison('2').'>'.__('February','my-calendar').'</option>'."\n".'
            <option value="3"'.mc_month_comparison('3').'>'.__('March','my-calendar').'</option>'."\n".'
            <option value="4"'.mc_month_comparison('4').'>'.__('April','my-calendar').'</option>'."\n".'
            <option value="5"'.mc_month_comparison('5').'>'.__('May','my-calendar').'</option>'."\n".'
            <option value="6"'.mc_month_comparison('6').'>'.__('June','my-calendar').'</option>'."\n".'
            <option value="7"'.mc_month_comparison('7').'>'.__('July','my-calendar').'</option>'."\n".'
            <option value="8"'.mc_month_comparison('8').'>'.__('August','my-calendar').'</option>'."\n".'
            <option value="9"'.mc_month_comparison('9').'>'.__('September','my-calendar').'</option>'."\n".'
            <option value="10"'.mc_month_comparison('10').'>'.__('October','my-calendar').'</option>'."\n".'
            <option value="11"'.mc_month_comparison('11').'>'.__('November','my-calendar').'</option>'."\n".'
            <option value="12"'.mc_month_comparison('12').'>'.__('December','my-calendar').'</option>'."\n".'
            </select>'."\n".'
            <label for="my-calendar-year">'.__('Year','my-calendar').':</label> <select id="my-calendar-year" name="yr">'."\n";
	$past = 5;
	$future = 5;
	$fut = 1;
	$offset = (60*60*get_option('gmt_offset'));
		while ($past > 0) {
		    $p .= '<option value="';
		    $p .= date("Y",time()+($offset))-$past;
		    $p .= '"'.mc_year_comparison(date("Y",time()+($offset))-$past).'>';
		    $p .= date("Y",time()+($offset))-$past."</option>\n";
		    $past = $past - 1;
		}
		while ($fut < $future) {
		    $f .= '<option value="';
		    $f .= date("Y",time()+($offset))+$fut;
		    $f .= '"'.mc_year_comparison(date("Y",time()+($offset))+$fut).'>';
		    $f .= date("Y",time()+($offset))+$fut."</option>\n";
		    $fut = $fut + 1;
		} 
	$my_calendar_body .= $p;
	$my_calendar_body .= '<option value="'.date("Y",time()+($offset)).'"'.mc_year_comparison(date("Y",time()+($offset))).'>'.date("Y",time()+($offset))."</option>\n";
	$my_calendar_body .= $f;
    $my_calendar_body .= '</select> <input type="submit" value="'.__('Go','my-calendar').'" /></div>
	</form></div>';
	return $my_calendar_body;
}

// Actually do the printing of the calendar
function my_calendar($name,$format,$category,$showkey,$shownav,$toggle,$time='month') {
    global $wpdb, $wp_plugin_url;
	$my_calendar_body = '';
	if (mc_is_mobile()) {
		$format = 'list';
	}
	if ( $format != 'mini' && $toggle == 'yes' ) {
		$format_toggle = "<div class='mc-format'>";
		$current_url = mc_get_current_url();
		switch ($format) {
			case 'list':
				$_GET['format'] = 'calendar';
				$query = build_query( $_GET );
				$query = ($query == '')?'format=calendar':$query;
				$current_url = str_replace('?format=list','',$current_url);
				$format_toggle .= "<a href='$current_url?$query'>".__('View as Grid','my-calendar')."</a>";			
			break;
			default:
				$_GET['format'] = 'list';
				$query = build_query( $_GET );	
				$query = ($query == '')?'format=list':$query;
				$current_url = str_replace('?format=calendar','',$current_url);
				$format_toggle .= "<a href='$current_url?$query'>".__('View as List','my-calendar')."</a>";
			break;
		}
		$format_toggle .= "</div>";
	} else {
		$format_toggle = '';
	}
	
	if ( isset( $_GET['mc_id'] ) ) {
		$mc_id = explode("_",$_GET['mc_id']);
		$id = (int) $mc_id[2];
		$date = $mc_id[1];
		$my_calendar_body .= my_calendar_get_event( $date, $id );
	} else {
	if ($category == "") {
		$category=null;
	}
    // First things first, make sure calendar is up to date
    check_my_calendar();
    // Deal with the week not starting on a monday
	$name_days = array(
		__('<abbr title="Sunday">Sun</abbr>','my-calendar'),
		__('<abbr title="Monday">Mon</abbr>','my-calendar'),
		__('<abbr title="Tuesday">Tues</abbr>','my-calendar'),
		__('<abbr title="Wednesday">Wed</abbr>','my-calendar'),
		__('<abbr title="Thursday">Thur</abbr>','my-calendar'),
		__('<abbr title="Friday">Fri</abbr>','my-calendar'),
		__('<abbr title="Saturday">Sat</abbr>','my-calendar')
		);
	
	if ($format == "mini") {
		$name_days = array(
		__('<abbr title="Sunday">S</abbr>','my-calendar'),
		__('<abbr title="Monday">M</abbr>','my-calendar'),
		__('<abbr title="Tuesday">T</abbr>','my-calendar'),
		__('<abbr title="Wednesday">W</abbr>','my-calendar'),
		__('<abbr title="Thursday">T</abbr>','my-calendar'),
		__('<abbr title="Friday">F</abbr>','my-calendar'),
		__('<abbr title="Saturday">S</abbr>','my-calendar')
		);
	}
	$start_of_week = (get_option('start_of_week')==1||get_option('start_of_week')==0)?get_option('start_of_week'):0;
	if ( $start_of_week == '1' ) {
   			$first = array_shift($name_days);
			$name_days[] = $first;	
	}
     // Carry on with the script
	$offset = (60*60*get_option('gmt_offset'));
    // If we don't pass arguments we want a calendar that is relevant to today
    $c_day = date("d",time()+($offset));
	
	if ( isset($_GET['month']) ) {
		$c_month = $_GET['month'];
		$c_day = 1;
	} else if ($month != '') {
		$c_month = $month;
	} else {
		$c_month = date("m",time()+($offset));			
	}
	if ( isset($_GET['week']) ) {
		$c_week = $_GET['week'];
	} else if ($week != '') {
		$c_week = $week;
	} else {
		if ( $start_of_week == 1 ) {
			$c_week = date("W",time()+($offset));		
		} else {
			$c_week = date("W",strtotime("+1 day")+($offset));
		}
	}
	if ( isset($_GET['yr']) ) {
		$c_year = $_GET['yr'];
	} else if ($yr != '') {
		$c_year = $yr;
	} else {
		$c_year = date("Y",time()+($offset));			
	}	
	if ($time == 'week') {
		$week_date = get_week_date( $c_week, $c_year );	
		$c_month = date("m",$week_date);
		$c_day = date("d",$week_date);
	}
    // Years get funny if we exceed 3000, so we use this check
    if ( !($year <= 3000 && $year >= 0)) {
		// No valid year causes the calendar to default to today	
        $c_year = date("Y",time()+($offset));
        $c_month = date("m",time()+($offset));
        $c_day = date("d",time()+($offset));
		$c_week = date("W",time()+($offset));
    }
	$num_months = get_option('my_calendar_show_months');	
	$current_date = mktime(0,0,0,$c_month,$c_day,$c_year);
	$through_date = mktime(0,0,0,$c_month+($num_months-1),$c_day,$c_year);
	$current_date_header = date_i18n('F Y',$current_date);
	if (date('Y',$current_date) == date('Y',$through_date)) {
		$current_month_header = date_i18n('F',$current_date);
	} else {
		$current_month_header = date_i18n('F Y',$current_date);	
	}
	$through_month_header = date_i18n('F Y', $through_date);
    // Fix the days of the week if week start is not on a monday
	if ($time == 'week') {
		$first_weekday = $start_of_week;
	} else {
		if ( $start_of_week == 0 ) {
			// what day of the week does the month start on
			$first_weekday = date("w",mktime(0,0,0,$c_month,1,$c_year));
		} else {
			$first_weekday = date("w",mktime(0,0,0,$c_month,1,$c_year));
			$first_weekday = ($first_weekday==0?6:$first_weekday-1);
		}
	}
		$anchor = (get_option('ajax_javascript') == '1' )?'#jd-calendar':'';
		
		
		$pLink = my_calendar_prev_link($c_year,$c_month,$c_week,$format,$time);
		$nLink = my_calendar_next_link($c_year,$c_month,$c_week,$format,$time);		
	
		// assign url parameters based on current setting.
		if ($time == 'week') {
			$pperiod = 'week='.$pLink['week'];
			$nperiod = 'week='.$nLink['week'];
		} else {
			$pperiod = 'month='.$pLink['month'];
			$nperiod = 'month='.$nLink['month'];
		}
		if ($shownav == 'yes') {
			$mc_nav = '
	<div class="my-calendar-nav">
		<ul>
			<li class="my-calendar-prev"><a id="prevMonth" href="' . my_calendar_permalink_prefix() . $pperiod .'&amp;yr=' . $pLink['yr'] . $anchor .'" rel="nofollow">&laquo; '.$pLink['label'].'</a></li>
			<li class="my-calendar-next"><a id="nextMonth" href="' . my_calendar_permalink_prefix() . $nperiod .'&amp;yr=' . $nLink['yr'] . $anchor .'" rel="nofollow">'.$nLink['label'].' &raquo;</a></li>
		</ul>
	</div>';
		} else {
			$mc_nav = '';
		}
		$days_in_month = date("t", mktime (0,0,0,$c_month,1,$c_year));
		$and = __("and",'my-calendar');
		if ($category != "" && $category != "all") {
			$category_label = str_replace("|"," $and ",$category) . ' ';
		} else {
			$category_label = "";
		}
		// Start the calendar and add header and navigation
		$my_calendar_body .= "<div id=\"jd-calendar\" class=\"$format\">";
		// Add the calendar table and heading
		$caption_text = ' '.stripslashes(trim( get_option('my_calendar_caption') ));
	if ($format == "calendar" || $format == "mini" ) {
		$my_calendar_body .= '
<div class="my-calendar-header">';
	    // We want to know if we should display the date switcher
	    $date_switcher = get_option('display_jump');
			if ($date_switcher == 'true') {
				$my_calendar_body .= mc_build_date_switcher();
			}
	    // The header of the calendar table and the links.
		$my_calendar_body .= "$mc_nav\n$format_toggle\n</div>";
		$my_calendar_body .= "\n<table class=\"my-calendar-table\" summary=\"$category_label".__('Calendar','my-calendar')."\">\n";
		$caption_heading = ($time != 'week')?$current_date_header.$caption_text:__('The week\'s events','my-calendar').$caption_text;
			$my_calendar_body .= "<caption class=\"my-calendar-$time\">".$caption_heading."</caption>\n";
	} else {
			if ( get_option('my_calendar_show_heading') == 'true' ) {
				$my_calendar_body .= "\n<h2 class=\"my-calendar-heading\">$category_label".__('Calendar','my-calendar')."</h2>\n";
			}
		// determine which header text to show depending on number of months displayed;
		if ( $time != 'week' ) {
			$list_heading = ($num_months <= 1)?__('Events in','my-calendar').' '.$current_date_header.$caption_text."\n":$current_month_header.'&thinsp;&ndash;&thinsp;'.$through_month_header.$caption_text;
		} else {
			$list_heading = __('This week\'s events','my-calendar');
		}
		$my_calendar_body .= "<h3 class=\"my-calendar-$time\">$list_heading</h3>\n";		
		$my_calendar_body .= '<div class="my-calendar-header">'; // this needs work
	    // We want to know if we should display the date switcher
		if ( $time != 'week' ) {
			$my_calendar_body .= ( get_option('display_jump') == 'true' )?mc_build_date_switcher():'';
		}
		$my_calendar_body .= "$mc_nav\n$format_toggle\n</div>";	
	}
    // If in a calendar format, print the headings of the days of the week
if ( $format == "calendar" || $format == "mini" ) {
    $my_calendar_body .= "<thead>\n<tr>\n";
    for ($i=0; $i<=6; $i++) {
		if ( $start_of_week == 0) {
			$class = ($i<6&&$i>0)?'day-heading':'weekend-heading';
		} else {
			$class = ($i<5)?'day-heading':'weekend-heading';
		}
		if ( ( $class == 'weekend-heading' && get_option('mc_show_weekends') == 'true' ) || $class != 'weekend-heading' ) {
			$my_calendar_body .= "<th scope='col' class='$class'>".$name_days[$i]."</th>\n";
		}
	}	
    $my_calendar_body .= "\n</tr>\n</thead>\n<tbody>";

	if ($time == 'week') {
		$firstday = date('j',mktime(0,0,0,$c_month,$c_day,$c_year));
		$lastday = $firstday + 6;
	} else {
		$firstday = 1;
		$lastday = $days_in_month;
	}
	$useday = 1;
	$inc_month = false;
    for ($i=$firstday; $i<=$lastday;) {
		$process_date = date_i18n('Y-m-d',mktime(0,0,0,$c_month,$thisday,$c_year));
        $my_calendar_body .= '<tr>';
		if ($time == 'week') {
			$ii_start = $first_weekday;
			$ii_end = $first_weekday + 6;
		} else {
			$ii_start = 0;
			$ii_end = 6;
		}
        for ($ii=$ii_start; $ii<=$ii_end; $ii++) {
            if ($ii==$first_weekday && $i==$firstday) {
				$go = TRUE;
			} elseif ($thisday > $days_in_month ) {
				$go = FALSE;
			}
			if ( empty($thisday) ) {
				$numdays = date('t',mktime(0,0,0,$c_month-1));
				$now = $numdays - ($first_weekday-($ii+1));
			}	
            if ($go) {
			$addclass = "";
				if ($i > $days_in_month) {
					$addclass = " nextmonth";
					$thisday = $useday;
					if ($inc_month == false) {
						$c_year = ($c_month == 12)?$c_year+1:$c_year;
						$c_month = ($c_month == 12)?1:$c_month+1;
					} 
					$inc_month = true;
					$useday++;
				} else {
					$thisday = $i;
				}
				$grabbed_events = my_calendar_grab_events($c_year,$c_month,$thisday,$category);
				$events_class = '';
					if (!count($grabbed_events)) {
						$events_class = " no-events$addclass";
						$element = 'span';
						$trigger = '';
					} else {
						$events_class = " has-events$addclass";
						if ($format == 'mini') {
							$element = 'a href="#"';
							$trigger = ' trigger';
						} else {
							$element = 'span';
							$trigger = '';
						}
					}
				if ( $start_of_week == 0) {
					$class = ($ii<6&&$ii>0?"$trigger":" weekend$trigger");
					$is_weekend = ($ii<6&&$ii>0)?false:true;
					$i++;
				} else {
					$class = ($ii<5)?"$trigger":" weekend$trigger";
					$is_weekend = ($ii<5)?false:true;
					$i++;
				}
				$week_date_format = date('M j, \'y',strtotime( "$c_year-$c_month-$thisday" ) );				
				$thisday_heading = ($time == 'week')?"<small>$week_date_format</small>":$thisday;
				if ( ( $is_weekend && get_option('mc_show_weekends') == 'true' ) || !$is_weekend ) {
					$my_calendar_body .= "\n".'<td class="'.(date("Ymd", mktime (0,0,0,$c_month,$thisday,$c_year))==date_i18n("Ymd",time()+$offset)?'current-day':'day-with-date').$events_class.'">'."\n	<$element class='mc-date".$class."'>".$thisday_heading."</$element>". my_calendar_draw_events($grabbed_events, $format, $process_date)."</td>";
				}				
	      } else {
			$process_date = date_i18n('Y-m-d',mktime(0,0,0,$c_month-1,$now,$c_year));
			//echo $process_date;
		  	$is_weekend = ( date('N',strtotime($process_date)) < 6 )?false:true;
			//$my_calendar_body .= date('N',$process_date);
			if ( ( $is_weekend && get_option('mc_show_weekends') == 'true' ) || !$is_weekend ) {
				$my_calendar_body .= "<td class='day-without-date'>&nbsp;</td>\n";
			}
	      }
        }
        $my_calendar_body .= "</tr>\n";
    }
	$my_calendar_body .= "\n</tbody>\n</table>";
} else if ($format == "list") {
	$my_calendar_body .= "<ul id=\"calendar-list\">";
	// show calendar as list
	$date_format = ( get_option('my_calendar_date_format') != '' ) ? ( get_option('my_calendar_date_format') ) : ( get_option( 'date_format' ) );
	$num_months = ($time == 'week')?1:get_option('my_calendar_show_months');
	$num_events = 0;
	for ($m=0;$m<$num_months;$m++) {
		$add_month = ($m == 0)?0:1;
		$c_month = (int) $c_month + $add_month;
		if ($c_month > 12) {
			$c_month = $c_month - 12;
			$c_year = $c_year + 1;
		}
		$days_in_month = date("t", mktime (0,0,0,$c_month,1,$c_year));
		
			if ($time == 'week') {
				$firstday = date('j',mktime(0,0,0,$c_month,$c_day,$c_year));
				$lastday = $firstday + 6;
			} else {
				$firstday = 1;
				$lastday = $days_in_month;
			}
			$useday = 1;
			$inc_month = false;		
	    for ($i=$firstday; $i<=$lastday; $i++) {
				if ($i > $days_in_month) {
					$thisday = $useday;
					if ($inc_month == false) {
						$c_month = ($c_month == 12)?1:$c_month+1;
					} 
					$inc_month = true;
					$useday++;
				} else {
					$thisday = $i;
				}		
		$process_date = date_i18n('Y-m-d',mktime(0,0,0,$c_month,$thisday,$c_year));
			$grabbed_events = my_calendar_grab_events($c_year,$c_month,$thisday,$category);
			if (count($grabbed_events)) {
				if ( get_option('list_javascript') != 1) {
					$is_anchor = "<a href='#'>";
					$is_close_anchor = "</a>";
				} else {
					$is_anchor = $is_close_anchor = "";
				}
				$classes = (date("Ymd", mktime (0,0,0,$c_month,$thisday,$c_year))==date("Ymd",time()+($offset)))?' current-day':'';
				$classes .= ( my_calendar_date_xcomp( $process_date, date('Y-m-d',time()+$offset) ) )?' past-date':'';
				$my_calendar_body .= "
				<li class='$class$classes'>
				<strong class=\"event-date\">$is_anchor".date_i18n($date_format,mktime(0,0,0,$c_month,$thisday,$c_year))."$is_close_anchor</strong>".my_calendar_draw_events($grabbed_events, $format, $process_date)."
				</li>";
				$num_events++;
			} 	
			$class = (my_calendar_is_odd($num_events))?"odd":"even";
		}	
	}
	if ($num_events == 0) {
		$my_calendar_body .= "<li class='no-events'>".__('There are no events scheduled during this period.','my-calendar') . "</li>";
	}
	$my_calendar_body .= "</ul>";
} else {
	$my_calendar_body .= "Unrecognized calendar format.";
}	
    if ($showkey != 'no') {
		$cat_limit = mc_select_category($category,'all','category');
		$sql = "SELECT * FROM " . MY_CALENDAR_CATEGORIES_TABLE . " $cat_limit ORDER BY category_name ASC";
		$cat_details = $wpdb->get_results($sql);
        $my_calendar_body .= '<div class="category-key">
		<h3>'.__('Category Key','my-calendar')."</h3>\n<ul>\n";
		if ( is_custom_icon() ) {
			$path = $wp_plugin_url . '/my-calendar-custom';
		} else {
			$path = $wp_plugin_url . '/my-calendar/icons';
		}
        foreach($cat_details as $cat_detail) {
			$hex = (strpos($cat_detail->category_color,'#') !== 0)?'#':'';
		
			$title_class = sanitize_title($cat_detail->category_name);
			if ($cat_detail->category_icon != "" && get_option('my_calendar_hide_icons')!='true') {
			$my_calendar_body .= '<li class="cat_'.$title_class.'"><span class="category-color-sample"><img src="'.$path.'/'.$cat_detail->category_icon.'" alt="" style="background:'.$hex.$cat_detail->category_color.';" /></span>'.stripcslashes($cat_detail->category_name)."</li>\n";
			} else {
			$my_calendar_body .= '<li class="cat_'.$title_class.'"><span class="category-color-sample no-icon" style="background:'.$hex.$cat_detail->category_color.';"> &nbsp; </span>'.stripcslashes($cat_detail->category_name)."</li>\n";			
			}
		}
        $my_calendar_body .= "</ul>\n</div>";
    }
	if ($format != 'mini') {
		$my_calendar_body .= mc_rss_links();
	}
	$my_calendar_body .= "\n</div>";
	}
    // The actual printing is done by the shortcode function.
    return $my_calendar_body;
}

function mc_rss_links() {
	global $wp_rewrite;
	if ( $wp_rewrite->using_permalinks() ) {
		$feed = '/feed/my-calendar-rss';
		$ics = '/feed/my-calendar-ics';
	} else {
		$feed = '?feed=my-calendar-rss';
		$ics = '?feed=my-calendar-ics';
	}
	$rss = (get_option('mc_show_rss')=='true')?"	<li class='rss'><a href='".home_url()."$feed'>".__('Subscribe by <abbr title="Really Simple Syndication">RSS</abbr>','my-calendar')."</a></li>":'';
	$ical = (get_option('mc_show_ical')=='true')?"	<li class='ics'><a href='".home_url()."$ics'>".__('Download as <abbr title="iCal Events Export">iCal</abbr>','my-calendar')."</a></li>":'';
	$output = "\n
<ul id='mc-export'>$rss
$ical
</ul>\n";	
	if ( get_option('mc_show_rss')=='true' || get_option('mc_show_ical')=='true' ) {
	return $output;
	}
}

// Configure the "Next" link in the calendar
function my_calendar_next_link($cur_year,$cur_month,$cur_week,$format,$time='month') {
  $next_year = $cur_year + 1;
  $next_events = ( get_option( 'mc_next_events') == '' )?"Next events":stripcslashes( get_option( 'mc_next_events') );
  $num_months = get_option('my_calendar_show_months');
  $nYr = $cur_year;
  if ($num_months <= 1 || $format!="list") {
	  if ($cur_month == 12) {
			$nMonth = 1;
			$nYr = $next_year;
	    } else {
			$next_month = $cur_month + 1;
			$nMonth = $next_month;
            $nYr = $cur_year;
	    }
	} else {
		$next_month = (($cur_month + $num_months) > 12)?(($cur_month + $num_months) - 12):($cur_month + $num_months);
		if ($cur_month >= (13-$num_months)) {	 
			$nMonth = $next_month;
			$nYr = $next_year;		
		} else {
			$nMonth = $next_month;
            $nYr = $cur_year;
		}	
	}
	if ($time == 'week') {
		if ( $cur_week == 0 ) { $cur_week = -1; }
		$nWeek = date('W',get_week_date( $cur_week+1, $cur_year ) );

		if ( jd_date_diff_precise( get_week_date( $cur_week, $cur_year ), get_week_date( $nWeek, $cur_year ) ) > 60*60*24*7*10 ) {
			$nWeek = '00';
		}
		
		$nYr = date('Y', get_week_date( $cur_week+1, $cur_year ) );
	}
	$output = array('month'=>$nMonth,'yr'=>$nYr,'week'=>$nWeek,'label'=>$next_events);
	return $output;
}

// Configure the "Previous" link in the calendar
function my_calendar_prev_link($cur_year,$cur_month,$cur_week,$format,$time='month') {
  $last_year = $cur_year - 1;
  $previous_events = ( get_option( 'mc_previous_events') == '' )?"Previous events":stripcslashes( get_option( 'mc_previous_events') );
  $num_months = get_option('my_calendar_show_months');
  $pYr = $cur_year;
  if ($num_months <= 1 || $format=="calendar") {  
		if ($cur_month == 1) {
			$pMonth = 12;
			$pYr = $last_year;
	    } else {
	      $next_month = $cur_month - 1;
		  $pMonth = $next_month;
          $pYr = $cur_year;
	    }
	} else {
		$next_month = ($cur_month > $num_months)?($cur_month - $num_months):(($cur_month - $num_months) + 12);
		if ($cur_month <= $num_months) {
			$pMonth = $next_month;
			$pYr = $last_year;
		} else {
			$pMonth = $next_month;
            $pYr = $cur_year;
		}	
	}
	if ($time == 'week') {
		$pWeek = date('W',get_week_date( $cur_week-1, $cur_year ) );
		if ( $pWeek == $cur_week) { $pWeek = '00'; }
		$pYr = date('Y', get_week_date( $cur_week-1, $cur_year ) );
	}
	$output = array( 'month'=>$pMonth,'yr'=>$pYr,'week'=>$pWeek,'label'=>$previous_events );
	return $output;
}

function my_calendar_categories_list($show='list',$context='public') {
	global $wpdb;
	if (isset($_GET['mc_id'])) {
		return;
	}
	$current_url = mc_get_current_url();
	$cv = (int) $_GET['cat'];

	$needle = array( "?cat=$cv", "&#038;cat=$cv", "&amp;cat=$cv","/&#038;",".php&#038;" );
	$replace = array( "","","","/?",".php?" );
	$current_url = str_replace( $needle,"",$current_url );

	$char = (strpos( $current_url, '?' ) == true )?'&#038;':'?';
	
	$admin_fields = ($context == 'public')?' ':' multiple="multiple" size="5" ';
	$admin_label = ($context == 'public')?'':__('(select to include)','my-calendar');
	$form = "<form action='".$current_url."' method='GET'>
				<div>";
			$qsa = array();
			parse_str($_SERVER['QUERY_STRING'],$qsa);
			foreach ($qsa as $name => $argument) {
				if ( $name != 'cat' ) {
					$form .= '		<input type="hidden" name="'.$name.'" value="'.$argument.'" />'."\n";
				}
			}
		$form .= ($show == 'list')?'':'
		</div><p>';
	$public_form = ($context == 'public')?$form:'';
	$name = ($context == 'public')?'cat':'category';
		
    $categories = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_CATEGORIES_TABLE . " ORDER BY category_id ASC");
	if ( !empty($categories) && count($categories)>1 ) {
		$output = "<div id='mc_categories'>\n";
		$output .= ($show == 'list')?"
		<ul>
			<li><a href='$current_url$char"."cat=all'>".__('All Categories','my-calendar')."</a></li>":$public_form.'
		<label for="category">'.__('Categories','my-calendar').' '.$admin_label.'</label>
			<select'.$admin_fields.'name="'.$name.'" id="category">
			<option value="all" selected="selected">'.__('All Categories','my-calendar').'</option>'."\n";
		
		foreach ($categories as $category) {
			if ($show == 'list') {
			$selected = ($category->category_id == (int) $_GET['cat'])?' class="selected"':'';
			$output .= "			<li$selected><a rel='nofollow' href='$current_url".$char."cat=$category->category_id'>$category->category_name</a></li>";
			} else {
			$selected = ($category->category_id == (int) $_GET['cat'])?' selected="selected"':'';			
			$output .= "			<option$selected value='$category->category_id'>$category->category_name</option>\n";
			}
		}
		$output .= ($show == 'list')?'</ul>':'</select>';
		$output .= ($context != 'admin' && $show != 'list')?"<input type='submit' value=".__('Submit','my-calendar')." /></p></form>":'';
		$output .= "\n</div>";
	}
	echo $output;
}

function my_calendar_locations_list($show='list',$type='saved',$datatype='name') {
global $wpdb;
	if ( isset( $_GET['mc_id'] ) ) {
		return;
	}
if ( $type == 'saved' ) {
	switch ( $datatype ) {
		case "name":$data = "location_label";
		break;
		case "city":$data = "location_city";
		break;
		case "state":$data = "location_state";
		break;
		case "zip":$data = "location_postcode";
		break;
		case "country":$data = "location_country";
		break;
		default:$data = "location_label";
		break;
	}
} else {
	$data = $datatype;
}
$current_url = mc_get_current_url();
$cv = urlencode($_GET['loc']);
$cd = urlencode($_GET['ltype']);
if (strpos($current_url,"?")===false) {
	$char = '?';
	$nonchar = '&#038;';
} else {
	$char = '&#038;';
	$nonchar = '?';
}
$needle = array("$nonchar"."loc=$cv&#038;ltype=$cd","$char"."loc=$cv&#038;ltype=$cd");
$current_url = str_replace( $needle,"",$current_url );

if (strpos($current_url,"/&#038;")!==false || strpos($current_url,".php&#038;")!==false) {
	$needle = array("/&#038;",".php&#038;");
	$replace = array("/?",".php?");
	$current_url = str_replace( $needle,$replace,$current_url );
} else {
	$char = '?';
}

	if ($type == 'saved') {
		$locations = $wpdb->get_results("SELECT DISTINCT $data FROM " . MY_CALENDAR_LOCATIONS_TABLE . " ORDER BY $data ASC", ARRAY_A );
	} else {
		$data = get_option( 'mc_user_settings' );
		$locations = $data['my_calendar_location_default']['values'];
		$datatype = str_replace('event_','',get_option( 'mc_location_type' ));
		$datatype = ($datatype=='label')?'name':$datatype;
		$datatype = ($datatype=='postcode')?'zip':$datatype;
	}
	if ( count($locations) > 1 ) {
		if ($show == 'list') {
			$output .= "<ul id='mc-locations-list'>
			<li><a href='$current_url$char"."loc=none&amp;ltype=none'>".__('Show all','my-calendar')."</a></li>\n";
		} else {
			$ltype = ($_GET['ltype']=='')?$datatype:$_GET['ltype'];
			$output .= "
	<div id='mc_locations'>
		<form action='".$current_url."' method='GET'>
		<div>
			<input type='hidden' name='ltype' value='$ltype' />";
		$qsa = array();
		parse_str($_SERVER['QUERY_STRING'],$qsa);
			foreach ($qsa as $name => $argument) {
				if ($name != 'loc' && $name != 'ltype') {
					$output .= '		<input type="hidden" name="'.$name.'" value="'.$argument.'" />'."\n";
				}
			}
			$output .= "
			<label for='mc-locations-list'>".__('Show events in:','my-calendar')."</label>
			<select name='loc' id='mc-locations-list'>
			<option value='none'>".__('Show all','my-calendar')."</option>\n";
		}
		foreach ( $locations as $key=>$location ) {
			if ($type == 'saved') {
				foreach ( $location as $key=>$value ) {
					$vt = urlencode(trim($value));
					if ($show == 'list') {
						$selected = ($vt == $_GET['loc'])?" class='selected'":'';
						$output .= "		<li$selected><a rel='nofollow' href='$current_url".$char."loc=$vt&amp;ltype=$datatype'>$value</a></li>\n";
					} else {
						$selected = ($vt == $_GET['loc'])?" selected='selected'":'';
						$output .= "		<option value='$vt'$selected>$value</option>\n";
					}
				}
			} else {
				$vk = urlencode(trim($key));
				$location = trim($location);
				if ($show == 'list') {
					$selected = ($vk == $_GET['loc'])?" class='selected'":'';
					$output .= "		<li$selected><a rel='nofollow' href='$current_url".$char."loc=$vk&amp;ltype=$datatype'>$location</a></li>\n";
				} else {
					$selected = ($vk == $_GET['loc'])?" selected='selected'":'';			
					$output .= "		<option value='$vk'$selected>$location</option>\n";	
				}			
			}
		}
		if ($show == 'list') {
			$output .= "</ul>";
		} else {
			$output .= "		</select> 
			<input type='submit' value=".__('Submit','my-calendar')." />
			</div>
		</form>
	</div>";
		}
	return $output;		
	} else {
	return;
	}
}

function mc_user_timezone($type='') {
global $user_ID;
	 $user_settings = get_option('mc_user_settings');
	 if ( get_option('mc_user_settings_enabled') == 'true' && $user_settings['my_calendar_tz_default']['enabled'] == 'on' ) {
		if ( is_user_logged_in() ) {
			get_currentuserinfo();
			$current_settings = get_user_meta( $user_ID, 'my_calendar_user_settings' );
			$tz = $current_settings['my_calendar_tz_default'];
		} else {
			$tz = '';
		}
	 } else {
		$tz = 'none';
	 }
	 if ( $tz == get_option('gmt_offset') || $tz == 'none' || $tz == '' ) {
		$gtz = '';
	 } else if ( $tz < get_option('gmt_offset') ) {
		$gtz = -(abs( get_option('gmt_offset') - $tz ) );
	 } else {
		$gtz = (abs( get_option('gmt_offset') - $tz ) );
	 }
	 return $gtz;
}

?>