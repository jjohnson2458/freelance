<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Freelance Proposal Optimizer</title>
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --primary: #2c5f8a; }
        body { background-color: #f0f4f8; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .brand { color: var(--primary); font-weight: 700; font-size: 1.5rem; text-align: center; margin-bottom: 1.5rem; }
        .card { border: none; box-shadow: 0 2px 12px rgba(0,0,0,0.1); max-width: 420px; width: 100%; }
        .btn-primary { background-color: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background-color: #234b6e; border-color: #234b6e; }
        a { color: var(--primary); }
    </style>
</head>
<body>
    <div class="px-3 w-100" style="max-width: 420px;">
        <div class="brand">Freelance Proposal Optimizer</div>
        <div class="card">
            <div class="card-body p-4">
                <h4 class="card-title mb-4 text-center">Log In</h4>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="POST" action="/login">
                    <?= \Core\Csrf::field() ?>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary">Log In</button>
                    </div>

                    <div class="text-center">
                        <a href="/forgot-password" class="small">Forgot password?</a>
                    </div>
                    <div class="text-center mt-2">
                        <span class="small text-muted">Don't have an account?</span>
                        <a href="/register" class="small">Register</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
