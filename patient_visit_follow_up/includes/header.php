<?php

$BASE_URL = "http://localhost/patient_visit_follow_up/";


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Healthcare Management System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --danger-gradient: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --dark-bg: #1a1d29;
            --sidebar-bg: #2d3142;
            --card-bg: #ffffff;
            --text-primary: #2d3142;
            --text-muted: #6c757d;
            --border-radius: 16px;
            --shadow: 0 10px 40px rgba(0,0,0,0.08);
            --shadow-hover: 0 15px 50px rgba(0,0,0,0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: var(--text-primary);
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 280px;
            background: var(--sidebar-bg);
            padding: 2rem 0;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar-logo {
            padding: 0 2rem 2rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 2rem;
        }

        .sidebar-logo h3 {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-logo .logo-icon {
            width: 45px;
            height: 45px;
            background: var(--primary-gradient);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .nav-section {
            margin-bottom: 2rem;
        }

        .nav-section-title {
            color: rgba(255,255,255,0.5);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 2rem;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }

        .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 0.875rem 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            font-weight: 500;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.05);
            color: #fff;
            border-left-color: #667eea;
        }

        .nav-link.active {
            background: rgba(102, 126, 234, 0.1);
            color: #fff;
            border-left-color: #667eea;
        }

        .nav-link i {
            width: 20px;
            font-size: 1.1rem;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 2rem 3rem;
            min-height: 100vh;
        }

        /* Header */
        .page-header {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        /* Cards */
        .modern-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: none;
            margin-bottom: 2rem;
        }

        .modern-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .card-header-modern {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .card-header-modern h4 {
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        /* Buttons */
        .btn-modern {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary-modern {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-success-modern {
            background: var(--success-gradient);
            color: white;
        }

        .btn-success-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(17, 153, 142, 0.4);
            color: white;
        }

        .btn-danger-modern {
            background: var(--danger-gradient);
            color: white;
        }

        .btn-info-modern {
            background: var(--info-gradient);
            color: white;
        }

        .btn-warning-modern {
            background: var(--warning-gradient);
            color: white;
        }

        /* Forms */
        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }

        /* Tables */
        .table-modern {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .table-modern thead {
            background: var(--primary-gradient);
            color: white;
        }

        .table-modern thead th {
            border: none;
            padding: 1.25rem 1rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table-modern tbody tr {
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.2s ease;
        }

        .table-modern tbody tr:hover {
            background: #f8f9fa;
        }

        .table-modern tbody td {
            padding: 1.25rem 1rem;
            vertical-align: middle;
        }

        /* Badges */
        .badge-modern {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .badge-success {
            background: var(--success-gradient);
        }

        .badge-danger {
            background: var(--danger-gradient);
        }

        .badge-warning {
            background: var(--warning-gradient);
        }

        .badge-info {
            background: var(--info-gradient);
        }

        /* Alerts */
        .alert {
            border-radius: 12px;
            border: none;
            padding: 1.25rem;
            font-weight: 500;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(17, 153, 142, 0.1) 0%, rgba(56, 239, 125, 0.1) 100%);
            color: #11998e;
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(238, 9, 121, 0.1) 0%, rgba(255, 106, 0, 0.1) 100%);
            color: #ee0979;
        }

        /* Stats Cards */
        .stat-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
        }

        .stat-content h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-primary);
        }

        .stat-content p {
            margin: 0;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .page-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.5s ease-out;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-logo">
        <h3>
            <span class="logo-icon"><i class="fas fa-heartbeat"></i></span>
            HealthCare
        </h3>
    </div>

    <!-- Logged user info -->
    <div class="text-center text-white mb-3">
        <small>
            <i class="fas fa-user-circle"></i>
            <?= $_SESSION['username'] ?? '' ?>
            (<?= $_SESSION['role'] ?? '' ?>)
        </small>
    </div>

    <div class="nav-section">
        <div class="nav-section-title">Patients</div>
        <a href="<?= $BASE_URL ?>patients/list.php" class="nav-link">
            <i class="fas fa-users"></i>
            <span>All Patients</span>
        </a>
        <a href="<?= $BASE_URL ?>patients/add.php" class="nav-link">
            <i class="fas fa-user-plus"></i>
            <span>Add Patient</span>
        </a>
    </div>

    <div class="nav-section">
        <div class="nav-section-title">Visits</div>
        <a href="<?= $BASE_URL ?>visits/list.php" class="nav-link">
            <i class="fas fa-calendar-check"></i>
            <span>All Visits</span>
        </a>
        <a href="<?= $BASE_URL ?>visits/add.php" class="nav-link">
            <i class="fas fa-calendar-plus"></i>
            <span>Add Visit</span>
        </a>
    </div>

    <div class="nav-section">
        <div class="nav-section-title">Reports</div>
        <a href="<?= $BASE_URL ?>reports/summary.php" class="nav-link">
            <i class="fas fa-chart-line"></i>
            <span>Summary</span>
        </a>
        <a href="<?= $BASE_URL ?>reports/birthdays.php" class="nav-link">
            <i class="fas fa-birthday-cake"></i>
            <span>Birthdays</span>
        </a>
        <a href="<?= $BASE_URL ?>reports/followups.php" class="nav-link">
            <i class="fas fa-clock"></i>
            <span>Follow-ups</span>
        </a>
        <a href="<?= $BASE_URL ?>reports/monthly.php" class="nav-link">
            <i class="fas fa-calendar-alt"></i>
            <span>Monthly Reports</span>
        </a>
        <a href="<?= $BASE_URL ?>reports/chart.php" class="nav-link">
            <i class="fas fa-chart-bar"></i>
            <span>Charts</span>
        </a>
    </div>

    <!-- ✅ Logout section INSIDE sidebar -->
    <div class="nav-section mt-4">
        <div class="nav-section-title">Account</div>
        <a href="../logout.php" class="nav-link text-danger">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
