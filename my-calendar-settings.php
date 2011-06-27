<?php
// Display the admin configuration page
function my_calendar_import() {
	if ( get_option('ko_calendar_imported') != 'true' ) {
	global $wpdb;
		define('KO_CALENDAR_TABLE', $wpdb->prefix . 'calendar');
		define('KO_CALENDAR_CATS', $wpdb->prefix . 'calendar_categories');
		$events = $wpdb->get_results("SELECT * FROM " . KO_CALENDAR_TABLE, 'ARRAY_A');
		$sql = "";
		foreach ($events as $key) {
			$title = mysql_real_escape_string($key['event_title']);
			$desc = mysql_real_escape_string($key['event_desc']);
			$begin = mysql_real_escape_string($key['event_begin']);
			$end = mysql_real_escape_string($key['event_end']);
			$time = mysql_real_escape_string($key['event_time']);
			$recur = mysql_real_escape_string($key['event_recur']);
			$repeats = mysql_real_escape_string($key['event_repeats']);
			$author = mysql_real_escape_string($key['event_author']);
			$category = mysql_real_escape_string($key['event_category']);
			$linky = mysql_real_escape_string($key['event_link']);
		    $sql = "INSERT INTO " . MY_CALENDAR_TABLE . " SET 
			event_title='" . ($title) . "', 
			event_desc='" . ($desc) . "', 
			event_begin='" . ($begin) . "', 
			event_end='" . ($end) . "', 
			event_time='" . ($time) . "', 
			event_recur='" . ($recur) . "', 
			event_repeats='" . ($repeats) . "', 
			event_author=".($author).", 
			event_category=".($category).", 
			event_link='".($linky)."';
			";
			$events_results = $wpdb->query($sql);		
		}	
		$cats = $wpdb->get_results("SELECT * FROM " . KO_CALENDAR_CATS, 'ARRAY_A');	
		$catsql = "";
		foreach ($cats as $key) {
			$name = mysql_real_escape_string($key['category_name']);
			$color = mysql_real_escape_string($key['category_colour']);
			$id = mysql_real_escape_string($key['category_id']);
			$catsql = "INSERT INTO " . MY_CALENDAR_CATEGORIES_TABLE . " SET 
				category_id='".$id."',
				category_name='".$name."', 
				category_color='".$color."' 
				ON DUPLICATE KEY UPDATE 
				category_name='".$name."', 
				category_color='".$color."';
				";	
			$cats_results = $wpdb->query($catsql);
			//$wpdb->print_error(); 			
		}			
		$message = ( $cats_results !== false )?__('Categories imported successfully.','my-calendar'):__('Categories not imported.','my-calendar');
		$e_message = ( $events_results !== false )?__('Events imported successfully.','my-calendar'):__('Events not imported.','my-calendar');
		$return = "<div id='message' class='updated fade'><ul><li>$message</li><li>$e_message</li></ul></div>";
		echo $return;
		if ( $cats_results !== false && $events_results !== false ) {
			update_option( 'ko_calendar_imported','true' );
		}
	} 
}

function edit_my_calendar_config() {
	global $wpdb,$default_user_settings;
	// We can't use this page unless My Calendar is installed/upgraded
	check_my_calendar();
	if (!empty($_POST)) {
		$nonce=$_REQUEST['_wpnonce'];
		if (! wp_verify_nonce($nonce,'my-calendar-nonce') ) die("Security check failed");  
	}
   if (isset($_POST['permissions'])) {
		// management
		$new_perms = $_POST['permissions'];
		$mc_event_approve = ($_POST['mc_event_approve']=='on')?'true':'false';
		$mc_event_approve_perms = $_POST['mc_event_approve_perms'];
		$mc_event_edit_perms = $_POST['mc_event_edit_perms'];
		update_option('mc_event_approve_perms',$mc_event_approve_perms);
		update_option('mc_event_approve',$mc_event_approve);
		update_option('can_manage_events',$new_perms);	  	
		update_option('mc_event_edit_perms',$mc_event_edit_perms);
		echo "<div class='updated'><p><strong>".__('Permissions Settings saved','my-calendar').".</strong></p></div>";
	}
 // output
	if (isset($_POST['my_calendar_show_months']) ) {
		$mc_title_template = $_POST['mc_title_template'];
		$templates = get_option('my_calendar_templates');
		$templates['title'] = $mc_title_template;
		update_option( 'mc_uri',$_POST['mc_uri'] );
		update_option('mc_skip_holidays_category',(int) $_POST['mc_skip_holidays_category']);
		update_option('mc_skip_holidays',($_POST['mc_skip_holidays']=='on')?'true':'false');
		update_option('my_calendar_templates',$templates);
		update_option('display_author',($_POST['display_author']=='on')?'true':'false');
		update_option('display_jump',($_POST['display_jump']=='on')?'true':'false');
		update_option('my_calendar_show_months',(int) $_POST['my_calendar_show_months']);
		update_option('my_calendar_date_format',$_POST['my_calendar_date_format']);
		update_option('my_calendar_show_map',($_POST['my_calendar_show_map']=='on')?'true':'false');
		update_option('my_calendar_show_address',($_POST['my_calendar_show_address']=='on')?'true':'false'); 
		update_option('my_calendar_show_heading',($_POST['my_calendar_show_heading']=='on')?'true':'false');	
		update_option('my_calendar_hide_icons',($_POST['my_calendar_hide_icons']=='on')?'true':'false');
		update_option('mc_event_link_expires',($_POST['mc_event_link_expires']=='on')?'true':'false');
		update_option('mc_apply_color',$_POST['mc_apply_color']);
		update_option('mc_event_registration',($_POST['mc_event_registration']=='on')?'true':'false');
		update_option('mc_short',($_POST['mc_short']=='on')?'true':'false');
		update_option('mc_desc',($_POST['mc_desc']=='on')?'true':'false');
		update_option('mc_details',($_POST['mc_details']=='on')?'true':'false');
		update_option('mc_show_weekends',($_POST['mc_show_weekends']=='on')?'true':'false');
		update_option('mc_no_fifth_week',$_POST['mc_no_fifth_week']);
		update_option('mc_show_rss',($_POST['mc_show_rss']=='on')?'true':'false');
		update_option('mc_show_ical',($_POST['mc_show_ical']=='on')?'true':'false');
		update_option('mc_default_sort',$_POST['mc_default_sort']);
		// styles (output)
		echo "<div class=\"updated\"><p><strong>".__('Output Settings saved','my-calendar').".</strong></p></div>";
	}
	// input
	if (isset($_POST['mc_input'])) {
		$mc_input_options_administrators = ($_POST['mc_input_options_administrators']=='on')?'true':'false'; 
		$mc_input_options = array(
			'event_short'=>$_POST['mci_event_short'],
			'event_desc'=>$_POST['mci_event_desc'],
			'event_category'=>$_POST['mci_event_category'],
			'event_link'=>$_POST['mci_event_link'],
			'event_recurs'=>$_POST['mci_event_recurs'],
			'event_open'=>$_POST['mci_event_open'],
			'event_location'=>$_POST['mci_event_location'],
			'event_location_dropdown'=>$_POST['mci_event_location_dropdown'],
			'event_use_editor'=>$_POST['mci_event_use_editor']
			);
		update_option('mc_input_options',$mc_input_options);
		update_option('mc_input_options_administrators',$mc_input_options_administrators);	
		echo "<div class=\"updated\"><p><strong>".__('Input Settings saved','my-calendar').".</strong></p></div>";
	}	  
	// custom text
	if (isset( $_POST['mc_previous_events'] ) ) {
		$my_calendar_notime_text = $_POST['my_calendar_notime_text'];
		$mc_previous_events = $_POST['mc_previous_events'];
		$mc_next_events = $_POST['mc_next_events'];
		$mc_event_open = $_POST['mc_event_open'];
		$mc_event_closed = $_POST['mc_event_closed'];
		$my_calendar_caption = $_POST['my_calendar_caption'];
		update_option('my_calendar_notime_text',$my_calendar_notime_text);
		update_option('mc_next_events',$mc_next_events);
		update_option('mc_previous_events',$mc_previous_events);	
		update_option('my_calendar_caption',$my_calendar_caption);
		update_option('mc_event_open',$mc_event_open);
		update_option('mc_event_closed',$mc_event_closed);
		echo "<div class=\"updated\"><p><strong>".__('Custom text settings saved','my-calendar').".</strong></p></div>";	 
	}
	// Mail function by Roland
	if (isset($_POST['mc_email']) ) {
		$mc_event_mail = ($_POST['mc_event_mail']=='on')?'true':'false';
		$mc_event_mail_to = $_POST['mc_event_mail_to'];
		$mc_event_mail_subject = $_POST['mc_event_mail_subject'];
		$mc_event_mail_message = $_POST['mc_event_mail_message'];
		update_option('mc_event_mail_to',$mc_event_mail_to);
		update_option('mc_event_mail_subject',$mc_event_mail_subject);
		update_option('mc_event_mail_message',$mc_event_mail_message);
		update_option('mc_event_mail',$mc_event_mail);
		echo "<div class=\"updated\"><p><strong>".__('Email notice settings saved','my-calendar').".</strong></p></div>";
	}
	// Custom User Settings
	if (isset($_POST['mc_user'])) {
		$mc_user_settings_enabled = $_POST['mc_user_settings_enabled'];
		$mc_location_type = $_POST['mc_location_type'];
		$mc_user_settings = $_POST['mc_user_settings'];
		$mc_user_settings['my_calendar_tz_default']['values'] = csv_to_array($mc_user_settings['my_calendar_tz_default']['values']);
		$mc_user_settings['my_calendar_location_default']['values'] = csv_to_array($mc_user_settings['my_calendar_location_default']['values']);
		update_option('mc_location_type',$mc_location_type);
		update_option('mc_user_settings_enabled',$mc_user_settings_enabled);
		update_option('mc_user_settings',$mc_user_settings);  
		echo "<div class=\"updated\"><p><strong>".__('User custom settings saved','my-calendar').".</strong></p></div>";
	}
	// Pull known values out of the options table
	$allowed_group = get_option('can_manage_events');
	$my_calendar_show_months = get_option('my_calendar_show_months');
	$my_calendar_show_map = get_option('my_calendar_show_map');
	$my_calendar_show_address = get_option('my_calendar_show_address');
	$disp_author = get_option('display_author');
	$mc_event_link_expires = get_option('mc_event_link_expires');
	$mc_event_mail = get_option('mc_event_mail');
	$mc_event_mail_to = get_option('mc_event_mail_to');
	$mc_event_mail_subject = get_option('mc_event_mail_subject');
	$mc_event_mail_message = get_option('mc_event_mail_message');
	$mc_event_approve = get_option('mc_event_approve');
	$mc_event_approve_perms = get_option('mc_event_approve_perms');
	$disp_jump = get_option('display_jump');
	$mc_no_fifth_week = get_option('mc_no_fifth_week');
	$templates = get_option('my_calendar_templates');
	$mc_title_template = $templates['title'];
	$mc_uri = get_option('mc_uri');
?>
    <div class="wrap">
<?php 
my_calendar_check_db();
check_akismet();
?>
    <div id="icon-options-general" class="icon32"><br /></div>
	<h2><?php _e('My Calendar Options','my-calendar'); ?></h2>
    <?php jd_show_support_box(); ?>
<div id="poststuff" class="jd-my-calendar">
<div class="postbox">
	<h3><?php _e('Calendar Management Settings','my-calendar'); ?></h3>
	<div class="inside">	
    <form id="my-calendar-manage" method="post" action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-config">
	<div><input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" /></div>    
	<fieldset>
    <legend><?php _e('Calendar Options: Management','my-calendar'); ?></legend>
    <ul>
    <li><label for="permissions"><?php _e('Lowest user group that may create events','my-calendar'); ?></label> <select id="permissions" name="permissions">
		<option value="read"<?php echo jd_option_selected( get_option('can_manage_events'),'read','option'); ?>><?php _e('Subscriber','my-calendar')?></option>
		<option value="edit_posts"<?php echo jd_option_selected(get_option('can_manage_events'),'edit_posts','option'); ?>><?php _e('Contributor','my-calendar')?></option>
		<option value="publish_posts"<?php echo jd_option_selected(get_option('can_manage_events'),'publish_posts','option'); ?>><?php _e('Author','my-calendar')?></option>
		<option value="moderate_comments"<?php echo jd_option_selected(get_option('can_manage_events'),'moderate_comments','option'); ?>><?php _e('Editor','my-calendar')?></option>
		<option value="manage_options"<?php echo jd_option_selected(get_option('can_manage_events'),'manage_options','option'); ?>><?php _e('Administrator','my-calendar')?></option>
	</select>
	</li>
    <li>
    <label for="mc_event_approve_perms"><?php _e('Lowest user group that may approve events','my-calendar'); ?></label> <select id="mc_event_approve_perms" name="mc_event_approve_perms">
		<option value="read"<?php echo jd_option_selected(get_option('mc_event_approve_perms'),'read','option'); ?>><?php _e('Subscriber','my-calendar')?></option>
		<option value="edit_posts"<?php echo jd_option_selected(get_option('mc_event_approve_perms'),'edit_posts','option'); ?>><?php _e('Contributor','my-calendar')?></option>
		<option value="publish_posts"<?php echo jd_option_selected(get_option('mc_event_approve_perms'),'publish_posts','option'); ?>><?php _e('Author','my-calendar')?></option>
		<option value="moderate_comments"<?php echo jd_option_selected(get_option('mc_event_approve_perms'),'moderate_comments','option'); ?>><?php _e('Editor','my-calendar')?></option>
		<option value="manage_options"<?php echo jd_option_selected(get_option('mc_event_approve_perms'),'manage_options','option'); ?>><?php _e('Administrator','my-calendar')?></option>
	</select> <input type="checkbox" id="mc_event_approve" name="mc_event_approve" <?php jd_cal_checkCheckbox('mc_event_approve','true'); ?> /> <label for="mc_event_approve"><?php _e('Enable approval options.','my-calendar'); ?></label>
	</li>
    <li>
    <label for="mc_event_edit_perms"><?php _e('Lowest user group that may edit or delete all events','my-calendar'); ?></label> <select id="mc_event_edit_perms" name="mc_event_edit_perms">
		<option value="edit_posts"<?php echo jd_option_selected(get_option('mc_event_edit_perms'),'edit_posts','option'); ?>><?php _e('Contributor','my-calendar')?></option>
		<option value="publish_posts"<?php echo jd_option_selected(get_option('mc_event_edit_perms'),'publish_posts','option'); ?>><?php _e('Author','my-calendar')?></option>
		<option value="moderate_comments"<?php echo jd_option_selected(get_option('mc_event_edit_perms'),'moderate_comments','option'); ?>><?php _e('Editor','my-calendar')?></option>
		<option value="manage_options"<?php echo jd_option_selected(get_option('mc_event_edit_perms'),'manage_options','option'); ?>><?php _e('Administrator','my-calendar')?></option>
	</select><br />
	<em><?php _e('By default, only administrators may edit or delete any event. Other users may only edit or delete events which they authored.','my-calendar'); ?></em>
	</li>
	</ul>
	</fieldset>
		<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save Approval Settings','my-calendar'); ?> &raquo;" />
		</p>
	</form>
	</div>
</div>
<div class="postbox">
	<h3><?php _e('Calendar Text Settings','my-calendar'); ?></h3>
	<div class="inside">
	    <form id="my-calendar-text" method="post" action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-config">
	<div><input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" /></div>		
<fieldset>
	<legend><?php _e('Calendar Options: Customizable Text Fields','my-calendar'); ?></legend>
	<ul>
	<li>
	<label for="my_calendar_notime_text"><?php _e('Label for events without a set time','my-calendar'); ?></label> <input type="text" id="my_calendar_notime_text" name="my_calendar_notime_text" value="<?php if ( get_option('my_calendar_notime_text') == "") { _e('N/A','my-calendar'); } else { echo stripslashes( get_option('my_calendar_notime_text') ); } ?>" />
	</li>
	<li>
	<label for="mc_previous_events"><?php _e('Previous events link','my-calendar'); ?></label> <input type="text" id="mc_previous_events" name="mc_previous_events" value="<?php if ( get_option('mc_previous_events') == "") { _e('Previous Events','my-calendar'); } else { echo stripslashes( get_option('mc_previous_events') ); } ?>" />
	</li>
	<li>
	<label for="mc_next_events"><?php _e('Next events link','my-calendar'); ?></label> <input type="text" id="mc_next_events" name="mc_next_events" value="<?php if ( get_option('mc_next_events') == "") { _e('Next Events','my-calendar'); } else { echo stripslashes( get_option('mc_next_events') ); } ?>" />
	</li>
	<li>
	<label for="mc_event_open"><?php _e('If events are open','my-calendar'); ?></label> <input type="text" id="mc_event_open" name="mc_event_open" value="<?php if ( get_option('mc_event_open') == "") { _e('Registration is open','my-calendar'); } else { echo stripslashes( get_option('mc_event_open') ); } ?>" />
	</li>
	<li>
	<label for="mc_event_closed"><?php _e('If events are closed','my-calendar'); ?></label> <input type="text" id="mc_event_closed" name="mc_event_closed" value="<?php if ( get_option('mc_event_closed') == "") { _e('Registration is closed','my-calendar'); } else { echo stripslashes( get_option('mc_event_closed') ); } ?>" />
	</li>	
	<li>
	<label for="my_calendar_caption"><?php _e('Additional caption:','my-calendar'); ?></label> <input type="text" id="my_calendar_caption" name="my_calendar_caption" value="<?php echo stripslashes( get_option('my_calendar_caption') ); ?>" /><br /><small><?php _e('The calendar caption is the text containing the displayed month and year in either list or calendar format. This text will be displayed following that existing text.','my-calendar'); ?></small>
	</li>
	</ul>
	</fieldset>	
		<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save Custom Text Settings','my-calendar'); ?> &raquo;" />
	</p>
	</form>
</div>
</div>
<div class="postbox">
	<h3><?php _e('Calendar Output Settings','my-calendar'); ?></h3>
	<div class="inside">
 <form id="my-calendar-output" method="post" action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-config">
	<div><input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" /></div>
	<fieldset>
	<legend><?php _e('Calendar Options: Customize the Output of your Calendar','my-calendar'); ?></legend>
	<fieldset>
	<legend><?php _e('General Calendar Options','my-calendar'); ?></legend>
	<ul>
	<li>
	<label for="mc_uri"><?php _e('<abbr title="Uniform resource locator">URL</abbr> for your public calendar (required to use the {details} template tag)','my-calendar'); ?></label> 
	<input type="text" name="mc_uri" id="mc_uri" size="40" value="<?php echo $mc_uri; ?>" />
	</li>
	<li>
	<label for="my_calendar_date_format"><?php _e('Date format in list mode','my-calendar'); ?></label> <input type="text" id="my_calendar_date_format" name="my_calendar_date_format" value="<?php if ( get_option('my_calendar_date_format')  == "") { echo get_option('date_format'); } else { echo get_option( 'my_calendar_date_format'); } ?>" /> <?php _e('Current:','my-calendar'); ?> <?php if ( get_option('my_calendar_date_format') == '') { echo date_i18n(get_option('date_format')); } else { echo date_i18n(get_option('my_calendar_date_format')); } ?><br />
	<small><?php _e('Date format uses the same syntax as the <a href="http://php.net/date">PHP <code>date()</code> function</a>. Save options to update sample output.','my-calendar'); ?></small>
	</li>	
	<li>
	<input type="checkbox" id="mc_show_rss" name="mc_show_rss" <?php jd_cal_checkCheckbox('mc_show_rss','true'); ?> /> <label for="mc_show_rss"><?php _e('Show link to My Calendar RSS feed.','my-calendar'); ?></label>
	</li>
	<li>
	<input type="checkbox" id="mc_show_ical" name="mc_show_ical" <?php jd_cal_checkCheckbox('mc_show_ical','true'); ?> /> <label for="mc_show_ical"><?php _e('Show link to iCal format download.','my-calendar'); ?></label>
	</li>		
	<li>
	<input type="checkbox" id="my_calendar_show_heading" name="my_calendar_show_heading" <?php jd_cal_checkCheckbox('my_calendar_show_heading','true'); ?> /> <label for="my_calendar_show_heading"><?php _e('Show Heading for Calendar','my-calendar'); ?></label><br /><?php _e('<strong style="color: red;">Note:</strong>  This feature will be removed from settings in the next major release. This calendar heading will be permanently removed.','my-calendar'); ?>
	</li>	
	<li>
	<input type="checkbox" id="display_jump" name="display_jump" <?php jd_cal_checkCheckbox('display_jump','true'); ?> /> <label for="display_jump"><?php _e('Display a jumpbox for changing month and year quickly?','my-calendar'); ?></label>
	</li>		
	</ul>	
	<?php // End General Options // ?>
	</fieldset>
	
	<fieldset>
	<legend><?php _e('Grid Layout Options','my-calendar'); ?></legend>
	<ul>
	<li>
	<input type="checkbox" id="mc_show_weekends" name="mc_show_weekends" <?php jd_cal_checkCheckbox('mc_show_weekends','true'); ?> /> <label for="mc_show_weekends"><?php _e('Show Weekends on Calendar','my-calendar'); ?></label>
	</li>		
	</ul>	
	<?php // End Grid Options // ?>
	</fieldset>	
	
	<fieldset>
	<legend><?php _e('List Layout Options','my-calendar'); ?></legend>
	<ul>
	<li>
	<label for="my_calendar_show_months"><?php _e('In list mode, show how many months of events at a time:','my-calendar'); ?></label> <input type="text" size="3" id="my_calendar_show_months" name="my_calendar_show_months" value="<?php echo $my_calendar_show_months; ?>" />
	</li>	
	</ul>	
	<?php // End List Options // ?>
	</fieldset>	

	<fieldset>
	<legend><?php _e('Event Details Options','my-calendar'); ?></legend>
	<ul>
	<li>
	<label for="mc_title_template"><?php _e('Event title template','my-calendar'); ?></label> 
	<input type="text" name="mc_title_template" id="mc_title_template" size="30" value="<?php echo $mc_title_template; ?>" /> <small><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-help#templates"><?php _e("Template Help",'my-calendar'); ?></a> <?php _e('All template shortcodes are available.','my-calendar'); ?></small>
	</li>	
	<li>
	<input type="checkbox" id="display_author" name="display_author" <?php jd_cal_checkCheckbox('display_author','true'); ?> /> <label for="display_jump"><?php _e('Do you want to display the author name on events?','my-calendar'); ?></label>
	</li>
		<li>
	<input type="checkbox" id="my_calendar_hide_icons" name="my_calendar_hide_icons" <?php jd_cal_checkCheckbox('my_calendar_hide_icons','true'); ?> /> <label for="my_calendar_hide_icons"><?php _e('Hide category icons in output','my-calendar'); ?></label>
	</li>
	<li>
	<input type="checkbox" id="my_calendar_show_map" name="my_calendar_show_map" <?php jd_cal_checkCheckbox('my_calendar_show_map','true'); ?> /> <label for="my_calendar_show_map"><?php _e('Show Link to Google Map (when sufficient address information is available.)','my-calendar'); ?></label>
	</li>
	<li>
	<input type="checkbox" id="my_calendar_show_address" name="my_calendar_show_address" <?php jd_cal_checkCheckbox('my_calendar_show_address','true'); ?> /> <label for="my_calendar_show_address"><?php _e('Show Event Address in Details','my-calendar'); ?></label>
	</li>
	<li>
	<input type="checkbox" id="mc_short" name="mc_short" <?php jd_cal_checkCheckbox('mc_short','true'); ?> /> <label for="mc_short"><?php _e('Show short description field on calendar.','my-calendar'); ?></label>
	</li>
	<li>
	<input type="checkbox" id="mc_desc" name="mc_desc" <?php jd_cal_checkCheckbox('mc_desc','true'); ?> /> <label for="mc_desc"><?php _e('Show full description field on calendar.','my-calendar'); ?></label>
	</li>
	<li>
	<input type="checkbox" id="mc_details" name="mc_details" <?php jd_cal_checkCheckbox('mc_details','true'); ?> /> <label for="mc_details"><?php _e('Show link to details on calendar. (requires a calendar URL, above)','my-calendar'); ?></label>
	</li>		
	<li>
	<input type="checkbox" id="mc_event_link_expires" name="mc_event_link_expires" <?php jd_cal_checkCheckbox('mc_event_link_expires','true'); ?> /> <label for="mc_event_link_expires"><?php _e('Links associated with events will automatically expire after the event has passed.','my-calendar'); ?></label>
	</li>
	<li>
	<input type="checkbox" id="mc_event_registration" name="mc_event_registration" <?php jd_cal_checkCheckbox('mc_event_registration','true'); ?> /> <label for="mc_event_registration"><?php _e('Show current availability status of events.','my-calendar'); ?></label>
	</li>
	<li>
    <input type="radio" id="mc_apply_color_default" name="mc_apply_color" value="default" <?php jd_cal_checkCheckbox('mc_apply_color','default'); ?> /> <label for="mc_apply_color_default"><?php _e('Default usage of category colors.','my-calendar'); ?></label><br />
    <input type="radio" id="mc_apply_color_to_titles" name="mc_apply_color" value="font"  <?php jd_cal_checkCheckbox('mc_apply_color','font'); ?> /> <label for="mc_apply_color_to_titles"><?php _e('Apply category colors to event titles as a font color.','my-calendar'); ?></label><br />
	<input type="radio" id="mc_apply_bgcolor_to_titles" name="mc_apply_color" value="background"  <?php jd_cal_checkCheckbox('mc_apply_color','background'); ?> /> <label for="mc_apply_bgcolor_to_titles"><?php _e('Apply category colors to event titles as a background color.','my-calendar'); ?></label>	
	</li>	
	</ul>	
	<?php // End Event Options // ?>
	</fieldset>	

	<fieldset>
	<legend><?php _e('Event Scheduling Options','my-calendar'); ?></legend>
	<ul>
	<li>
	<input type="checkbox" id="mc_no_fifth_week" name="mc_no_fifth_week" value="true" <?php jd_cal_checkCheckbox('mc_no_fifth_week','true'); ?> /> <label for="mc_no_fifth_week"><?php _e('If a recurring event is scheduled for a date which doesn\'t exist (such as the 5th Wednesday in February), move it back one week.','my-calendar'); ?></label><br />
	<?php _e('If this option is unchecked, recurring events which fall on dates which don\'t exist will simply not be shown on the calendar.','my-calendar'); ?> <?php _e('<strong style="color: red;">Note:</strong> This feature will be made event-specific and will be removed from settings in the next major release.','my-calendar'); ?>
	</li>
	<li>
	<label for="mc_skip_holidays_category"><?php _e('Holiday Category','my-calendar'); ?></label>
	<select id="mc_skip_holidays_category" name="mc_skip_holidays_category">
			<?php
			// Grab all the categories and list them
			$sql = "SELECT * FROM " . MY_CALENDAR_CATEGORIES_TABLE;
			$cats = $wpdb->get_results($sql);
				foreach($cats as $cat) {
					echo '<option value="'.$cat->category_id.'"';
						if ( get_option('mc_skip_holidays_category') == $cat->category_id ){
						 echo ' selected="selected"';
						}
					echo '>'.$cat->category_name."</option>\n";
				}
			?>
			</select>
    </li>
	<li>
	<input type="checkbox" id="mc_skip_holidays" name="mc_skip_holidays" <?php jd_cal_checkCheckbox('mc_skip_holidays','true'); ?> /> <label for="mc_skip_holidays"><?php _e('If an event coincides with an event in the designated "Holiday" category, do not show the event.','my-calendar'); ?></label><br /><?php _e('<strong style="color: red;">Note:</strong>  This feature will be made event-specific and will be removed from settings in the next major release.','my-calendar'); ?>
	</li>
	<li>	
	<label for="mc_default_sort"><?php _e('Default Sort order for Admin Events List','my-calendar'); ?></label>
	<select id="mc_default_sort" name="mc_default_sort">
		<option value='1' <?php jd_cal_checkSelect( 'mc_default_sort','1'); ?>><?php _e('Event ID','my-calendar'); ?></option>
		<option value='2' <?php jd_cal_checkSelect( 'mc_default_sort','2'); ?>><?php _e('Title','my-calendar'); ?></option>
		<option value='3' <?php jd_cal_checkSelect( 'mc_default_sort','3'); ?>><?php _e('Description','my-calendar'); ?></option>
		<option value='4' <?php jd_cal_checkSelect( 'mc_default_sort','4'); ?>><?php _e('Start Date','my-calendar'); ?></option>
		<option value='5' <?php jd_cal_checkSelect( 'mc_default_sort','5'); ?>><?php _e('Author','my-calendar'); ?></option>
		<option value='6' <?php jd_cal_checkSelect( 'mc_default_sort','6'); ?>><?php _e('Category','my-calendar'); ?></option>
		<option value='7' <?php jd_cal_checkSelect( 'mc_default_sort','7'); ?>><?php _e('Location Name','my-calendar'); ?></option>
	</select>	
	</li>	
	</ul>	
	<?php // End Scheduling Options // ?>
	</fieldset>
	</fieldset>
		<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save Output Settings','my-calendar'); ?> &raquo;" />
	</p>
</form>
</div>
</div>
<div class="postbox">
	<h3><?php _e('Calendar Input Settings','my-calendar'); ?></h3>
	<div class="inside">
<form id="my-calendar-input" method="post" action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-config">
	<div><input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" /></div>
	<fieldset>
	<legend><?php _e('Select which input fields will be available when adding or editing events.','my-calendar'); ?></legend>
	<div><input type='hidden' name='mc_input' value='true' /></div>
	<ul>
	<?php 
		$input_options = get_option('mc_input_options');
		$input_labels = array('event_location_dropdown'=>__('Show Event Location Dropdown Menu','my-calendar'),'event_short'=>__('Show Event Short Description field','my-calendar'),'event_desc'=>__('Show Event Description Field','my-calendar'),'event_category'=>__('Show Event Category field','my-calendar'),'event_link'=>__('Show Event Link field','my-calendar'),'event_recurs'=>__('Show Event Recurrence Options','my-calendar'),'event_open'=>__('Show event registration options','my-calendar'),'event_location'=>__('Show event location fields','my-calendar'),'event_use_editor'=>__('Use HTML Editor in Event Description Field') );
		$output = '';
		// if input options isn't an array, we'll assume that this plugin wasn't upgraded properly, and reset them to the default.
		if ( !is_array($input_options) ) {
			update_option( 'mc_input_options',array('event_short'=>'on','event_desc'=>'on','event_category'=>'on','event_link'=>'on','event_recurs'=>'on','event_open'=>'on','event_location'=>'on','event_location_dropdown'=>'on','event_use_editor'=>'on' ) );	
		}
	foreach ($input_options as $key=>$value) {
			$checked = ($value == 'on')?"checked='checked'":'';
			$output .= "<li><input type=\"checkbox\" id=\"mci_$key\" name=\"mci_$key\" $checked /> <label for=\"mci_$key\">$input_labels[$key]</label></li>";
		}
		echo $output;
	?>
	<li>
	<input type="checkbox" id="mc_input_options_administrators" name="mc_input_options_administrators" <?php jd_cal_checkCheckbox('mc_input_options_administrators','true'); ?> /> <label for="mc_input_options_administrators"><strong><?php _e('Administrators see all input options','my-calendar'); ?></strong></label>
	</li>
	</ul>
	</fieldset>
		<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save Input Settings','my-calendar'); ?> &raquo;" />
	</p>
</form>
</div>
</div>
<div class="postbox">
	<h3><?php _e('Calendar Email Settings','my-calendar'); ?></h3>
	<div class="inside">
<form id="my-calendar-email" method="post" action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-config">
	<div><input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" /></div>
	<fieldset>
	<legend><?php _e('Calendar Options: Email Notifications','my-calendar'); ?></legend>
<div><input type='hidden' name='mc_email' value='true' /></div>
	<ul>
	<li>
	<input type="checkbox" id="mc_event_mail" name="mc_event_mail" <?php jd_cal_checkCheckbox('mc_event_mail','true'); ?> /> <label for="mc_event_mail"><strong><?php _e('Send Email Notifications when new events are scheduled or reserved.','my-calendar'); ?></strong></label>
	</li>
	<li>
	<label for="mc_event_mail_to"><?php _e('Notification messages are sent to: ','my-calendar'); ?></label> <input type="text" id="mc_event_mail_to" name="mc_event_mail_to" size="40"  value="<?php if ( get_option('mc_event_mail_to') == "") { bloginfo('admin_email'); } else { echo stripslashes( get_option('mc_event_mail_to') ); } ?>" />
	</li>	
	<li>
	<label for="mc_event_mail_subject"><?php _e('Email subject','my-calendar'); ?></label> <input type="text" id="mc_event_mail_subject" name="mc_event_mail_subject" size="60" value="<?php if ( get_option('mc_event_mail_subject') == "") { bloginfo('name'); echo ': '; _e('New event Added','my-calendar'); } else { echo stripslashes( get_option('mc_event_mail_subject') ); } ?>" />
	</li>
	<li>
	<label for="mc_event_mail_message"><?php _e('Message Body','my-calendar'); ?></label><br /> <textarea rows="6" cols="80"  id="mc_event_mail_message" name="mc_event_mail_message"><?php if ( get_option('mc_event_mail_message') == "") { _e('New Event:','my-calendar'); echo "\n{title}: {date}, {time} - {event_status}"; } else { echo stripcslashes( get_option('mc_event_mail_message') ); } ?></textarea><br />
	<a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-help#templates"><?php _e("Shortcode Help",'my-calendar'); ?></a> <?php _e('All template shortcodes are available.','my-calendar'); ?>
	</li>
	</ul>
	</fieldset>
		<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save Email Settings','my-calendar'); ?> &raquo;" />
		</p>
</form>
</div>
</div>
<div class="postbox">
	<h3><?php _e('Calendar User Settings','my-calendar'); ?></h3>
	<div class="inside">
<form id="my-calendar-user" method="post" action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-config">
<div><input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" /></div>
<div><input type='hidden' name='mc_user' value='true' /></div>

	<fieldset>
	<legend><?php _e('Settings which can be configured in registered user\'s accounts','my-calendar'); ?></legend>
	<p>
	<input type="checkbox" id="mc_user_settings_enabled" name="mc_user_settings_enabled" value="true" <?php jd_cal_checkCheckbox('mc_user_settings_enabled','true'); ?> /> <label for="mc_user_settings_enabled"><strong><?php _e('Allow registered users to provide timezone or location presets in their user profiles.','my-calendar'); ?></strong></label>
	</p>

<?php

$mc_user_settings = get_option('mc_user_settings'); 
if (!is_array($mc_user_settings)) {
	update_option( 'mc_user_settings', $default_user_settings );
	$mc_user_settings = get_option('mc_user_settings');
}
?>
<fieldset>
<legend><?php _e('Timezone Settings','my-calendar'); ?></legend>
<p><?php _e('These settings provide registered users with the ability to select a time zone in their user profile. When they view your calendar, the times for events will display the time the event happens in their time zone as well as the entered value.','my-calendar'); ?></p>
	<p>
	<input type="checkbox" id="tz_enabled" name="mc_user_settings[my_calendar_tz_default][enabled]" <?php jd_cal_checkCheckbox('mc_user_settings','on','my_calendar_tz_default'); ?> /> <label for="tz_enabled"><?php _e('Enable Timezone','my-calendar'); ?></label>
	</p>
	<p>
	<label for="tz_label"><?php _e('Select Timezone Label','my-calendar'); ?></label> <input type="text" name="mc_user_settings[my_calendar_tz_default][label]" id="tz_label" value="<?php echo $mc_user_settings['my_calendar_tz_default']['label']; ?>" size="40" />
	</p>
	<p>
	<label for="tz_values"><?php _e('Timezone Options','my-calendar'); ?> (<?php _e('Value, Label; one per line','my-calendar'); ?>)</label><br />
 	<?php 
	$timezones = '';
foreach ( $mc_user_settings['my_calendar_tz_default']['values'] as $key=>$value ) {
$timezones .= "$key,$value\n";
}
	?>	
	<textarea name="mc_user_settings[my_calendar_tz_default][values]" id="tz_values" cols="60" rows="8"><?php echo trim($timezones); ?></textarea>
	</p>
</fieldset>

<fieldset>
<legend><?php _e('Location Settings','my-calendar'); ?></legend>
<p><?php _e('These settings provide registered users with the ability to select a location in their user profile. When they view your calendar, their initial view will be limited to locations which include that location parameter.','my-calendar'); ?></p>
	<p>
	<input type="checkbox" id="loc_enabled" name="mc_user_settings[my_calendar_location_default][enabled]" <?php jd_cal_checkCheckbox('mc_user_settings','on','my_calendar_location_default'); ?> /> <label for="loc_enabled"><?php _e('Enable Location','my-calendar'); ?></label>
	</p>
	<p>
	<label for="loc_label"><?php _e('Select Location Label','my-calendar'); ?></label> <input type="text" name="mc_user_settings[my_calendar_location_default][label]" id="loc_label" value="<?php echo $mc_user_settings['my_calendar_location_default']['label']; ?>" size="40" />
	</p>
	<p>
	<label for="loc_values"><?php _e('Location Options','my-calendar'); ?> (<?php _e('Value, Label; one per line','my-calendar'); ?>)</label><br />
	<?php 
	$locations = '';
foreach ( $mc_user_settings['my_calendar_location_default']['values'] as $key=>$value ) {
$locations .= "$key,$value\n";
}
?>
	<textarea name="mc_user_settings[my_calendar_location_default][values]" id="loc_values" cols="60" rows="8"><?php echo trim($locations); ?></textarea>
	</p>
	<p>
	<label for="loc_type"><?php _e('Location Type','my-calendar'); ?></label><br />
	<select id="loc_type" name="mc_location_type">
	<option value="event_label" <?php jd_cal_checkSelect( 'mc_location_type','event_label' ); ?>><?php _e('Location Name','my-calendar'); ?></option>
	<option value="event_city" <?php jd_cal_checkSelect( 'mc_location_type','event_city' ); ?>><?php _e('City','my-calendar'); ?></option>
	<option value="event_state" <?php jd_cal_checkSelect( 'mc_location_type','event_state'); ?>><?php _e('State/Province','my-calendar'); ?></option>
	<option value="event_country" <?php jd_cal_checkSelect( 'mc_location_type','event_country'); ?>><?php _e('Country','my-calendar'); ?></option>
	<option value="event_postcode" <?php jd_cal_checkSelect( 'mc_location_type','event_postcode'); ?>><?php _e('Postal Code','my-calendar'); ?></option>
	<option value="event_region" <?php jd_cal_checkSelect( 'mc_location_type','event_region'); ?>><?php _e('Region','my-calendar'); ?></option>	
	</select>
	</p>
</fieldset>
	</fieldset>
	<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save User Settings','my-calendar'); ?> &raquo;" />
	</p>
  </form>
  <?php
//update_option( 'ko_calendar_imported','false' );
if (isset($_POST['import']) && $_POST['import'] == 'true') {
	$nonce=$_REQUEST['_wpnonce'];
    if (! wp_verify_nonce($nonce,'my-calendar-nonce') ) die("Security check failed");
	my_calendar_import();
}
if ( get_option( 'ko_calendar_imported' ) != 'true' ) {
  	if (function_exists('check_calendar')) {
	echo "<div class='import'>";
	echo "<p>";
	_e('My Calendar has identified that you have the Calendar plugin by Kieran O\'Shea installed. You can import those events and categories into the My Calendar database. Would you like to import these events?','my-calendar');
	echo "</p>";
?>
		<form method="post" action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-config">
		<div><input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" /></div>		
		<div>
		<input type="hidden" name="import" value="true" />
		<input type="submit" value="<?php _e('Import from Calendar','my-calendar'); ?>" name="import-calendar" class="button-primary" />
		</div>
		</form>
<?php
	echo "</div>";
	}
}
?>
	</div>
</div>
</div>
</div>
<?php
}
?>