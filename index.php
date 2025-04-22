<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELVIAL QR GENERATOR</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 50px;
        }

        h1 {
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
    </style>
</head>
<body>
    <h1>ELVIAL QR Generator</h1>

    <!-- File Upload Form -->
    <div class="form-container">
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <label for="file">Choose Excel File:</label>
            <input type="file" name="file" id="file" accept=".xlsx" required>
            <div class="button-container">
                <button type="submit" class="button">Preview</button>
            </div>
        </form>
    </div>

    <!-- Link to Settings Page -->
    <a href="settings.php" class="settings-link">Settings</a>
</body>
</html>
