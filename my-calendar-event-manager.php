<?php
$nonce = "";
global $number_of_occurrences; //used for events with specified occurrences 
if (!empty($_SERVER['SCRIPT_FILENAME']) && 'my-calendar-event-manager.php' == basename($_SERVER['SCRIPT_FILENAME'])) {
	die ('Please do not load this page directly. Thanks!');
}
global $new_event_id;
$new_event_id = 1;

/*																DRAW PAGE BEGIN													 */
function edit_my_calendar() {
    global $current_user, $wpdb, $users_entries;
	
	if ( get_option('ko_calendar_imported') != 'true' ) {  
		if (function_exists('check_calendar')) {
		echo "<div id='message' class='updated'>";
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
		echo "<p>";
		_e('Although it is possible that this import could fail to import your events correctly, it should not have any impact on your existing Calendar database. If you encounter any problems, <a href="http://www.joedolson.com/contact.php">please contact me</a>!','my-calendar');
		echo "</p>";
		echo "</div>";
		}
	}

// First some quick cleaning up 
$edit = $create = $save = $delete = false;

$action = !empty($_POST['event_action']) ? $_POST['event_action'] : '';
$event_id = !empty($_POST['event_id']) ? $_POST['event_id'] : '';



if ( isset( $_GET['mode'] ) ) {
	if ( $_GET['mode'] == 'edit' ) {
		$action = "edit";
		$event_id = (int) $_GET['event_id'];
	}
	if ( $_GET['mode'] == 'copy' ) {
		$action = "copy";
		$event_id = (int) $_GET['event_id'];	
	}
	if ( $_GET['mode'] == 'occ_del' ) {
		/* GET this is used instead of a POST to delete 1 or more occurrences, because the confirmation button is already inside of a form, so not sure if we can POST from there without submitting form.  */
		$action = "occ_del";	
		$event_id = (int) $_GET['event_id'];
	}
}

// Lets see if this is first run and create us a table if it is!
check_my_calendar();

if ( !empty($_POST['mass_delete']) ) {
	$nonce=$_REQUEST['_wpnonce'];
    if (! wp_verify_nonce($nonce,'my-calendar-nonce') ) die("Security check failed");
	$events = $_POST['mass_delete'];
	$sql = 'DELETE FROM ' . MY_CALENDAR_TABLE . ' WHERE event_id IN (';	
	$i=0;
	foreach ($events as $value) {
		$value = (int) $value;
		$ea = "SELECT event_author FROM " . MY_CALENDAR_TABLE . " WHERE event_id = $value";
		$result = $wpdb->get_results( $ea, ARRAY_A );
		$total = count($events);
		
		if ( mc_can_edit_event( $result[0]['event_author'] ) ) {
			$sql .= mysql_real_escape_string($value).',';
			$i++;
		}
  }
 	$sql = substr( $sql, 0, -1 );
	$sql .= ')';
	$result = $wpdb->query($sql);
  /*DELETE ANY ASSOCIATED OCCURRENCES TO EACH EVENT*/
  foreach($events as $ev) {
    $osql = "DELETE FROM " . MY_CALENDAR_OCCURRENCES_TABLE . " WHERE `event_id` = ".intval($ev);
    $wpdb->query($osql);
    }
	if ( $result !== 0 && $result !== false ) {
		$message = "<div class='updated'><p>".sprintf(__('%1$d events deleted successfully out of %2$d selected','my-calendar'), $i, $total )."</p></div>";
	} else {
		$message = "<div class='error'><p><strong>".__('Error','my-calendar').":</strong>".__('Your events have not been deleted. Please investigate.','my-calendar')."</p></div>";
	}
	echo $message;
}

if ( isset( $_GET['mode'] ) && $_GET['mode'] == 'delete' ) {
	    $sql = "SELECT event_title, event_author FROM " . MY_CALENDAR_TABLE . " WHERE event_id=" . (int) $_GET['event_id'];
	   $result = $wpdb->get_results( $sql, ARRAY_A );
	if ( mc_can_edit_event( $result[0]['event_author'] ) ) {
	?>
		<div class="error">
		<p><strong><?php _e('Delete Event','my-calendar'); ?>:</strong> <?php _e('Are you sure you want to delete this event?','my-calendar'); ?></p>
		<form action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar" method="post">
		<div>
		<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" />		
		<input type="hidden" value="delete" name="event_action" />
		<input type="hidden" value="<?php echo (int) $_GET['event_id']; ?>" name="event_id" />
		<input type="submit" name="submit" class="button-primary" value="<?php _e('Delete','my-calendar'); echo " &quot;".$result[0]['event_title']."&quot;"; ?>" />
		</div>
		</form>
		</div>
	<?php
	} else {
	?>
		<div class="error">
		<p><strong><?php _e('You do not have permission to delete that event.','my-calendar'); ?></strong></p>
		</div>
	<?php
	}
}


// Approve and show an Event ...by Roland
if ( isset( $_GET['mode'] ) && $_GET['mode'] == 'approve' ) {
	if ( current_user_can( get_option( 'mc_event_approve_perms' ) ) ) {
	    $sql = "UPDATE " . MY_CALENDAR_TABLE . " SET event_approved = 1 WHERE event_id=" . (int) $_GET['event_id'];
		$result = $wpdb->get_results( $sql, ARRAY_A );
	} else {
	?>
		<div class="error">
		<p><strong><?php _e('You do not have permission to approve that event.','my-calendar'); ?></strong></p>
		</div>
	<?php
	}
}

// Reject and hide an Event ...by Roland
if ( isset( $_GET['mode'] ) && $_GET['mode'] == 'reject' ) {
	if ( current_user_can( get_option( 'mc_event_approve_perms' ) ) ) {
	    $sql = "UPDATE " . MY_CALENDAR_TABLE . " SET event_approved = 2 WHERE event_id=" . (int) $_GET['event_id'];
		$result = $wpdb->get_results( $sql, ARRAY_A );
	} else {
	?>
		<div class="error">
		<p><strong><?php _e('You do not have permission to reject that event.','my-calendar'); ?></strong></p>
		</div>
	<?php
	}
}

if ($action == "occ_del") {
	$nonce=$_REQUEST['_wpnonce'];
  if (! wp_verify_nonce($nonce,'my-calendar-nonce') )	{	die("Security check failed");		}
	/*delete the requested occurrences */
		$occurrences_list ='(';
		foreach($_GET['occ_del'] as $o) {
			$occurrences_list .= strval($o).",";
		}
		$occurrences_list = substr($occurrences_list,0,-1).");";
		$qu1="DELETE FROM ".MY_CALENDAR_OCCURRENCES_TABLE." WHERE `occ_id` IN $occurrences_list";
		$wpdb->query($qu1);
	/*redisplay the page */
		jd_events_edit_form('edit', $event_id);
		jd_events_display_list();		
	}

if ( isset( $_POST['event_action'] ) ) {
	$nonce=$_REQUEST['_wpnonce'];
  if (! wp_verify_nonce($nonce,'my-calendar-nonce') )	{	die("Security check failed");		}
	$proceed = false;
	global $mc_output;
	$mc_output = mc_check_data($action,$_POST);
	if ($action == 'add' || $action == 'copy' ) {
		$response = my_calendar_save($action,$mc_output, &$event_id);
	} else {
		$response = my_calendar_save($action,$mc_output,$event_id);	
	} 
	
}

?>

<div class="wrap">
<?php 
my_calendar_check_db();
check_akismet();
?>
	<?php
	if ($action=='add' || $action == 'edit' || ($action == 'edit' && $error_with_saving == 1) ) {
		?>
<div id="icon-edit" class="icon32"></div>		
		<h2><?php _e('Edit Event','my-calendar'); ?></h2>
		<?php jd_show_support_box(); ?>
		<?php
	
		if ( empty($event_id) ) {
			echo "<div class=\"error\"><p>".__("You must provide an event id in order to edit it",'my-calendar')."</p></div>";
		} else {
			jd_events_edit_form('edit', $event_id);
		}		
		jd_events_display_list();					
	} else if ( $action == 'copy' || ($action == 'copy' && $error_with_saving == 1)) { ?>
<div id="icon-edit" class="icon32"></div>	
		<h2><?php _e('Copy Event','my-calendar'); ?></h2>
		<?php jd_show_support_box(); ?>
		<?php
		if ( empty($event_id) ) {
			echo "<div class=\"error\"><p>".__("You must provide an event id in order to edit it",'my-calendar')."</p></div>";
		} else {
			jd_events_edit_form('copy', $event_id);
		}
		jd_events_display_list();		
	} else {
	?>	
<div id="icon-edit" class="icon32"></div>	
		<h2><?php _e('Add Event','my-calendar'); ?></h2>
		<?php jd_show_support_box(); ?>
		<?php jd_events_edit_form(); ?>
		<?php jd_events_display_list(); ?>
	<?php } ?>
</div>
<?php
} //END edit_my_calendar()

/* ******************************************************																
 * 								DRAW PAGE END 
 * *******************************************************/

function my_calendar_save( $action,$output,$event_id=false ) {
global $wpdb,$event_author;
	$proceed = $output[0];
$arrived_correctly = "no+$action+$proceed output: ";

	if ( ( $action == 'add' || $action == 'copy' ) && $proceed == true ) {
	$arrived_correctly = "add/copy ".$wpdb->last_query;    
		if ($_POST['event_recur'] == 'G') {
	/* 'G', therefore, save date / time info  the occurrences table */
			$add = $output[2]; // add format here
			$formats = array( '%s','%s','%s','%s','%s','%s','%d','%d','%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%d','%f','%f','%d','%s','%d','%d','%d','%d','%d' );		
			$result = $wpdb->insert( 
					MY_CALENDAR_TABLE, 
					$add, 
					$formats 
					);
    $event_id=$wpdb->insert_id;
	/* begin of put occurrences on new event record */
    update_occurrences($event_id); 
   set_event_begin_end_dates($event_id);   
  /* end of put occurrences on new event record */
		} else /* new event, but not recurrences == 'G'*/
		{
			$add = $output[2]; // add format here
			$formats = array( '%s','%s','%s','%s','%s','%s','%d','%d','%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%d','%f','%f','%d','%s','%d','%d','%d','%d','%d' );		
			$result = $wpdb->insert( 
					MY_CALENDAR_TABLE, 
					$add, 
					$formats 
					);
			$event_id = $wpdb->insert_id;
		}
		if ( !$result ) {
			$message = "<div class='error'><p><strong>". __('Error','my-calendar') .":</strong> ". __('I\'m sorry! I couldn\'t add that event to the database.','my-calendar') . "</p></div>";	      
		} else {
	    // Call mail function
			$sql = "SELECT * FROM ". MY_CALENDAR_TABLE." WHERE event_id = ".$wpdb->insert_id;
			$event = $wpdb->get_results($sql);
			my_calendar_send_email( $event[0] );
			$message = "<div class='updated'><p>". __('Event added. It will now show in your calendar.','my-calendar') . "</p></div>";
		}
	}
	elseif ( $action == 'edit' && $proceed == true ) {
		$event_id = intval(trim($_POST['event_id']));
		$event_author = (int) ($_POST['event_author']);
			if ( mc_can_edit_event( $event_author ) ) {	
			if ($_POST['event_recur'] == 'G') {
				/* 'G', therefore, save date / time info  the occurrences table */
				/* first update the event information */
				$update = $output[2];
				$formats = array('%s','%s','%s','%s','%s','%s','%d','%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%d','%f','%f','%d','%s','%d','%d','%d','%d','%d' );
				//$wpdb->show_errors();
				$result = $wpdb->update( 
						MY_CALENDAR_TABLE, 
						$update, 
						array( 'event_id'=>$event_id ),
						$formats, 
						'%d' );			
			/* now update the occurrences information.*/
      update_occurrences($event_id);
      set_event_begin_end_dates($event_id);   
			/* first the new occurrences */			
			} else /* the following is for all types of recurrences besides "each specified"*/
			{
				$update = $output[2];
				$formats = array('%s','%s','%s','%s','%s','%s','%d','%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%d','%f','%f','%d','%s','%d','%d','%d','%d','%d' );
				//$wpdb->show_errors();
				$result = $wpdb->update( 
						MY_CALENDAR_TABLE, 
						$update, 
						array( 'event_id'=>$event_id ),
						$formats, 
						'%d' );
				//$wpdb->print_error();
			}
			if ( $result === false ) {
				$message = "<div class='error'><p><strong>".__('Error','my-calendar').":</strong>".__('Your event was not updated.','my-calendar')."</p></div>";
			} else if ( $result === 0 ) {
				$message = "<div class='updated'><p>".__('Nothing was changed in that update.','my-calendar')."</p></div>";
			} else {
				$message = "<div class='updated'><p>".__('Event updated successfully','my-calendar')."</p></div>";
			}
		} else { /*User doesn't have privileges on this event */
			$message = "<div class='error'><p><strong>".__('You do not have sufficient permissions to edit that event.','my-calendar')."</strong></p></div>";
		}		

		} /* END action == add or copy */
			
	elseif ( $action == 'delete' ) {
// Deal with deleting an event from the database
  $arrived_correctly = "delete ".$wpdb->last_query;    
$sql2='XXX';
$event_recur = 'XXX';
		{
			if ( empty($event_id) )	{
				$message = "<div class='error'><p><strong>".__('Error','my-calendar').":</strong>".__("You can't delete an event if you haven't submitted an event id",'my-calendar')."</p></div>";
			} else {
				$sql = "SELECT `event_recur` FROM " . MY_CALENDAR_TABLE . " WHERE event_id='" . mysql_real_escape_string($event_id) . "'";
				$event_recur = $wpdb->get_results($sql);
				$event_recur = $event_recur[0]->event_recur;
				$sql = "DELETE FROM " . MY_CALENDAR_TABLE . " WHERE event_id='" . mysql_real_escape_string($event_id) . "'";
				$wpdb->query($sql);
				$sql = "SELECT event_id FROM " . MY_CALENDAR_TABLE . " WHERE event_id='" . mysql_real_escape_string($event_id) . "'";
				$result = $wpdb->get_results($sql);
			
			if ($event_recur == "G") {
				/* 'G', therefore, save date / time info  the occurrences table */
					$sql2="DELETE FROM " . MY_CALENDAR_OCCURRENCES_TABLE . " WHERE `event_id`=" . strval($event_id) . ";";
					$wpdb->query($sql2);
				}  
			}

		}
		if ( empty($result) || empty($result[0]->event_id) ) {
			return "<div class='updated'><p>$sql2".__('Event deleted successfully','my-calendar')."</p>$event_id </div>";
		} else {
			$message = "<div class='error'><p><strong>".__('Error','my-calendar').":</strong>".__('Despite issuing a request to delete, the event still remains in the database. Please investigate.','my-calendar')."</p></div>";
		}	

	}
	$message = $message ."\n". $output[3];

	return $message;
}

function jd_acquire_form_data($event_id=false) {
global $wpdb,$users_entries;
	if ( $event_id !== false ) {
		if ( intval($event_id) != $event_id ) {
			return "<div class=\"error\"><p>".__('Sorry! That\'s an invalid event key.','my-calendar')."</p></div>";
		} else {
			$data = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_TABLE . " WHERE event_id='" . mysql_real_escape_string($event_id) . "' LIMIT 1");
			if ( empty($data) ) {
				return "<div class=\"error\"><p>".__("Sorry! We couldn't find an event with that ID.",'my-calendar')."</p></div>";
			}
			$data = $data[0];
		}
		// Recover users entries if they exist; in other words if editing an event went wrong
		if (!empty($users_entries)) {
		    $data = $users_entries;
		}
	} else {
	  // Deal with possibility that form was submitted but not saved due to error - recover user's entries here
	  $data = $users_entries;
	}
	return $data;

}

//Event host field added by Jeff Allen - http://jdadesign.net
function my_calendar_getUsers() {
	global $wpdb;
	$authors = $wpdb->get_results( "SELECT ID, user_nicename, display_name from $wpdb->users ORDER BY display_name" );
	return $authors;
}
		


// The event edit form for the manage events admin page
function jd_events_edit_form($mode='add', $event_id=false) {
	global $wpdb,$users_entries,$user_ID, $output;
	if ($event_id != false) {
		$data = jd_acquire_form_data($event_id);
	} else {
		$data = $users_entries;
	}
?>

	<?php 
	if ( is_object($data) && $data->event_approved != 1 && $mode == 'edit' ) {
		$message = __('This event must be approved in order for it to appear on the calendar.','my-calendar');
	} else {
		$message = "";
	}
	echo ($message != '')?"<div class='error'><p>$message</p></div>":'';
	?>
	<form id="my-calendar" method="post" action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar">
	<?php my_calendar_print_form_fields($data,$mode,$event_id); ?>
			<p>
                <input type="submit" name="save" class="button-primary" value="<?php _e('Save Event','my-calendar'); ?> &raquo;" />
			</p>
	</form>

<?php
}
function my_calendar_print_form_fields( $data,$mode,$event_id,$context='' ) {
	global $user_ID,$wpdb;
	global $output;
	get_currentuserinfo();
	$user = get_userdata($user_ID);		
	$mc_input_administrator = (get_option('mc_input_options_administrators')=='true' && current_user_can('manage_options'))?true:false;
	$mc_input = get_option('mc_input_options');
?>
<div>
<?php $nonce = wp_create_nonce('my-calendar-nonce'); ?>
<input type="hidden" name="_wpnonce" value="<?php echo $nonce; ?>" />
<input type="hidden" name="event_action" value="<?php echo $mode; ?>" />
<input type="hidden" name="event_id" value="<?php echo $event_id; ?>" />
<input type="hidden" name="event_author" value="<?php echo $user_ID; ?>" />
<input type="hidden" name="event_nonce_name" value="<?php echo wp_create_nonce('event_nonce'); ?>" />
</div>
<div id="poststuff" class="jd-my-calendar">
<div class="postbox">	
	<div class="inside">	

   <fieldset>
		<legend><?php _e('Enter your Event Information','my-calendar'); ?></legend>
		<p>
		<label for="event_title"><?php _e('Event Title','my-calendar'); ?><span><?php _e('(required)','my-calendar'); ?></span></label> <input type="text" id="event_title" name="event_title" class="input" size="60" value="<?php if ( !empty($data) ) echo htmlspecialchars(stripslashes($data->event_title)); ?>" />
<?php if ( $mode == 'edit' ) { ?>
	<?php if ( get_option( 'mc_event_approve' ) == 'true' ) { ?>
		<?php if ( current_user_can( get_option('mc_event_approve_perms') ) ) { // (Added by Roland P. ?>
				<input type="checkbox" value="1" id="event_approved" name="event_approved"<?php if ( !empty($data) && $data->event_approved == '1' ) { echo " checked=\"checked\""; } else if ( !empty($data) && $data->event_approved == '0' ) { echo ""; } else if ( get_option( 'mc_event_approve' ) == 'true' ) { echo "checked=\"checked\""; } ?> /> <label for="event_approved"><?php _e('Publish','my-calendar'); ?><?php if ($event->event_approved != 1) { ?> <small>[<?php _e('You must approve this event to promote it to the calendar.','my-calendar'); ?>]</small> <?php } ?></label>
		<?php } else { // case: editing, approval enabled, user cannot approve ?>
				<input type="hidden" value="0" name="event_approved" /><?php _e('An administrator must approve your new event.','my-calendar'); ?>
		<?php } ?> 
	<?php } else { // Case: editing, approval system is disabled - auto approve ?>	
				<input type="hidden" value="1" name="event_approved" />
	<?php } ?>
<?php } else { // case: adding new event (if use can, then 1, else 0) ?>
<?php if ( current_user_can( get_option('mc_event_approve_perms') ) ) { $dvalue = 1; } else { $dvalue = 0; } ?>
			<input type="hidden" value="<?php echo $dvalue; ?>" name="event_approved" />
<?php } ?>
		</p>
		<?php if (  is_object($data) && $data->event_flagged == 1 ) { ?>
		<div class="error">
		<p>
		<input type="checkbox" value="0" id="event_flagged" name="event_flagged"<?php if ( !empty($data) && $data->event_flagged == '0' ) { echo " checked=\"checked\""; } else if ( !empty($data) && $data->event_flagged == '1' ) { echo ""; } ?> /> <label for="event_flagged"><?php _e('This event is not spam','my-calendar'); ?></label>
		</p>
		</div>
		<?php } ?>
		<?php if ($mc_input['event_desc'] == 'on' || $mc_input_administrator ) { ?>
		<?php if ($context != 'post') { ?>
		<p>
		<label for="event_desc"><?php _e('Event Description (<abbr title="hypertext markup language">HTML</abbr> allowed)','my-calendar'); ?></label><br /><?php if ( $mc_input['event_use_editor'] == 'on' ) { ?><div id='mceditor'><?php } ?><textarea id="event_desc" name="event_desc" class="event_desc" rows="5" cols="80"><?php if ( !empty($data) ) echo htmlspecialchars(stripslashes($data->event_desc)); ?></textarea><?php if ( $mc_input['event_use_editor'] == 'on' ) { ?></div><?php } ?>
		</p>
		<?php if ( $mc_input['event_use_editor'] == 'on' ) { ?>
			<ul id="toggle">
			<li><a class="button toggleVisual">Visual</a></li>
			<li><a class="button toggleHTML">HTML</a></li>
			</ul>
		<?php } ?>
		<?php } ?>
		<?php } ?>
		<?php if ($mc_input['event_short'] == 'on') { ?>
		<p>
		<label for="event_short"><?php _e('Event Short Description (<abbr title="hypertext markup language">HTML</abbr> allowed)','my-calendar'); ?></label><br /><textarea id="event_short" name="event_short" class="input" rows="2" cols="80"><?php if ( !empty($data) ) echo htmlspecialchars(stripslashes($data->event_short)); ?></textarea>
		</p>
		<?php } ?>
	<p>
	<label for="event_host"><?php _e('Event Host','my-calendar'); ?></label>
	<select id="event_host" name="event_host">
		<?php 
			 // Grab all the categories and list them
			$userList = my_calendar_getUsers();				 
			foreach($userList as $u) {
			 echo '<option value="'.$u->ID.'"';
					if (  is_object($data) && $data->event_host == $u->ID ) {
					 echo ' selected="selected"';
					} else if(  is_object($u) && $u->ID == $user->ID && empty($data->event_host) ) {
				    echo ' selected="selected"';
					}
				echo '>'.$u->display_name."</option>\n";
			}
		?>
	</select>
	</p>			
		<?php if ($mc_input['event_category'] == 'on') { ?>
        <p>
		<label for="event_category"><?php _e('Event Category','my-calendar'); ?></label>
		<select id="event_category" name="event_category">
			<?php
			// Grab all the categories and list them
			$sql = "SELECT * FROM " . MY_CALENDAR_CATEGORIES_TABLE;
				$cats = $wpdb->get_results($sql);
				foreach($cats as $cat) {
					echo '<option value="'.$cat->category_id.'"';
					if (!empty($data)) {
						if ($data->event_category == $cat->category_id){
						 echo 'selected="selected"';
						}
					}
					echo '>'.$cat->category_name.'</option>';
				}
			?>
			</select>
            </p>
			<?php } else { ?>
			<div>
			<input type="hidden" name="event_category" value="1" />
			</div>
			<?php } ?>
			<?php if ($mc_input['event_link'] == 'on') { ?>
			<p>
			<?php if ($context != 'post') { ?><label for="event_link"><?php _e('Event Link (Optional)','my-calendar'); ?></label> <input type="text" id="event_link" name="event_link" class="input" size="40" value="<?php if ( !empty($data) ) { echo htmlspecialchars($data->event_link); } ?>" /> <?php } ?><input type="checkbox" value="1" id="event_link_expires" name="event_link_expires"<?php if ( !empty($data) && $data->event_link_expires == '1' ) { echo " checked=\"checked\""; } else if ( !empty($data) && $data->event_link_expires == '0' ) { echo ""; } else if ( get_option( 'mc_event_link_expires' ) == 'true' ) { echo " checked=\"checked\""; } ?> /> <label for="event_link_expires"><?php _e('This link will expire when the event passes.','my-calendar'); ?></label>
			</p>
			<?php } ?>
			</fieldset>
</div>
</div>
			<?php if ($mc_input['event_recurs'] == 'on') { ?>
<div class="postbox">
<div class="inside">
			<fieldset>
			<legend><?php _e('Recurring Events','my-calendar'); ?></legend> 
			<?php if (  is_object($data) && $data->event_repeats != NULL ) { $repeats = $data->event_repeats; } else { $repeats = 0; } ?>
			<p>
			<span class="recurrence_explanation"><label for="event_repeats"><?php _e('Repeats for','my-calendar'); ?></label> <input type="text" name="event_repeats" id="event_repeats" class="input" size="1" value="<?php echo $repeats; ?>" /> 
			<label for="event_recur"><?php _e('Units','my-calendar'); ?></label></span> <select name="event_recur" class="input" id="event_recur"  onChange="set_occurrence_pane(this.value);" ;>
				<option class="input" <?php if ( is_object($data) ) echo jd_option_selected( $data->event_recur,'S','option'); ?> value="S"  ><?php _e('Does not recur','my-calendar'); ?></option>
				<option class="input" <?php if ( is_object($data) ) echo jd_option_selected( $data->event_recur,'D','option'); ?> value="D" onClick="set_occurrence_pane('D') ;"><?php _e('Daily','my-calendar'); ?></option>						
				<option class="input" <?php if ( is_object($data) ) echo jd_option_selected( $data->event_recur,'W','option'); ?> value="W"  onClick="set_occurrence_pane('WW');" ><?php _e('Weekly','my-calendar'); ?></option>
				<option class="input" <?php if ( is_object($data) ) echo jd_option_selected( $data->event_recur,'B','option'); ?> value="B"><?php _e('Bi-weekly','my-calendar'); ?></option>						
				<option class="input" <?php if ( is_object($data) ) echo jd_option_selected( $data->event_recur,'M','option'); ?> value="M"><?php _e('Date of Month (e.g., the 24th of each month)','my-calendar'); ?></option>
				<option class="input" <?php if ( is_object($data) ) echo jd_option_selected( $data->event_recur,'U','option'); ?> value="U"><?php _e('Day of Month (e.g., the 3rd Monday of each month)','my-calendar'); ?></option>
				<option class="input" <?php if ( is_object($data) ) echo jd_option_selected( $data->event_recur,'Y','option'); ?> value="Y" ><?php _e('Annually','my-calendar'); ?></option>
				<option class="input" <?php if ( is_object($data) ) echo jd_option_selected( $data->event_recur,'G','option'); ?> value="G" ><?php _e('Specify Each Occurrence Manually','my-calendar'); ?></option>
			</select><br /> <span class="recurrence_explanation">
					<?php _e('Enter "0" if the event should recur indefinitely. Your entry is the number of events after the first occurrence of the event: a recurrence of <em>2</em> means the event will happen three times.','my-calendar'); ?>
				</span>
			</p>
			</fieldset>	
</div>
</div>				
			<?php } else { ?>
			<div>
			<input type="hidden" name="event_repeats" value="0" />
			<input type="hidden" name="event_recur" value="S" />
			</div>
		
			<?php } ?>

<div class="postbox">	
<div class="inside">
<div id="occurrences_g">
	
		<!-- print_occurrence_table -->
		<p>Existing occurrences for this event:</p>
	<?php 	
		/* Display list of events that have been manually entered */
    show_existing_occurrences($event_id);
	?>
			<fieldset id="occurrences"><legend><?php _e('Event Date and Time','my-calendar'); ?></legend>
		

			<!-- MY  new occurrence row -->
			<tr><td></td><td>New Occurrence:</td><td></td><td></td><td></td><td></td><td></td></tr>
			</table>
			<input id="new_occurrence_count" name="new_occurrence_count" type="hidden" value=1></input>
				
			<p><input type="submit" name="save" class="button-primary" value="<?php _e('Save Event','my-calendar'); ?> &raquo;" />&nbsp;&nbsp;<input class = "button-primary" id="new_occurrence_accept" type="button" value=" Add Another Occurrence" class="new_event_date_time_accept" onclick="create_blank_occurrence();"></input>
			</p>
			<!-- MY new occurrence row ends -->
			
			<p>
			<?php _e('Current time difference from GMT is ','my-calendar'); echo get_option('gmt_offset'); _e(' hour(s)', 'my-calendar'); ?>
			</p> 
			</fieldset>


</div>
</div>
</div>
<div class="postbox">	
<div class="inside">
<div id="occurrences_regular">
			<!-- /* print_standard_event_time_date */
		/* display recurrence rules for auto-recurring events */ -->  
			<fieldset><legend><?php _e('Event Date and Time','my-calendar'); ?></legend>
			<p>
			<?php _e('Enter the beginning and ending information for the first occurrence of this event.','my-calendar'); ?><br />
			<label for="event_begin"><?php _e('Start Date (YYYY-MM-DD)','my-calendar'); ?> <span><?php _e('(required)','my-calendar'); ?></span></label> <input type="text" id="event_begin" name="event_begin" class="calendar_input" size="12" value="<?php if ( !empty($data) ) { esc_attr_e($data->event_begin);} else { echo date_i18n("Y-m-d");} ?>" /> <label for="event_time"><?php _e('Time (hh:mm am/pm)','my-calendar'); ?></label> <input type="text" id="event_time" name="event_time" class="input" size="12"	value="<?php 
					$offset = (60*60*get_option('gmt_offset'));
					if ( !empty($data) ) {
						echo ($data->event_time == "00:00:00")?'':date("h:ia",strtotime($data->event_time));
					} else {
						echo date_i18n("h:ia",time()+$offset);
					}?>" /> 
			</p>				
			<p>
			<label for="event_end"><?php _e('End Date (YYYY-MM-DD)','my-calendar'); ?></label> <input type="text" name="event_end" id="event_end" class="calendar_input" size="12" value="<?php if ( !empty($data) ) {esc_attr_e($data->event_end);} ?>" /> <label for="event_endtime"><?php _e('End Time (hh:mm am/pm)','my-calendar'); ?></label> <input type="text" id="event_endtime" name="event_endtime" class="input" size="12" value="<?php
					if ( !empty($data) ) {
						echo ($data->event_endtime == "00:00:00")?'':date("h:ia",strtotime($data->event_endtime));
					} else {
						echo '';
					}?>" /> 
			</p>
			<p>
			<?php _e('Current time difference from GMT is ','my-calendar'); echo get_option('gmt_offset'); _e(' hour(s)', 'my-calendar'); ?>
			</p> 
			</fieldset>

	
</div>
</div>
</div>

			<?php if ($mc_input['event_open'] == 'on') { ?>			
<div class="postbox">
<div class="inside">
			<fieldset>
			<legend><?php _e('Event Registration Status','my-calendar'); ?></legend>
			<p><em><?php _e('My Calendar does not manage event registrations. Use this for information only.','my-calendar'); ?></em></p>
			<p>
			<input type="radio" id="event_open" name="event_open" value="1" <?php if (!empty($data)) { echo jd_option_selected( $data->event_open,'1'); } else { echo " checked='checked'"; } ?> /> <label for="event_open"><?php _e('Open','my-calendar'); ?></label> 
			<input type="radio" id="event_closed" name="event_open" value="0" <?php if (!empty($data)) {  echo jd_option_selected( $data->event_open,'0'); } ?> /> <label for="event_closed"><?php _e('Closed','my-calendar'); ?></label>
			<input type="radio" id="event_none" name="event_open" value="2" <?php if (!empty($data)) { echo jd_option_selected( $data->event_open, '2' ); } ?> /> <label for="event_none"><?php _e('Does not apply','my-calendar'); ?></label>	
			</p>	
			<p>
			<input type="checkbox" name="event_group" id="event_group" <?php if (  is_object($data) ) { echo jd_option_selected( $data->event_group,'1'); } ?> /> <label for="event_group"><?php _e('If this event recurs, it can only be registered for as a complete series.','my-calendar'); ?></label>
			</p>				
			</fieldset>
</div>
</div>			
			<?php } else { ?>
			<div>
			<input type="hidden" name="event_open" value="2" />
			</div>

			<?php } ?>

			<?php if ($mc_input['event_location'] == 'on' || $mc_input['event_location_dropdown'] == 'on') { ?>

<div class="postbox">
<div class="inside">
			<fieldset>
			<legend><?php _e('Event Location','my-calendar'); ?></legend>
			<?php } ?>
			<?php if ($mc_input['event_location_dropdown'] == 'on') { ?>
			<?php $locations = $wpdb->get_results("SELECT location_id,location_label FROM " . MY_CALENDAR_LOCATIONS_TABLE . " ORDER BY location_id ASC");
				if ( !empty($locations) ) {
			?>				
			<p>
			<label for="location_preset"><?php _e('Choose a preset location:','my-calendar'); ?></label> <select name="location_preset" id="location_preset">
				<option value="none"> -- </option>
				<?php
				foreach ( $locations as $location ) {
					$selected = ($data->event_label == $location->location_label)?" selected='selected'":'';
					echo "<option value=\"".$location->location_id."\"$selected>".stripslashes($location->location_label)."</option>";
				}
?>
			</select>
			</p>
<?php
				} else {
				?>
				<input type="hidden" name="location_preset" value="none" />
				<p><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-locations"><?php _e('Add recurring locations for later use.','my-calendar'); ?></a></p>
				<?php
				}
			?>
			<?php } else { ?>
				<input type="hidden" name="location_preset" value="none" />			
			<?php } ?>
			<?php if ($mc_input['event_location'] == 'on') { ?>			
			<p>
			<?php _e('All location fields are optional: <em>insufficient information may result in an inaccurate map</em>.','my-calendar'); ?>
			</p>
			<p>
			<label for="event_label"><?php _e('Name of Location (e.g. <em>Joe\'s Bar and Grill</em>)','my-calendar'); ?></label> <input type="text" id="event_label" name="event_label" class="input" size="40" value="<?php if ( !empty($data) ) esc_attr_e(stripslashes($data->event_label)); ?>" />
			</p>
			<p>
			<label for="event_street"><?php _e('Street Address','my-calendar'); ?></label> <input type="text" id="event_street" name="event_street" class="input" size="40" value="<?php if ( !empty($data) ) esc_attr_e(stripslashes($data->event_street)); ?>" />
			</p>
			<p>
			<label for="event_street2"><?php _e('Street Address (2)','my-calendar'); ?></label> <input type="text" id="event_street2" name="event_street2" class="input" size="40" value="<?php if ( !empty($data) ) esc_attr_e(stripslashes($data->event_street2)); ?>" />
			</p>
			<p>
			<label for="event_city"><?php _e('City','my-calendar'); ?></label> <input type="text" id="event_city" name="event_city" class="input" size="40" value="<?php if ( !empty($data) ) esc_attr_e(stripslashes($data->event_city)); ?>" /> <label for="event_state"><?php _e('State/Province','my-calendar'); ?></label> <input type="text" id="event_state" name="event_state" class="input" size="10" value="<?php if ( !empty($data) ) esc_attr_e(stripslashes($data->event_state)); ?>" /> <label for="event_postcode"><?php _e('Postal Code','my-calendar'); ?></label> <input type="text" id="event_postcode" name="event_postcode" class="input" size="10" value="<?php if ( !empty($data) ) esc_attr_e(stripslashes($data->event_postcode)); ?>" />
			</p>
			<p>
			<label for="event_region"><?php _e('Region','my-calendar'); ?></label> <input type="text" id="event_region" name="event_region" class="input" size="40" value="<?php if ( !empty( $data ) ) esc_attr_e(stripslashes($data->event_region)); ?>" />
			</p>
			<p>
			<label for="event_country"><?php _e('Country','my-calendar'); ?></label> <input type="text" id="event_country" name="event_country" class="input" size="10" value="<?php if ( !empty($data) ) esc_attr_e(stripslashes($data->event_country)); ?>" />
			</p>
			<p>
			<label for="event_zoom"><?php _e('Initial Zoom','my-calendar'); ?></label> 
				<select name="event_zoom" id="event_zoom">
				<option value="16"<?php if ( !empty( $data ) && ( $data->event_zoom == 16 ) ) { echo " selected=\"selected\""; } ?>><?php _e('Neighborhood','my-calendar'); ?></option>
				<option value="14"<?php if ( !empty( $data ) && ( $data->event_zoom == 14 ) ) { echo " selected=\"selected\""; } ?>><?php _e('Small City','my-calendar'); ?></option>
				<option value="12"<?php if ( !empty( $data ) && ( $data->event_zoom == 12 ) ) { echo " selected=\"selected\""; } ?>><?php _e('Large City','my-calendar'); ?></option>
				<option value="10"<?php if ( !empty( $data ) && ( $data->event_zoom == 10 ) ) { echo " selected=\"selected\""; } ?>><?php _e('Greater Metro Area','my-calendar'); ?></option>
				<option value="8"<?php if ( !empty( $data ) && ( $data->event_zoom == 8 ) ) { echo " selected=\"selected\""; } ?>><?php _e('State','my-calendar'); ?></option>
				<option value="6"<?php if ( !empty( $data ) && ( $data->event_zoom == 6 ) ) { echo " selected=\"selected\""; } ?>><?php _e('Region','my-calendar'); ?></option>
				</select>
			</p>
			<fieldset>
			<legend><?php _e('GPS Coordinates (optional)','my-calendar'); ?></legend>
			<p>
			<small><?php _e('If you supply GPS coordinates for your location, they will be used in place of any other address information to provide your map link.','my-calendar'); ?></small>
			</p>
			<p>
			<label for="event_latitude"><?php _e('Latitude','my-calendar'); ?></label> <input type="text" id="event_latitude" name="event_latitude" class="input" size="10" value="<?php if ( !empty( $data ) ) esc_attr_e(stripslashes($data->event_latitude)); ?>" /> <label for="event_longitude"><?php _e('Longitude','my-calendar'); ?></label> <input type="text" id="event_longitude" name="event_longitude" class="input" size="10" value="<?php if ( !empty( $data ) ) esc_attr_e(stripslashes($data->event_longitude)); ?>" />
			</p>			
			</fieldset>	
			<?php } ?>
			<?php if ($mc_input['event_location'] == 'on' || $mc_input['event_location_dropdown'] == 'on') { ?>
			</fieldset>
		</div>
		</div>
</div>
			<?php }
} // end function my_calendar_print_form_fields( $data,$mode,$event_id,$context='' )

// Used on the manage events admin page to display a list of events
function jd_events_display_list( $type='normal' ) {
	global $wpdb;
	
		$sortby = ( isset( $_GET['sort'] ) )?(int) $_GET['sort']:get_option('mc_default_sort');

		if ( isset( $_GET['order'] ) ) {
			$sortdir = ( isset($_GET['order']) && $_GET['order'] == 'ASC' )?'ASC':'default';
		} else {
			$sortdir = 'default';
		}
		if ( isset( $_GET['limit'] ) ) {
			switch ($_GET['limit']) {
				case 'reserved':$status = 'reserved';
				break;
				case 'published':$status = 'published';
				break;
				default:
				$status = 'all';
				break;
			}
		} else {
			$status = 'all';
		}
	
	if ( empty($sortby) ) {
		$sortbyvalue = 'event_begin';
	} else {
		switch ($sortby) {
		    case 1:$sortbyvalue = 'event_ID';
			break;
			case 2:$sortbyvalue = 'event_title';
			break;
			case 3:$sortbyvalue = 'event_desc';
			break;
			case 4:$sortbyvalue = 'event_begin';
			break;
			case 5:$sortbyvalue = 'event_author';
			break;
			case 6:$sortbyvalue = 'event_category';
			break;
			case 7:$sortbyvalue = 'event_label';
			break;
			default:$sortbyvalue = 'event_begin';
		}
	}
	if ($sortdir == 'default') {
		$sortbydirection = 'DESC';
	} else {
		$sortbydirection = $sortdir;
	}
	
	switch ($status) {
		case 'all':$limit = '';
		break;
		case 'reserved':$limit = 'WHERE event_approved = 0';
		break;
		case 'published':$limit = 'WHERE event_approved = 1';
		break;
		default:$limit = '';
	}
	$events = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_TABLE . " $limit ORDER BY $sortbyvalue $sortbydirection");
	if ($sortbydirection == 'DESC') {
		$sorting = "&amp;order=ASC";
	} else {
		$sorting = '';
	}
	?>
<div id="icon-edit" class="icon32"></div>	
		<h2><?php _e('Manage Events','my-calendar'); ?></h2>
		<?php if ( get_option('mc_event_approve') == 'true' ) { ?>
		<ul class="links">
		<li><a <?php echo ($_GET['limit']=='published')?' class="active-link"':''; ?> href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;limit=published"><?php _e('Published','my-calendar'); ?></a></li>
		<li><a <?php echo ($_GET['limit']=='reserved')?' class="active-link"':''; ?>  href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;limit=reserved"><?php _e('Reserved','my-calendar'); ?></a></li> 
		<li><a <?php echo ($_GET['limit']=='all' || !isset($_GET['limit']))?' class="active-link"':''; ?>  href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;limit=all"><?php _e('All','my-calendar'); ?></a></li>
		</ul>
		<?php } ?>	
	<?php
	if ( !empty($events) ) {
		?>
		<form action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar" method="post">
		<div>
		<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" />
		</div>
<table class="widefat page fixed" id="my-calendar-admin-table" summary="<?php _e('Table of Calendar Events','my-calendar'); ?>">
	<thead>
	<tr>
		<th class="manage-column n4" scope="col"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;sort=1<?php echo $sorting; ?>"><?php _e('ID','my-calendar') ?></a></th>
		<th class="manage-column" scope="col"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;sort=2<?php echo $sorting; ?>"><?php _e('Title','my-calendar') ?></a></th>
		<th class="manage-column n1" scope="col"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;sort=7<?php echo $sorting; ?>"><?php _e('Location','my-calendar') ?></a></th>
		<th class="manage-column n8" scope="col"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;sort=3<?php echo $sorting; ?>"><?php _e('Description','my-calendar') ?></a></th>
		<th class="manage-column n5" scope="col"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;sort=4<?php echo $sorting; ?>"><?php _e('Start Date','my-calendar') ?></a></th>
		<th class="manage-column n6" scope="col"><?php _e('Recurs','my-calendar') ?></th>
		<th class="manage-column n3" scope="col"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;sort=5<?php echo $sorting; ?>"><?php _e('Author','my-calendar') ?></a></th>
		<th class="manage-column n2" scope="col"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;sort=6<?php echo $sorting; ?>"><?php _e('Category','my-calendar') ?></a></th>
		<th class="manage-column n7" scope="col"><?php _e('Edit / Delete','my-calendar') ?></th>
	</tr>
	</thead>
		<?php
		$class = '';
		$sql = "SELECT * FROM " . MY_CALENDAR_CATEGORIES_TABLE ;
        $categories = $wpdb->get_results($sql);
			
		foreach ( $events as $event ) {
			$number_of_occurrences = $wpdb->get_var("SELECT COUNT(*) FROM " . MY_CALENDAR_OCCURRENCES_TABLE . " WHERE `event_id`=" . $event->event_id . ";" );

			$class = ($class == 'alternate') ? '' : 'alternate';
			$spam = ($event->event_flagged == 1) ? ' spam' : '';
			$spam_label = ($event->event_flagged == 1) ? '<strong>Possible spam:</strong> ' : '';
			$author = get_userdata($event->event_author);
			if ($event->event_link != '') { 
			$title = "<a href='".esc_attr($event->event_link)."'>$event->event_title</a>";
			} else {
			$title = $event->event_title;
			}
			?>
			<tr class="<?php echo $class; echo $spam; ?>">
				<th scope="row"><input type="checkbox" value="<?php echo $event->event_id; ?>" name="mass_delete[]" id="mc<?php echo $event->event_id; ?>" <?php echo ($event->event_flagged == 1)?' checked="checked"':''; ?> /> <label for="mc<?php echo $event->event_id; ?>"><?php echo $event->event_id; ?></label></th>
				<td><?php echo $spam_label; echo stripslashes($title); ?></td>
				<td><?php echo stripslashes($event->event_label); ?></td>
				<td><?php echo substr(strip_tags(stripslashes($event->event_desc)),0,60); ?>&hellip;</td>
				<?php if ($event->event_time != "00:00:00") { $eventTime = date_i18n(get_option('time_format'), strtotime($event->event_time)); } else { $eventTime = get_option('my_calendar_notime_text'); } ?>
				<td><?php echo "$event->event_begin, $eventTime"; ?></td>
				<?php /* <td><?php echo $event->event_end; ?></td> */ ?>
				<td>
				<?php 
				$number_of_occurrences;
					// Interpret the DB values into something human readable
					if ($event->event_recur == 'S') { _e('Never','my-calendar'); } 
					else if ($event->event_recur == 'D') { _e('Daily&thinsp;&ndash;&thinsp;','my-calendar'); }
					else if ($event->event_recur == 'W') { _e('Weekly&thinsp;&ndash;&thinsp;','my-calendar'); }
					else if ($event->event_recur == 'B') { _e('Bi-Weekly&thinsp;&ndash;&thinsp;','my-calendar'); }
					else if ($event->event_recur == 'M') { _e('Monthly (by date)&thinsp;&ndash;&thinsp;','my-calendar'); }
					else if ($event->event_recur == 'U') { _e('Monthly (by day)&thinsp;&ndash;&thinsp;','my-calendar'); }
					else if ($event->event_recur == 'Y') { _e('Yearly&thinsp;&ndash;&thinsp;','my-calendar'); }
					else if ($event->event_recur == 'G') { _e('Scheduled occurrences: ','my-calendar'); }
				
					if ($event->event_recur == 'S') { echo __('N/A','my-calendar'); }
					else if ($event->event_recur == 'G') { echo __("$number_of_occurrences",'my-calendar'); }
					else if ($event->event_repeats == 0) { echo __('Forever','my-calendar'); }
					else if ($event->event_repeats > 0) { echo $event->event_repeats.' '.__('Times','my-calendar'); }					
				?>				
				</td>
				<td><?php echo $author->display_name; ?></td>
                                <?php
								$this_category = $event->event_category;
								foreach ($categories as $key=>$value) {
									if ($value->category_id == $this_category) {
										$this_cat = $categories[$key];
									} 
								}
                                ?>
				<td><div class="category-color" style="background-color:<?php echo (strpos($this_cat->category_color,'#') !== 0)?'#':''; echo $this_cat->category_color;?>;"> </div> <?php echo stripslashes($this_cat->category_name); ?></td>
				<?php unset($this_cat); ?>
				<td>
				<a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;mode=copy&amp;event_id=<?php echo $event->event_id;?>" class='copy'><?php echo __('Copy','my-calendar'); ?></a> &middot; 
				<?php if ( mc_can_edit_event( $event->event_author ) ) { ?>
				<a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;mode=edit&amp;event_id=<?php echo $event->event_id;?>" class='edit'><?php echo __('Edit','my-calendar'); ?></a> &middot; <a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;mode=delete&amp;event_id=<?php echo $event->event_id;?>" class="delete"><?php echo __('Delete','my-calendar'); ?></a>
				<?php } else { _e("Not editable.",'my-calendar'); } ?>
				<?php if ( get_option( 'mc_event_approve' ) == 'true' ) { ?>
				 &middot; 
						<?php if ( current_user_can( get_option('mc_event_approve_perms') ) ) { // Added by Roland P.?>
							<?php	// by Roland 
							if ( $event->event_approved == '1' )  { ?>
								<a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;mode=reject&amp;event_id=<?php echo $event->event_id;?>" class='reject'><?php echo __('Reject','my-calendar'); ?></a>
							<?php } else { 	?>
								<a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;mode=approve&amp;event_id=<?php echo $event->event_id;?>" class='publish'><?php echo __('Approve','my-calendar'); ?></a>		
							<?php } ?>
						<?php } else { ?>
							<?php	// by Roland 
							if ( $event->event_approved == '1' )  { ?>
								<?php echo __('Approved','my-calendar'); ?>
							<?php } else if ($event->event_approved == '2' ) { 	?>
								<?php echo __('Rejected','my-calendar'); ?>							
							<?php } else { ?>
								<?php echo __('Awaiting Approval','my-calendar'); ?>		
							<?php } ?>
						<?php } ?>	
				<?php } ?>					
				</td>	
			</tr>
<?php
		}
?>
		</table>
    <span id="delete_events_confirm">Delete checked events?&nbsp&nbsp&nbsp;<button  class="button-red" type="submit" style="display:inline;">Confirm</button></span>
		</form>
  <input type="button" id = "delete_checked_events"  class="button-primary" value="<?php _e('Delete checked events','my-calendar'); ?>" onClick="delete_checked_events();"></input>
<?php
	} else {
?>
		<p><?php _e("There are no events in the database!",'my-calendar') ?></p>
<?php	
	}
} //function jd_events_display_list( $type='normal' )



function mc_check_data($action,$_POST) {
global $wpdb, $current_user, $users_entries;

if ( get_magic_quotes_gpc() ) {
    $_POST = array_map( 'stripslashes_deep', $_POST );
}

if (!wp_verify_nonce($_POST['event_nonce_name'],'event_nonce')) {
return;
}

$errors = "";
if ( $action == 'add' || $action == 'edit' || $action == 'copy' ) {
	$title = !empty($_POST['event_title']) ? trim($_POST['event_title']) : '';
	$desc = !empty($_POST['event_desc']) ? trim($_POST['event_desc']) : '';
	$short = !empty($_POST['event_short']) ? trim($_POST['event_short']) : '';
	$begin = !empty($_POST['event_begin']) ? trim($_POST['event_begin']) : '';
	$end = !empty($_POST['event_end']) ? trim($_POST['event_end']) : $begin;
	$time = !empty($_POST['event_time']) ? trim($_POST['event_time']) : '';
	$endtime = !empty($_POST['event_endtime']) ? trim($_POST['event_endtime']) : '';
	$recur = !empty($_POST['event_recur']) ? trim($_POST['event_recur']) : '';
	$repeats = !empty($_POST['event_repeats']) ? trim($_POST['event_repeats']) : 0;
	$host = !empty($_POST['event_host']) ? $_POST['event_host'] : $current_user->ID;	
	$category = !empty($_POST['event_category']) ? $_POST['event_category'] : '';
    $linky = !empty($_POST['event_link']) ? trim($_POST['event_link']) : '';
    $expires = !empty($_POST['event_link_expires']) ? $_POST['event_link_expires'] : '0';
    $approved = !empty($_POST['event_approved']) ? $_POST['event_approved'] : '0';
	$location_preset = !empty($_POST['location_preset']) ? $_POST['location_preset'] : '';
    $event_author = !empty($_POST['event_author']) ? $_POST['event_author'] : $current_user->ID;
	$event_open = !empty($_POST['event_open']) ? $_POST['event_open'] : '2';
	$event_group = !empty($_POST['event_group']) ? 1 : 0;
	$event_flagged = ( !isset($_POST['event_flagged']) || $_POST['event_flagged']===0 )?0:1;
	// set location
		if ($location_preset != 'none') {
			$sql = "SELECT * FROM " . MY_CALENDAR_LOCATIONS_TABLE . " WHERE location_id = $location_preset";
			$location = $wpdb->get_row($sql);
			$event_label = $location->location_label;
			$event_street = $location->location_street;
			$event_street2 = $location->location_street2;
			$event_city = $location->location_city;
			$event_state = $location->location_state;
			$event_postcode = $location->location_postcode;
			$event_region = $location->location_region;
			$event_country = $location->location_country;
			$event_longitude = $location->location_longitude;
			$event_latitude = $location->location_latitude;
			$event_zoom = $location->location_zoom;
		} else {
			$event_label = !empty($_POST['event_label']) ? $_POST['event_label'] : '';
			$event_street = !empty($_POST['event_street']) ? $_POST['event_street'] : '';
			$event_street2 = !empty($_POST['event_street2']) ? $_POST['event_street2'] : '';
			$event_city = !empty($_POST['event_city']) ? $_POST['event_city'] : '';
			$event_state = !empty($_POST['event_state']) ? $_POST['event_state'] : '';
			$event_postcode = !empty($_POST['event_postcode']) ? $_POST['event_postcode'] : '';
			$event_region = !empty($_POST['event_region']) ? $_POST['event_region'] : '';
			$event_country = !empty($_POST['event_country']) ? $_POST['event_country'] : '';
			$event_longitude = !empty($_POST['event_longitude']) ? $_POST['event_longitude'] : '';	
			$event_latitude = !empty($_POST['event_latitude']) ? $_POST['event_latitude'] : '';	
			$event_zoom = !empty($_POST['event_zoom']) ? $_POST['event_zoom'] : '';	
	    }
	// Perform some validation on the submitted dates - this checks for valid years and months
	$date_format_one = '/^([0-9]{4})-([0][1-9])-([0-3][0-9])$/';
    $date_format_two = '/^([0-9]{4})-([1][0-2])-([0-3][0-9])$/';
	if ((preg_match($date_format_one,$begin) || preg_match($date_format_two,$begin)) && (preg_match($date_format_one,$end) || preg_match($date_format_two,$end))   ) {
        // We know we have a valid year and month and valid integers for days so now we do a final check on the date
        $begin_split = split('-',$begin);
	    $begin_y = $begin_split[0]; 
	    $begin_m = $begin_split[1];
	    $begin_d = $begin_split[2];
        $end_split = split('-',$end);
	    $end_y = $end_split[0];
	    $end_m = $end_split[1];
	    $end_d = $end_split[2];
        if (checkdate($begin_m,$begin_d,$begin_y) && checkdate($end_m,$end_d,$end_y)) {
		// Ok, now we know we have valid dates, we want to make sure that they are either equal or that the end date is later than the start date
			if (strtotime($end) >= strtotime($begin)) {
			$start_date_ok = 1;
			$end_date_ok = 1;
			} else {
				$errors .= "<div class='error'><p><strong>".__('Error','my-calendar').":</strong> ".__('Your event end date must be either after or the same as your event begin date','my-calendar')."</p></div>";
			}
		} else {
				$errors .= "<div class='error'><p><strong>".__('Error','my-calendar').":</strong> ".__('Your date formatting is correct but one or more of your dates is invalid. Check for number of days in month and leap year related errors.','my-calendar')."</p></div>";
		}
	} elseif (($recur != "G")) {
		$errors .= "<div class='error'><p><strong>".__('Error','my-calendar').":</strong> ".__('Both start and end dates must be in the format YYYY-MM-DD','my-calendar')."</p></div>";
	}
        // We check for a valid time, or an empty one
		$time = ($time == '')?'00:00:00':date( 'H:i:00',strtotime($time) );
        $time_format_one = '/^([0-1][0-9]):([0-5][0-9]):([0-5][0-9])$/';
		$time_format_two = '/^([2][0-3]):([0-5][0-9]):([0-5][0-9])$/';
        if (preg_match($time_format_one,$time) || preg_match($time_format_two,$time) || $time == '') {
            $time_ok = 1;
        } else {
			$errors .= "<div class='error'><p><strong>".__('Error','my-calendar').":</strong> ".__('The time field must either be blank or be entered in the format hh:mm','my-calendar')."</p></div>";
	    }
        // We check for a valid end time, or an empty one
		$endtime = ($endtime == '')?'00:00:00':date( 'H:i:00',strtotime($endtime) );
        if (preg_match($time_format_one,$endtime) || preg_match($time_format_two,$endtime) || $endtime == '') {
            $endtime_ok = 1;
        } else {
            $errors .= "<div class='error'><p><strong>".__('Error','my-calendar').":</strong> ".__('The end time field must either be blank or be entered in the format hh:mm','my-calendar')."</p></div>";
	    }		
		// We check to make sure the URL is acceptable (blank or starting with http://)                                                        
		if ($linky == '') {
			$url_ok = 1;
		} else if ( preg_match('/^(http)(s?)(:)\/\//',$linky) ) {
			$url_ok = 1;
		} else {
			$linky = "http://" . $linky;
		}
	}
	// The title must be at least one character in length and no more than 255 - only basic punctuation is allowed
	$title_length = strlen($title);
	if ( $title_length > 1 && $title_length <= 255 ) {
	    $title_ok =1;
	} else {
		$errors .= "<div class='error'><p><strong>".__('Error','my-calendar').":</strong> ".__('The event title must be between 1 and 255 characters in length.','my-calendar')."</p></div>";
	}
	// We run some checks on recurrence                                
	
	if (($repeats == 0 && $recur == 'S') || (($repeats >= 0) && ($recur == 'W' || $recur == 'B' || $recur == 'M' || $recur == 'U' || $recur == 'Y' || $recur == 'D')) || $recur == 'G') {
	    $recurring_ok = 1;
	} else {
		$errors .= "<div class='error'><p><strong>".__('Error','my-calendar').":</strong> ".__('The repetition value must be 0 unless a type of recurrence is selected.','my-calendar')."</p></div>";
	}
	if ( (($start_date_ok == 1 && $end_date_ok == 1 && $time_ok == 1 && $endtime_ok == 1 && $url_ok == 1 && $title_ok == 1) || ($recur=='G')) && $recurring_ok == 1) {
		$proceed = true;
		if ($action == 'add' || $action == 'copy' ) {
			$submit = array(
				'event_begin'=>$begin, 
				'event_end'=>$end, 
				'event_title'=>$title, 
				'event_desc'=>$desc, 			
				'event_time'=>$time, 
				'event_recur'=>$recur, 
				'event_repeats'=>$repeats, 
				'event_author'=>$current_user->ID,
				'event_category'=>$category, 
				'event_link'=>$linky,
				'event_label'=>$event_label, 
				'event_street'=>$event_street, 
				'event_street2'=>$event_street2, 
				'event_city'=>$event_city, 
				'event_state'=>$event_state, 
				'event_postcode'=>$event_postcode,
				'event_region'=>$event_region,
				'event_country'=>$event_country,
				'event_endtime'=>$endtime, 								
				'event_link_expires'=>$expires, 				
				'event_longitude'=>$event_longitude,
				'event_latitude'=>$event_latitude,
				'event_zoom'=>$event_zoom,
				'event_short'=>$short,
				'event_open'=>$event_open,
				'event_group'=>$event_group,
				'event_approved'=>$approved,
				'event_host'=>$host,
				'event_flagged'=> mc_akismet( $linky, $desc )
				);
			
		} else if ($action == 'edit') {
			$submit = array(
				'event_begin'=>$begin, 
				'event_end'=>$end, 
				'event_title'=>$title, 
				'event_desc'=>$desc, 			
				'event_time'=>$time, 
				'event_recur'=>$recur, 
				'event_repeats'=>$repeats, 
				'event_category'=>$category, 
				'event_link'=>$linky,
				'event_label'=>$event_label, 
				'event_street'=>$event_street, 
				'event_street2'=>$event_street2, 
				'event_city'=>$event_city, 
				'event_state'=>$event_state, 
				'event_postcode'=>$event_postcode,
				'event_region'=>$event_region,
				'event_country'=>$event_country,
				'event_endtime'=>$endtime, 				
				'event_link_expires'=>$expires, 				
				'event_longitude'=>$event_longitude,
				'event_latitude'=>$event_latitude,
				'event_zoom'=>$event_zoom,
				'event_short'=>$short,
				'event_open'=>$event_open,
				'event_group'=>$event_group,
				'event_approved'=>$approved,
				'event_host'=>$host,
				'event_flagged'=>$event_flagged			
				);		
		}
	} else {
	    // The form is going to be rejected due to field validation issues, so we preserve the users entries here
		$users_entries->event_title = $title;
		$users_entries->event_desc = $desc;
		$users_entries->event_begin = $begin;
		$users_entries->event_end = $end;
		$users_entries->event_time = $time;
		$users_entries->event_endtime = $endtime;
		$users_entries->event_recur = $recur;
		$users_entries->event_repeats = $repeats;
		$users_entries->event_host = $host;
		$users_entries->event_category = $category;
		$users_entries->event_link = $linky;
		$users_entries->event_link_expires = $expires;
		$users_entries->event_label = $event_label;
		$users_entries->event_street = $event_street;
		$users_entries->event_street2 = $event_street2;
		$users_entries->event_city = $event_city;
		$users_entries->event_state = $event_state;
		$users_entries->event_postcode = $event_postcode;
		$users_entries->event_country = $event_country;	
		$users_entries->event_region = $event_region;
		$users_entries->event_longitude = $event_longitude;		
		$users_entries->event_latitude = $event_latitude;		
		$users_entries->event_zoom = $event_zoom;
		$users_entries->event_author = $event_author;
		$users_entries->event_open = $event_open;
		$users_entries->event_short = $short;
		$users_entries->event_group = $event_group;
		$users_entries->event_approved = $approved;
		$proceed = false;
	}
	$data = array($proceed, $users_entries, $submit,$errors);
	return $data;
}

function update_occurrences($event_id){
  global $wpdb;
  if (!isset($event_id)) { $event_id = $_REQUEST['event_id'];}		
  if (!isset($event_id)) { $event_id = $_POST['event_id'];}
  if (!isset($event_id)) { $event_id = $_GET['event_id'];}
  /* now update the occurrences information.*/
			/* first insert  the new occurrences */			
			$lim = intval($_POST["new_occurrence_count"]);
			for($i2=0; $i2<$lim; $i2++){
			
				if ($_POST['new_occ_beg_date'.$i2] != '' &&
						$_POST['new_occ_beg_time'.$i2] != '' &&
						$_POST['new_occ_end_date'.$i2]  != '' &&
						$_POST['new_occ_end_time'.$i2] != '') {
          $ob = date("Y-m-d H:i",strtotime($_POST['new_occ_beg_date'.$i2]." ".$_POST['new_occ_beg_time'.$i2]));
					$oe = date("Y-m-d H:i",strtotime($_POST['new_occ_end_date'.$i2]." ".$_POST['new_occ_end_time'.$i2]));
					$activer=intval( array_key_exists("new_occ_active$i2",$_POST));
					$result = $wpdb->insert(MY_CALENDAR_OCCURRENCES_TABLE,
				 array('event_id'=>$event_id,'occ_id'=>'NULL',"occ_begin"=>$ob,"occ_end"=>$oe,"occ_active"=>$activer,"occ_text"=>$_POST['new_occ_text'.$i2])
				 );
				} 
			}

			/* and now, updates to existing occurrences:  */
      foreach($_POST as $k=>$v) {
				if (stripos($k,"up_occ") > -1) {
						$v=substr($k,15,4);
            if (strlen($v) > 0) $occs_updated[]= $v;
            $occs_updated = array_unique($occs_updated); 
					}
			}
      
			 if (is_array($occs_updated)){
				foreach($occs_updated as $e) {
					if (isset($_POST['up_occ_beg_time'.$e]) && isset($_POST['up_occ_beg_date'.$e]) &&isset($_POST['up_occ_end_time'.$e]) && isset($_POST['up_occ_end_date'.$e])) {
						$ob = date("Y-m-d H:i",strtotime($_POST['up_occ_beg_date'.$e]." ".$_POST['up_occ_beg_time'.$e]));
						$oe = date("Y-m-d H:i",strtotime($_POST['up_occ_end_date'.$e]." ".$_POST['up_occ_end_time'.$e]));
						$activer=intval( array_key_exists("up_occ_active$e",$_POST));
						$text = $_POST['up_occ_text'.$e];
						$sql='UPDATE '.MY_CALENDAR_OCCURRENCES_TABLE.' SET `occ_begin`="'.$ob.'", `occ_end`="'.$oe.'", `occ_active`='.$activer.', `occ_text`="'.$text.'" WHERE `occ_id`='.$e.';';
						$result = $wpdb->query($sql);
					}
					}
				}
} /*  end function() */
    
function show_existing_occurrences($event_id){
  global $number_of_occurrences,$wpdb;
    if (!isset($event_id)) { $event_id = $_REQUEST['event_id'];}		
    if (!isset($event_id)) { $event_id = $_POST['event_id'];}
    if (!isset($event_id)) { $event_id = $_GET['event_id'];}
		$sql = "select * from `".MY_CALENDAR_OCCURRENCES_TABLE."` where `event_id` = $event_id ORDER BY `occ_begin`;";
		$occs=$wpdb->get_results($sql);
		
		$number_of_occurrences = count($occs);

		echo "<table class='widefat page fixed' id='occurrences_table' name='".$event_id."'>";
		echo "<thead><tr><th style='width:20px;'></th><th>Start Date</th><th>Start Time</th><th>End Date</th><th>End Time</th><th style=\"width:180px;\">Note</th><th  style='width:60px;' >Active?</th></tr></thead>";
		
				echo "<tr><td id=\"occ_delete_button\" colspan=\"7\" style='width:20px;'>
				<a onClick=\"confirm_occ_delete();\" class=\"button-primary\" href='#this'>Delete Checked</a><span style=\"display:inline;\" id=\"delete_checked_occurrences\"> Delete checked occurrences? &nbsp;&nbsp;&nbsp;<a class=\"button-primary\">Cancel</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a class=\"button-red\" id=\"occs_confirm_delete\">Confirm</a></span>
				</td></tr>";
				
		foreach( $occs as $o) {
				$begindate = date("Y-m-d",strtotime($o->occ_begin));
				$enddate = date("Y-m-d",strtotime($o->occ_end));
				$begintime =date("h:i A",strtotime($o->occ_begin));
				$endtime = date("h:i A",strtotime($o->occ_end));
				$checked = $o->occ_active == 1 ? ' checked="checked" ' : '';
				echo '<tr class="up_occ" id="'.$o->occ_id.'"><td style="width:20px;"><input name="del_occ['.$o->occ_id.']" type="checkbox" class="occ_del" id='.$o->occ_id.' ></td><td> <input style="width:95%" type="text" id="up_occ_beg_date'.$o->occ_id.'" name="up_occ_beg_date'.$o->occ_id.'" size="12" value="'.$begindate.'" ></input></td><td> <input style="width:95%" type="text" id="up_occ_beg_time'.$o->occ_id.'" name="up_occ_beg_time'.$o->occ_id.'" size="12"	value="'.$begintime.'" /></td><td><input  style="width:95%" type="text" name="up_occ_end_date'.$o->occ_id.'" id="up_occ_end_date'.$o->occ_id.'" size="12" value="'.$enddate.'" /></td><td><input style="width:95%" type="text" id="up_occ_end_time'.$o->occ_id.'" name="up_occ_end_time'.$o->occ_id.'"  size="12" value="'.$endtime.'" /></td><td><input style="width:95%" type="text" id="occ_text" name="up_occ_text'.$o->occ_id.'" size="10" value="'.$o->occ_text.'"></input></td><td><input type="checkbox" value="'.$o->occ_active.'" '.$checked.'  id="up_occ_active'.$o->occ_id.'" name="up_occ_active'.$o->occ_id.'"></input></td></tr>';
		}
  }  /*end function()*/
  function set_event_begin_end_dates($event_id){
    global $wpdb;
    /*we set the $event's begin and end dates to the earliest and latest occurrence dates, respectively*/
    if (isset($event_id) ) {
        $sql = "SELECT * FROM " . MY_CALENDAR_OCCURRENCES_TABLE . " WHERE `event_id`=" . $event_id . " ORDER BY `occ_begin` ; ";
        $results = $wpdb->get_results($sql);
    if (count($results) == 1) {
        $f = $results[0];
        $l = $results[0];
    } else 
    { 
      $f = $results[0];
      $l = $results[count($results)-1];
    }
    $sql = "UPDATE ". MY_CALENDAR_TABLE . " SET `event_begin` = '".$f->occ_begin."', `event_end` = '".$l->occ_end."', `event_time` = '".$f->occ_begin."', `event_endtime` = '".$l->occ_end."' WHERE `event_id`=$event_id; ";
    $wpdb->query($sql);
    }

  }

?>

