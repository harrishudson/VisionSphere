<?php 

 include 'common.php';

 if (! active_session()) {
  $return_url = $_SERVER['PHP_SELF'];
  $param_cam_label = urlencode($_GET['CAM_LABEL']);
  $param_score_threshold = urlencode($_GET['SCORE_THRESHOLD']);
  $param_bom_id = urlencode($_GET['BOM_ID']);
  $param_bom_wmo = urlencode($_GET['BOM_WMO']);
  $param_stop_wind_kmh = urlencode($_GET['STOP_WIND_KMH']);
  $query_string = "?CAM_LABEL=".$param_cam_label."&SCORE_THRESHOLD=".$param_score_threshold."&BOM_ID=".$param_bom_id."&BOM_WMO=".$param_bom_wmo."&STOP_WIND_KMH=".$param_stop_wind_kmh;
  $full_return_url = $return_url.$query_string;
  $full_return_url_hex = urlencode($full_return_url);
  header('Location: login.php?RETURN_URL='.$full_return_url_hex);
  exit();
 }; 

 page_top();
?>

<h2>Set Cam Config</h2>

<script>
function form_validation() {
 document.forms[0].addEventListener('submit',
  function(e) {

   let cam = document.getElementById('CAM_LABEL')
   if (!cam.value) {
    status_msg("Cam Name is Required")
    e.preventDefault()
    return false;
   }

   let noise = document.getElementById('SCORE_THRESHOLD')
   let n = noise.value
   if ((n !== '') && (n <= 0)) {
    status_msg("Noise Threshold Must Be Greater Than Zero")
    e.preventDefault()
    return false;
   }

   let wind = document.getElementById('STOP_WIND_KMH')
   let w = wind.value
   if ((w !== '') && (w <= 0)) {
    status_msg("Wind Stop Must Be Greater Than Zero")
    e.preventDefault()
    return false;
   }

   return true;
  });
}
window.onload = form_validation;
</script>

<?php

 $cam_label = escHTML($_GET['CAM_LABEL']);
 $score_threshold = escHTML($_GET['SCORE_THRESHOLD']);
 $bom_id = escHTML($_GET['BOM_ID']);
 $bom_wmo = escHTML($_GET['BOM_WMO']);
 $stop_wind_kmh = escHTML($_GET['STOP_WIND_KMH']);
 echo <<<EOF
  <form action="do_set_config.php" method="POST">
   <dl>
    <dt>Cam Name *</dt>
    <dd><input id="CAM_LABEL" name="CAM_LABEL" value="{$cam_label}" placeholder="Cam Name"></dd>
    <dt>Image Noise Threshold</dt>
    <dd><input id="SCORE_THRESHOLD" type="number" step="0.01"
         name="SCORE_THRESHOLD" value="{$score_threshold}" placeholder="20"></dd>
    <dt>BOM Station ID</dt>
    <dd><input type="text" name="BOM_ID" value="{$bom_id}"></dd>
    <dt>BOM Station WMO</dt>
    <dd><input type="text" name="BOM_WMO" value="{$bom_wmo}"></dd>
    <dt>Stop When Wind Above</dt>
    <dd><input id="STOP_WIND_KMH" type="number" step="0.01"
         name="STOP_WIND_KMH" value="{$stop_wind_kmh}" placeholder="20"> Km/h</dd>
    <dt>Camera</dt>
    <dd><select name="STATUS">
         <option value="None" selected="selected">Select</option>
         <option>Start</option>
         <option>Stop</option>
         <option>Take Photo</option>
         <option>Take Recording</option>
        </select>
    </dd>
    <dt>System</dt>
    <dd><select name="SYSTEM">
         <option value="None" selected="selected">No Action</option>
         <option>Reboot</option>
         <option>Speed Test</option>
         <option>Wifi Scan</option>
         <option>Update</option>
        </select>
    </dd>
   </dl>
   <p>
    <input id="submit" type="submit" value="Set">
   </p>
  </form>
EOF;

 page_bottom();
?>
