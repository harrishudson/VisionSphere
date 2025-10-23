<?php

 include 'weather.php';

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
 function station_weather($state, $wmo_id) {

  global $STATE_MAP;

  $stateParam = strtoupper($state);
  if ($stateParam !== 'ACT' && 
      !isset($STATE_MAP[$stateParam]) && !in_array($stateParam, $STATE_MAP)) {
    return null; 
  };

  // WMO ID parameter: only digits allowed
  $wmoFilter = isset($_GET['wmo_id']) ? preg_replace('/[^0-9]/', '', $_GET['wmo_id']) : null;
  if (!isset($wmoFilter)) {
   return null;
  };


  // Determine which states to fetch
  $statesToFetch = [];
  if ($stateParam === 'ACT') {
   $statesToFetch = ['NSW'];
  } elseif (isset($STATE_MAP[$stateParam])) {
   $statesToFetch = [$stateParam];
  } elseif (in_array($stateParam, $STATE_MAP)) {
   $statesToFetch = [array_search($stateParam, $STATE_MAP)];
  };

  $geojson = get_weather($statesToFetch, $wmoFilter);

  return $geojson;

 };

?>
