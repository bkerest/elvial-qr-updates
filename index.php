<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELVIAL QR GENERATOR</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>ELVIAL QR Generator</h1>

    <div class="form-container">
        <form action="upload.php" method="post" enctype="multipart/form-data" target="_blank">
            <label for="file">Choose Excel File:</label>
            <input type="file" name="file" id="file" accept=".xlsx" required>
            <div class="button-container">
                <button type="submit" class="button">Preview</button>
            </div>
        </form>
    </div>

    <a href="settings.php" class="settings-link">Settings</a>
</body>
</html>
