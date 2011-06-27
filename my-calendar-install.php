<?php
// define global variables;
global $initial_listjs, $initial_caljs, $initial_minijs, $initial_ajaxjs, $initial_db, $initial_loc_db, $initial_cat_db, $initial_occ_db, $default_template,$default_user_settings,$stored_styles, $wpdb;

$initial_ajaxjs = "jQuery(document).ready(function(){
	jQuery('.calendar .my-calendar-nav a').live('click', function(e){
		e.preventDefault();
		var link = jQuery(this).attr('href');
		jQuery('#jd-calendar.calendar').html('Loading...');
		jQuery('#jd-calendar.calendar').load(link+' #jd-calendar.calendar > *', function() {
			jQuery('.calendar-event').children().not('h3').hide();
		});
	});	
	jQuery('.mini .my-calendar-nav a').live('click', function(e){
		e.preventDefault();
		var link = jQuery(this).attr('href');
		jQuery('#jd-calendar.mini').html('Loading...');
		jQuery('#jd-calendar.mini').load(link+' #jd-calendar.mini > *', function() {
			jQuery('.mini .has-events').children().not('.trigger').hide();
		});
	});	
	jQuery('.list .my-calendar-nav a').live('click', function(e){
		e.preventDefault();
		var link = jQuery(this).attr('href');
		jQuery('#jd-calendar.list').html('Loading...');
		jQuery('#jd-calendar.list').load(link+' #jd-calendar.list > *', function() {
			jQuery('#calendar-list li').children().not('.event-date').hide();
			jQuery('#calendar-list li.current-day').children().show();
		});
	});	
});";
// defaults will go into the options table on a new install
$initial_caljs = 'jQuery(document).ready(function($) {
  $(".calendar-event").children().not(".event-title").hide();
  $(".calendar-event .event-title").live("click",
     function(e) {
         e.preventDefault(); // remove line if you are using a link in the event title
	 $(this).parent().children().not(".event-title").toggle();
	 });
  $(".calendar-event .close").live("click",
     function(e) {
         e.preventDefault();
	 $(this).parent().toggle();
	 });
});';  

$initial_listjs = 'jQuery(document).ready(function($) {
  $("#calendar-list li").children().not(".event-date").hide();
  $("#calendar-list li.current-day").children().show();
  $(".event-date").live("click",
     function(e) {
	 e.preventDefault();
	 $(this).parent().children().not(".event-date").toggle();
     });
});';  

$initial_minijs = 'jQuery(document).ready(function($) {
  $(".mini .has-events").children().not(".trigger").hide();
  $(".mini .has-events .trigger").live("click",
     function(e) {
	 e.preventDefault();	 
	 $(this).parent().children().not(".trigger").toggle(); 
	 });
});';

$default_template = "<strong>{date}</strong> &#8211; {link_title}<br /><span>{time}, {category}</span>";

if ( ! empty($wpdb->charset) ) {
	$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
}
if ( ! empty($wpdb->collate) ) {
	$charset_collate .= " COLLATE $wpdb->collate";
}

$initial_db = "CREATE TABLE " . MY_CALENDAR_TABLE . " ( 
 event_id INT(11) NOT NULL AUTO_INCREMENT,
 event_begin DATE NOT NULL,
 event_end DATE NOT NULL,
 event_title VARCHAR(255) NOT NULL,
 event_desc TEXT NOT NULL,
 event_short TEXT NOT NULL,
 event_open INT(3) DEFAULT '2',
 event_time TIME,
 event_endtime TIME,
 event_recur CHAR(1),   
 event_repeats INT(3),
 event_status INT(1) NOT NULL DEFAULT '1',  
 event_author BIGINT(20) UNSIGNED,
 event_host BIGINT(20) UNSIGNED, 
 event_category BIGINT(20) UNSIGNED NOT NULL DEFAULT '1',
 event_link TEXT,
 event_link_expires TINYINT(1) NOT NULL,
 event_label VARCHAR(60) NOT NULL,
 event_street VARCHAR(60) NOT NULL,
 event_street2 VARCHAR(60) NOT NULL,
 event_city VARCHAR(60) NOT NULL,
 event_state VARCHAR(60) NOT NULL,
 event_postcode VARCHAR(10) NOT NULL,
 event_region VARCHAR(255) NOT NULL,
 event_country VARCHAR(60) NOT NULL,
 event_longitude FLOAT(10,6) NOT NULL DEFAULT '0',
 event_latitude FLOAT(10,6) NOT NULL DEFAULT '0',
 event_zoom INT(2) NOT NULL DEFAULT '14',
 event_group INT(1) NOT NULL DEFAULT '0',
 event_approved INT(1) NOT NULL DEFAULT '1',
 event_flagged INT(1) NOT NULL DEFAULT '0',
 PRIMARY KEY  (event_id),
 KEY event_recur (event_recur)
 ) $charset_collate;";

$initial_cat_db = "CREATE TABLE " . MY_CALENDAR_CATEGORIES_TABLE . " ( 
 category_id INT(11) NOT NULL AUTO_INCREMENT, 
 category_name VARCHAR(255) NOT NULL, 
 category_color VARCHAR(7) NOT NULL, 
 category_icon VARCHAR(128) NOT NULL,
 PRIMARY KEY  (category_id) 
 ) $charset_collate;";
 
$initial_loc_db = "CREATE TABLE " . MY_CALENDAR_LOCATIONS_TABLE . " ( 
 location_id INT(11) NOT NULL AUTO_INCREMENT, 
 location_label VARCHAR(60) NOT NULL,
 location_street VARCHAR(60) NOT NULL,
 location_street2 VARCHAR(60) NOT NULL,
 location_city VARCHAR(60) NOT NULL,
 location_state VARCHAR(60) NOT NULL,
 location_postcode VARCHAR(10) NOT NULL,
 location_region VARCHAR(255) NOT NULL,
 location_country VARCHAR(60) NOT NULL,
 location_longitude FLOAT(10,6) NOT NULL DEFAULT '0',
 location_latitude FLOAT(10,6) NOT NULL DEFAULT '0',
 location_zoom INT(2) NOT NULL DEFAULT '14',
 PRIMARY KEY  (location_id)
 ) $charset_collate;";

/* DRH */
$initial_occ_db = "CREATE TABLE " . MY_CALENDAR_OCCURRENCES_TABLE . " ( 
 event_id INT(11) NOT NULL,
 occ_id INT(11) NOT NULL AUTO_INCREMENT,
 occ_begin DATETIME NOT NULL,
 occ_end DATETIME NOT NULL,
 occ_active BOOLEAN NOT NULL DEFAULT TRUE,  
 occ_text TEXT,
 PRIMARY KEY  (occ_id),
 KEY (event_id)
 ) $charset_collate;";
 
 
$default_user_settings = array(
	'my_calendar_tz_default'=>array(
		'enabled'=>'off',
		'label'=>__('My Calendar Default Timezone','my-calendar'),
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
		'label'=>__('My Calendar Default Location','my-calendar'),
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
 
function mc_default_settings( ) {
global $default_template, $initial_listjs, $initial_caljs, $initial_minijs, $initial_ajaxjs, $initial_db, $initial_loc_db, $initial_occ_db, $initial_cat_db, $default_user_settings,$stored_styles;
// no arguments
	add_option('can_manage_events','edit_posts');
	add_option('display_author','false');
	add_option('display_jump','false');
	add_option('my_calendar_version','1.4.0');
	add_option('my_calendar_use_styles','false');
	add_option('my_calendar_show_months',1);
	add_option('my_calendar_show_map','true');
	add_option('my_calendar_show_address','false');
	add_option('my_calendar_today_template',$default_template);
	add_option('my_calendar_upcoming_template',$default_template);
	add_option('my_calendar_today_title','Today\'s Events');
	add_option('my_calendar_upcoming_title','Upcoming Events');
	add_option('calendar_javascript',0);
	add_option('list_javascript',0);
	add_option('mini_javascript',0);
	add_option('ajax_javascript',1);	
	add_option('my_calendar_minijs',$initial_minijs);
	add_option('my_calendar_listjs',$initial_listjs);
	add_option('my_calendar_caljs',$initial_caljs);
	add_option('my_calendar_ajaxjs',$initial_ajaxjs);
	add_option('my_calendar_notime_text','N/A');
	add_option('my_calendar_hide_icons','false');	 
	add_option('mc_event_link_expires','no');
	add_option('mc_apply_color','default');
	add_option('mc_input_options',array('event_short'=>'on','event_desc'=>'on','event_category'=>'on','event_link'=>'on','event_recurs'=>'on','event_open'=>'on','event_location'=>'on','event_location_dropdown'=>'on','event_use_editor'=>'off') );	
	add_option('mc_input_options_administrators','false');
	add_option('mc_event_mail','false');
	add_option('mc_desc','true');
	add_option('mc_short','false');
	add_option('mc_event_mail_subject','');
	add_option('mc_event_mail_to','');
	add_option('mc_event_mail_message','');
	add_option('mc_event_approve','false');	
	add_option('mc_event_approve_perms','manage_options');
	add_option('mc_no_fifth_week','true');
	$mc_user_settings = $default_user_settings;	
	add_option('mc_user_settings',$mc_user_settings);
	add_option('mc_location_type','event_state');
	add_option('mc_user_settings_enabled',false);
	add_option('mc_user_location_type','state');
	add_option('my_calendar_show_js','' );   
	add_option('my_calendar_show_css','' );   
	add_option('my_calendar_templates', array(
		'title'=>'{title}'
	));	
	add_option('mc_skip_holidays','false');
    add_option('mc_event_edit_perms','manage_options');
	add_option('my_calendar_css_file','my-calendar.css');
	add_option('mc_show_rss','false');
	add_option('mc_show_ical','false');	
		$defaults = array(
			'upcoming'=>array(	
				'type'=>'event',
				'before'=>3,
				'after'=>3,
				'template'=>$default_template,
				'category'=>'',
				'text'=>'',
				'title'=>'Upcoming Events'
			),
			'today'=>array(
				'template'=>$default_template,
				'category'=>'',
				'title'=>'Today\'s Events',
				'text'=>''
			)
		);
	add_option('my_calendar_widget_defaults',$defaults);
	add_option( 'mc_show_weekends','true' );
	add_option( 'mc_uri','' );	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($initial_db);
	dbDelta($initial_cat_db);
	dbDelta($initial_loc_db);
  
  /*  DRH */	
	dbDelta($initial_occ_db);
}

function upgrade_db() {
global $initial_db, $initial_loc_db, $initial_cat_db ,$initial_occ_db;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($initial_db);
	dbDelta($initial_cat_db);
	dbDelta($initial_loc_db);	
	/* DRH */
	dbDelta($initial_occ_db);
}

function my_calendar_copyr($source, $dest) {
	// Sanity check
	if ( !file_exists($source) ) {
		return false;
	}
    // Check for symlinks
    if (is_link($source)) {
        return symlink(readlink($source), $dest);
    }
    // Simple copy for a file
    if (is_file($source)) {
        return @copy($source, $dest);
    }
    // Make destination directory
    if (!is_dir($dest)) {
        @mkdir($dest);
    }
    // Loop through the folder
	$dir = dir($source);
	while (false !== $entry = $dir->read()) {
		// Skip pointers
		if ($entry == '.' || $entry == '..') {
			continue;
		}
		// Deep copy directories
		my_calendar_copyr("$source/$entry", "$dest/$entry");
	}
	// Clean up
	$dir->close();
    return true;
}
function my_calendar_rmdirr($dirname) {
	// Sanity check
	if (!file_exists($dirname)) {
	return false;
	}
	// Simple delete for a file
	if (is_file($dirname)) {
	return unlink($dirname);
	}
	// Loop through the folder
	$dir = dir($dirname);
	while (false !== $entry = $dir->read()) {
	// Skip pointers
		if ($entry == '.' || $entry == '..') {
		continue;
		}
		// Recurse
		my_calendar_rmdirr("$dirname/$entry");
	}
	// Clean up
	$dir->close();
	return @rmdir($dirname);
}
function my_calendar_backup() {
    $to = dirname(__FILE__)."/../styles_backup/";
    $from = dirname(__FILE__)."/styles/";
    my_calendar_copyr($from, $to);
	
    $to = dirname(__FILE__)."/../icons_backup/";
    $from = dirname(__FILE__)."/icons/";
    my_calendar_copyr($from, $to);	
}
function my_calendar_recover() {
    $from = dirname(__FILE__)."/../styles_backup/";
    $to = dirname(__FILE__)."/styles/";
    my_calendar_copyr($from, $to);
    if (is_dir($from)) {
        my_calendar_rmdirr($from);
    }
	
    $from = dirname(__FILE__)."/../icons_backup/";
    $to = dirname(__FILE__)."/icons/";
    my_calendar_copyr($from, $to);
    if (is_dir($from)) {
        my_calendar_rmdirr($from);
    }	
}
add_filter('upgrader_pre_install', 'my_calendar_backup', 10, 2);
add_filter('upgrader_post_install', 'my_calendar_recover', 10, 2);

$stored_styles = array(
'my-calendar.css'=>'
#jd-calendar,#calendar-list {
background: #fff;
}

#jd-calendar caption, #jd-calendar .my-calendar-date-switcher, 
#jd-calendar .category-key, #jd-calendar .calendar-event .details, 
#jd-calendar .calendar-events {
background: #edf7ff;
}

#jd-calendar .category-key .no-icon {
border: 1px solid #555;
}

#jd-calendar caption, #jd-calendar .my-calendar-date-switcher, #jd-calendar .my-calendar-nav li a:hover, #jd-calendar .category-key {
border: 1px solid #a9e3ff; 
}
#jd-calendar .list-event .details, #jd-calendar td {
border:1px solid #eee; 
}
#jd-calendar .calendar-event .details, #jd-calendar .calendar-events {
color:#000;
}

#jd-calendar .my-calendar-nav li a, #jd-calendar .calendar-event .details, #jd-calendar .calendar-events  {
border:1px solid #9b5;
}

#jd-calendar .list-event .details, #jd-calendar .day-without-date {
background:#fafafa;
}

#jd-calendar .nextmonth, #jd-calendar .nextmonth .weekend {
color: #777;
}

#jd-calendar #calendar-list .odd {
background:#d3e3e3;
}

#jd-calendar .odd .list-event .details {
background:#e3f3f3;
border:1px solid #c3d3d3;
}

#jd-calendar .current-day {
background:#ffb;
}
#jd-calendar .current-day .mc-date {
color: #000; 
background: #eee;
}
#jd-calendar .weekend {
background:#bd7; 
color: #000; 
}
#jd-calendar .mc-date {
background:#f6f6f6; 
}
#jd-calendar .my-calendar-nav li a {
color: #243f82; 
background:#fff;

}
#jd-calendar .my-calendar-nav li a:hover {
color:#000; 
border: 1px solid #243f82;
}
#upcoming-events .past-event {
color: #777; 
}
#upcoming-events .today {
color: #111; 
}
#upcoming-events .future-event {
color: #555; 
}

#jd-calendar caption, #jd-calendar .my-calendar-date-switcher  {
margin: 2px 0;
font-weight:700;
padding:2px 0;
}

#jd-calendar table {
width:100%;
line-height:1.2;
border-collapse:collapse;
}

#jd-calendar td {
vertical-align:top;
text-align:left;
width:13%;
height:70px;
padding:2px!important;
}
.mini td {
height: auto!important;
}
#jd-calendar th {
text-align: center;
padding: 5px 0!important;
letter-spacing: 1px;
}
#jd-calendar th abbr {
border-bottom: none;
}
#jd-calendar h3 {
font-size:.8em;
font-family: Arial, Verdana, sans-serif;
font-weight:700;
margin:3px 0;
padding:0;
width: 100%;
-moz-border-radius: 3px;
-webkit-border-radius: 3px;
border-radius: 3px;
}
#jd-calendar h3 img {
vertical-align: middle;
margin: 0 3px 0 0!important;
}
#jd-calendar #calendar-list h3 img {
vertical-align: middle;
}

#jd-calendar .list-event h3 {
font-size:1.2em;
margin:0;
}
#jd-calendar .calendar-event .details, #jd-calendar .calendar-events {
position:absolute;
left:15%;
width:70%;
-moz-border-radius:10px;
-moz-box-shadow:3px 3px 6px #777;
-webkit-box-shadow:3px 3px 6px #777;
box-shadow:3px 3px 6px #777;
padding:5px;
z-index: 3;
}
#jd-calendar .details .close {
float: right;
width: 12px!important;
margin-top: -2px!important;
}
#jd-calendar .calendar-events {
width: 200px!important;
left: 0px;
}
#jd-calendar .list-event .details {
-moz-border-radius:5px;
-webkit-border-radius:5px;
border-radius:5px;
margin:5px 0;
padding:5px 5px 0;
}
#jd-calendar #calendar-list {
margin: 0;
padding: 0;
}
#jd-calendar #calendar-list li {
padding:5px;
list-style-type: none;
margin: 0;
}

#jd-calendar .mc-date {
display:block;
margin:-2px -2px 2px;
padding:2px 4px;
}
#jd-calendar th {
font-size:.8em;
text-transform:uppercase;
padding:2px 4px 2px 0;
}
#jd-calendar .category-key {
padding: 5px;
margin: 5px 0;
}
#jd-calendar .category-key ul {
list-style-type: none;
margin: 0;
padding: 0;
}
#jd-calendar .category-key li {
margin: 2px 10px;
}
#jd-calendar .category-key span {
margin-right:5px;
vertical-align:middle;
}
#jd-calendar .category-key .no-icon {
width: 10px;
height: 10px;
display: inline-block;
-moz-border-radius: 2px;
-webkit-border-radius: 2px;
border-radius: 2px;
}

#calendar-list li {
text-indent:0;
margin:0;
padding:0;
}

#jd-calendar .calendar-event .event-time, #jd-calendar .list-event .event-time {
display:block;
float:left;
height:100%;
margin-right:10px;
margin-bottom:10px;
font-weight:700;
font-size:.9em;
width: 6em;
}

#jd-calendar p {
line-height:1.5;
margin:0 0 1em;
padding:0;
}

#jd-calendar .sub-details {
margin-left:6em;
}
#jd-calendar {
position: relative;
}
#jd-calendar img {
border: none;
}
.category-color-sample img {
margin-right: 5px;
vertical-align: top;
}

#jd-calendar .my-calendar-nav ul {
height: 2.95em;
list-style-type:none;
margin:0;
padding:0;
}

.mini .my-calendar-nav ul {
height: 2em!important;
}

#jd-calendar .my-calendar-nav li {
float:left;
list-style-type: none;
}

#jd-calendar .my-calendar-nav li:before {
content:\'\';
}
#jd-calendar .my-calendar-nav li a {
display:block;
text-align:center;
padding:1px 20px;
}
.mini .my-calendar-nav li a {
padding: 1px 3px!important;
font-size: .7em;
}
#jd-calendar .my-calendar-next {
margin-left: 4px;
text-align:right;
}
#jd-calendar .my-calendar-next a {
-webkit-border-top-right-radius: 8px;
-webkit-border-bottom-right-radius: 8px;
-moz-border-radius-topright: 8px;
-moz-border-radius-bottomright: 8px;
border-top-right-radius: 8px;
border-bottom-right-radius: 8px;
}
#jd-calendar .my-calendar-prev a {
-webkit-border-top-left-radius: 8px;
-webkit-border-bottom-left-radius: 8px;
-moz-border-radius-topleft: 8px;
-moz-border-radius-bottomleft: 8px;
border-top-left-radius: 8px;
border-bottom-left-radius: 8px;
}

#jd-calendar.mini .my-calendar-date-switcher label {
display: block;
float: left;
width: 6em;
}
#jd-calendar.mini .my-calendar-date-switcher {
padding: 4px;
}
#jd-calendar.mini td .category-icon {
display: none;
}
#jd-calendar.mini h3 {
font-size: 1.1em;
}

#jd-calendar.mini .day-with-date span, #jd-calendar.mini .day-with-date a {
font-family: Arial, Verdana, sans-serif;
font-size: .9em;
padding:1px;
}
#jd-calendar .mini-event .sub-details {
margin: 0;
border-bottom: 1px solid #ccc;
padding: 2px 0 0;
margin-bottom: 5px;
}
#jd-calendar.mini .day-with-date a {
display: block;
margin: -2px;
font-weight: 700;
text-decoration: underline;
}',
	'dark.css'=>'/* A theme in dark grays and blacks with light text and dark blue highlighting. */

#jd-calendar .event-title {
color: #fff;
}
#jd-calendar,#calendar-list {
background: #333;
color: #fff;
}
#jd-calendar a {
color: #9cf;
text-decoration: none;
}
#jd-calendar a:hover {
text-decoration: underline;
color: #fff;
}
#jd-calendar caption, #jd-calendar .my-calendar-date-switcher, 
#jd-calendar .category-key, #jd-calendar .calendar-event .details, 
#jd-calendar .calendar-events {
background: #222;
color: #fff;
}

#jd-calendar .category-key .no-icon {
border: 1px solid #bbb;
}

#jd-calendar caption, #jd-calendar .my-calendar-date-switcher, #jd-calendar .my-calendar-nav li a:hover, #jd-calendar .category-key {
border: 1px solid #222;
}
#jd-calendar .list-event .details, #jd-calendar td {
border:1px solid #222; 
}
#jd-calendar .calendar-event .details, #jd-calendar .calendar-events {
background: #444;
border: 1px solid #222;
color:#fff;
}

#jd-calendar .my-calendar-nav li a, #jd-calendar .calendar-event .details, #jd-calendar .calendar-events  {
border:1px solid #444;
}

#jd-calendar .list-event .details, #jd-calendar .day-without-date {
background:#252525;
color: #eee;
}

#jd-calendar .nextmonth, #jd-calendar .nextmonth .weekend {
color: #999;
}

#jd-calendar #calendar-list .odd {
background:#353535;
}

#jd-calendar .odd .list-event .details {
background:#151515; 
border:1px solid #353535; 
}

#jd-calendar .current-day {
background:#224; 
}
#jd-calendar .current-day .mc-date {
color: #fff;
background: #111; 
}
#jd-calendar .weekend {
background:#555!important;
color: #fff!important;
}
#jd-calendar .mc-date {
background:#080808; 
color: #fff;
}
#jd-calendar .my-calendar-nav li a {
color: #aaf;
background:#000; 

}
#jd-calendar .my-calendar-nav li a:hover {
color:#fff; 
border: 1px solid #aaf;
}
#upcoming-events .past-event {
color: #aaa; 
}
#upcoming-events .today {
color: #eee; 
}
#upcoming-events .future-event {
color: #bbb;
}

#jd-calendar caption, #jd-calendar .my-calendar-date-switcher  {
margin: 2px 0;
font-weight:700;
padding:2px 0;
}

#jd-calendar table {
width:100%;
line-height:1.2;
border-collapse:collapse;
}

#jd-calendar td {
vertical-align:top;
text-align:left;
width:13%;
height:70px;
padding:2px!important;
}
.mini td {
height: auto!important;
}
#jd-calendar th {
text-align: center;
padding: 5px 0!important;
letter-spacing: 1px;
}
#jd-calendar th abbr {
border-bottom: none;
}
#jd-calendar h3 {
font: 700 .8em Arial, Verdana, sans-serif;
margin:3px 0;
padding:0;
width: 100%;
color: #fff;
}
#jd-calendar h2 {
color: #fff;
}
#jd-calendar h3 img {
vertical-align: middle;
margin: 0 3px 0 0!important;
}
#jd-calendar #calendar-list h3 img {
vertical-align: middle;
}

#jd-calendar .list-event h3 {
font-size:1.2em;
margin:0;
}
#jd-calendar .calendar-event .details, #jd-calendar .calendar-events {
position:absolute;
left: 15%;
width:70%;
padding:5px;
z-index: 3;
border: 1px solid #222;
-moz-box-shadow: 3px 3px 10px #000;
-webkit-box-shadow: 3px 3px 10px #000;
box-shadow: 3px 3px 10px #000;
}

#jd-calendar .details .close a img {
float: right;
background: #aaa;
text-align: center;
padding: 2px;
-moz-border-radius: 3px;
-webkit-border-radius: 3px;
border-radius: 3px;
}
#jd-calendar .calendar-events {
width: 200px!important;
left: 0px;
}
#jd-calendar .list-event .details {
margin:5px 0;
padding:5px 5px 0;
}
#jd-calendar #calendar-list {
margin: 0;
padding: 0;
}
#jd-calendar #calendar-list li {
padding:5px;
list-style-type: none;
margin: 0;
}

#jd-calendar .mc-date {
display:block;
margin:-2px -2px 2px;
padding:2px 4px;
}
#jd-calendar th {
font-size:.8em;
text-transform:uppercase;
padding:2px 4px 2px 0;
}
#jd-calendar .category-key {
padding: 5px;
margin: 5px 0;
}
#jd-calendar .category-key ul {
list-style-type: none;
margin: 0;
padding: 0;
}
#jd-calendar .category-key li {
margin: 2px 10px;
}
#jd-calendar .category-key span {
margin-right:5px;
vertical-align:middle;
}
#jd-calendar .category-key .no-icon {
width: 10px;
height: 10px;
display: inline-block;
}

#calendar-list li {
text-indent:0;
margin:0;
padding:0;
}

#jd-calendar .calendar-event .event-time, #jd-calendar .list-event .event-time {
display:block;
height:100%;
margin-right:10px;
margin-bottom:10px;
font-weight:700;
font-size:.9em;
}

#jd-calendar p {
line-height:1.5;
margin:0 0 1em;
padding:0;
}
#jd-calendar {
position: relative;
}
#jd-calendar img {
border: none;
}
.category-color-sample img {
margin-right: 5px;
vertical-align: top;
}

#jd-calendar .my-calendar-nav ul {
height: 2em;
list-style-type:none;
margin:0 5px;
padding:0;
}

.mini .my-calendar-nav ul {
height: 2em!important;
}

#jd-calendar .my-calendar-nav li {
float:left;
list-style-type: none;
}

#jd-calendar .my-calendar-nav li:before {
content:\'\';
}
#jd-calendar .my-calendar-nav li a {
display:block;
text-align:center;
padding:1px 20px;
}
.mini .my-calendar-nav li a {
padding: 1px 3px!important;
font-size: .7em;
}
#jd-calendar .my-calendar-next {
margin-left: 4px;
text-align:right;
}

#jd-calendar.mini .my-calendar-date-switcher label {
display: block;
float: left;
width: 6em;
}
#jd-calendar.mini .my-calendar-date-switcher {
padding: 4px;
}
#jd-calendar.mini td .category-icon {
display: none;
}
#jd-calendar.mini h3 {
font-size: 1.1em;
}

#jd-calendar.mini .day-with-date span, #jd-calendar.mini .day-with-date a {
font: .9em Arial, Verdana, sans-serif;
padding:1px;
color: #fff;
}
#jd-calendar .mini-event .sub-details {
margin: 0;
border-bottom: 1px solid #ccc;
padding: 2px 0 0;
margin-bottom: 5px;
}
#jd-calendar.mini .day-with-date a {
display: block;
margin: -2px;
font-weight: 700;
text-decoration: underline;
}

.mini td {
height: auto!important;
}

.mini .my-calendar-nav ul {
height: 2em!important;
}
.mini .my-calendar-nav li a {
padding: 1px 3px!important;
font-size: .7em;
}',
	'light.css'=>'/* A light-colored theme almost entirely in whites and light grays with black text. */

#jd-calendar,#calendar-list {
background: #fff; 
}

#jd-calendar caption, #jd-calendar .my-calendar-date-switcher, 
#jd-calendar .category-key, #jd-calendar .calendar-event .details, 
#jd-calendar .calendar-events {
background: #fff; 
}

#jd-calendar .category-key .no-icon {
border: 1px solid #555; 
}

#jd-calendar caption, #jd-calendar .my-calendar-date-switcher, #jd-calendar .my-calendar-nav li a:hover, #jd-calendar .category-key {
border: 1px solid #ddd; 
}
#jd-calendar .list-event .details, #jd-calendar td {
border:1px solid #eee; 
}
#jd-calendar .calendar-event .details, #jd-calendar .calendar-events {
color:#000;
}

#jd-calendar .my-calendar-nav li a, #jd-calendar .calendar-event .details, #jd-calendar .calendar-events  {
border:1px solid #bbb; 
}

#jd-calendar .list-event .details, #jd-calendar .day-without-date {
background:#fafafa;
}
#jd-calendar .nextmonth, #jd-calendar .nextmonth .weekend {
color: #777;
}
#jd-calendar #calendar-list .odd {
background:#e3e3e3; 
}

#jd-calendar .odd .list-event .details {
background:#f3f3f3;
border:1px solid #d3d3d3;
}

#jd-calendar .current-day {
background:#ffd; 
}
#jd-calendar .current-day .mc-date {
color: #000; 
background: #eee; 
}
#jd-calendar .weekend {
background:#bbb; 
color: #000; 
}
#jd-calendar .mc-date {
background:#f6f6f6; 
}
#jd-calendar .my-calendar-nav li a {
color: #00a; 
background:#fff; 

}
#jd-calendar .my-calendar-nav li a:hover {
color:#000; 
border: 1px solid #00a; 
}
#upcoming-events .past-event {
color: #777; 
}
#upcoming-events .today {
color: #111; 
}
#upcoming-events .future-event {
color: #555; 
}

#jd-calendar caption, #jd-calendar .my-calendar-date-switcher  {
margin: 2px 0;
font-weight:700;
padding:2px 0;
}

#jd-calendar table {
width:100%;
line-height:1.2;
border-collapse:collapse;
}

#jd-calendar td {
vertical-align:top;
text-align:left;
width:13%;
height:70px;
padding:2px!important;
}
.mini td {
height: auto!important;
}
#jd-calendar th {
text-align: center;
padding: 5px 0!important;
letter-spacing: 1px;
}
#jd-calendar th abbr {
border-bottom: none;
}
#jd-calendar h3 {
font: 700 .8em Arial, Verdana, sans-serif;
margin:3px 0;
padding:0;
width: 100%;
}
#jd-calendar h3 img {
vertical-align: middle;
margin: 0 3px 0 0!important;
}
#jd-calendar #calendar-list h3 img {
vertical-align: middle;
}

#jd-calendar .list-event h3 {
font-size:1.2em;
margin:0;
}
#jd-calendar .calendar-event .details, #jd-calendar .calendar-events {
position:absolute;
left: 15%;
width:70%;
padding:5px;
z-index: 3;
}
#jd-calendar .details .close {
float: right;
width: 12px!important;
margin-top: -2px!important;
}
#jd-calendar .calendar-events {
width: 200px!important;
left: 0px;
}
#jd-calendar .list-event .details {
margin:5px 0;
padding:5px 5px 0;
}
#jd-calendar #calendar-list {
margin: 0;
padding: 0;
}
#jd-calendar #calendar-list li {
padding:5px;
list-style-type: none;
margin: 0;
}

#jd-calendar .mc-date {
display:block;
margin:-2px -2px 2px;
padding:2px 4px;
}
#jd-calendar th {
font-size:.8em;
text-transform:uppercase;
padding:2px 4px 2px 0;
}
#jd-calendar .category-key {
padding: 5px;
margin: 5px 0;
}
#jd-calendar .category-key ul {
list-style-type: none;
margin: 0;
padding: 0;
}
#jd-calendar .category-key li {
margin: 2px 10px;
}
#jd-calendar .category-key span {
margin-right:5px;
vertical-align:middle;
}
#jd-calendar .category-key .no-icon {
width: 10px;
height: 10px;
display: inline-block;
}

#calendar-list li {
text-indent:0;
margin:0;
padding:0;
}

#jd-calendar .calendar-event .event-time, #jd-calendar .list-event .event-time {
display:block;
height:100%;
margin-right:10px;
margin-bottom:10px;
font-weight:700;
font-size:.9em;
}

#jd-calendar p {
line-height:1.5;
margin:0 0 1em;
padding:0;
}
#jd-calendar {
position: relative;
}
#jd-calendar img {
border: none;
}
.category-color-sample img {
margin-right: 5px;
vertical-align: top;
}

#jd-calendar .my-calendar-nav ul {
height: 2.95em;
list-style-type:none;
margin:0;
padding:0;
}

.mini .my-calendar-nav ul {
height: 2em!important;
}

#jd-calendar .my-calendar-nav li {
float:left;
list-style-type: none;
}

#jd-calendar .my-calendar-nav li:before {
content:\'\';
}
#jd-calendar .my-calendar-nav li a {
display:block;
text-align:center;
padding:1px 20px;
}
.mini .my-calendar-nav li a {
padding: 1px 3px!important;
font-size: .7em;
}
#jd-calendar .my-calendar-next {
margin-left: 4px;
text-align:right;
}

#jd-calendar.mini .my-calendar-date-switcher label {
display: block;
float: left;
width: 6em;
}
#jd-calendar.mini .my-calendar-date-switcher {
padding: 4px;
}
#jd-calendar.mini td .category-icon {
display: none;
}
#jd-calendar.mini h3 {
font-size: 1.1em;
}

#jd-calendar.mini .day-with-date span, #jd-calendar.mini .day-with-date a {
font: .9em Arial, Verdana, sans-serif;
padding:1px;
}
#jd-calendar .mini-event .sub-details {
margin: 0;
border-bottom: 1px solid #ccc;
padding: 2px 0 0;
margin-bottom: 5px;
}
#jd-calendar.mini .day-with-date a {
display: block;
margin: -2px;
font-weight: 700;
text-decoration: underline;
}
.mini td {
height: auto!important;
}

.mini .my-calendar-nav ul {
height: 2em!important;
}
.mini .my-calendar-nav li a {
padding: 1px 3px!important;
font-size: .7em;
}',
	'inherit.css'=>'/* These styles provide a minimal degree of styling, allowing most theme defaults to be dominant. */
#jd-calendar .details {
background: #fff;
border: 1px solid #000;
}
#jd-calendar.mini .mini-event {
background: #fff;
border: 1px solid #000;
padding: 5px;
}
#jd-calendar.mini .mini-event .details {
background: none;
border: none;
}
/* The end of all colors set (or set and removed) in this style sheet. */
#jd-calendar caption, #jd-calendar .my-calendar-date-switcher  {
margin: 2px 0;
font-weight:700;
padding:2px 0;
}
#jd-calendar table {
width:100%;
line-height:1.2;
border-collapse:collapse;
}

#jd-calendar td {
vertical-align:top;
text-align:left;
width:13%;
height:70px;
padding:2px!important;
}
.mini td {
height: auto!important;
}
#jd-calendar th {
text-align: center;
padding: 5px 0!important;
letter-spacing: 1px;
}
#jd-calendar th abbr {
border-bottom: none;
}
#jd-calendar h3 {
font: 700 .8em Arial, Verdana, sans-serif;
margin:3px 0;
padding:0;
width: 100%;
}
#jd-calendar h3 img {
vertical-align: middle;
margin: 0 3px 0 0!important;
}
#jd-calendar #calendar-list h3 img {
vertical-align: middle;
}

#jd-calendar .list-event h3 {
font-size:1.2em;
margin:0;
}
#jd-calendar .calendar-event .details, #jd-calendar .calendar-events {
position:absolute;
left: 15%;
width:70%;
padding:5px;
z-index: 3;
}
#jd-calendar .details .close {
float: right;
width: 12px!important;
margin-top: -2px!important;
}
#jd-calendar .calendar-events {
width: 200px!important;
left: 0px;
}
#jd-calendar .list-event .details {
margin:5px 0;
padding:5px 5px 0;
}
#jd-calendar #calendar-list {
margin: 0;
padding: 0;
}
#jd-calendar #calendar-list li {
padding:5px;
list-style-type: none;
margin: 0;
}

#jd-calendar .mc-date {
display:block;
margin:-2px -2px 2px;
padding:2px 4px;
}
#jd-calendar th {
font-size:.8em;
text-transform:uppercase;
padding:2px 4px 2px 0;
}
#jd-calendar .category-key {
padding: 5px;
margin: 5px 0;
}
#jd-calendar .category-key ul {
list-style-type: none;
margin: 0;
padding: 0;
}
#jd-calendar .category-key li {
margin: 2px 10px;
}
#jd-calendar .category-key span {
margin-right:5px;
vertical-align:middle;
}
#jd-calendar .category-key .no-icon {
width: 10px;
height: 10px;
display: inline-block;
}

#calendar-list li {
text-indent:0;
margin:0;
padding:0;
}

#jd-calendar .calendar-event .event-time, #jd-calendar .list-event .event-time {
display:block;
height:100%;
margin-right:10px;
margin-bottom:10px;
font-weight:700;
font-size:.9em;
}

#jd-calendar p {
line-height:1.5;
margin:0 0 1em;
padding:0;
}
#jd-calendar { position: relative; }
#jd-calendar img { border: none; }
.category-color-sample img {
margin-right: 5px;
vertical-align: top;
}

#jd-calendar .my-calendar-nav ul {
height: 2.95em;
list-style-type:none;
margin:0;
padding:0;
}

.mini .my-calendar-nav ul { height: 2em!important; }

#jd-calendar .my-calendar-nav li {
float:left;
list-style-type: none;
}
#jd-calendar .my-calendar-nav li:before { content:\'\'; }
#jd-calendar .my-calendar-nav li a {
display:block;
text-align:center;
padding:1px 20px;
}
.mini .my-calendar-nav li a {
padding: 1px 3px!important;
font-size: .7em;
}
#jd-calendar .my-calendar-next {
margin-left: 4px;
text-align:right;
}

#jd-calendar.mini .my-calendar-date-switcher label {
display: block;
float: left;
width: 6em;
}
#jd-calendar.mini .my-calendar-date-switcher { padding: 4px; }
#jd-calendar.mini td .category-icon { display: none; }
#jd-calendar.mini h3 { font-size: 1.1em; }
#jd-calendar.mini .day-with-date span, #jd-calendar.mini .day-with-date a {
font: .9em Arial, Verdana, sans-serif;
padding:1px;
}
#jd-calendar .mini-event .sub-details {
margin: 0;
border-bottom: 1px solid #ccc;
padding: 2px 0 0;
margin-bottom: 5px;
}
#jd-calendar.mini .day-with-date a {
display: block;
margin: -2px;
font-weight: 700;
text-decoration: underline;
}
.mini td { height: auto!important; }
.mini .my-calendar-nav ul { height: 2em!important; }
.mini .my-calendar-nav li a {
padding: 1px 3px!important;
font-size: .7em;
}'
	)

?>
