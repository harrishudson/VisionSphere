<?php 

 include 'common.php';

 if (! active_session()) {
  header('Location: login.php?RETURN_URL=list_cams.php');
  exit();
 }; 

 $CAM_LABEL = $_POST['CAM_LABEL'];
 $cams = get_cams();
 foreach($cams as $cam) {
  if ($cam[0] == $CAM_LABEL) {
   $param_cam_label = urlencode($cam[0]);
   $param_score_threshold = urlencode($cam[1]);
   $param_bom_id = urlencode($cam[4]);
   $param_bom_wmo = urlencode($cam[5]);
   $param_stop_wind_kmh = urlencode($cam[6]);
   $query_string = "?CAM_LABEL=".$param_cam_label."&SCORE_THRESHOLD=".$param_score_threshold."&BOM_ID=".$param_bom_id."&BOM_WMO=".$param_bom_wmo."&STOP_WIND_KMH=".$param_stop_wind_kmh;
   $edit_url = "set_config.php".$query_string;
   header('Location: '.$edit_url);
   exit();
  }; 
 };

 header('Location: list_cams.php');
?>
