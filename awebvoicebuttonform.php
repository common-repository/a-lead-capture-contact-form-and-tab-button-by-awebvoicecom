<?php
/**
 * Plugin Name: Contact Form by AWebVoice.com
 * Plugin URI: http://www.awebvoice.com/
 * Description: Contact button and form by AWebVoice.com
 * Author: AWebVoice.com
 * Version: 3.0
 * Author URI: http://www.awebvoice.com/
 */

function load_button_code() {
	$awebvoice_data = unserialize(get_option("awebvoice_data"));
	if (count($awebvoice_data["button"]) == 0) return;
	echo "<script type='text/javascript'>var _aweb = {";
	$count = 0;
	foreach ($awebvoice_data["button"] as $key => $value) {
		$count++;
		echo $key.': "'. $value.'"';
		if ($count < count($awebvoice_data["button"])) echo ",";
	}
	echo "}; (function() {var cms = document.createElement('script'); cms.type = 'text/javascript'; cms.async = true; cms.src = '".$awebvoice_data["button_src"]."'; var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(cms, s);})();</script>";
}

add_action('init', 'awebvoice_assets');

function awebvoice_assets() {
	$awebvoice_data = unserialize(get_option("awebvoice_data"));
	if (function_exists('wp_footer')) {
		if (!is_admin()){
			if (!$_POST['awebvoice_data']) {
				if (isset($awebvoice_data["form_id"]) && $awebvoice_data["sticky_button"] == "true") {
					add_action( 'wp_head', 'load_button_code' ); 
				}
			}
		}
	}
	elseif (function_exists('wp_head')) {
		if (!is_admin()){
			if (!$_POST['awebvoice_data']) {
				if (isset($awebvoice_data["form_id"]) && $awebvoice_data["sticky_button"] == "true") {
					add_action( 'wp_head', 'load_button_code' ); 
				}
			}
		}
	}
}

// Adding Admin Menu
add_action('admin_menu', 'awebvoice_plugin_menu');

function awebvoice_plugin_menu() {
	add_options_page('AWebVoice Settings', 'AWebVoice Form', 10, __FILE__, 'awebvoice_setup');
}


function awebvoice_setup() {
	$awebvoice_data = unserialize(get_option("awebvoice_data"));

	if ($_POST) {
		$awebvoice_data = unserialize(stripslashes($_POST["awebvoice_data"]));
		$awebvoice_action = $awebvoice_data["action"];
		unset($awebvoice_data["action"]);
		
		if ($awebvoice_action == "save_account") {  // save initial email and set up contact page 
			$old_data = unserialize(get_option('awebvoice_data'));
			if ($old_data["wp_page_id"]) wp_delete_post($old_data["wp_page_id"]);
			if ($awebvoice_data["publish_page"] == "true") {
				if (!get_post($awebvoice_data["wp_page_id"])) {
					$awebvoice_page_id = wp_insert_post(array(
						'post_status' => 'publish',
						'post_type' => 'page',
						'post_name' => $awebvoice_data["button"]["label"],
						'post_title' => $awebvoice_data["button"]["label"],
						'comment_status' => 'closed',
						'post_content' => '<iframe src="'.$awebvoice_data["embed_src"].'" frameborder="0" scrolling="no" allowtransparency="true" style="height: '.$awebvoice_data["embed_height"].'px; width: '.$awebvoice_data["embed_width"].'px;"></iframe>'
					));
					$awebvoice_data["wp_page_id"] = $awebvoice_page_id;
				}
			}
			update_option("awebvoice_data", serialize($awebvoice_data));
		}
		elseif ($awebvoice_action == "save_settings") { // save just configuration settings
			if (get_option('awebvoice_data')) {
				$awebvoice_data = array_merge(unserialize(get_option('awebvoice_data')), $awebvoice_data);
			}

			if ($awebvoice_data["publish_page"] == "true") {
				if (!get_post($awebvoice_data["wp_page_id"])) {
					$awebvoice_page_id = wp_insert_post(array(
						'post_status' => 'publish',
						'post_type' => 'page',
						'post_name' => $awebvoice_data["button"]["label"],
						'post_title' => $awebvoice_data["button"]["label"],
						'comment_status' => 'closed',
						'post_content' => '<iframe src="'.$awebvoice_data["embed_src"].'" frameborder="0" scrolling="no" allowtransparency="true" style="height: '.$awebvoice_data["embed_height"].'px; width: '.$awebvoice_data["embed_width"].'px;"></iframe>'
					));
					$awebvoice_data["wp_page_id"] = $awebvoice_page_id;
				} else {
					$awebvoice_page_id = wp_update_post(array(
						'ID' => $awebvoice_data["wp_page_id"],
						'post_status' => 'publish',
						'post_type' => 'page',
						'post_name' => $awebvoice_data["button"]["label"],
						'post_title' => $awebvoice_data["button"]["label"],
						'comment_status' => 'closed',
						'post_content' => '<iframe src="'.$awebvoice_data["embed_src"].'" frameborder="0" scrolling="no" allowtransparency="true" style="height: '.$awebvoice_data["embed_height"].'px; width: '.$awebvoice_data["embed_width"].'px;"></iframe>'
					));
					$awebvoice_data["wp_page_id"] = $awebvoice_page_id;
				}
			} else {
				wp_delete_post($awebvoice_data["wp_page_id"]);
				unset($awebvoice_data["wp_page_id"]);
			}
			update_option("awebvoice_data", serialize($awebvoice_data));
		}
	}
?>
<script type="text/javascript" src="http://www.awebvoice.com/jscripts/jquery.ba-postmessage.js"></script>
<script type="text/javascript">

jQuery(function(){
  jQuery.receiveMessage(
  function(e){
	jQuery('#awebvoice_data').val(e.data);
	jQuery("#awebvoice_form").submit();	
  },
  'http://www.awebvoice.com'
);

});

</script>


<form method="post" id="awebvoice_form" action="">
	<input type="hidden" id="awebvoice_data" name="awebvoice_data" value="" />
</form>

<iframe id="awebvoice_frame" src="http://www.awebvoice.com/wordpress?l=<?php echo get_bloginfo('wpurl')?>&<?php echo http_build_query($awebvoice_data) ?>" frameborder="0" scrolling="no" allowtransparency="true" style="width: 720px; height: 650px; margin: 15px 15px 0 5px;"></iframe>
<?php  
}

register_uninstall_hook(__FILE__, 'awebvoice_uninstall');
register_deactivation_hook(__FILE__, 'awebvoice_uninstall');
	
function awebvoice_uninstall() {
	$old_data = unserialize(get_option('awebvoice_data'));
	if ($old_data["wp_page_id"]) wp_delete_post($old_data["wp_page_id"]);
	update_option("awebvoice_data", "");
}
?>