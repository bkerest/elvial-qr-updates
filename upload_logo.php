<?php
// Start output buffering to control HTML response
ob_start();

// Handle POST + File Upload
$message = '';
$status = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['companyLogo'])) {
    $uploadDir = __DIR__ . '/files/';
    $uploadFile = $_FILES['companyLogo'];
    $allowedTypes = ['image/bmp', 'image/jpeg', 'image/png', 'image/jpg'];

    if (!in_array($uploadFile['type'], $allowedTypes)) {
        $message = '❌ Invalid file type. Only BMP, JPG, JPEG, and PNG files are allowed.';
    } elseif ($uploadFile['error'] !== UPLOAD_ERR_OK) {
        $message = '❌ An error occurred during the file upload.';
    } else {
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $tempPath = $uploadFile['tmp_name'];
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
                $image = false;
        }

        if ($image) {
            imagepng($image, $outputPath);
            imagedestroy($image);
            $message = '✅ Logo uploaded and converted successfully!';
            $status = 'success';
        } else {
            $message = '❌ Failed to process the uploaded image.';
        }
    }
} else {
    $message = '⚠️ No file uploaded.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Logo Result</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .status {
            padding: 15px;
            border-radius: 6px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .log { background: #fff; padding: 15px; border: 1px solid #ccc; border-radius: 6px; }
        button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            border: none;
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }
        button:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <div class="status <?= $status ?>"><?= $message ?></div>
    <button onclick="refreshBack()">← Back</button>

    <script>
        function refreshBack() {
            window.location = document.referrer;
        }
    </script>
</body>
</html>
