<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page introuvable - PharmaSys</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f7ff; }
        .error-box {
            max-width: 500px;
            margin: 120px auto;
            background: #fff;
            border-radius: 12px;
            padding: 50px 40px;
            box-shadow: 0 4px 24px rgba(46,117,182,0.10);
            text-align: center;
        }
        .error-code {
            font-size: 80px;
            font-weight: 800;
            color: #2E75B6;
            line-height: 1;
        }
    </style>
</head>
<body>
    <div class="error-box">
        <div class="error-code">404</div>
        <i class="bi bi-search fs-1 text-secondary my-3 d-block"></i>
        <h4 class="mb-2">Page introuvable</h4>
        <p class="text-muted mb-4">
            La page que vous demandez n'existe pas ou a été déplacée.
        </p>
        <a href="/" class="btn btn-primary px-4">
            <i class="bi bi-house me-2"></i>Retour à l'accueil
        </a>
    </div>
</body>
</html>