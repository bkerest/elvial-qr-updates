<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Include the TCPDF library
require_once __DIR__ . '/libs/tcpdf/tcpdf.php';

// Check if data exists in the session
if (isset($_SESSION['tags']) && isset($_SESSION['jobName']) && isset($_SESSION['jobNumber']) && isset($_SESSION['customerName']) &&
    isset($_SESSION['addressStreet']) && isset($_SESSION['addressCity']) && isset($_SESSION['addressZip']) &&
    isset($_SESSION['addressCountry']) && isset($_SESSION['contactPhone']) && isset($_SESSION['fileDate']) &&
    isset($_SESSION['elevationTitle']) && isset($_SESSION['instanceTitle']) && isset($_SESSION['jobNameTitle']) &&
    isset($_SESSION['jobNumberTitle']) && isset($_SESSION['customerNameTitle']) && isset($_SESSION['addressStreetTitle']) &&
    isset($_SESSION['addressCityTitle']) && isset($_SESSION['addressZipTitle']) && isset($_SESSION['addressCountryTitle']) &&
    isset($_SESSION['contactPhoneTitle']) && isset($_SESSION['fileDateTitle'])) {

    // Create a new TCPDF object
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

    // Disable the default header
    $pdf->setPrintHeader(false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('ELVIAL QR Generator');
    $pdf->SetTitle('Generated Tags');
    $pdf->SetSubject('Tags PDF');

    // Set margins
    $pdf->SetMargins(0, 0, 0);
    $pdf->SetAutoPageBreak(false, 0);

    // Set font
    $pdf->SetFont('freesans', '', 9);

    // Add the first page
    $pdf->AddPage();

    // Define tag dimensions
    $tagWidth = 74.25; // Width of each tag in mm
    $tagHeight = 105; // Height of each tag in mm
    $tagsPerRow = 4; // Number of tags per row
    $tagsPerColumn = 2; // Number of tags per column
    $tagsPerPage = $tagsPerRow * $tagsPerColumn;

    // Loop through tags and generate the PDF
    foreach ($_SESSION['tags'] as $index => $tag) {
        // Add a new page every time we exceed the tags per page
        if ($index % $tagsPerPage == 0 && $index > 0) {
            $pdf->AddPage();
        }

        // Calculate the position of the tag on the page
        $x = ($index % $tagsPerRow) * $tagWidth;
        $y = floor(($index % $tagsPerPage) / $tagsPerRow) * $tagHeight;

        // Draw the tag border (optional, for debugging)
        $pdf->Rect($x, $y, $tagWidth, $tagHeight);

        // Top Area: Logo and QR Code
        $logoPath = __DIR__ . '/files/logo.png';
        if (file_exists($logoPath)) {
            list($origWidth, $origHeight) = getimagesize($logoPath);
        
            $maxWidth = 47;
            $maxHeight = 20;
        
            // Calculate scaling while keeping aspect ratio
            $widthRatio = $maxWidth / $origWidth;
            $heightRatio = $maxHeight / $origHeight;
            $scale = min($widthRatio, $heightRatio); // Choose the smaller one
        
            $finalWidth = $origWidth * $scale;
            $finalHeight = $origHeight * $scale;
        
            $pdf->Image(
                $logoPath,
                $x + 3,
                $y + 3,
                $finalWidth,
                $finalHeight,
                '',
                '',
                '',
                false,
                300,
                '',
                false,
                false,
                0,
                false,
                false,
                false
            );
        }

        if (!empty($tag['guid'])) {
            $style = array(
                'border' => false,
                'padding' => 0,
                'fgcolor' => array(0, 0, 0),
                'bgcolor' => false
            );
            $pdf->write2DBarcode($tag['guid'], 'QRCODE', $x + $tagWidth - 24, $y + 4, 20, 20, $style, 'N');
        }

        // Middle Area: Text Information
        $pdf->SetFont('freesans', '', 9);
        $pdf->SetXY($x + 3, $y + 24);
        $pdf->MultiCell(
            $tagWidth - 4, 
            5, 
            $_SESSION['elevationTitle'] . ": " . $tag['elevation_name'] . "\n" .
            $_SESSION['instanceTitle'] . ": " . $tag['instance_no'] . "\n" .
            $_SESSION['jobNameTitle'] . ": " . $_SESSION['jobName'] . "\n" .
            $_SESSION['jobNumberTitle'] . ": " . $_SESSION['jobNumber'] . "\n" .
            $_SESSION['customerNameTitle'] . ": " . $_SESSION['customerName'] . "\n" .
            $_SESSION['addressStreetTitle'] . ": " . $_SESSION['addressStreet'] . "\n" .
            $_SESSION['addressCityTitle'] . ": " . $_SESSION['addressCity'] . "\n" .
            $_SESSION['addressZipTitle'] . ": " . $_SESSION['addressZip'] . "\n" .
            $_SESSION['addressCountryTitle'] . ": " . $_SESSION['addressCountry'] . "\n" .
            $_SESSION['contactPhoneTitle'] . ": " . $_SESSION['contactPhone'] . "\n" .
            $_SESSION['fileDateTitle'] . ": " . $_SESSION['fileDate'], 
            0, 'L', false
        );
        
        

        // Bottom Area: BMP Image
        if (!empty($tag['bmp_path']) && file_exists($tag['bmp_path'])) {
            // Get the original dimensions of the BMP image
            list($originalWidth, $originalHeight) = getimagesize($tag['bmp_path']);
        
            // Define the maximum dimensions for the image
            $maxWidth = 70; // Maximum width in mm
            $maxHeight = 30; // Maximum height in mm
        
            // Calculate the aspect ratio
            $aspectRatio = $originalWidth / $originalHeight;
        
            // Calculate the scaled dimensions while maintaining the aspect ratio
            if ($maxWidth / $maxHeight > $aspectRatio) {
                // Constrain by height
                $scaledHeight = $maxHeight;
                $scaledWidth = $maxHeight * $aspectRatio;
            } else {
                // Constrain by width
                $scaledWidth = $maxWidth;
                $scaledHeight = $maxWidth / $aspectRatio;
            }
        
            // Center the image within the available space
            $imageX = $x + 3 + ($maxWidth - $scaledWidth) / 2;
            $imageY = $y + 73 + ($maxHeight - $scaledHeight) / 2;
        
            // Add the image to the PDF
            $pdf->Image($tag['bmp_path'], $imageX, $imageY, $scaledWidth, $scaledHeight, '', '', '', false, 300, '', false, false, 0, false, false, false);
        }
    }

        // Check if the original filename exists in the session
        if (isset($_SESSION['originalFileName'])) {
            $pdfFileName = $_SESSION['originalFileName'] . '.pdf';
        } else {
            $pdfFileName = 'tags.pdf'; // Fallback filename
        }

        // Output the PDF with the dynamic filename
        $pdf->Output($pdfFileName, 'D');

} else {
    echo "Error: No data to generate PDF.";
}

/**
 * Replace all occurrences of '/' with a non-breaking slash in a string or array.
 *
 * @param mixed $data The data to process (string or array).
 * @return mixed The processed data with '/' replaced.
 */

?>
