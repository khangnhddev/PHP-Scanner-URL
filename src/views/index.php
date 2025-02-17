<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Scanner Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            padding: 1rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px 15px 0 0 !important;
            border-bottom: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            border: none;
            padding: 10px 25px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13,110,253,0.3);
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13,110,253,0.1);
        }
        .badge {
            padding: 8px 12px;
            border-radius: 8px;
        }
        .table {
            margin-bottom: 0;
        }
        .table td {
            padding: 12px;
            vertical-align: middle;
        }
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px;
        }
        .result-section {
            margin-top: 2rem;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.5s ease forwards;
        }
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .status-good {
            background-color: #198754;
        }
        .status-warning {
            background-color: #ffc107;
        }
        .status-danger {
            background-color: #dc3545;
        }
        .url-input-container {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .url-input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        .features-list {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .feature-item {
            background: white;
            padding: 10px 15px;
            border-radius: 10px;
            font-size: 0.9rem;
            color: #6c757d;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-shield-alt"></i> URL Scanner Pro
            </a>
            <div class="text-light">
                <i class="fas fa-clock"></i> 
                <?php echo date('Y-m-d H:i:s'); ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="card-title mb-3">URL Scanner</h4>
                        <div class="features-list">
                            <div class="feature-item">
                                <i class="fas fa-lock"></i> SSL Check
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-virus-slash"></i> Malware Scan
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-shield-virus"></i> Phishing Detection
                            </div>
                        </div>
                        <form method="POST" action="">
                            <div class="url-input-container">
                                <textarea class="form-control" name="urls" rows="4" 
                                    placeholder="Enter URLs to scan (one per line)&#10;Example:&#10;https://www.google.com&#10;https://www.github.com"><?= $_POST['urls'] ?? '' ?></textarea>
                                <i class="fas fa-link url-input-icon"></i>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Start Scanning
                            </button>
                        </form>
                    </div>
                </div>

                <?php if (!empty($results)): ?>
                    <div class="result-section">
                        <?php foreach ($results as $url => $data): ?>
                            <div class="card mb-4">
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <span class="status-indicator <?= $data['ssl_info']['valid'] ? 'status-good' : 'status-danger' ?>"></span>
                                        <h5 class="mb-0">
                                            <i class="fas fa-globe"></i> 
                                            <?= htmlspecialchars($url) ?>
                                        </h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- SSL Information -->
                                    <div class="mb-4">
                                        <h6 class="border-bottom pb-2">
                                            <i class="fas fa-lock"></i> SSL Information
                                        </h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <tr>
                                                    <td width="30%"><i class="fas fa-check-circle"></i> Status:</td>
                                                    <td>
                                                        <?php if ($data['ssl_info']['valid']): ?>
                                                            <span class="badge bg-success">
                                                                <i class="fas fa-shield-alt"></i> Valid
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">
                                                                <i class="fas fa-exclamation-triangle"></i> Invalid
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><i class="fas fa-calendar"></i> Expires:</td>
                                                    <td><?= $data['ssl_info']['expires'] ?? 'N/A' ?></td>
                                                </tr>
                                                <tr>
                                                    <td><i class="fas fa-certificate"></i> Issuer:</td>
                                                    <td><?= $data['ssl_info']['issuer'] ?? 'N/A' ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Security Analysis -->
                                    <?php if (!empty($data['content_analysis']['security_checks'])): ?>
                                        <div class="alert alert-warning">
                                            <h6 class="d-flex align-items-center">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                Security Issues Detected
                                            </h6>
                                            <ul class="mb-0 mt-2">
                                                <?php foreach ($data['content_analysis']['security_checks'] as $issue): ?>
                                                    <li><?= htmlspecialchars($issue) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Malware Scan -->
                                    <?php if (!empty($data['content_analysis']['malware_indicators'])): ?>
                                        <div class="alert alert-danger">
                                            <h6 class="d-flex align-items-center">
                                                <i class="fas fa-virus-slash me-2"></i>
                                                Malware Indicators
                                            </h6>
                                            <ul class="mb-0 mt-2">
                                                <?php foreach ($data['content_analysis']['malware_indicators'] as $indicator): ?>
                                                    <li><?= htmlspecialchars($indicator) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 