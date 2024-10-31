<?php
/*
Plugin Name: Quick Whois
Plugin URI: http://whois.fasterthemes.com
Description: Quick Whois Plugin Shows Whois information of the provided domain name.  You can do this using Widget/Shortcode.
Version: 1.0
Author: Faster Themes
Author URI: http://fasterthemes.com/
License: GPLv3 or later 
*/

/*defines variables*/

error_reporting(0);
$siteurl = get_option('siteurl');
define('WHOIS_FILE_PATH',dirname(__FILE__));
define('WHOIS_FOLDER', dirname(plugin_basename(__FILE__)));
define('WHOIS_DIR_NAME', basename(WHOIS_FILE_PATH));
define('WHOIS_URL', $siteurl.'/wp-content/plugins/' .WHOIS_FOLDER);
define(ADMIN_URL, admin_url());

function my_css_add(){
	wp_deregister_style( 'whois-css' );
		wp_register_style( 'whois-css', plugins_url( '/css/mywhois.css', __FILE__) , array() , false, false);
		wp_enqueue_style( 'whois-css' );
	}
add_action('wp_head','my_css_add');
function whois_settings(){
if (isset($_POST) && !empty($_POST))
{		
	$data = array();	
	$data=$_POST['choice'];		  
	$option_name = 'check-choice' ;

	if ( get_option( $option_name ) !== false ) 
	{
		// The option already exists, so we just update it.
		update_option( $option_name, $data );
	}
	else
	{
		// The option hasn't been added yet. We'll add it with $autoload set to 'no'.
		$deprecated = null;
		$autoload = 'no';
		add_option( $option_name, $data, $deprecated, $autoload );
	}

}

$options=array('Domain Name',
				'Registrar',
				'Whois Server',
				'Referral URL',
				'Updated Date',
				'Creation Date',
				'Expiration Date',
				'Registrant ID',
				'Registrant Name',
				'Registrant Organization',
				'Registrant Street', 
				'Registrant City',
				'Registrant State/Province',
				'Registrant Postal Code',
				'Registrant Country',
				'Registrant Phone',
				'Registrant Phone Ext ',
				'Registrant Fax', 
				'Registrant Fax Ext', 
				'Registrant Email'
				);	
				
$tempoptions = get_option('check-choice');  			
	echo '<div class=wrapper">';
	echo '<form method="post" action="" name="frm_whois_setting">
			<h2>Select  Your Choice for Domain Data</h2>
			<ul>';			  			
	foreach($options as $k=>$val)
	{
		$check='';	
		if(!empty($tempoptions))
		{			
			if (in_array($val, $tempoptions)) $check = "checked";
		}
		echo '<li><input type="checkbox" name="choice[]" value="'.$val.'" '.$check.' />'. $val. '</li>';
	}
		echo '</ul>
		<input type="submit" name="frm_submit" value="submit">
		</form></div>';
}
/*create short code*/
function whois_show() 
{
	$domain = $_POST['whois_name'];		
	if (!isset($domain))
	{	
	?>
	
	<div class="mywhois">
	  <form action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>"  name="frm_domain" method="post">
		<ul>
		  <li>
			<input type="text" value="" name="whois_name"  placeholder="Enter domain URL" >
		  </li>
		  <li>
			<input type="submit" value="Get Data" name="get_domain">
		  </li>
		</ul>
	  </form>
	</div>
	<?php } 
	else 
	{ 
		$data=array();			
		require_once('phpwhois/whois.main.php');
		$whois = new Whois();
		$query = $domain;
		$whois->deep_whois=TRUE; 
		$result = $whois->Lookup($query, false);
		 
		$regrinfo = $result['regrinfo'];
		$regyinfo = $result['regyinfo'];
		$owner = $regrinfo['owner'];
		$admin = $regrinfo['admin'];
		$tech = $regrinfo['tech'];
		$owneremail = $owner['email'];
		$techemail = $tech['email'];
		$adminemail = $admin['email'];		 
		$rawdata = $result['rawdata'];
			
		/* The following three fields are returned when deep_whois=FALSE (or TRUE) */
		$regyinfo = $result['regyinfo'];
		$registrar = $regyinfo['registrar'];
		$regurl = $regyinfo['referrer'];
			echo "<div id='mywhois'>";
	        echo "<a href=\"javascript:history.back()\">Go Back</a><br><br>";	
				$flag=0;				
				echo '<ul>';	
				   foreach($rawdata as $key=>$val){	
				   		    
					 $matches =  split(":", $val);								
							if(!empty($matches[1])){
						        echo '<li style="list-style:none;"> <a style=" text-decoration:none">' .$matches[0] .'</a> </strong> : &nbsp; '.$matches[1].'</li>';
								$flag=1;	
				            }
							else{	
									if($flag==0){ 
									 echo '<li style="list-style:none;"> <a style=" text-decoration:none">This  domain name not registered yet?</a></li>';
									}							
								break;
								}
							                          } 
		 	    echo '</ul>';
			}//if condition ends rowdata non empty				 
		
} // whois_show function ends
add_shortcode( 'quick_whois', 'whois_show' );

class whois_widget extends WP_Widget {
// constructor
	function whois_widget() {
                $widget_options = array(
	        'classname'=>'Quick Whois',
	        'description'=>__('This widget shows Whois information of the provided domain name.')
         	);
		parent::WP_Widget(false, $name = __('Quick Whois', 'whois_widget'),$widget_options);
	}
	// widget display
	function widget($args, $instance) {
		 extract( $args );
		echo $before_widget;
		echo  whois_show();	
		echo $after_widget;	 
	}
	
}
	// register widget
add_action('widgets_init', create_function('', 'return register_widget("whois_widget");'));
?>
