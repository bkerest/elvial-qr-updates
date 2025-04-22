<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 50px;
        }

        h2 {
            margin-bottom: 30px;
        }

        .form-container {
            margin-bottom: 30px;
        }

        .button-container {
            margin-top: 20px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007BFF;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            margin: 10px;
        }

        .button:hover {
            background-color: #0056b3;
        }

        .settings-link {
            display: inline-block;
            margin-top: 20px;
            font-size: 14px;
            color: #007BFF;
            text-decoration: none;
        }

        .settings-link:hover {
            text-decoration: underline;
        }

        img {
            max-width: 200px;
            max-height: 200px;
        }
    </style>
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

    <!-- Form to Upload Company Logo -->
    <form action="upload_logo.php" method="post" enctype="multipart/form-data">
        <label for="companyLogo">Upload Company Logo:</label>
        <input type="file" name="companyLogo" id="companyLogo" accept=".bmp, .jpg, .jpeg, .png" required>
        <button type="submit">Upload Logo</button>
    </form>

    <!-- Display the uploaded logo if it exists -->
    <?php if (file_exists(__DIR__ . '/files/logo.png')): ?>
        <h3>Current Company Logo:</h3>
        <img src="files/logo.png" alt="Company Logo" style="max-width: 200px; max-height: 200px;">
    <?php endif; ?>
    
    <!-- Button to Check for Updates -->
    <div class="button-container">
        <form action="check_updates.php" method="post">
            <button type="submit" class="button">Check for Updates</button>
        </form>
    </div>

    <!-- Back Button -->
    <div class="button-container">
        <button class="button" onclick="window.location.href='index.php';">Back</button>
    </div>
</body>
</html>
