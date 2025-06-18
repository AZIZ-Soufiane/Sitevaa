<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$isAdmin = $_SESSION['role'] === 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $project_id = $_POST['project_id'];
    $new_status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE Project SET status = ? WHERE id_project = ?");
    $result = $stmt->execute([$new_status, $project_id]);
    
    if ($result) {
        $message = "Project status updated successfully!";
    } else {
        $message = "Error updating project status";
    }
}

$stmt = $pdo->query("
    SELECT 
        p.*,
        c.name as client_name,
        pr.description as request_description,
        i.amount as project_budget
    FROM Project p
    JOIN Invoice i ON p.id_invoice = i.id_invoice
    JOIN Project_request pr ON i.id_request = pr.id_request
    JOIN Client c ON pr.Client_id = c.Client_Id
    ORDER BY p.id_project DESC
");
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects - SITEVAA</title>
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
                <li><a href="index.php" data-page="overview">Overview</a></li>
                <li><a href="invoice.php" data-page="invoice-generator">Invoice Generator</a></li>
                <li><a href="projects.php" class="active" data-page="projects">Projects</a></li>
                <li><a href="TaskManagement.php" data-page="task-management">Task Management</a></li>
                <?php endif; ?>
                <li><a href="Tasks.php" data-page="tasks">My Tasks</a></li>
                <li><a href="#" data-page="chat">Team Chat</a></li>
            </ul>
        </nav>
        
        <main class="main-content">
            <header class="top-bar">
                <h1>Projects</h1>
                <div class="user-info">
                    <span class="user-role"><?php echo ucfirst($_SESSION['role']); ?></span>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                    <a href="../logout.php" class="logout-btn">Logout</a>
                </div>
            </header>

            <?php if (isset($message)): ?>
                <div class="alert success"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="projects-grid">
                <?php foreach ($projects as $project): ?>
                    <div class="project-card">
                        <div class="project-header">
                            <div class="project-title"><?php echo htmlspecialchars($project['title']); ?></div>
                            <div class="project-client">Client: <?php echo htmlspecialchars($project['client_name']); ?></div>
                            <div class="project-budget">Budget: $<?php echo number_format($project['project_budget'], 2); ?></div>
                        </div>
                        <div class="project-description">
                            <?php echo ucfirst(htmlspecialchars($project['request_description'])); ?>
                        </div>
                        <div class="project-status">
                            <form method="POST">
                                <input type="hidden" name="project_id" value="<?php echo $project['id_project']; ?>">
                                <input type="hidden" name="update_status" value="1">
                                <select name="status" onchange="this.form.submit()" class="status-select" data-status="<?php echo $project['status']; ?>">
                                    <option value="not_started" <?php echo $project['status'] === 'not_started' ? 'selected' : ''; ?>>Not Started</option>
                                    <option value="in_progress" <?php echo $project['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="completed" <?php echo $project['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="blocked" <?php echo $project['status'] === 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                                    <option value="delivered" <?php echo $project['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="archived" <?php echo $project['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                                </select>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
    <script src="dashboard.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            document.querySelectorAll('.status-select').forEach(select => {
                select.setAttribute('data-status', select.value);
                                
                select.addEventListener('change', function() {
                    this.setAttribute('data-status', this.value);
                });
            });
        });
    </script>
</body>
</html>
