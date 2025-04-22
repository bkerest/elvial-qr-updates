<?php
// File: check_updates.php

/**
 * Fetches a remote file's content with cache-busting headers.
 *
 * @param string $url The URL of the remote file.
 * @return string|false The file content or false on failure.
 */
function fetch_remote_file($url) {
    $url_with_bust = $url . (str_contains($url, '?') ? '&' : '?') . 't=' . microtime(true);

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: ELVIAL QR Updater\r\nCache-Control: no-cache, no-store, must-revalidate\r\nPragma: no-cache\r\nExpires: 0\r\n"
        ]
    ]);

    $data = @file_get_contents($url_with_bust, false, $context);
    if ($data === false) {
        $error = error_get_last();
        error_log("Failed to fetch $url: " . $error['message']);
    }
    return ($data !== false && strlen($data) > 0) ? $data : false;
}

/**
 * Checks for updates and updates local files if newer versions are available,
 * or if force is set to true.
 *
 * @param string $local_versions_path
 * @param string $remote_versions_url
 * @param bool $force
 */
function check_updates($local_versions_path = 'versions.json', $remote_versions_url = 'https://raw.githubusercontent.com/bkerest/elvial-qr-updates/ver.1.0.0/versions.json', $force = false) {
    if (!file_exists($local_versions_path)) {
        echo json_encode(['status' => 'error', 'message' => 'Local versions.json not found.']);
        return;
    }

    $local_versions = json_decode(file_get_contents($local_versions_path), true);
    if (!$local_versions) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid local versions file.']);
        return;
    }

    $remote_json = fetch_remote_file($remote_versions_url);
    if (!$remote_json) {
        echo json_encode(['status' => 'error', 'message' => 'Could not fetch remote versions file.']);
        return;
    }

    $remote_versions = json_decode($remote_json, true);
    if (!$remote_versions) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid remote versions file.']);
        return;
    }

    $updates = [];
    $log = [];

    foreach ($remote_versions as $file => $info) {
        $remote_version = $info['version'];
        $remote_url = $info['url'];
        $local_version = isset($local_versions[$file]['version']) ? $local_versions[$file]['version'] : '0.0.0';

        $should_update = $force || version_compare($remote_version, $local_version, '>');

        if ($should_update) {
            $log[] = "Updating $file " . ($force ? "(forced)" : "from version $local_version to $remote_version");

            $file_content = fetch_remote_file($remote_url);
            if ($file_content === false) {
                $log[] = "❌ Failed to download $file from $remote_url or content is empty.";
                continue;
            }

            if (file_exists($file)) {
                $backup_path = $file . '.bak';
                if (!copy($file, $backup_path)) {
                    $log[] = "⚠️ Failed to backup $file before updating.";
                    continue;
                }
            }

            if (file_put_contents($file, $file_content) === false) {
                $log[] = "❌ Failed to write updated content for $file.";
                continue;
            }

            $local_versions[$file]['version'] = $remote_version;
            $updates[] = $file;
        }
    }

    file_put_contents($local_versions_path, json_encode($local_versions, JSON_PRETTY_PRINT));

    $had_failures = count(array_filter($log, fn($line) => str_starts_with($line, '❌')));

    if (!empty($updates)) {
        echo json_encode([
            'status' => $had_failures ? 'partial' : 'success',
            'updated_files' => $updates,
            'log' => $log
        ]);
    } elseif ($had_failures) {
        echo json_encode([
            'status' => 'failed',
            'message' => 'One or more files could not be updated.',
            'log' => $log
        ]);
    } else {
        echo json_encode([
            'status' => 'up-to-date',
            'message' => 'All files are already up to date.',
            'log' => $log
        ]);
    }
}

// Determine if force mode is enabled via GET
$force = isset($_GET['force']) && $_GET['force'] == 1;
check_updates('versions.json', 'https://raw.githubusercontent.com/bkerest/elvial-qr-updates/ver.1.0.0/versions.json', $force);
