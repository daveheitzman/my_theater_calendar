<?php
// Display the style configuration page

function mc_get_style_path($filename,$type='path') {
global $wp_plugin_url,$wp_plugin_dir;
		if ( strpos( $filename,'mc_custom_' ) === 0 ) {
			$filename = str_replace('mc_custom_','',$filename);
			$stylefile = ($type=='path')?$wp_plugin_dir.'/my-calendar-custom/styles/'.$filename:$wp_plugin_url.'/my-calendar-custom/styles/'.$filename;
		} else {
			$stylefile = ($type=='path')?dirname(__FILE__).'/styles/' . $filename:$wp_plugin_url.'/my-calendar/styles/'.$filename;
		}
		if ( $type == 'path' ) {
			if ( is_file($stylefile) ) {
			return $stylefile;
			} else {
			return false;
			}
		} else {
			return $stylefile;
		}
}

function mc_write_styles($stylefile, $my_calendar_style) {
	if ( is_writeable( $stylefile ) ) {
		$f = fopen( $stylefile, 'w+' );
		fwrite( $f, $my_calendar_style );
		fclose( $f );
		return true;
	} else {
		return false;
	}				
}

function edit_my_calendar_styles() {
	global $wpdb,$wp_plugin_dir, $stored_styles;
	// We can't use this page unless My Calendar is installed/upgraded
	check_my_calendar();
	if ( isset( $_POST['mc_edit_style'] ) ) {
		$nonce=$_REQUEST['_wpnonce'];
		if (! wp_verify_nonce($nonce,'my-calendar-nonce') ) die("Security check failed");
		$my_calendar_style = stripcslashes( $_POST['style'] );
		$my_calendar_show_css = stripcslashes( $_POST['my_calendar_show_css'] );
		$my_calendar_css_file = stripcslashes( $_POST['my_calendar_css_file'] );
		
		$stylefile = mc_get_style_path($my_calendar_css_file);	
		$wrote_styles = mc_write_styles($stylefile, $my_calendar_style);
		if ($wrote_styles == true) {
			delete_option('my_calendar_file_permissions');
			delete_option('my_calendar_style');
		}
		$message .= ( $wrote_styles == true)?'<p>'. __('The stylesheet has been updated.', 'my-calendar') .'</p>':'<p><strong>'. __('Write Error! Please verify write permissions on the style file.', 'my-calendar') .'</strong></p>';

	
		$my_calendar_show_css = ($_POST['my_calendar_show_css']=='')?'':$_POST['my_calendar_show_css'];
		update_option('my_calendar_show_css',$my_calendar_show_css);
		$use_styles = ($_POST['use_styles']=='on')?'true':'false';
		update_option('my_calendar_use_styles',$use_styles);
	
		if ( $_POST['reset_styles'] == 'on') {
			$my_calendar_css_file = get_option('my_calendar_css_file');
			$stylefile = mc_get_style_path($my_calendar_css_file);
			$styles = $stored_styles[$my_calendar_css_file];
			$wrote_styles = mc_write_styles($stylefile, $styles);
		}
		if ($wrote_styles == true) {
			$message .= "<p>".__('Stylesheet reset to default.','my-calendar')."</p>";
		}	
		$message .= "<p><strong>".__('Style Settings Saved','my-calendar').".</strong></p>";
		echo "<div id='message' class='updated fade'>$message</div>";
	}
	if ( isset( $_POST['mc_choose_style'] ) ) {
		$nonce=$_REQUEST['_wpnonce'];
		if (! wp_verify_nonce($nonce,'my-calendar-nonce') ) die("Security check failed");
		$my_calendar_css_file = stripcslashes( $_POST['my_calendar_css_file'] );

		update_option('my_calendar_css_file',$my_calendar_css_file);
		$message = '<p><strong>'. __('New theme selected.', 'my-calendar') .'</strong></p>';	
		echo "<div id='message' class='updated fade'>$message</div>";	
	}
	
	$my_calendar_show_css = get_option('my_calendar_show_css');
	$my_calendar_css_file = get_option('my_calendar_css_file');
	$stylefile = mc_get_style_path($my_calendar_css_file);
	if ( $stylefile ) {
		$f = "";
		$f = fopen($stylefile, 'r');
		$file = fread($f, filesize($stylefile));
		$my_calendar_style = $file;
		fclose($f);
	} else {
		$my_calendar_style = __('Sorry. The file you are looking for doesn\'t appear to exist. Please check your file name and location!', 'my-calendar');
	}
	
?>
<div class="wrap">
<?php 
my_calendar_check_db();
?>
<h2><?php _e('My Calendar Styles','my-calendar'); ?></h2>
    <?php jd_show_support_box(); ?>
<div id="poststuff" class="jd-my-calendar">
<div class="postbox">
	<h3><?php _e('Calendar Style Settings','my-calendar'); ?></h3>
	<div class="inside">	
    <form method="post" action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-styles">
	<div><input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" /></div>
	<div><input type="hidden" value="true" name="mc_choose_style" /></div>
	<fieldset>	
	<p>
	<label for="my_calendar_css_file"><?php _e('Select My Calendar Theme','my-calendar'); ?></label>
	<select name="my_calendar_css_file" id="my_calendar_css_file">
<?php
	$custom_directory = $wp_plugin_dir . '/my-calendar-custom/styles/';
	$directory = dirname(__FILE__).'/styles/';
	
	$files = @my_csslist($custom_directory);
	if (!empty($files)) {
	echo "<optgroup label='".__('Your Custom Stylesheets')."'>\n";
	foreach ($files as $value) {
		$test = "mc_custom_".$value;
		$selected = (get_option('my_calendar_css_file') == $test )?" selected='selected'":"";
		echo "<option value='mc_custom_$value'$selected>$value</option>\n";
	}	
	echo "</optgroup>";
	} 
	$files = my_csslist($directory);
	echo "<optgroup label='".__('Installed Stylesheets','my-calendar')."'>\n";
	foreach ($files as $value) {
		$selected = (get_option('my_calendar_css_file') == $value )?" selected='selected'":"";
		echo "<option value='$value'$selected>$value</option>\n";
	}
	echo "</optgroup>";
?>
	</select>
	<input type="submit" name="save" class="button-primary" value="<?php _e('Choose Style','my-calendar'); ?> &raquo;" />
	</p>	
	</fieldset>
	</form>
	
    <form method="post" action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-styles">
	<div><input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('my-calendar-nonce'); ?>" /></div>
	<div><input type="hidden" value="true" name="mc_edit_style" />
	<input type="hidden" name="my_calendar_css_file" value="<?php echo get_option('my_calendar_css_file'); ?>" />
	</div>
	<?php 
	if ( get_option('my_calendar_file_permissions') == 'false' ) {
		echo "<div id='my_calendar_old_styles'>
		<p>".__('My Calendar was unable to update your CSS files during the upgrade. Please check your file permissions if you wish to edit your My Calendar styles. Your previously stored styles are below. This message and these styles will be deleted from the database when you successfully update your stylesheet.','my-calendar')."</p><pre>";
		echo stripcslashes(get_option('my_calendar_style')); 
		echo get_option('my_calendar_file_permission');
		echo "</pre>
		</div>";
	}
	?>
	<fieldset>
    <legend><?php _e('CSS Style Options','my-calendar'); ?></legend>

	<p>
	<label for="my_calendar_show_css"><?php _e('Apply CSS only on these pages (comma separated page IDs)','my-calendar'); ?></label> <input type="text" id="my_calendar_show_css" name="my_calendar_show_css" value="<?php echo $my_calendar_show_css; ?>" />
	</p> 	
	<p>
	<input type="checkbox" id="reset_styles" name="reset_styles" /> <label for="reset_styles"><?php _e('Reset the My Calendar stylesheet to the default','my-calendar'); ?></label> <input type="checkbox" id="use_styles" name="use_styles" <?php jd_cal_checkCheckbox('my_calendar_use_styles','true'); ?> /> <label for="use_styles"><?php _e('Disable My Calendar Stylesheet','my-calendar'); ?></label>
	</p>	
	<p>
	<label for="style"><?php _e('Edit the stylesheet for My Calendar','my-calendar'); ?></label><br /><textarea id="style" name="style" rows="30" cols="80"><?php echo $my_calendar_style; ?></textarea>
	</p>	
	<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save','my-calendar'); ?> &raquo;" />
	</p>	
	</fieldset>
  </form>
  </div>

 </div>
 </div>
 </div>
  <?php


}

?>