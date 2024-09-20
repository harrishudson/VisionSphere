<?php

 $GLOBALS['CACHE_LOCATION'] = "./cache";
 $GLOBALS['CACHE_TIMEOUT'] = 10 * 60;
 $GLOBALS['CACHE_FILENAME_SUFFIX'] = "json";  // Suffix must not have any spaces
 
 // Make cache file name (note - there must be no spaces in suffix)
 function get_cache_filename($url) {
  $prefix = base64_encode($url);
  $filename = $GLOBALS['CACHE_LOCATION']."/{$prefix}.{$GLOBALS['CACHE_FILENAME_SUFFIX']}";
  return $filename;
 }

 function cache_cleanup() {
  $path = $GLOBALS['CACHE_LOCATION'];
  if ($handle = opendir($path)) {
   while (false !== ($file = readdir($handle))) { 
    if (pathinfo($file, PATHINFO_EXTENSION) == $GLOBALS['CACHE_FILENAME_SUFFIX']) {
     $filelastmodified = filemtime("{$path}/{$file}");
     if((time() - $filelastmodified) > $GLOBALS['CACHE_TIMEOUT']) {
      unlink("{$path}/{$file}");
      }
     }
    }
   closedir($handle); 
  }  
 }

 function weather_request($bom_id, $bom_wmo) {
  if (is_null($bom_id) || $bom_id == '')
   return null;
  if (is_null($bom_wmo) || $bom_wmo == '')
   return null;
  //$url = curl_init("http://www.bom.gov.au/fwo/".$bom_id."/".$bom_id.".".$bom_wmo.".json");
  $url = "http://www.bom.gov.au/fwo/{$bom_id}/{$bom_id}.{$bom_wmo}.json";
  $cache_file = get_cache_filename($url);
  $mode = '';
  if ((file_exists($cache_file)) && 
      (filemtime($cache_file) > (time() - $GLOBALS['CACHE_TIMEOUT']))) {
   // File is cached and still valid 
   // Don't bother refreshing, just use the file as-is.
   $mode = 'cache';
   $file = file_get_contents($cache_file);
  } else {
   // Our cache is out-of-date, so load the data from our remote server,
   // and also save it over our cache for next time.
   $mode = 'fetch';
   //$file = file_get_contents($url);
   $ch = curl_init($url);
   $useragent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.135 Safari/537.36';
   curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
   curl_setopt($ch, CURLOPT_REFERER, 'http://www.bom.gov.au/');
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   if( ($file = curl_exec($ch) ) === false) {
    //echo "curl fetch failure";
    return null;
   } else {
    file_put_contents($cache_file, $file, LOCK_EX);
   }
  }
  /* Perform some cache maintenance every so often */
  if (rand(0,100) < 4) {
   cache_cleanup();
  }
  return [ $file, $mode ];
 };

 function get_weather($bom_id, $bom_wmo) {
  if (is_null($bom_id) || $bom_id == '')
   return null;
  if (is_null($bom_wmo) || $bom_wmo == '')
   return null;
  $bom = weather_request($bom_id, $bom_wmo);
  if (is_null($bom)) {
   return null;
  }
  $bom_json = json_decode($bom[0], true);
  $myObj = json_decode('{}');
  $myObj->copyright = $bom_json["observations"]["notice"][0]["copyright"];
  $myObj->refresh_message = $bom_json["observations"]["header"][0]["refresh_message"];
  $myObj->name = $bom_json["observations"]["header"][0]["name"];
  $myObj->wind_spd_kmh = $bom_json["observations"]["data"][0]["wind_spd_kmh"];
  $myObj->gust_kmh = $bom_json["observations"]["data"][0]["gust_kmh"];
  $myObj->air_temp= $bom_json["observations"]["data"][0]["air_temp"];
  $myObj->mm_rain_since_9am= $bom_json["observations"]["data"][0]["rain_trace"];
  $myObj->local_date_time = $bom_json["observations"]["data"][0]["local_date_time"];
  $myObj->retrieval_mode = $bom[1];
  return $myObj;
 };

 // Testing
 //$weather = get_weather('IDN60801','94927');
 //echo json_encode($weather);

?>
