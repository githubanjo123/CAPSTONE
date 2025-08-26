<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Logout - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logout-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            width: 100%;
            max-width: 500px;
        }
        .logout-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logout-icon {
            color: #ffc107;
            margin-bottom: 1rem;
        }
        .btn-logout {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 24px;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
            color: white;
        }
        .btn-cancel {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 24px;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="logout-card">
                    <div class="logout-header">
                        <i class="fas fa-sign-out-alt fa-3x logout-icon"></i>
                        <h4 class="card-title">Confirm Logout</h4>
                        <p class="card-text text-muted">Are you sure you want to logout from the admin panel?</p>
                    </div>
                    
                    <div class="d-flex justify-content-center gap-3">
                        <a href="<?= $logoutUrl ?>" class="btn btn-logout">
                            <i class="fas fa-check me-2"></i>Yes, Logout
                        </a>
                        <a href="<?= $dashboardUrl ?>" class="btn btn-cancel">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>