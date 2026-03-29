<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Freelance Proposal Optimizer</title>
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
                <h4 class="card-title mb-4 text-center">Create Account</h4>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="/register">
                    <?= \Core\Csrf::field() ?>

                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="8">
                        <div class="form-text">Must be at least 8 characters.</div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required minlength="8">
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>

                    <div class="text-center">
                        <span class="small text-muted">Already have an account?</span>
                        <a href="/login" class="small">Log In</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
