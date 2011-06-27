<?php
function my_calendar_ical() {
global $mc_version;
// establish template
	$template = "\nBEGIN:VEVENT
UID:{guid}
LOCATION:{ical_location}
SUMMARY:{title}
DTSTAMP:{ical_start}
ORGANIZER;CN={host}:MAILTO:{host_email}
DTSTART:{ical_start}
DTEND:{ical_end}
URL;VALUE=URI:{link}
DESCRIPTION;ENCODING=QUOTED-PRINTABLE:{ical_desc}
END:VEVENT";
// add ICAL headers
$output = 'BEGIN:VCALENDAR
VERSION:2.0
METHOD:PUBLISH
X-WR-CALNAME: '. get_bloginfo('name') .' Calendar
PRODID:-//Accessible Web Design//My Calendar//http://www.mywpcal.com//v'.$mc_version.'//EN';
	
	$events = mc_get_all_events($category);
	$before = 0;
	$after = 15;
	$output .= mc_produce_upcoming_events( $events,$template,$before,$after,'ical' );
$output .= "\nEND:VCALENDAR";
$output = preg_replace("~(?<!\r)\n~","\r\n",$output);
	header("Content-Type: text/calendar");
	header("Pragma: no-cache");
	header("Expires: 0");		
	header("Content-Disposition: inline; filename=my-calendar.ics");
	echo $output;
}
?>