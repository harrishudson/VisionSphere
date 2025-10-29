<?php

date_default_timezone_set('Australia/Sydney');

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$CACHE_DIR = __DIR__ . '/cache';
if (!file_exists($CACHE_DIR)) mkdir($CACHE_DIR, 0777, true);
$CACHE_TTL = 240; // 4 minutes
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

// --- SAFE TEMP CLEANUP ---
// Cleans up only /tmp/bom_tmp_* dirs older than 3 days.
function cleanup_old_temp_dirs() {
    $baseDir = sys_get_temp_dir();
    $pattern = $baseDir . '/bom_tmp_*';
    $maxAge = 3 * 24 * 60 * 60; // 3 days
    $now = time();

    foreach (glob($pattern, GLOB_ONLYDIR) as $dir) {
        $lastModified = filemtime($dir);
        if ($lastModified !== false && ($now - $lastModified) > $maxAge) {
            try {
                $it = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
                $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
                foreach ($files as $file) {
                    $path = $file->getPathname();
                    if ($file->isDir()) {
                        @rmdir($path);
                    } else {
                        @unlink($path);
                    }
                }
                @rmdir($dir);
            } catch (Exception $e) {
                // Optional: log cleanup errors if needed
                // error_log("Cleanup failed for $dir: " . $e->getMessage());
            }
        }
    }
}

function humaniseTime($timestamp) {
    $dt = DateTime::createFromFormat('YmdHis', $timestamp);
    return $dt ? $dt->format('l, F j, Y g:i A') : '';
}

function validate_states($stateParam) {
    global $STATE_MAP;
    if ($stateParam !== 'ALL' && $stateParam !== 'ACT' &&
        !isset($STATE_MAP[$stateParam]) && !in_array($stateParam, $STATE_MAP)) {
        return false;
    }
    return true;
}

function getStatesToFetch($stateParam) {
    global $STATE_MAP;
    $statesToFetch = [];
    if ($stateParam === 'ALL') {
        $statesToFetch = array_keys($STATE_MAP);
    } elseif ($stateParam === 'ACT') {
        $statesToFetch = ['NSW'];
    } elseif (isset($STATE_MAP[$stateParam])) {
        $statesToFetch = [$stateParam];
    } elseif (in_array($stateParam, $STATE_MAP)) {
        $statesToFetch = [array_search($stateParam, $STATE_MAP)];
    }
    return $statesToFetch;
}

function extractJsonFromTgz($tgzFile, $stateAbbrev, $lastFetched) {
    $data = [];

    // Create a unique temporary directory for extraction
    $tempDir = sys_get_temp_dir() . '/bom_tmp_' . bin2hex(random_bytes(4));
    mkdir($tempDir);
    $tempTgz = "$tempDir/source.tgz";
    copy($tgzFile, $tempTgz);

    try {
        $phar = new PharData($tempTgz);
        $phar->decompress(); // creates source.tar inside $tempDir
        $tarPath = "$tempDir/source.tar";

        if (!file_exists($tarPath)) {
            throw new Exception("Failed to decompress $tempTgz");
        }

        $tar = new PharData($tarPath);
        $iterator = new RecursiveIteratorIterator($tar);

        foreach ($iterator as $file) {
            $name = $file->getFilename();
            if (substr($name, -5) === '.json') {
                $content = file_get_contents($file->getPathname());
                $json = json_decode($content, true);
                if (!$json || empty($json['observations']['data'])) continue;

                $header = $json['observations']['header'][0] ?? [];
                foreach ($json['observations']['data'] as $obs) {
                    if (($obs['sort_order'] ?? 1) == 0) {
                        $data[] = [
                            'state_abbrev' => $stateAbbrev,
                            'state' => $header['state'] ?? '',
                            'copyright' => $json['observations']['notice'][0]['copyright'] ?? '',
                            'id' => $header['ID'] ?? '',
                            'name' => $header['name'] ?? '',
                            'wmo_id' => $header['wmo_id'] ?? '',
                            'aifstime_utc' => humaniseTime($obs['aifstime_utc']) ?? '',
                            'refresh_message' => humaniseTime($obs['aifstime_local']) ?? '',
                            'lat' => (float)($obs['lat'] ?? 0),
                            'lon' => (float)($obs['lon'] ?? 0),
                            'gust_kmh' => $obs['gust_kmh'] ?? null,
                            'wind_spd_kmh' => $obs['wind_spd_kmh'] ?? null,
                            'air_temp' => $obs['air_temp'] ?? null,
                            'apparent_temp' => $obs['apparent_t'] ?? null,
                            'mm_rain_since_9am' => $obs['rain_trace'] ?? null,
                            'last_poll' => date('c', $lastFetched),
                            'retrieval_mode' => 'Intelligent Fetch/Cache'
                        ];
                        break;
                    }
                }
            }
        }

    } catch (Exception $e) {
        // error_log("extractJsonFromTgz error: " . $e->getMessage());
    }

    // Cleanup this temp dir only
    foreach (glob("$tempDir/*") as $f) @unlink($f);
    @rmdir($tempDir);

    return $data;
}

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

function downloadWithCache($ftpUrl, $localTgz, $lockFile, $cacheTtl) {
    $now = time();
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

    // Recheck within lock
    $localExists = file_exists($localTgz);
    $localAge = $localExists ? $now - filemtime($localTgz) : PHP_INT_MAX;
    if ($localAge < $cacheTtl) {
        flock($fpLock, LOCK_UN);
        fclose($fpLock);
        return $localTgz;
    }

    $remoteMtime = getRemoteFileMtime($ftpUrl);
    $lastSeen = file_exists($indexFile) ? (int)file_get_contents($indexFile) : 0;

    if ($remoteMtime !== false && $remoteMtime > $lastSeen) {
        $data = @file_get_contents($ftpUrl);
        if ($data !== false) file_put_contents($localTgz, $data);
        file_put_contents($indexFile, $remoteMtime);
    } elseif ($remoteMtime === false && $localExists) {
        touch($localTgz);
    } else {
        if ($localExists) touch($localTgz);
    }

    flock($fpLock, LOCK_UN);
    fclose($fpLock);

    return file_exists($localTgz) ? $localTgz : false;
}

// --- MAIN ---
function get_weather($statesToFetch, $wmoFilter) {
    global $STATE_MAP, $FTP_BASE, $CACHE_DIR, $CACHE_TTL;
    $allData = [];

    // Random cleanup trigger (~1 in 30 runs)
    if (mt_rand(0, 30) === 0) {
        cleanup_old_temp_dirs();
    }

    foreach ($statesToFetch as $state) {
        $productId = $STATE_MAP[$state] ?? '';
        if ($productId === '') continue;

        $ftpUrl = $FTP_BASE . $productId . '.tgz';
        $localTgz = "$CACHE_DIR/{$productId}.tgz";
        $lockFile = "$CACHE_DIR/{$productId}.lock";

        $tgzFile = downloadWithCache($ftpUrl, $localTgz, $lockFile, $CACHE_TTL);
        if (!$tgzFile) continue;

        $lastFetched = filemtime($tgzFile);
        $stateData = extractJsonFromTgz($tgzFile, $state, $lastFetched);
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

    return $geojson;
}

?>
