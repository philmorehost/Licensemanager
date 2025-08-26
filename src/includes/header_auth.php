<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'License Manager') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: #f8f9fa; }
        .form-container { background: white; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 4px 8px rgba(0,0,0,.1); width: 100%; max-width: 400px; }
    </style>
</head>
<body>
    <div class="form-container">
    <!-- Auth page content starts here -->
