<?php
$page_title = 'Installation - Step 1: Requirements Check';
$all_checks_passed = true;

// --- Requirement Checks ---

// 1. PHP Version
$min_php_version = '7.4';
$php_version = phpversion();
$php_check = version_compare($php_version, $min_php_version, '>=');
if (!$php_check) $all_checks_passed = false;

// 2. PHP Extensions
$required_extensions = ['pdo_mysql', 'json'];
$extensions_check = [];
foreach ($required_extensions as $ext) {
    $is_loaded = extension_loaded($ext);
    $extensions_check[$ext] = $is_loaded;
    if (!$is_loaded) $all_checks_passed = false;
}

// 3. Directory Permissions
$writable_paths = [
    '../src/',
    '../public/uploads/'
];
$permissions_check = [];
foreach ($writable_paths as $path) {
    $is_writable = is_writable($path);
    $permissions_check[$path] = $is_writable;
    if (!$is_writable) $all_checks_passed = false;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h1 class="h3 m-2 text-center">License Manager Installation</h1>
                    </div>
                    <div class="card-body p-4">
                        <h4 class="mb-4">Step 1: Server Requirements</h4>

                        <ul class="list-group">
                            <!-- PHP Version Check -->
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>PHP Version >= <?= $min_php_version ?> (Your version: <?= $php_version ?>)</div>
                                <?php if ($php_check): ?>
                                    <span class="badge bg-success">Pass</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Fail</span>
                                <?php endif; ?>
                            </li>

                            <!-- PHP Extensions Check -->
                            <?php foreach ($extensions_check as $ext => $is_loaded): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div><code><?= $ext ?></code> extension loaded</div>
                                <?php if ($is_loaded): ?>
                                    <span class="badge bg-success">Pass</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Fail</span>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>

                            <!-- Directory Permissions Check -->
                            <?php foreach ($permissions_check as $path => $is_writable): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div><code><?= str_replace('../', '', $path) ?></code> is writable</div>
                                <?php if ($is_writable): ?>
                                    <span class="badge bg-success">Pass</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Fail</span>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="d-grid mt-4">
                            <?php if ($all_checks_passed): ?>
                                <a href="setup.php" class="btn btn-primary">Proceed to Step 2: Setup</a>
                            <?php else: ?>
                                <button class="btn btn-primary" disabled>Proceed to Step 2: Setup</button>
                                <p class="text-danger mt-2 text-center">Please fix the failed requirements before you can proceed.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
