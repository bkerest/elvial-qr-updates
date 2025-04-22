<?php
// File: check_updates.php

function fetch_remote_file($url) {
    $url_with_bust = $url . (str_contains($url, '?') ? '&' : '?') . 't=' . microtime(true);

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: ELVIAL QR Updater\r\nCache-Control: no-cache, no-store, must-revalidate\r\nPragma: no-cache\r\nExpires: 0\r\n"
        ]
    ]);

    $data = @file_get_contents($url_with_bust, false, $context);
    return ($data !== false && strlen($data) > 0) ? $data : false;
}

function check_updates($local_versions_path = 'versions.json', $remote_versions_url = 'https://raw.githubusercontent.com/bkerest/elvial-qr-updates/ver.1.0.0/versions.json', $force = false) {
    $status = 'up-to-date';
    $message = 'All files are already up to date.';
    $log = [];
    $updates = [];

    if (!file_exists($local_versions_path)) {
        $status = 'error';
        $message = 'Local versions.json not found.';
    } else {
        $local_versions = json_decode(file_get_contents($local_versions_path), true);

        if (!$local_versions) {
            $status = 'error';
            $message = 'Invalid local versions file.';
        } else {
            $remote_json = fetch_remote_file($remote_versions_url);

            if (!$remote_json) {
                $status = 'error';
                $message = 'Could not fetch remote versions file.';
            } else {
                $remote_versions = json_decode($remote_json, true);

                if (!$remote_versions) {
                    $status = 'error';
                    $message = 'Invalid remote versions file.';
                } else {
                    foreach ($remote_versions as $file => $info) {
                        $remote_version = $info['version'];
                        $remote_url = $info['url'];
                        $local_version = isset($local_versions[$file]['version']) ? $local_versions[$file]['version'] : '0.0.0';

                        $should_update = $force || version_compare($remote_version, $local_version, '>');

                        if ($should_update) {
                            $log[] = "🔄 Updating <strong>$file</strong> " . ($force ? "(forced)" : "from version $local_version to $remote_version");

                            $file_content = fetch_remote_file($remote_url);
                            if ($file_content === false) {
                                $log[] = "❌ Failed to download <strong>$file</strong> from $remote_url or content is empty.";
                                continue;
                            }

                            if (file_exists($file)) {
                                $backup_path = $file . '.bak';
                                if (!copy($file, $backup_path)) {
                                    $log[] = "⚠️ Failed to backup <strong>$file</strong> before updating.";
                                    continue;
                                }
                            }

                            if (file_put_contents($file, $file_content) === false) {
                                $log[] = "❌ Failed to write updated content for <strong>$file</strong>.";
                                continue;
                            }

                            $local_versions[$file]['version'] = $remote_version;
                            $updates[] = $file;
                        }
                    }

                    file_put_contents($local_versions_path, json_encode($local_versions, JSON_PRETTY_PRINT));

                    $had_failures = count(array_filter($log, fn($line) => str_contains($line, '❌')));

                    if (!empty($updates)) {
                        $status = $had_failures ? 'partial' : 'success';
                        $message = $had_failures ? 'Some files updated, but some failed.' : 'All updates applied successfully.';
                    } elseif ($had_failures) {
                        $status = 'failed';
                        $message = 'One or more files could not be updated.';
                    }
                }
            }
        }
    }

    // HTML Output
    echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'>";
    echo "<title>Update Results</title>";
    echo "<link rel=\"stylesheet\" href=\"style.css\">";
    echo "</head><body>";

    echo "<div class='status $status'>$message</div>";
    if (!empty($log)) {
        echo "<div class='log'>" . implode("<br>", $log) . "</div>";
    }

    echo "<button onclick='history.back()'>← Back</button>";
    echo "</body></html>";
}

// Run the updater
$force = isset($_GET['force']) && $_GET['force'] == 1;
check_updates('versions.json', 'https://raw.githubusercontent.com/bkerest/elvial-qr-updates/ver.1.0.0/versions.json', $force);
