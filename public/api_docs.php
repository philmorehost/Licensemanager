<?php
$page_title = 'API Documentation - License Manager';
require_once '../src/includes/header.php';
?>

<style>
    .content { padding: 40px 0; }
    .code-block { background-color: #e9ecef; padding: 1rem; border-radius: 0.25rem; white-space: pre-wrap; word-wrap: break-word; }
</style>

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

<?php require_once '../src/includes/footer.php'; ?>
