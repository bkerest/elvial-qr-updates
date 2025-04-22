<?php
// File: check_updates.php

/**
 * Fetches a remote file's content.
 *
 * @param string $url The URL of the remote file.
 * @return string|false The file content or false on failure.
 */
function fetch_remote_file($url) {
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: ELVIAL QR Updater\r\n"
        ]
    ]);

    $data = @file_get_contents($url, false, $context);
    if ($data === false) {
        $error = error_get_last();
        error_log("Failed to fetch $url: " . $error['message']);
    }
    return ($data !== false && strlen($data) > 0) ? $data : false;
}

/**
 * Checks for updates and updates local files if newer versions are available.
 *
 * @param string $local_versions_path Path to the local versions.json file.
 * @param string $remote_versions_url URL to the remote versions.json file.
 */
function check_updates($local_versions_path = 'versions.json', $remote_versions_url = 'https://raw.githubusercontent.com/bkerest/elvial-qr-updates/ver.1.0.0/versions.json') {
    // Check if the local versions.json file exists
    if (!file_exists($local_versions_path)) {
        echo json_encode(['status' => 'error', 'message' => 'Local versions.json not found.']);
        return;
    }

    // Load and decode the local versions.json file
    $local_versions = json_decode(file_get_contents($local_versions_path), true);
    if (!$local_versions) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid local versions file.']);
        return;
    }

    // Fetch and decode the remote versions.json file
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

    // Iterate through the remote versions and compare with local versions
    foreach ($remote_versions as $file => $info) {
        $remote_version = $info['version'];
        $remote_url = $info['url'];
        $local_version = isset($local_versions[$file]['version']) ? $local_versions[$file]['version'] : '0.0.0';

        // Check if the remote version is newer
        if (version_compare($remote_version, $local_version, '>')) {
            $log[] = "Updating $file from version $local_version to $remote_version";

            // Fetch the remote file content
            $file_content = fetch_remote_file($remote_url);
            if ($file_content === false) {
                $log[] = "❌ Failed to download $file from $remote_url or content is empty.";
                continue;
            }

            // Backup the existing file if it exists
            if (file_exists($file)) {
                $backup_path = $file . '.bak';
                if (!copy($file, $backup_path)) {
                    $log[] = "⚠️ Failed to backup $file before updating.";
                    continue;
                }
            }

            // Write the updated content to the file
            if (file_put_contents($file, $file_content) === false) {
                $log[] = "❌ Failed to write updated content for $file.";
                continue;
            }

            // Update the local versions.json file
            $local_versions[$file]['version'] = $remote_version;
            $updates[] = $file;
        }
    }

    // Save the updated local versions.json file
    file_put_contents($local_versions_path, json_encode($local_versions, JSON_PRETTY_PRINT));

    // Determine the status of the update process
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

// Run the updater
check_updates();
