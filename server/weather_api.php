<?php

 header('Content-Type: application/json');

 include 'common.php';
 include 'weather.php';

 $authorised = false;
 if (isset($_POST['AUTH_KEY'])) {
  if (check_auth($_POST['AUTH_KEY'])) {
   $authorised = true;
  }
 } else {
  $authorised = active_session();
 };

 if (! $authorised) {
  http_response_code(400);
  echo json_encode(['error' => 'Unauthorised - Auth Key Invalid or Expired']);
  exit();
 }; 

 $STATE_MAP = [
  'NSW' => 'IDN60910',
  'VIC' => 'IDV60910',
  'QLD' => 'IDQ60910',
  'WA'  => 'IDW60910',
  'SA'  => 'IDS60910',
  'TAS' => 'IDT60910',
  'NT'  => 'IDD60910'
 ];

 // --- PARAMETERS ---
 // State parameter: uppercase, valid values only
 $stateParam = strtoupper($_GET['state'] ?? 'ALL');
 if ($stateParam !== 'ALL' && $stateParam !== 'ACT' && 
   !isset($STATE_MAP[$stateParam]) && !in_array($stateParam, $STATE_MAP)) {
   http_response_code(400);
   echo json_encode(['error' => 'Unsupported State Value']);
   exit();
 };

 // WMO ID parameter: only digits allowed
 $wmoFilter = isset($_GET['wmo_id']) ? preg_replace('/[^0-9]/', '', $_GET['wmo_id']) : null;

 // Determine which states to fetch
 $statesToFetch = [];
 if ($stateParam === 'ALL') {
     $statesToFetch = array_keys($STATE_MAP);
 } elseif ($stateParam === 'ACT') {
     $statesToFetch = ['NSW'];
 } elseif (isset($STATE_MAP[$stateParam])) {
     $statesToFetch = [$stateParam];
 } elseif (in_array($stateParam, $STATE_MAP)) {
     $statesToFetch = [array_search($stateParam, $STATE_MAP)];
 };

 $geojson = get_weather($statesToFetch, $wmoFilter);

 echo json_encode($geojson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

?>
