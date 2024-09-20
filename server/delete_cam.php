<?php 

 include 'common.php';

 if (! active_session()) {
  header('Location: login.php?RETURN_URL=list_cams.php');
  exit();
 }; 

 $file_path = './conf/cam_configs.txt';
 $file_exists = file_exists($file_path);
 if (!$file_exists) {
  header('Location: list_cams.php');
  exit();
 };
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
    };
   };
   rewind($handle);
   ftruncate($handle, 0);
   foreach ($out_data as $row) {
    fputcsv($handle, $row);
   };
   flock($handle, LOCK_UN);
  } else {
   header('Location: list_cams.php');
   exit();
  };
  fclose($handle);
 };

 header('Location: list_cams.php');
?>
