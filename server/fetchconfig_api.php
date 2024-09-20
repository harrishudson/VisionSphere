<?php

 include 'common.php';
 include 'weather.php';

 function validate_inputs() {
  if (($_POST['CAM_LABEL'] == "") or (is_null($_POST['CAM_LABEL']))) {
   return false;
  }
  return true;
 }

 function retrieve_weather($bom_id, $bom_wmo) {
  if (is_null($bom_id) or ($bom_id == "")) {
   return json_decode('{}');
  };
  if (is_null($bom_wmo) or ($bom_wmo == "")) {
   return json_decode('{}');
  };
  try {
   return get_weather($bom_id, $bom_wmo);
   }
  catch (Exception $e) {
   return json_decode('{}');
  }
 };

 function update_configs($date) {
  $file_path = './conf/cam_configs.txt';
  $file_exists = file_exists($file_path);
  if (!$file_exists) {
   return json_decode('{}');
  };
  $found_cam = false;
  $myObj = json_decode('{}');
  if (($handle = fopen($file_path, 'r+')) !== false) {
   if (flock($handle, LOCK_EX)) {
    $data = [];
    while (($row = fgetcsv($handle)) !== false) {
      $data[] = $row;
    }
    $out_data = [];
    foreach ($data as $row) {
     if ($row[0] != $_POST['CAM_LABEL']) {
      array_push($out_data, $row);
     } else {
      $found_cam = true;
      $myObj->score_threshold = $row[1];
      $myObj->status = $row[2];
      $myObj->system = $row[3];
      $myObj->bom_id = $row[4];
      $myObj->bom_wmo = $row[5];
      $myObj->stop_wind_kmh = $row[6];
      $myObj->weather_obs = retrieve_weather($row[4], $row[5]);
      // Reaet 'status' and 'action'
      $out_row = $row;
      $out_row[2] = 'None';
      $out_row[3] = 'None';
      $out_row[7] = $date;
      array_push($out_data, $out_row);
     }
    };
    rewind($handle);
    ftruncate($handle, 0);
    foreach ($out_data as $row) {
     fputcsv($handle, $row);
    };
    flock($handle, LOCK_UN);
   } else {
    //echo "<p>Could not lock the file for writing.</p>";
    return json_decode('{}');
   };
   fclose($handle);
  } else {
   //echo "<p>Could not open the file.</p>";
   return json_decode('{}');
  };

  if ($found_cam) {
   return $myObj;
  } else {
   return json_decode('{}');
  };
 };

 header('Content-type: application/json');
 header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
 header("Cache-Control: post-check=0, pre-check=0", false);
 header("Pragma: no-cache");

 $date = new DateTime();
 $formattedDate = $date->format('Y-m-d H:i:s');

 if (check_auth($_POST['AUTH_KEY'])) {
  if (validate_inputs()) {
   sleep(rand(0,5));
   echo json_encode(update_configs($formattedDate));
   } else {
   echo "{}";
  }
 } else {
  sleep(1);
  echo "{}";
 };

?>
