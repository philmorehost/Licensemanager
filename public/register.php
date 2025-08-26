<?php
session_start();
require_once '../src/db.php';
require_once '../src/includes/language.php'; // Manually include for POST logic

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username)) { $errors[] = trans('error_username_required'); }
    if (empty($email)) { $errors[] = trans('error_email_required'); }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = trans('error_invalid_email'); }
    if (empty($password)) { $errors[] = trans('error_password_required'); }
    elseif (strlen($password) < 8) { $errors[] = trans('error_password_min_length'); }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = trans('error_user_exists');
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $api_key = bin2hex(random_bytes(16));
            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, api_key) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $email, $password_hash, $api_key]);
                $success_message = trans('success_registration');
            } catch (PDOException $e) {
                $errors[] = trans('error_db_register');
            }
        }
    }
}

$page_title = trans('register_page_title');
require_once '../src/includes/header_auth.php';
?>

<h2 class="text-center mb-4"><?= trans('create_account_heading') ?></h2>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <p class="mb-0"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($success_message): ?>
    <div class="alert alert-success">
        <p class="mb-0"><?= $success_message // Raw output because it contains a link ?></p>
    </div>
<?php else: ?>
    <form method="POST" action="register.php">
        <div class="mb-3">
            <label for="username" class="form-label"><?= trans('form_label_username') ?></label>
            <input type="text" class="form-control" id="username" name="username" required value="<?= htmlspecialchars($username ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label"><?= trans('form_label_email') ?></label>
            <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($email ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label"><?= trans('form_label_password') ?></label>
            <input type="password" class="form-control" id="password" name="password" minlength="8" required>
        </div>
        <button type="submit" class="btn btn-primary w-100"><?= trans('register') ?></button>
    </form>
<?php endif; ?>

<p class="text-center mt-3">
    <?= trans('prompt_have_account') ?> <a href="login.php"><?= trans('login_here') ?></a>.
</p>

<?php require_once '../src/includes/footer_auth.php'; ?>
