<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wartungsarbeiten | LSPD Panel</title>
    <style>
        body {
            background-color: #f4f6f9;
            color: #333;
            font-family: 'Source Sans Pro', sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            text-align: center;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 90%;
        }
        h1 { color: #007bff; margin-bottom: 10px; }
        p { font-size: 1.1rem; color: #6c757d; margin-bottom: 30px; }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        <h1>Systemupdate</h1>
        <p>Wir führen gerade ein wichtiges Update durch, um das LSPD Panel zu verbessern.</p>
        <p><small>Die Seite lädt automatisch neu, sobald wir fertig sind.</small></p>
    </div>
</body>
</html>