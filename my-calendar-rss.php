<?php
function my_calendar_rss() {
// establish template
	$template = "\n<item>
    <title>{title}</title>
    <link>{link}</link>
	<pubDate>{rssdate}</pubDate>
	<dc:creator>{author}</dc:creator>  	
    <description><![CDATA[
    &lt;div class=&quot;vevent&quot;&gt;
    &lt;h1 class=&quot;summary&quot;&gt;{rss_title}&lt;/h1&gt;
    &lt;p class=&quot;description&quot;&gt;{rss_description}&lt;/p&gt;
    &lt;p class=&quot;dtstart&quot; title=&quot;{ical_start}&quot;&gt;Begin: {time} on {date}&lt;/p&gt;
    &lt;p class=&quot;dtend&quot; title=&quot;{ical_end}&quot;&gt;End: {endtime} on {enddate}&lt;/p&gt;	
    &lt;div class=&quot;location&quot;&gt;{rss_hcard}&lt;/div&gt;
	{rss_link_title}
    &lt;/div&gt;
    ]]></description>
	<content:encoded>
    <![CDATA[
    <div class='vevent'>
    <h1 class='summary'>{title}</h1>
    <p class='description'>{description}</p>
    <p class='dtstart' title='{ical_start}'>Begin: {time} on {date}</p>
    <p class='dtend' title='{ical_end}'>End: {endtime} on {enddate}</p>	
    <div class='location'>{hcard}</div>
	{link_title}
    </div>
    ]]>	
	</content:encoded>
	<dc:format xmlns:dc='http://purl.org/dc/elements/1.1/'>text/html</dc:format>
	<dc:source xmlns:dc='http://purl.org/dc/elements/1.1/'>".home_url()."</dc:source>	
	{guid}
  </item>\n";
// add RSS headers
$output = '<?xml version="1.0" encoding="'.get_bloginfo('charset').'"?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	>
<channel>
  <title>'. get_bloginfo('name') .' Calendar</title>
  <link>'. home_url() .'</link>
  <description>'. get_bloginfo('description') . ': My Calendar Events</description>
  <language>'. get_bloginfo('language') .'</language>
  <managingEditor>'. get_bloginfo('admin_email') .' (' . get_bloginfo('name') . ' Admin)</managingEditor>
  <generator>My Calendar WordPress Plugin http://www.joedolson.com/articles/my-calendar/</generator>
  <lastBuildDate>'. mysql2date('D, d M Y H:i:s +0000', time()+$offset) .'</lastBuildDate>
  <atom:link href="'. mc_get_current_url() .'" rel="self" type="application/rss+xml" />';

	$events = mc_get_all_events( $category, 15 );
	if ( is_array( $events) ) {
		$output .=  "Yes it is";
	}
	$before = 0;
	$after = 15;
	$output .= mc_produce_upcoming_events( $events,$template,$before,$after,'rss' );
$output .= '</channel>
</rss>';
	header('Content-type: application/rss+xml');
	header("Pragma: no-cache");
	header("Expires: 0");	
echo $output;
}
?>