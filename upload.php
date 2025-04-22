<?php
session_start(); // Start the session to store data

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include required libraries
require_once __DIR__ . '/libs/SimpleXLSX.php';
require_once __DIR__ . '/libs/phpqrcode/qrlib.php';

// Use the correct namespace for SimpleXLSX
use Shuchkin\SimpleXLSX;

// Check if SimpleXLSX class is loaded
if (!class_exists(SimpleXLSX::class)) {
    die("Failed to load SimpleXLSX class.");
}

// Check if a file is uploaded
if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['file']['tmp_name'];

    // Verify file type
    if ($_FILES['file']['type'] !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
        die('Invalid file type. Please upload an Excel file (.xlsx).');
    }

    // Load the Excel file using SimpleXLSX
    if ($xlsx = SimpleXLSX::parse($fileTmpPath)) {
        $data = $xlsx->rows();

        // Extract Job Details
        $jobName = $data[0][1] ?? 'N/A';
        $jobNumber = $data[1][1] ?? 'N/A';
        $customerName = $data[2][1] ?? 'N/A';
        $addressStreet = $data[3][1] ?? 'N/A';
        $addressCity = $data[4][1] ?? 'N/A';
        $addressZip = $data[5][1] ?? 'N/A';
        $addressCountry = $data[6][1] ?? 'N/A';
        $contactPhone = $data[7][1] ?? 'N/A';
        $fileDate = $data[8][1] ?? 'N/A';

        // Extract Elevation Data
        $tags = [];
        $start = false;

        foreach ($data as $index => $row) {
            if (isset($row[0])) {
                if ($row[0] === '!Start!') {
                    $start = true;
                    continue;
                }
                if ($row[0] === '!End!') {
                    break;
                }
            }

            // Skip rows until after !Start! and skip the header row (first row after !Start!)
            if ($start && $index === array_search('!Start!', array_column($data, 0)) + 1) {
                continue; // Skip the header row
            }

            if ($start && !empty($row[0])) {
                // Dynamically adjust the path in column E (header "Picture Path")
                $originalPath = $row[4] ?? ''; // Column E (index 4)
                $adjustedPath = adjustDynamicPath($originalPath);

                $tags[] = [
                    'elevation_name' => $row[0] ?? '',
                    'elevation_id'   => $row[1] ?? '',
                    'guid'           => $row[2] ?? '',
                    'instance_no'    => $row[3] ?? '',
                    'bmp_path'       => $adjustedPath, // Use the adjusted path
                ];
            }
        }

        // Find the row index of "!Start!"
        $startRowIndex = array_search('!Start!', array_column($data, 0));

        // Set the dynamic row index to the row immediately after "!Start!"
        $dynamicRowIndex = $startRowIndex + 1;
        
        // Extract Titles
        $elevationTitle = $data[$dynamicRowIndex][0] ?? 'Elevation'; // Elevation title
        $instanceTitle = $data[$dynamicRowIndex][3] ?? 'Instance'; // Instance title
        $jobNameTitle = $data[0][0] ?? 'Job Name'; // Job Name title
        $jobNumberTitle = $data[1][0] ?? 'Job Number'; // Job Number title
        $customerNameTitle = $data[2][0] ?? 'Order Number'; // Order Number title
        $addressStreetTitle = $data[3][0] ?? 'Address Street'; // Address Street title
        $addressCityTitle = $data[4][0] ?? 'Address City'; // Address City title
        $addressZipTitle = $data[5][0] ?? 'Address Zip'; // Address Zip title
        $addressCountryTitle = $data[6][0] ?? 'Address Country'; // Address Country title
        $contactPhoneTitle = $data[7][0] ?? 'Contact Phone'; // Contact Phone title
        $fileDateTitle = $data[8][0] ?? 'File Date'; // File Date title

        // Extract the original filename without the extension
        $originalFileName = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
        
        // Store data in session
        $_SESSION['jobName'] = $jobName;
        $_SESSION['jobNumber'] = $jobNumber;
        $_SESSION['customerName'] = $customerName;
        $_SESSION['addressStreet'] = $addressStreet;
        $_SESSION['addressCity'] = $addressCity;
        $_SESSION['addressZip'] = $addressZip;
        $_SESSION['addressCountry'] = $addressCountry;
        $_SESSION['contactPhone'] = $contactPhone;
        $_SESSION['fileDate'] = $fileDate;        
        $_SESSION['tags'] = $tags;

        // Store titles in session
        $_SESSION['elevationTitle'] = $elevationTitle;
        $_SESSION['instanceTitle'] = $instanceTitle;
        $_SESSION['jobNameTitle'] = $jobNameTitle;
        $_SESSION['jobNumberTitle'] = $jobNumberTitle;
        $_SESSION['customerNameTitle'] = $customerNameTitle;
        $_SESSION['addressStreetTitle'] = $addressStreetTitle;
        $_SESSION['addressCityTitle'] = $addressCityTitle;
        $_SESSION['addressZipTitle'] = $addressZipTitle;
        $_SESSION['addressCountryTitle'] = $addressCountryTitle;
        $_SESSION['contactPhoneTitle'] = $contactPhoneTitle;
        $_SESSION['fileDateTitle'] = $fileDateTitle;

        // Store the filename in the session
        $_SESSION['originalFileName'] = $originalFileName;

                // --- HTML Preview ---
                ?>
                <!DOCTYPE html>
                <html>
                <head>
                    <title>PDF Preview</title>
                    <link rel="stylesheet" href="style.css">
                    <script>
                        // Automatically load generate_pdf.php after the page is fully loaded
                        window.onload = function() {
                            // Create a hidden iframe to load generate_pdf.php
                            const iframe = document.createElement('iframe');
                            iframe.style.display = 'none'; // Hide the iframe
                            iframe.src = 'generate_pdf.php'; // Set the source to generate_pdf.php
                            document.body.appendChild(iframe); // Append the iframe to the body
                        };
                    </script>
                </head>
                <body>            
                    <div class="page-container">
                    <?php
                foreach ($_SESSION['tags'] as $index => $tag) {
                    ?>
                    <div class="tag-container">
                        <div class="top-area">
                            <?php if (file_exists(__DIR__ . '/files/logo.png')): ?>
                                <img src="files/logo.png" alt="Company Logo" class="logo">
                            <?php endif; ?>

                            <?php if (!empty($tag['guid'])): ?>
                                <img src="data:image/png;base64,<?php
                                    ob_start();
                                    QRcode::png($tag['guid'], null, QR_ECLEVEL_L, 3, 3);
                                    $image_data = ob_get_contents();
                                    ob_end_clean();
                                    echo base64_encode($image_data);
                                ?>" alt="QR Code" class="qr-code">
                            <?php endif; ?>
                        </div>

                        <div class="middle-area">
                            <div style="font-size: 14px;">
                                <strong><?php echo htmlspecialchars($_SESSION['elevationTitle']); ?>:</strong> <?php echo htmlspecialchars($tag['elevation_name']); ?>
                            </div>                            
                            <strong><?php echo htmlspecialchars($_SESSION['instanceTitle']); ?>:</strong> <?php echo htmlspecialchars($tag['instance_no']); ?><br>
                            <strong><?php echo htmlspecialchars($_SESSION['jobNameTitle']); ?></strong> <?php echo htmlspecialchars($_SESSION['jobName']); ?><br>
                            <strong><?php echo htmlspecialchars($_SESSION['jobNumberTitle']); ?></strong> <?php echo htmlspecialchars($_SESSION['jobNumber']); ?><br>
                            <strong><?php echo htmlspecialchars($_SESSION['customerNameTitle']); ?></strong> <?php echo htmlspecialchars($_SESSION['customerName']); ?><br>
                            <strong><?php echo htmlspecialchars($_SESSION['addressStreetTitle']); ?></strong> <?php echo htmlspecialchars($_SESSION['addressStreet']); ?><br>
                            <strong><?php echo htmlspecialchars($_SESSION['addressCityTitle']); ?></strong> <?php echo htmlspecialchars($_SESSION['addressCity']); ?><br>
                            <strong><?php echo htmlspecialchars($_SESSION['addressZipTitle']); ?></strong> <?php echo htmlspecialchars($_SESSION['addressZip']); ?><br>
                            <strong><?php echo htmlspecialchars($_SESSION['addressCountryTitle']); ?></strong> <?php echo htmlspecialchars($_SESSION['addressCountry']); ?><br>
                            <strong><?php echo htmlspecialchars($_SESSION['contactPhoneTitle']); ?></strong> <?php echo htmlspecialchars($_SESSION['contactPhone']); ?><br>
                            <strong><?php echo htmlspecialchars($_SESSION['fileDateTitle']); ?></strong> <?php echo htmlspecialchars($_SESSION['fileDate']); ?><br>
                        </div>

                        <div class="bottom-area">
                            <?php if (!empty($tag['bmp_path']) && file_exists($tag['bmp_path'])): ?>
                            <img src="data:image/bmp;base64,<?php
                            $image_data = base64_encode(file_get_contents($tag['bmp_path']));
                            echo $image_data;
                            ?>" alt="BMP Image" class="bmp-image">
                            <?php else: ?>
                                <strong>BMP not found</strong>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </body>
        </html>
        <?php

    } else {
        die(SimpleXLSX::parseError());
    }
} else {
    echo "Error uploading file.";
}

// Adjust the dynamic path by replacing the dynamic folder with the correct one.
function adjustDynamicPath($originalPath) {
  // Get the dynamic base path from the original path
  $localAppData = getenv('LOCALAPPDATA'); // Retrieve the %LOCALAPPDATA% environment variable
  $basePathPattern = preg_quote($localAppData, '/'); // Escape special characters in the base path
  $basePathRegex = "/^" . $basePathPattern . "\\\\OFCAS\\\\.*?\\\\0\\\\/"; // Match up to \0\ dynamically

  // Extract the base path dynamically from the original path
  if (preg_match($basePathRegex, $originalPath, $matches)) {
      $basePath = $matches[0]; // Get the base path up to \0\
  } else {
      error_log("Base path could not be determined from the original path: $originalPath");
      return $originalPath; // Return the original path if the base path cannot be determined
  }

  // Check if the base path exists
  if (!is_dir($basePath)) {
      error_log("Base path does not exist: $basePath");
      return $originalPath; // Return the original path if the base path is invalid
  }

  // Scan the directory for folders
  $folders = scandir($basePath);
  $correctFolder = null;

  foreach ($folders as $folder) {
      // Match folders starting with "u-LogiComWrapper-"
      if (preg_match("/^u-LogiComWrapper-/", $folder)) {
          $correctFolder = $folder;
          break; // Stop once we find the first matching folder
      }
  }

  // If a correct folder is found, replace the dynamic part of the path
  if ($correctFolder) {
      // Log the folder being used for debugging
      error_log("Using folder: $correctFolder");

      // Replace the dynamic part of the path with the correct folder
      return preg_replace("/(p|u)-LogiComWrapper-[^\\\\]+/", $correctFolder, $originalPath);
  }

  // Log if no matching folder was found
  error_log("No matching folder found in base path: $basePath");

  // If no correct folder is found, return the original path
  return $originalPath;
}
