<?php
function my_calendar_help() {
global $wp_plugin_dir;
?>

<div class="wrap">
<h2><?php _e('How to use My Calendar','my-calendar'); ?></h2>
<?php jd_show_support_box(); ?>
<div id="poststuff" class="jd-my-calendar">

<div class="postbox">
	<h3><?php _e('Shortcode Syntax','my-calendar'); ?></h3>
	<div class="inside">	
<p>
<?php _e('These shortcodes can be used in Posts, Pages, or in text widgets.','my-calendar'); ?>
</p>
<h4><?php _e('Main Calendar Shortcode (List or Grid, Weekly or Monthly view)','my-calendar'); ?></h4>
<p><code>[my_calendar]</code><br />
<?php _e('This basic shortcode will show the calendar on a post or page including all categories and the category key, in a traditional month-by-month format.','my-calendar'); ?>
</p>
<p><code>[my_calendar category="General|Other" format="list" showkey="yes" shownav="yes" toggle="no" time="week"]</code><br />
<?php _e('The shortcode supports six attributes, <code>category</code>, <code>format</code>, <code>showkey</code>, <code>shownav</code>, <code>toggle</code>, and <code>time</code>. There two alternate options for <code>format</code>: <code>list</code>, which will show the calendar in a list format, skipping dates without any events, and <code>mini</code>, which will display the calendar in a form more suitable to being displayed in smaller spaces, such as the sidebar. The <code>category</code> attribute requires either the name of or ID number one of your event categories (the name is case-sensitive). This will show a calendar only including events in that category. Multiple categories can be specified by separating the category names or IDs using either the pipe character (<code>|</code>) or a comma. Setting <code>showkey</code> to <code>no</code> will prevent the category key from being displayed &mdash; this can be useful with single-category output. Setting <code>shownav</code> to <code>no</code> will disable the Previous/Next links. Setting <code>toggle</code> to yes will show a link to switch between the list and grid views. The <code>time</code> shortcode switches between a weekly view and the default monthly view.','my-calendar'); ?>
</p>
<p>
<em><?php _e('The main My Calendar short code can be generated from a button in your post and page editor. The mini calendar can also be accessed and configured as a widget.','my-calendar'); ?></em>
</p>
<h4><?php _e('Additional Calendar Views (Upcoming events, today\'s events)','my-calendar'); ?></h4>
<p><code>[my_calendar_upcoming before="3" after="3" type="event" fallback="No events coming up!" category="General" template="{title} {date}" order="asc"]</code><br />
	<?php _e('This shortcode displays the output of the Upcoming Events widget. The <code>before</code> and <code>after</code> attributes should be numbers; the <code>type</code> attribute can be either "event" or "days", and the <code>category</code> attribute works the same way as the category attribute on the main calendar shortcode. Templates work using the template codes listed below. <code>fallback</code> provides text in case there are no events meeting your criteria. Order provides a sort order for the events list &ndash; either ascending (<code>asc</code>) or descending (<code>desc</code>).','my-calendar'); ?>
</p>
<p><code>[my_calendar_today category="" fallback="Nothing today!" template="{title} {date}"]</code><br />
	<?php _e('Predictably enough, this shortcode displays the output of the Today\'s Events widget, with three configurable attributes: category, template and fallback text.','my-calendar'); ?>
</p>
<p>
<em><?php _e('Both Upcoming Events and Today\'s Events can also be configured using widgets.','my-calendar'); ?></em>
</p>

<h4><?php _e('Supplement Features (Locations filter, Categories filter)','my-calendar'); ?></h4>

<p><code>[my_calendar_locations show="list" type="saved" datatype="name"]</code><br />
	<?php _e('This shortcode produces a list of event locations, either as a list of links or as a select dropdown form. The <code>show</code> attribute can either be <code>list</code> or <code>form</code>, <code>type</code> is either <code>saved</code> (to show items from your stored locations), or <code>custom</code> (to show the options configured in your user settings). <code>datatype</code> must be the type of data your limits are choosing from: <code>name</code> (business name), <code>city</code>, <code>state</code>, <code>country</code>, <code>zip</code> (postal code), or <code>region</code>.','my-calendar'); ?>
</p>
<p><code>[my_calendar_categories show="list"]</code><br />
	<?php _e('This shortcode produces a list of event categories, either as a list of links or as a select dropdown form. The <code>show</code> attribute can either be <code>list</code> or <code>form</code>.','my-calendar'); ?>
</p>	
</div>
</div>
<div id="icons">
<div class="postbox">
<h3><?php _e('Category Icons','my-calendar'); ?></h3>
	<div class="inside">	
<p>
<?php _e('My Calendar is designed to manage multiple calendars. The basis for these calendars are categories; you can easily setup a calendar page which includes all categories, or you can dedicate separate pages to calendars in each category. For an example, this might be useful for you in managing the tour calendars for multiple bands; event calendars for a variety of locations, etc.','my-calendar'); ?>
</p>
<p>
<?php _e('The pre-installed category icons may not be especially useful for your needs or design. I\'m assuming that you\'re going to upload your own icons -- all you need to do is upload them to the plugin\'s icons folder, and they\'ll be available for immediate use, or place them in a folder at "my-calendar-custom" to avoid having them overwritten by upgrades.','my-calendar'); ?> <?php _e('Your icons folder is:','my-calendar'); ?> <code><?php echo $wp_plugin_dir; ?>/my-calendar/icons/</code> <?php _e('You can alternately place icons in:','my-calendar'); ?> <code><?php echo $wp_plugin_dir; ?>/my-calendar-custom/</code>
</p>
</div>
</div>
</div>

<div id="customstyles">
<div class="postbox">
<h3><?php _e('Custom Styles','my-calendar'); ?></h3>
	<div class="inside">	
<p>
<?php _e('My Calendar comes with four basic stylesheets. My Calendar will retain changes to these basic stylesheets on upgrade, but if you want to add an entirely new stylesheet, you may wish to store it in the My Calendar custom styles directory.','my-calendar'); ?>
</p>
<ul>
<li><?php _e('Your stylesheet directory is','my-calendar'); ?> <code><?php echo $wp_plugin_dir; ?>/my-calendar/styles/</code></li>
<li><?php _e('Your custom stylesheets directory is','my-calendar'); ?> <code><?php echo $wp_plugin_dir; ?>/my-calendar-custom/styles/</code></li>
</ul>
</div>
</div>
</div>

<div id="templates">
<div class="postbox">
<h3 id="template"><?php _e('Widget Templating','my-calendar'); ?></h3>
	<div class="inside">
<p>
<?php _e('These codes are available in calendar widgets, email notifications, and event titles.','my-calendar'); ?>
</p>
<dl>
<dt><code>{category}</code></dt>
<dd><?php _e('Displays the name of the category the event is in.','my-calendar'); ?></dd>

<dt><code>{title}</code></dt>
<dd><?php _e('Displays the title of the event.','my-calendar'); ?></dd>

<dt><code>{time}</code></dt>
<dd><?php _e('Displays the start time for the event.','my-calendar'); ?></dd>

<dt><code>{usertime}</code></dt>
<dd><?php _e('Displays the start time for the event adjusted to the current user\'s time zone settings. Blank output if user settings are disabled or the user has not selected a preferred time zone.','my-calendar'); ?></dd>

<dt><code>{date}</code></dt>
<dd><?php _e('Displays the date on which the event begins.','my-calendar'); ?></dd>

<dt><code>{enddate}</code></dt>
<dd><?php _e('Displays the date on which the event ends.','my-calendar'); ?></dd>

<dt><code>{endtime}</code></dt>
<dd><?php _e('Displays the time at which the event ends.','my-calendar'); ?></dd>

<dt><code>{author}</code></dt>
<dd><?php _e('Displays the WordPress author who posted the event.','my-calendar'); ?></dd>

<dt><code>{host}</code></dt>
<dd><?php _e('Displays the name of the person assigned as host for the event.','my-calendar'); ?></dd>

<dt><code>{link}</code></dt>
<dd><?php _e('Displays the URL provided for the event.','my-calendar'); ?></dd>

<dt><code>{details}</code></dt>
<dd><?php _e('Provides a link to an auto-generated page containing all information on the given event.','my-calendar'); ?> <strong><?php _e('Requires that the site URL has been provided on the Settings page','my-calendar'); ?></strong>

<dt><code>{description}</code></dt>
<dd><?php _e('Displays the description of the event.','my-calendar'); ?></dd>

<dt><code>{link_title}</code></dt>
<dd><?php _e('Displays title of the event as a link if a URL is present, or the title alone if no URL is available.','my-calendar'); ?></dd>

<dt><code>{location}</code></dt>
<dd><?php _e('Displays the name of the location of the event.','my-calendar'); ?></dd>

<dt><code>{street}</code></dt>
<dd><?php _e('Displays the first line of the site address.','my-calendar'); ?></dd>

<dt><code>{street2}</code></dt>
<dd><?php _e('Displays the second line of the site address.','my-calendar'); ?></dd>

<dt><code>{city}</code></dt>
<dd><?php _e('Displays the city for the event.','my-calendar'); ?></dd>

<dt><code>{state}</code></dt>
<dd><?php _e('Displays the state for the event.','my-calendar'); ?></dd>

<dt><code>{postcode}</code></dt>
<dd><?php _e('Displays the postcode for the event.','my-calendar'); ?></dd>

<dt><code>{country}</code></dt>
<dd><?php _e('Displays the country for the event location.','my-calendar'); ?></dd>

<dt><code>{hcard}</code></dt>
<dd><?php _e('Displays the event address in <a href="http://microformats.org/wiki/hcard">hcard</a> format.','my-calendar'); ?></dd>

<dt><code>{link_map}</code></dt>
<dd><?php _e('Displays a link to a Google Map of the event, if sufficient address information is available. If not, will be empty.','my-calendar'); ?></dd>

<dt><code>{event_open}</code></dt>
<dd><?php _e('Displays text indicating whether registration for the event is currently open or closed; displays nothing if that choice is selected in the event.','my-calendar'); ?></dd>

<dt><code>{shortdesc}</code></dt>
<dd><?php _e('Displays the short version of the event description.','my-calendar'); ?></dd>

<dt><code>{event_status}</code></dt>
<dd><?php _e('Displays the current status of the event: either "Published" or "Reserved" - primary used in email templates.','my-calendar'); ?></dd>

<dt><code>{icon}</code></dt>
<dd><?php _e('Produces the address of the current event\'s category icon.','my-calendar'); ?></dd>

<dt><code>{color}</code></dt>
<dd><?php _e('Produces the hex code for the current event\'s category color.','my-calendar'); ?></dd>

</dl>
			</div>
		</div>
	</div>
	
<div id="notes">
<div class="postbox">
<h3 id="template"><?php _e('Helpful Information','my-calendar'); ?></h3>
	<div class="inside">
<p>
<?php _e('<strong>Uninstalling the plugin</strong>: Although the WordPress standard and expectation is for plug-ins to delete any custom database tables when they\'re uninstalled, My Calendar <em>does not do this</em>. This was a conscious decision on my part -- the data stored in your My Calendar tables is yours; with the sole exception of the "General" category, you added every piece of it yourself. As such, I feel it would be a major disservice to you to delete this information if you uninstall the plug-in. As a result, if you wish to get rid of the plug-in completely, you\'ll need to remove those tables yourself. All your My Calendar settings will be deleted, however.','my-calendar'); ?>
</p>
<p>
<?php _e('<strong>Donations</strong>: I appreciate anything you can give. $2 may not seem like much, but it can really add up when thousands of people are using the software. Please note that I am not a non-profit organization, and your gifts are not tax deductible. Thank you!','my-calendar'); ?>
</p>
	</div>
</div>
</div>

</div>
</div>
<?php } ?>