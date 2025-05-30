<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Expired</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .container {
            text-align: center;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 90%;
        }
        h1 {
            color: #374151;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        p {
            color: #6b7280;
            margin-bottom: 1.5rem;
        }
        .btn {
            display: inline-block;
            background-color: #f59e0b;
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #d97706;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Session Expired</h1>
        <p>Your session has expired due to inactivity. Please log in again to continue.</p>
        <a href="{{ route('filament.admin.auth.login') }}" class="btn">
            Back to Login
        </a>
    </div>
</body>
</html>
