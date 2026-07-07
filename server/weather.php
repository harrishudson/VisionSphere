<?php
date_default_timezone_set('Australia/Sydney');
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$CACHE_DIR = __DIR__ . '/cache'; 
if (!file_exists($CACHE_DIR)) mkdir($CACHE_DIR, 0777, true);
$CACHE_TTL = 180; // 3 minutes
$FTP_BASE = 'ftp://ftp.bom.gov.au/anon/gen/fwo/';
$STATE_MAP = [
    'NSW' => 'IDN60910',
    'VIC' => 'IDV60910',
    'QLD' => 'IDQ60910',
    'WA'  => 'IDW60910',
    'SA'  => 'IDS60910',
    'TAS' => 'IDT60910',
    'NT'  => 'IDD60910',
    'IDN60910' => 'IDN60910',
    'IDV60910' => 'IDV60910',
    'IDQ60910' => 'IDQ60910',
    'IDW60910' => 'IDW60910',
    'IDS60910' => 'IDS60910',
    'IDT60910' => 'IDT60910',
    'IDD60910' => 'IDD60910'
];

function humaniseTime($timestamp) {
    $dt = DateTime::createFromFormat('YmdHis', $timestamp);
    return $dt ? $dt->format('l, F j, Y g:i A') : '';
};

// --- HELPER FUNCTIONS ---

/**
 * Extracts observation records from a local .tgz file entirely in memory.
 * Decodes the gzip stream and loops over the 512-byte tar architecture layout.
 */
function extractJsonFromTgz($tgzFile, $stateAbbrev, $lastFetched) {
    $data = [];

    $compressedData = @file_get_contents($tgzFile);
    if (!$compressedData) return $data;

    // Decompress the gzip stream straight into memory
    $tarData = @gzdecode($compressedData);
    if (!$tarData) return $data;

    $offset = 0;
    $tarLength = strlen($tarData);

    // Parse the in-memory tar stream block by block (512 bytes per block)
    while ($offset + 512 <= $tarLength) {
        $header = substr($tarData, $offset, 512);
        
        // A block composed entirely of null bytes indicates the end of the tar archive
        if (trim($header, "\0") === '') {
            break;
        }

        // Target file metadata records inside the header structure
        $filename = trim(substr($header, 0, 100), "\0");
        $sizeOctal = trim(substr($header, 124, 12), "\0 ");
        $size = octdec($sizeOctal); // Convert octal tar size to decimal bytes

        $offset += 512; // Advance past the header block

        if ($size > 0) {
            if (substr($filename, -5) === '.json') {
                $content = substr($tarData, $offset, $size);
                $json = json_decode($content, true);
                
                if ($json && !empty($json['observations']['data'])) {
                    $headerData = $json['observations']['header'][0] ?? [];
                    foreach ($json['observations']['data'] as $obs) {
                        if (($obs['sort_order'] ?? 1) == 0) {
                            $data[] = [
                                'state_abbrev'   => $stateAbbrev,
                                'state'          => $headerData['state'] ?? '',
                                'copyright'      => $json['observations']['notice'][0]['copyright'] ?? '',
                                'id'             => $headerData['ID'] ?? '',
                                'name'           => $headerData['name'] ?? '',
                                'wmo_id'         => $headerData['wmo_id'] ?? '',
                                'aifstime_utc'   => humaniseTime($obs['aifstime_utc']) ?? '',
                                'refresh_message' => humaniseTime($obs['aifstime_local']) ?? '',
                                'lat'            => (float)($obs['lat'] ?? 0),
                                'lon'            => (float)($obs['lon'] ?? 0),
                                'gust_kmh'       => $obs['gust_kmh'] ?? null,
                                'wind_spd_kmh'   => $obs['wind_spd_kmh'] ?? null,
                                'air_temp'       => $obs['air_temp'] ?? null,
                                'apparent_temp'  => $obs['apparent_t'] ?? null,
                                'mm_rain_since_9am' => $obs['rain_trace'] ?? null,
                                'last_poll'   => date('c', $lastFetched),
                                'retrieval_mode' => 'Intelligent Fetch/Cache'
                            ];
                            break;
                        }
                    }
                }
            }
            // Move pointer forward past file contents (padded out to the next 512-byte boundary)
            $offset += ceil($size / 512) * 512;
        }
    }

    return $data;
}

// Returns the remote file's mtime via FTP, or false on any failure.
function getRemoteFileMtime($ftpUrl) {
    $parts = parse_url($ftpUrl);
    $conn = @ftp_connect($parts['host'], 21, 10);
    if (!$conn) return false;
    if (!@ftp_login($conn, 'anonymous', '')) {
        ftp_close($conn);
        return false;
    }
    $mtime = ftp_mdtm($conn, ltrim($parts['path'], '/'));
    ftp_close($conn);
    return ($mtime !== -1) ? $mtime : false;
}

// Ensures a local copy of the remote .tgz exists and is reasonably current.
function downloadWithCache($ftpUrl, $localTgz, $lockFile, $cacheTtl) {
    $now = time();
    clearstatcache(true, $localTgz);
    $localExists = file_exists($localTgz);
    $localAge = $localExists ? $now - filemtime($localTgz) : PHP_INT_MAX;
    $indexFile = $localTgz . '.index';

    if ($localAge < $cacheTtl) return $localTgz;

    $fpLock = fopen($lockFile, 'c');
    if (!$fpLock) return false;

    if (!flock($fpLock, LOCK_EX)) {
        fclose($fpLock);
        return false;
    }

    // Double-check within lock
    clearstatcache(true, $localTgz);
    $localExists = file_exists($localTgz);
    $localAge = $localExists ? $now - filemtime($localTgz) : PHP_INT_MAX;
    if ($localAge < $cacheTtl) {
        flock($fpLock, LOCK_UN);
        fclose($fpLock);
        return $localTgz;
    }

    $remoteMtime = getRemoteFileMtime($ftpUrl);
    clearstatcache(true, $indexFile);
    $lastSeen = file_exists($indexFile) ? (int)file_get_contents($indexFile) : 0;

    if (!$localExists || ($remoteMtime !== false && $remoteMtime > $lastSeen)) {
        $data = @file_get_contents($ftpUrl);
        if ($data !== false) {
            file_put_contents($localTgz, $data);
            clearstatcache(true, $localTgz);
            
            $indexTime = ($remoteMtime !== false) ? $remoteMtime : time();
            file_put_contents($indexFile, $indexTime);
        }
    } elseif ($remoteMtime === false && $localExists) {
        touch($localTgz);
        clearstatcache(true, $localTgz);
    } else {
        if ($localExists) {
            touch($localTgz);
            clearstatcache(true, $localTgz);
        }
    }

    flock($fpLock, LOCK_UN);
    fclose($fpLock);

    return file_exists($localTgz) ? $localTgz : false;
}

// Returns observation data for one state, using a JSON cache built from the local .tgz.
function getStateDataWithJsonCache($tgzFile, $stateAbbrev, $cacheDir, $productId) {
    $jsonCache = "$cacheDir/{$productId}.json";
    $builtForFile = "$jsonCache.builtfor";
    $indexFile = "$tgzFile.index";

    clearstatcache(true, $indexFile);
    $remoteMtime = file_exists($indexFile) ? (int)file_get_contents($indexFile) : 0;

    clearstatcache(true, $builtForFile);
    $jsonBuiltFor = file_exists($builtForFile) ? (int)file_get_contents($builtForFile) : 0;

    if ($remoteMtime > 0 && $jsonBuiltFor >= $remoteMtime && file_exists($jsonCache)) {
        $cached = json_decode(@file_get_contents($jsonCache), true);
        if (is_array($cached)) {
            return $cached;
        }
    }

    clearstatcache(true, $tgzFile);
    $tgzMtime = file_exists($tgzFile) ? filemtime($tgzFile) : time();
    $data = extractJsonFromTgz($tgzFile, $stateAbbrev, $tgzMtime);
    file_put_contents($jsonCache, json_encode($data));
    file_put_contents($builtForFile, $remoteMtime);
    clearstatcache(true, $jsonCache);
    clearstatcache(true, $builtForFile);

    return $data;
}

// --- MAIN ---
function get_weather($statesToFetch, $wmoFilter) {
    global $STATE_MAP, $FTP_BASE, $CACHE_DIR, $CACHE_TTL;
    $allData = [];

    foreach ($statesToFetch as $state) {
        $productId = $STATE_MAP[$state] ?? '';
        if ($productId === '') continue;

        $ftpUrl = $FTP_BASE . $productId . '.tgz';
        $localTgz = "$CACHE_DIR/{$productId}.tgz";
        $lockFile = "$CACHE_DIR/{$productId}.lock";
    
        $tgzFile = downloadWithCache($ftpUrl, $localTgz, $lockFile, $CACHE_TTL);
        if (!$tgzFile) continue;

        $stateData = getStateDataWithJsonCache($tgzFile, $state, $CACHE_DIR, $productId);
        $allData = array_merge($allData, $stateData);
    }

    if ($wmoFilter) {
        $allData = array_values(array_filter($allData, fn($d) => $d['wmo_id'] == $wmoFilter));
    }

    $geojson = [
        'type' => 'FeatureCollection',
        'features' => array_map(fn($obs) => [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [$obs['lon'], $obs['lat']]
            ],
            'properties' => $obs
        ], $allData)
    ];

    //return json_encode($geojson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    return $geojson;
}

?>
