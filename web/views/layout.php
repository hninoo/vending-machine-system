<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Vending Machine'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/style.css">
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
</head>
<body class="app-shell">
    <nav class="navbar navbar-expand-lg app-nav sticky-top">
        <div class="container">
            <a class="navbar-brand fw-semibold" href="/admin/dashboard">Vending Machine</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav gap-lg-1">
                    <li class="nav-item">
                        <a class="nav-link" href="/products">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/products/create">Add Product</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/users">Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/users/create">Add User</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/transactions">Transactions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/transactions/create">Add Transaction</a>
                    </li>
                </ul>
                <form class="d-flex ms-auto" method="POST" action="/admin/logout">
                    <button class="btn btn-outline-danger btn-sm" type="submit">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="container app-main">
        <?php echo $content; ?>
    </main>

    <footer class="app-footer">
        <div class="container">
            <span class="text-muted">&copy; 2026 Vending Machine</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
