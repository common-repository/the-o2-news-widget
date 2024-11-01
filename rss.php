<?php
/*
  Plugin Name: O2 News Widget
  Description: Shows the latest O2 news on your website, automatically updated and kept fresh. Includes the functionality to tag links with your affiliate code.
  Author: O2 - Official Plugin
  Version: 0.1
  Author URI: http://www.o2.co.uk/
*/

  //Function for Parsing the Atom Feed From Feedburner
function parseRSS($xml,$aff,$tru_title,$tru_content)
{
	$tmp_title = $tru_title;
	$tmp_content = $tru_content;
	
	$cnt = 5;
    for($i=0; $i<$cnt; $i++)
    {
	
	$tmp_title = $tru_title;
	$tmp_content = $tru_content;
	
	$url 	= $xml->channel->item[$i]->cleanlink;
	$title 	= $xml->channel->item[$i]->title;
	$desc 	= $xml->channel->item[$i]->description;
	
	if (ord(substr($title,$tmp_title,1))>32 && ord(substr($title,$tmp_title-1,1))>32) {
		while(ord(substr($title,$tmp_title-1,1))>32) {
			$tmp_title--;
		}
	}
	if (ord(substr($desc,$tmp_content,1))>32 && ord(substr($desc,$tmp_content-1,1))>32) {
		while(ord(substr($desc,$tmp_content-1,1))>32) {
			$tmp_content--;
		}
	}
	
	//Shorten the title
	$title = substr($title,0,$tmp_title);
	//Shorten the description
	$desc = substr($desc,0,$tmp_content);
 
	echo '<div class="alpha"><a style="margin-bottom: 10px;" href="'.$url.'" onclick="location.href=\''.$aff.$url.'\'; return false;">'.$title.'</a><br /><div class="rss-desc">'.$desc.'...</div></div>';
    }
}


  
  //Widget Content Creation
  function o2rss_content(){ 
   
  
$options = get_option("widget_o2rss");
  if (!is_array( $options ))
{
$options = array(
      'aff_id' => '',
	  'truncate_title' => '',
	  'truncate_content' => '',
	  'cache_content' => 'no'
      );
  }

  
  //Check for caching
  if($options['cache_content'] == 'yes') {
	  
	  //Check the date/time
	  $current_date = date("Y-m-d g:i a");
	  //Change the date to Seconds Based
	  $current_time = time($current_date);
	  $cache_date = $options['cache_date'];
  
	  //if the content is too old refresh it
	  if($current_date > $cache_date) {
		  $o2_feed = curl_init("http://feeds.feedburner.com/o2blog");
          curl_setopt($o2_feed, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($o2_feed, CURLOPT_HEADER, 0);
          $cached_o2_data = curl_exec($o2_feed);
          $cached_o2_data = str_ireplace("feedburner:origLink", "cleanlink", $cached_o2_data);
		  curl_close($o2_feed);

		  //Add 12 hours to current time
		  $newtime = $current_time + 720 * 60;
		  
		  $options["cache_object"] = $cached_o2_data;
		  $options["cache_date"] = $newtime;
		  update_option("widget_o2rss", $options);
	  }	  
	  //return the data	  
	  $o2_data = $options["cache_object"];
	  $test_data = 'cached';
  } else {
    //Grab RSS Feed and return it
  $o2_feed = curl_init("http://feeds.feedburner.com/o2blog");
  curl_setopt($o2_feed, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($o2_feed, CURLOPT_HEADER, 0);
  $o2_data = curl_exec($o2_feed);
  $o2_data = str_ireplace("feedburner:origLink", "cleanlink", $o2_data);
   curl_close($o2_feed);
  }
   
   //Parse it to get what we want
   $o2_doc = new SimpleXmlElement($o2_data, LIBXML_NOCDATA);
  
if ($options['aff_id']) { $affinfo = 'http://postpaymobiles.at/'.$options['aff_id'].'?DURL='; } else { $affinfo = ''; }
$trunc_title = $options['title_chars'];
$trunc_content = $options['desc_chars'];
parseRSS($o2_doc, $affinfo, $trunc_title, $trunc_content);

}//end widget content creation
  
  
  //Widget Construction
 function widget_o2rss($args) {
	 //grab widget options to customize the style
	 $options = get_option("widget_o2rss");
	 if($options['theme'] == 'dark') {
		 $style_class = 'o2_rss_dark';
	 }
	 if($options['theme'] == 'light') {
		 $style_class = 'o2_rss_light';
	 }
	 
	  extract($args);
	if ($options['aff_id']) { $affinfo = 'http://postpaymobiles.at/'.$options['aff_id'].'?DURL='; } else { $affinfo = ''; }
  echo $before_widget;
  echo '<div class="'.$style_class.'">';
  echo '<div class="widget-title"><a href="http://www.o2.co.uk/" onclick="location.href=\''.$affinfo.'http://www.o2.co.uk/\'; return false;" title="O2: We\'re better connected!" >O2</a></div><div class="widget-blogtitle"><a href="http://blog.o2.co.uk/" onclick="location.href=\''.$affinfo.'http://blog.o2.co.uk/\'; return false;" title="O2 blog feed" >O2 blog feed</a></div>';
  echo o2rss_content();
  echo '<div class="better-connected">O2 - We\'re better connected</div>';
  echo '</div>';
  echo $after_widget;
} 
  
  
//O2 RSS Control Module
function o2rss_control() { 
  $options = get_option("widget_o2rss");
  if (!is_array( $options ))
{
$options = array(
      );
  }
  if($options['theme'] == 'light') { $light = 'selected'; } else { $dark = 'selected'; };
    if($options['cache_content'] == 'yes') {
	  $cache = "checked";
  } else { $cache = ""; }
  ?>
	  <p>
    <label>Buy.at Affiliate Handle: <br /></label><input type="text" id="aff_id" name="aff_id" value="<?php echo $options['aff_id'];?>"><br />
    <label>Theme: <br /></label><select id="rss_theme_select" name="rss_theme_select" >
    <option value="light" <?php echo $light; ?>>Light</option>
    <option value="dark" <?php echo $dark; ?>>Dark</option></select><br />
    <label># of Title Characters:<br /> </label><input type="text" id="title_chars" name="title_chars" value="<?php echo $options['title_chars'];?>"><br />
    <label># of Desc Characters:<br /> </label><input type="text" id="desc_chars" name="desc_chars" value="<?php echo $options['desc_chars'];?>"><br />
    <label>Turn on 12 hour caching: </label><input type="checkbox" id="cache_content" name="cache_content" <?php echo $cache; ?> value="cache_content" /><br />

    <input type="hidden" id="o2_rss_sub" name="o2_rss_sub" value="1" />
  </p>
<?php 
 if ($_POST['o2_rss_sub'])
{	
if($_POST['cache_content'] == 'cache_content') {
	$cacheit = 'yes';
} else {
	$cacheit = 'no';
}
    $options['theme'] = $_POST['rss_theme_select'];
    $options['aff_id'] = ($_POST['aff_id']);
	$options['title_chars'] = ($_POST['title_chars']);
	$options['desc_chars'] = ($_POST['desc_chars']);
	$options['cache_content'] = $cacheit;
	
    update_option("widget_o2rss", $options);
  }
}
  
  
  //Wrap it up in a bow and fire away
  function RSS_sidebar_init() {
  wp_register_sidebar_widget('o2_rss_widget', 'O2 News Feed', 'widget_o2rss');
  wp_register_widget_control('o2_rss_widget', 'O2 News Feed', 'o2rss_control');
  }
  
  add_action("widgets_init", "RSS_sidebar_init");

	// styling
	$o2_rss_style_url = str_replace(' ', '%20', plugins_url('style.css',__FILE__));
    wp_enqueue_style('o2_rss_style_sheet', $o2_rss_style_url);

	global $wp_styles;
	$o2_rss_style_ie_url = str_replace(' ', '%20', plugins_url('ie.css',__FILE__));
	wp_enqueue_style('o2_rss_style_ie_sheet', $o2_rss_style_ie_url);
    $wp_styles->add_data('o2_rss_style_ie_sheet', 'conditional', 'lt IE 8');
  ?>