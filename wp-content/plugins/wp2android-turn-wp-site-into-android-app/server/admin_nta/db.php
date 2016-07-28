 <?php 
  function lacz_bd()
  {
     $wynik = new mysqli("localhost", "wpadmin", "hik21mah", "wordpress");
     if (!$wynik)
        throw new Exception("Error...Please try again later...");
     else
        return $wynik;
  }
  $base_url = "http://ec2-52-90-170-121.compute-1.amazonaws.com";
  $plugin_url = "http://ec2-52-90-170-121.compute-1.amazonaws.com/wp-content/plugins/wp2android-turn-wp-site-into-android-app/";
      $table_prefix = "wp_";
   