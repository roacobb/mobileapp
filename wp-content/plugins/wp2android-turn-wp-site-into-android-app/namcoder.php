<?php
/*
Plugin Name: Wp2Android - webapp builder
Plugin URI: http://wordpress-mobile-app-plugin.xyz
Description: Go Wordpress Admin >> Setting >> Turn wp to Android - to build your app
Version: 1.1.4
Author: Wp2android 
Author URI: http://http://wp2android.wps.edu.vn
*/
// echo "<div class='updated'>Test Plugin Notice</div>";

register_activation_hook(__FILE__, 'myxnamewpa1_activation');
register_deactivation_hook(__FILE__, 'myxnamewpa1_deactivation');

function myxnamewpa1_activation() {
  // use wordpress dbDelta to query sql and import sql file
  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();
  $filename = plugin_dir_path( __FILE__ ).'sql.sql';
  $mysql_host = DB_HOST;
  $mysql_username = DB_USER;
  $mysql_password = DB_PASSWORD;
  $mysql_database = DB_NAME;
  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  $templine = '';
  $lines = file($filename); //read
  // importing
  foreach ($lines as $line)
  {
    if (substr($line, 0, 2) == '--' || $line == '')
        continue;
    $templine .= $line;
    if (substr(trim($line), -1, 1) == ';')
    {
        dbDelta( $templine );
        $templine = '';
    }
  }

  $content = '<?php';
  $content .= ' function dataxnam_base(){ ';
  $content .= ' $iba = new mysqli("'.DB_HOST.'", "'.DB_USER.'","'.DB_PASSWORD.'" ,"'.DB_NAME.'" ); ';
  $content .= ' if (!$iba) ';
  $content .= ' throw new Exception("Error..."); ';
  $content .= ' else ';
  $content .= ' return $iba; ';
  $content .= ' } ';
  $content .= ' $base_url = "'.get_bloginfo('wpurl').'"; ';
  $content .= ' $plugin_url = "'.plugin_dir_url( __FILE__ ).'"; ';
  $content .= ' $table_prefix = "'.$wpdb->prefix.'"; ';
  $content .= ' ?> ';

  // write into function.php
  $myfile = fopen(plugin_dir_path( __FILE__ )."server/function.php", "w") or die("Unable to open file!");
  fwrite($myfile, $content);
  fclose($myfile);

  $db2 =' <?php 
  function lacz_bd()
  {
     $wynik = new mysqli("'.DB_HOST.'", "'.DB_USER.'", "'.DB_PASSWORD.'", "'.DB_NAME.'");
     if (!$wynik)
        throw new Exception("Error...Please try again later...");
     else
        return $wynik;
  }
  $base_url = "'.get_bloginfo('wpurl').'";
  $plugin_url = "'.plugin_dir_url( __FILE__ ).'";
      $table_prefix = "'.$wpdb->prefix.'";
   ';

  ;

  // write into admin_nta/db.php

  $myfile2 = fopen(plugin_dir_path( __FILE__ )."server/admin_nta/db.php", "w") or die("Unable to open file!");
  fwrite($myfile2, $db2);
  fclose($myfile2);


  // write for android html

  $js = 'var url = "'.plugin_dir_url( __FILE__ ).'";';
 
  $myfile3 = fopen(plugin_dir_path( __FILE__ )."android/base_url.js", "w") or die("Unable to open file!");
  fwrite($myfile3, $js);
  fclose($myfile3);

}


function Zip($source, $destination){
    if (!extension_loaded('zip') || !file_exists($source)) {
        return false;
    }

    $zip = new ZipArchive();
    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
        return false;
    }

    $source = str_replace('\\', '/', realpath($source));

    if (is_dir($source) === true)
    {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file)
        {
            $file = str_replace('\\', '/', $file);

            // Ignore "." and ".." folders
            if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
                continue;

            $file = realpath($file);

            if (is_dir($file) === true)
            {
                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
            }
            else if (is_file($file) === true)
            {
                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
            }
        }
    }
    else if (is_file($source) === true)
    {
        $zip->addFromString(basename($source), file_get_contents($source));
    }

    return $zip->close();
}





add_action('admin_menu', 'register_my_custom_submenu_page');
function register_my_custom_submenu_page() {
  add_submenu_page( 'options-general.php', 'Wp2Android', 'Wp2Android', 'manage_options', 'my-custom-submenu-page', 'my_custom_submenu_page_callback' );
}

function my_custom_submenu_page_callback() {

  

  if(isset($_POST['createzip'])){
    Zip(plugin_dir_path( __FILE__ ).'android', plugin_dir_path( __FILE__ ).'android-'.date('d-m-Y').'.zip');
    echo '
    <div id="setting-error-settings_updated" class="updated settings-error"> 
<p><strong>Create success. <a href="'. plugin_dir_url( __FILE__ ).'android-'.date('d-m-Y').'.zip">Click here to download</a></strong></p></div>
    ';
  }




?>


  <div class="wrap"><div id="icon-tools" class="icon32"></div>
<h2>1. Wp2Android </h2>

<h3>Step 1. Create APP code </h3><br>
<form action="" method="post">
      <input type="submit" class="btn" name="createzip" value="Create APK File">
    </form>
Click here to create App code for your website. <br>
<h3>Step 2. Download .Zip File</h3><br>
Download .Zip File, upload to  <a  target="_blank" href="https://build.phonegap.com/">Phonegap Free APP build tool</a> . <br>
Demo username: wp2android.xyz@gmail.com<br>
Demo password: Webapp@2016<br>

This user is for test only, please don't change password. Register yourself for better security <br>
<h3>Step 3. Done</h3><br>
Then you get your app, ready to upload to Google Play.<br>

Demo App using this plugin:<br>

<a target="_blank" href="https://play.google.com/store/apps/details?id=wps.edu.vn.Wp2Android">Google Play Store</a><br>
Wordpress site of this app: <a target="_blank" href="http://wordpress2app.com/kinhthanh">Site demo</a> <br>


  Free version:<br>
- Get 2 lastest post from category.<br>
<br>
Pro version:<br>
- Get all category<br>
- Get all post from your wordpress<br>
- Search Function <br>
- Abmod Ready    <br>

<a href="http://wordpress2app.com"  target="_blank">	GO PRO NOW - only 35usd/ lifetime</a><br>
</p>


<h2>3.Video Tutorial how to setup</h2>

<iframe width="560" height="315" src="https://www.youtube.com/embed/IVCSHgvE-7Y" frameborder="0" allowfullscreen></iframe>
<br>
Change your app icon, name, etc<br>
<iframe width="560" height="315" src="https://www.youtube.com/embed/MvRTA98Pphk" frameborder="0" allowfullscreen></iframe><br>

  </div>
<?php
}