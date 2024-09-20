<?php 

 include 'common.php';

 if (! active_session()) {
  $return_url = $_POST['RETURN_URL'];
  if ((! isset($return_url) || $return_url == '')) {
   $return_url = get_home();
  };
  header("Location: ".$return_url);
  exit;
 };

 page_top();
?>

<h2>Save Config</h2>

<?php

 function validate_inputs() {
  if (($_POST['CAM_LABEL'] == "") or (is_null($_POST['CAM_LABEL']))) {
   return false;
  }
  return true;
 }

 function update_configs() {
  $file_path = './conf/cam_configs.txt';
  $file_exists = file_exists($file_path);
  if (!$file_exists) {
   file_put_contents($file_path, null);
  };
  if (($handle = fopen($file_path, 'r+')) !== false) {
   if (flock($handle, LOCK_EX)) {
    $data = [];
    while (($row = fgetcsv($handle)) !== false) {
      $data[] = $row;
    }
    $out_data = [];
    $score_threshold = $_POST['SCORE_THRESHOLD'];
    if ((is_null($score_threshold)) or ($score_threshold == "")) {
     $score_threshold = 20;
    };
    $wind_stop = $_POST['STOP_WIND_KMH'];
    if ((is_null($wind_stop)) or ($wind_stop == "")) {
     $wind_stop = 20;
    };
    $status = $_POST['STATUS'];
    if ((is_null($status)) or ($status == "")) {
     $status = 'None';
    };
    $system = $_POST['SYSTEM'];
    if ((is_null($system)) or ($system == "")) {
     $system = 'None';
    };
    // This cam
    array_push(
     $out_data,
     [ $_POST['CAM_LABEL'],
       $score_threshold,
       $status,
       $system,
       $_POST['BOM_ID'],
       $_POST['BOM_WMO'],
       $wind_stop,
       null ]); // Used to store last config fetch time
    // Other cams
    foreach ($data as $row) {
     if ($row[0] != $_POST['CAM_LABEL']) {
      array_push($out_data, $row);
     }
    };
    rewind($handle);
    ftruncate($handle, 0);
    foreach ($out_data as $row) {
     fputcsv($handle, $row);
    };
    flock($handle, LOCK_UN);
   } else {
    echo "<p>Could not lock the cams file for writing.</p>";
    return false;
   };
   fclose($handle);
  } else {
   echo "<p>Could not open the cams file.</p>";
   return false;
  };
  return true;
 };

 if (validate_inputs()) {
  if (update_configs()) {

   $date = new DateTime();
   $formattedDate = $date->format('Y-m-d H:i:s');

   echo "<p>{$formattedDate}</p>";
   echo "<p>Success - Config Saved</p>";
  }
 } else {
 echo "<p>Parameter Validation Failed.</p>";
 };

 page_bottom();

?>
