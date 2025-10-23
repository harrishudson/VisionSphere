<?php 

 include 'common.php';

 header('Content-Type: text/plain');

 if (! active_session()) {
  echo 'Unauthorised - Auth Key Invalid or Expired';
  exit();
 }

 function validate_inputs() {
  if (($_GET['CAM_LABEL'] == "") or (is_null($_GET['CAM_LABEL']))) {
   echo 'Error - Missing Parameter CAM_LABEL';
   return false;
  }

  if (($_GET['BOM_ID'] == "") or (is_null($_GET['BOM_ID']))) {
   echo 'Error - Missing Parameter BOM_ID';
   return false;
  }

  if (($_GET['BOM_WMO'] == "") or (is_null($_GET['BOM_WMO']))) {
   echo 'Error - Missing Parameter BOM_WMO';
   return false;
  }

  return true;
 }

 function update_configs() {
  $file_path = './conf/cam_configs.txt';
  $file_exists = file_exists($file_path);
  $return_status = false;
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

    foreach ($data as $row) {
     if ($row[0] != $_GET['CAM_LABEL']) {
      array_push($out_data, $row);
     } else {
      // Update weather config for this cam
      array_push(
       $out_data,
       [ $_GET['CAM_LABEL'],
         $row[1],
         $row[2],
         $row[3],
         $_GET['BOM_ID'],
         $_GET['BOM_WMO'],
         $row[6],
         null ]); // Used to store last config fetch time
      $return_status = true;
     };
    };
    rewind($handle);
    ftruncate($handle, 0);
    foreach ($out_data as $row) {
     fputcsv($handle, $row);
    };
    flock($handle, LOCK_UN);
   } else {
    echo "Error - Could not lock the cams file for writing";
    return false;
   };
   fclose($handle);
  } else {
   echo "Error - Could not open the cams file";
   return false;
  };
  if (! $return_status) {
   echo "Error - Cam Not Found";
  };
  return $return_status;
 };

 if (validate_inputs()) {
  if (update_configs()) {
   echo "Success - Cam ".$_GET['CAM_LABEL'].
        " will now use weather info from station; ".
        $_GET['BOM_ID']." / ".$_GET['BOM_WMO'];
  }
 };

?>
