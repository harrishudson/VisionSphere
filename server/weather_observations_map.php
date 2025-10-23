<?php

 include 'common.php';

 if (! active_session()) {
  $return_url = $_SERVER['PHP_SELF'];
  $return_url_hex = urlencode($return_url);
  header('Location: login.php?RETURN_URL='.$return_url_hex);
  exit();
 };

 $cams = get_cams();
 $cam_list = [];
 foreach($cams as $cam) {
  array_push($cam_list, escHTML($cam[0])); //Name
 };

 page_top(false, true);
?>

  <style>
    html, body, #map {
      height: 100%;
      margin: 0;
      padding: 0;
    }

    #loading {
      position: absolute;
      top: 10px;
      right: 10px;
      z-index: 1000;
      background: rgba(255, 255, 255, 0.9);
      padding: 5px 10px;
      border-radius: 5px;
      font-family: sans-serif;
      font-size: 14px;
      display: none;
      align-items: center;
    }

    .spinner {
      border: 3px solid #f3f3f3;
      border-top: 3px solid #3498db;
      border-radius: 50%;
      width: 14px;
      height: 14px;
      margin-right: 5px;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Popup content styling */
    .leaflet-popup-content {
      max-height: 250px;
      overflow-y: auto;
    }
    .leaflet-popup-content dl {
      font-size: 0.85em;
      margin: 0;
      padding: 0;
    }
    .leaflet-popup-content dt {
      font-weight: bold;
    }
    .leaflet-popup-content dd {
      margin: 0 0 5px 10px;
    }
  </style>

<div id="map"></div>
 <div id="loading"><div class="spinner"></div>Loading data...</div>

<script>
  const map = L.map('map')

  // Tile layer with updated attribution
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors | Data &copy; BOM'
  }).addTo(map)

  // Australia bounds
  const australiaBounds = L.latLngBounds(
    L.latLng(-44, 112),
    L.latLng(-10, 154)
  )
  map.fitBounds(australiaBounds)

  // Color scale based on air_temp
  function getColor(temp) {
    if (temp === null || isNaN(temp)) return '#808080'; // grey for null
    return temp >= 40 ? '#800026' :
           temp >= 35 ? '#e31a1c' :
           temp >= 30 ? '#fc4e2a' :
           temp >= 25 ? '#fd8d3c' :
           temp >= 20 ? '#feb24c' :
           temp >= 15 ? '#fed976' :
           temp >= 10 ? '#ffffb2' :
           temp >= 5  ? '#c7e9b4' :
           temp >= 0  ? '#7fcdbb' :
           temp >= -5 ? '#41b6c4' :
                        '#1d91c0';
  }

  let geojsonLayer

  async function loadGeoJSON() {
    const loading = document.getElementById('loading')
    loading.style.display = 'flex'

    try {
      const response = await fetch('weather_api.php', { cache: "no-store" })
      let data
      try { 
       data = await response.json()
      } catch(e) {}
      if (!response.ok) throw new Error(data.error || 'Network response was not ok')

      if (geojsonLayer) map.removeLayer(geojsonLayer)

      geojsonLayer = L.geoJSON(data, {
        pointToLayer: function(feature, latlng) {
          const temp = feature.properties.air_temp
          const color = getColor(temp)
          return L.circleMarker(latlng, {
            radius: 8,
            fillColor: color,
            color: '#000',   // black outline
            weight: 1,
            opacity: 1,
            fillOpacity: 0.8
          })
        },
        onEachFeature: function(feature, layer) {
          if (feature.properties) {

            const props = feature.properties
            const temp = props.air_temp

            const tooltipEl = document.createElement('div');
            tooltipEl.textContent = `${props.name ?? 'Unknown'} (${temp !== null ? temp : 'N/A'}°C)`;

            layer.bindTooltip(tooltipEl, {
              permanent: false,
              direction: 'top'
            });

            layer.on('click', () => {
              const dl = document.createElement('dl')
            
              const addPair = (key, value, small = false) => {
                const dt = document.createElement('dt')
                dt.textContent = key
                const dd = document.createElement('dd')
                if (small) {
                  const smallTag = document.createElement('small')
                  smallTag.textContent = value
                  dd.appendChild(smallTag)
                } else {
                  dd.textContent = value
                }
                dl.appendChild(dt)
                dl.appendChild(dd)
              }
            
              addPair('Name', props.name ?? 'N/A')
              addPair('Air Temp', props.air_temp !== null ? `${props.air_temp}°C` : 'N/A')
            
              // Other properties
              for (let key in props) {
                if (key !== 'name' && key !== 'air_temp') {
                  const val = props[key]
                  const isSmall = key.toLowerCase().includes('copyright') || 
                                  key.toLowerCase().includes('licence')
                  addPair(key, val, isSmall)
                }
              }
            
              const dtLink = document.createElement('dt')
              dtLink.textContent = 'Link Station Obs to Cam'
              dl.appendChild(dtLink)
            
              const ddLink = document.createElement('dd')
              const select = document.createElement('select')
              select.className = 'cam_select'
              select.dataset.id = props.id ?? '' 
              select.dataset.wmo_id = props.wmo_id ?? '' 
              select.dataset.name = props.name ?? ''
           
              const opt1 = document.createElement('option')
              opt1.value = ""
              opt1.textContent = "Select"
              select.appendChild(opt1)
 
              const camList = <?php echo json_encode($cam_list); ?>;
              camList.forEach(cam => {
                const opt = document.createElement('option')
                opt.value = cam
                opt.textContent = cam
                select.appendChild(opt)
              })
    
              ddLink.appendChild(select)
              dl.appendChild(ddLink)
    
              layer.bindPopup(dl).openPopup()
              add_events()
            })
    
          }
        }
      }).addTo(map)

    } catch (error) {
      status_msg(error.toString())
    } finally {
      loading.style.display = 'none'
    }
  }

 function add_events() {
  document.querySelectorAll('.cam_select').forEach(sel => {
   sel.addEventListener('change', e => {
    const el = e.target
    const stationState = el.dataset.id || ''
    const stationName = el.dataset.name || ''
    const wmo_id = el.dataset.wmo_id || ''
    const camValue = el.options[el.selectedIndex].value
    const camName = el.options[el.selectedIndex].textContent

    linkStationToCam(stationState, stationName, wmo_id, camValue, camName)

    })
  })
 }

 async function linkStationToCam(state, name, wmo_id, camValue, camName) {
  if (!camValue) return;  // "Select" selected

  let payload = new URLSearchParams( { "CAM_LABEL": camValue,
                                       "BOM_ID": state,
                                       "BOM_WMO": wmo_id } )

  let theUrl = "link_weather_station_api.php?" + payload.toString()

  const response = await fetch(theUrl)

  if (!response.ok) {
   status_msg('Network response failed')
   return
  }

  const msg = await response.text()
  status_msg(msg)

 }

 // Initial load
 loadGeoJSON()
 // Refresh every 3 minutes
 setInterval(loadGeoJSON, 180000)
</script>

<?php
 
 page_bottom(false, true);

?>
