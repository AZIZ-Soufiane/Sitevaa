<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$isAdmin = $_SESSION['role'] === 'admin';


$stmt = $pdo->query(" SELECT pr.*, c.name as client_name, c.email as client_email 
                    FROM Project_request pr
                    JOIN Client c ON pr.Client_id = c.Client_Id
                    ORDER BY pr.id_request DESC");

$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Office-SITEVAA</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="logo">
                <img src="../../Landing page/images/logo.png" alt="SITEVAA">
            </div>
            <ul class="nav-links">
                <?php if ($isAdmin): ?>
                <li><a href="#" class="active" data-page="overview">Overview</a></li>
                <li><a href="#" data-page="invoice-generator">Invoice Generator</a></li>
                <li><a href="#" data-page="task-management">Task Management</a></li>
                <li><a href="#" data-page="projects">Projects</a></li>
                <?php endif; ?>
                <li><a href="#" data-page="tasks">My Tasks</a></li>
                <li><a href="#" data-page="chat">Team Chat</a></li>
            </ul>
        </nav>
        
        <main class="main-content">
            <header class="top-bar">
                <h1 id="page-title"><?php echo $isAdmin ? 'Dashboard Overview' : 'My Tasks'; ?></h1>
                <div class="user-info">
                    <span class="user-role"><?php echo ucfirst($_SESSION['role']); ?></span>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                    <a href="../logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <?php if ($isAdmin): ?>
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h3>Total Projects</h3>
                    <div id="projectCount" class="stat-value">8</div>
                </div>
                <div class="stat-card">
                    <h3>Total Income</h3>
                    <div id="totalIncome" class="stat-value">$20,000</div>
                </div>
                <div class="stat-card">
                    <h3>Active Tasks</h3>
                    <div id="taskCount" class="stat-value">20</div>
                </div>
            </div>

            <div class="recent-requests">
                <h2>Recent Project Requests</h2>
                <div class="requests-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Description</th>
                                <th>Budget</th>
                                <th>Timeline</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($requests): ?>
                                <?php foreach ($requests as $request): ?>
                                    <tr>
                                        <td>
                                            <div class="client-info">
                                                <span class="client-name"><?php echo htmlspecialchars($request['client_name']); ?></span>
                                                <span class="client-email"><?php echo htmlspecialchars($request['client_email']); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($request['description']); ?></td>
                                        <td>$<?php echo number_format($request['budget'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($request['timeline']); ?></td>
                                        <td><span class="status-badge <?php echo $request['status']; ?>"><?php echo ucfirst($request['status']); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="no-data">No project requests found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            
            <div id="content-area"></div>
        </main>
    </div>
    <script src="dashboard.js"></script>
</body>
</html>