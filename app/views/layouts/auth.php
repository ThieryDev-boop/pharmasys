<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - PharmaSys</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a3a5c 0%, #2E75B6 60%, #5ba3d9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            padding: 45px 40px;
            width: 100%;
            max-width: 420px;
        }
        .login-logo {
            font-size: 2.2rem;
            font-weight: 800;
            color: #2E75B6;
            letter-spacing: 2px;
        }
        .login-logo span {
            color: #1a3a5c;
        }
        .login-subtitle {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 30px;
        }
        .form-control:focus {
            border-color: #2E75B6;
            box-shadow: 0 0 0 3px rgba(46,117,182,0.15);
        }
        .btn-login {
            background: #2E75B6;
            border: none;
            padding: 12px;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: background 0.2s;
        }
        .btn-login:hover {
            background: #1a3a5c;
        }
        .input-group-text {
            background: #f0f7ff;
            border-color: #dee2e6;
            color: #2E75B6;
        }
        .footer-text {
            color: rgba(255,255,255,0.6);
            font-size: 0.8rem;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div>
        <div class="login-card">
            <!-- Logo -->
            <div class="text-center mb-4">
                <div class="login-logo">
                    <i class="bi bi-capsule-pill text-primary"></i>
                    Pharma<span>Sys</span>
                </div>
                <p class="login-subtitle">Système de Gestion de Pharmacie</p>
            </div>

            <!-- Contenu de la vue (formulaire) -->
            <?= $content ?>
        </div>

        <p class="footer-text">
            &copy; <?= date('Y') ?> PharmaSys &mdash; BTS Genie Logiciel
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
