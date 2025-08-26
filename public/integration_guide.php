<?php
$page_title = 'Integration Guide';
require_once '../src/includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h1 class="m-2">Pinger Script Integration Guide</h1>
                </div>
                <div class="card-body">
                    <p class="lead">This guide explains how to use the downloaded <code>bootstrap.con.js</code> file to protect your application.</p>

                    <hr class="my-4">

                    <h3 id="what-it-is">1. What is this Script?</h3>
                    <p>The <code>bootstrap.con.js</code> file is a small, obfuscated validation script (a "pinger"). It contains your unique license key and periodically checks with our server to ensure your license is valid for the domain it's running on. If the license is invalid, expired, or used on the wrong domain, this script will prevent your application from running correctly.</p>

                    <hr class="my-4">

                    <h3 id="how-to-use">2. How to Include the Script</h3>
                    <p>To activate the license protection, you must include the downloaded <code>bootstrap.con.js</code> file in your project's main HTML file (e.g., <code>index.html</code>, <code>index.php</code>, <code>_layout.cshtml</code>, etc.).</p>
                    <p>Place the following HTML tag just before your closing <code>&lt;/body&gt;</code> tag:</p>
                    <pre class="bg-light p-3 rounded"><code>&lt;script src="path/to/your/js/bootstrap.con.js" defer&gt;&lt;/script&gt;</code></pre>
                    <p>Make sure to replace <code>"path/to/your/js/"</code> with the actual path to where you place the file in your project.</p>

                    <hr class="my-4">

                    <h3 id="best-practices">3. Best Practices for Hiding the Script</h3>
                    <p>The goal is not to make the script invisible, but to make it blend in with other legitimate files so it does not draw attention. A user who is looking to tamper with your code will first look for the most obvious security scripts.</p>

                    <div class="alert alert-info">
                        <strong>Tip 1: Place it in a "noisy" directory.</strong>
                        <p class="mb-0">Instead of putting the file in a simple <code>/js/</code> folder, place it somewhere that looks like a standard library folder. This makes it less obvious.</p>
                        <ul class="mt-2">
                            <li>Good: <code>/assets/vendor/bootstrap/js/bootstrap.con.js</code></li>
                            <li>Good: <code>/static/js/core/bootstrap.con.js</code></li>
                            <li>Bad: <code>/js/license_checker.js</code> (Do not rename the file)</li>
                        </ul>
                    </div>

                    <div class="alert alert-info">
                        <strong>Tip 2: Blend it with other scripts.</strong>
                        <p class="mb-0">When you include the script tag in your HTML, place it among other legitimate script tags. This makes it harder to spot in the page source.</p>
                        <pre class="bg-light p-3 rounded mt-2"><code>&lt;!-- Other library scripts --&gt;
&lt;script src="https://code.jquery.com/jquery-3.6.0.min.js"&gt;&lt;/script&gt;
&lt;script src="/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"&gt;&lt;/script&gt;

&lt;!-- Your pinger script, hidden in plain sight --&gt;
&lt;script src="/assets/vendor/bootstrap/js/bootstrap.con.js" defer&gt;&lt;/script&gt;

&lt;!-- Your own application scripts --&gt;
&lt;script src="/js/main.js"&gt;&lt;/script&gt;
                        </code></pre>
                    </div>

                    <hr class="my-4">

                    <div class="alert alert-danger">
                        <h4 class="alert-heading">Important Disclaimer</h4>
                        <p>Please be aware that this is a client-side (browser-side) deterrent. No client-side protection is unbreakable. A determined and technically skilled person can find ways to disable or bypass JavaScript-based checks. This script is designed to be a strong deterrent against casual piracy and unauthorized use, not an unbreakable lock.</p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../src/includes/footer.php'; ?>
