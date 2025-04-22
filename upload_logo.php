<?php
// Check if a file was uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['companyLogo'])) {
    $uploadDir = __DIR__ . '/files/'; // Directory to store the logo
    $uploadFile = $_FILES['companyLogo'];
    $allowedTypes = ['image/bmp', 'image/jpeg', 'image/png', 'image/jpg']; // Allowed MIME types

    // Validate the uploaded file
    if (!in_array($uploadFile['type'], $allowedTypes)) {
        die('Invalid file type. Only BMP, JPG, JPEG, and PNG files are allowed.');
    }

    // Check for upload errors
    if ($uploadFile['error'] !== UPLOAD_ERR_OK) {
        die('An error occurred during the file upload.');
    }

    // Ensure the /files directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Temporary file path
    $tempPath = $uploadFile['tmp_name'];

    // Convert the uploaded file to PNG and save it as logo.png
    $outputPath = $uploadDir . 'logo.png';

    switch ($uploadFile['type']) {
        case 'image/bmp':
            $image = imagecreatefrombmp($tempPath);
            break;
        case 'image/jpeg':
        case 'image/jpg':
            $image = imagecreatefromjpeg($tempPath);
            break;
        case 'image/png':
            $image = imagecreatefrompng($tempPath);
            break;
        default:
            die('Unsupported image type.');
    }

    // Save the image as PNG
    if ($image) {
        imagepng($image, $outputPath);
        imagedestroy($image); // Free up memory

        // Redirect back to settings.php with a success flag
        header('Location: settings.php?success=1');
        exit;
    } else {
        die('Failed to process the uploaded image.');
    }
} else {
    die('No file uploaded.');
}
?>
