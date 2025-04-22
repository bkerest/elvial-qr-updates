<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // Show a pop-up if the success flag is present in the URL
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success')) {
                alert('Logo uploaded successfully!');
            }
            if (urlParams.has('update')) {
                const updateStatus = urlParams.get('update');
                if (updateStatus === 'success') {
                    alert('Update completed successfully!');
                } else if (updateStatus === 'none') {
                    alert('You are already using the latest version.');
                } else {
                    alert('Failed to check for updates.');
                }
            }
        };
    </script>
</head>
<body>
    <h2>Settings</h2>

    <form action="upload_logo.php" method="post" enctype="multipart/form-data">
        <label for="companyLogo">Upload Company Logo:</label>
        <input type="file" name="companyLogo" id="companyLogo" accept=".bmp, .jpg, .jpeg, .png" required>
        <button type="submit">Upload Logo</button>
    </form>

    <?php if (file_exists(__DIR__ . '/files/logo.png')): ?>
        <h3>Current Company Logo:</h3>
        <img src="files/logo.png" alt="Company Logo" style="max-width: 200px; max-height: 200px;">
    <?php endif; ?>
    
    <div class="button-container">
        <form action="check_updates.php" method="post">
            <button type="submit" class="button">Check for Updates</button>
        </form>
    </div>

    <div class="button-container">
        <button class="button" onclick="window.location.href='index.php';">Back</button>
    </div>
</body>
</html>
