<?php function dataxnam_base(){  $iba = new mysqli("localhost", "wpadmin","hik21mah" ,"wordpress" );  if (!$iba)  throw new Exception("Error...");  else  return $iba;  }  $base_url = "http://ec2-52-90-170-121.compute-1.amazonaws.com";  $plugin_url = "http://ec2-52-90-170-121.compute-1.amazonaws.com/wp-content/plugins/wp2android-turn-wp-site-into-android-app/";  $table_prefix = "wp_";  ?> 