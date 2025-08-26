<?php
session_start();
require_once '../src/db.php';
require_once '../src/includes/language.php'; // Manually include for POST logic before header

$errors = [];

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_identifier = trim($_POST['login_identifier']);
    $password = $_POST['password'];

    if (empty($login_identifier)) { $errors[] = trans('error_login_identifier_required'); }
    if (empty($password)) { $errors[] = trans('error_password_required'); }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$login_identifier, $login_identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: dashboard.php');
            exit();
        } else {
            $errors[] = trans('error_invalid_credentials');
        }
    }
}

$page_title = trans('login_page_title');
require_once '../src/includes/header_auth.php';
?>

<h2 class="text-center mb-4"><?= trans('login_heading') ?></h2>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <p class="mb-0"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="POST" action="login.php">
    <div class="mb-3">
        <label for="login_identifier" class="form-label"><?= trans('form_label_username_or_email') ?></label>
        <input type="text" class="form-control" id="login_identifier" name="login_identifier" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label"><?= trans('form_label_password') ?></label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary w-100"><?= trans('login') ?></button>
</form>

<p class="text-center mt-3">
    <?= trans('prompt_no_account') ?> <a href="register.php"><?= trans('register_here') ?></a>.
</p>

<?php require_once '../src/includes/footer_auth.php'; ?>
