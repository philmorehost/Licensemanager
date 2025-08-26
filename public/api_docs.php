<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - License Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,.1); }
        .content { padding: 40px 0; }
        .code-block { background-color: #e9ecef; padding: 1rem; border-radius: 0.25rem; white-space: pre-wrap; word-wrap: break-word; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">License Manager</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#pricing">Pricing</a></li>
                    <li class="nav-item"><a class="nav-link active" href="api_docs.php">API Docs</a></li>
                    <li class="nav-item"><a class="btn btn-outline-primary me-2" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="btn btn-primary" href="register.php">Register</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container content">
        <h1 class="mb-4">API Documentation</h1>
        <p class="lead">This documentation explains how to use our API to validate software licenses.</p>

        <hr class="my-5">

        <h2 id="validate">License Validation</h2>
        <p>This endpoint allows you to check if a license key is valid for a specific domain.</p>

        <h4 class="mt-4">Endpoint</h4>
        <p><code>POST /api.php</code></p>

        <h4 class="mt-4">Parameters</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Type</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>key</code></td>
                    <td>String</td>
                    <td><strong>Required.</strong> The license key you want to validate.</td>
                </tr>
                <tr>
                    <td><code>domain</code></td>
                    <td>String</td>
                    <td><strong>Required.</strong> The domain name the software is running on.</td>
                </tr>
            </tbody>
        </table>

        <h4 class="mt-4">Example Request (cURL)</h4>
        <pre class="code-block"><code>curl -X POST https://yourwebsite.com/api.php \
-d "key=YOUR_LICENSE_KEY" \
-d "domain=customerdomain.com"</code></pre>

        <h4 class="mt-4">Responses</h4>
        <p>The API returns a JSON object.</p>

        <h5>Successful Response (HTTP 200)</h5>
        <p>When a license is valid and active for the given domain.</p>
        <pre class="code-block"><code>{
    "status": 1,
    "message": "License is valid."
}</code></pre>

        <h5>Error Response (HTTP 200)</h5>
        <p>When the license is invalid, inactive, or the parameters are incorrect.</p>
        <pre class="code-block"><code>{
    "status": 0,
    "message": "Invalid license key or domain."
}</code></pre>
        <p>Other possible error messages include "Missing key or domain parameter." and "A database error occurred."</p>
    </div>

    <footer class="py-4 bg-dark text-white text-center">
        <div class="container">
            <p>&copy; 2024 License Manager. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
