<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>License Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .hero {
            background: #343a40;
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
        }
        .section {
            padding: 60px 0;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,.1);
            transition: transform .2s;
        }
        .card:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="#">License Manager</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#pricing">Pricing</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-primary me-2" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary" href="register.php">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="hero">
        <div class="container">
            <h1>The Ultimate License Management Solution</h1>
            <p class="lead">Protect your software with our robust and easy-to-use license manager.</p>
            <a href="#pricing" class="btn btn-primary btn-lg">Get Started</a>
        </div>
    </header>

    <section id="features" class="section">
        <div class="container">
            <h2 class="text-center mb-5">Features</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="card text-center p-4 mb-4">
                        <h3>Easy Integration</h3>
                        <p>Integrate our license system into your software in minutes with our simple API.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center p-4 mb-4">
                        <h3>Secure Licensing</h3>
                        <p>Protect your software from piracy and unauthorized usage with our secure license keys.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center p-4 mb-4">
                        <h3>Dashboard</h3>
                        <p>Manage your licenses, users, and packages from a single, easy-to-use dashboard.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="pricing" class="section bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Pricing</h2>
            <div class="row">
                <div class="col-lg-4">
                    <div class="card text-center p-4 mb-4">
                        <h3>Basic</h3>
                        <p class="h1">$10</p>
                        <ul class="list-unstyled my-4">
                            <li>10 Licenses</li>
                            <li>1 Website</li>
                            <li>Basic Support</li>
                        </ul>
                        <a href="register.php" class="btn btn-primary">Choose Plan</a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card text-center p-4 mb-4">
                        <h3>Pro</h3>
                        <p class="h1">$50</p>
                        <ul class="list-unstyled my-4">
                            <li>100 Licenses</li>
                            <li>10 Websites</li>
                            <li>Priority Support</li>
                        </ul>
                        <a href="register.php" class="btn btn-primary">Choose Plan</a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card text-center p-4 mb-4">
                        <h3>Enterprise</h3>
                        <p class="h1">$100</p>
                        <ul class="list-unstyled my-4">
                            <li>Unlimited Licenses</li>
                            <li>Unlimited Websites</li>
                            <li>24/7 Support</li>
                        </ul>
                        <a href="register.php" class="btn btn-primary">Choose Plan</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="py-4 bg-dark text-white text-center">
        <div class="container">
            <p>&copy; 2024 License Manager. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
