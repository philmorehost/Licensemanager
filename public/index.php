<?php
require_once '../src/db.php'; // For fetching packages
$page_title = 'Welcome - License Manager';
require_once '../src/includes/header.php';

// Fetch packages for the pricing section
$stmt = $pdo->query("SELECT * FROM packages WHERE price > 0 ORDER BY price");
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Load settings for currency
$settings_file = '../src/settings.json';
$settings = [];
if (file_exists($settings_file)) {
    $settings = json_decode(file_get_contents($settings_file), true);
}
$currency = $settings['currency'] ?? 'USD';
?>

<style>
    .hero { background: #343a40; color: white; padding: 100px 0; text-align: center; }
    .hero h1 { font-size: 3.5rem; font-weight: 700; }
    .section { padding: 60px 0; }
    .card { box-shadow: 0 4px 8px rgba(0,0,0,.1); transition: transform .2s; }
    .card:hover { transform: scale(1.05); }
</style>

<header class="hero">
    <div class="container">
        <h1><?= trans('hero_title') ?></h1>
        <p class="lead"><?= trans('hero_subtitle') ?></p>
        <a href="#pricing" class="btn btn-primary btn-lg"><?= trans('get_started') ?></a>
    </div>
</header>

<section id="features" class="section">
    <div class="container">
        <h2 class="text-center mb-5"><?= trans('features') ?></h2>
        <div class="row">
            <div class="col-md-4">
                <div class="card text-center p-4 mb-4">
                    <h3><?= trans('feature_easy_integration_title') ?></h3>
                    <p><?= trans('feature_easy_integration_desc') ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center p-4 mb-4">
                    <h3><?= trans('feature_secure_licensing_title') ?></h3>
                    <p><?= trans('feature_secure_licensing_desc') ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center p-4 mb-4">
                    <h3><?= trans('feature_dashboards_title') ?></h3>
                    <p><?= trans('feature_dashboards_desc') ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="pricing" class="section bg-light">
    <div class="container">
        <h2 class="text-center mb-5"><?= trans('pricing') ?></h2>
        <div class="row justify-content-center">
            <?php foreach ($packages as $package): ?>
                <div class="col-lg-4">
                    <div class="card text-center p-4 mb-4">
                        <h3><?= htmlspecialchars($package['name']) ?></h3>
                        <p class="h1"><?= htmlspecialchars($currency) ?> <?= htmlspecialchars(number_format($package['price'], 2)) ?></p>
                        <ul class="list-unstyled my-4">
                            <?php foreach (explode(',', $package['features']) as $feature): ?>
                                <li><?= htmlspecialchars(trim($feature)) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="register.php" class="btn btn-primary"><?= trans('choose_plan') ?></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once '../src/includes/footer.php'; ?>
