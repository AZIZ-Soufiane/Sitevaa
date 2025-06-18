<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$isAdmin = $_SESSION['role'] === 'admin';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $task_id = $_POST['task_id'];
    $new_status = $_POST['new_status'];
    
    // Validate that non-admin users can't set status to 'done'
    if (!$isAdmin && $new_status === 'done') {
        $message = "Only administrators can mark tasks as done.";
        $message_type = "error";
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE Task 
                SET status = ? 
                WHERE id_task = ? 
                AND id_member = ?
            ");
            $result = $stmt->execute([$new_status, $task_id, $user_id]);
            
            if ($result) {
                $message = "Task status updated successfully!";
                $message_type = "success";
            }
        } catch (PDOException $e) {
            $message = "Error updating task: " . $e->getMessage();
            $message_type = "error";
        }
    }
}

// Fetch user's tasks
$stmt = $pdo->prepare("
    SELECT t.*, 
           p.title as project_name,
           p.description as project_description,
           tm.name as member_name
    FROM Task t
    JOIN Project p ON t.id_project = p.id_project
    LEFT JOIN Team_member tm ON t.id_member = tm.id_member
    WHERE t.id_member = ?
    ORDER BY t.deadline ASC
");
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks - SITEVAA</title>
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
                <li><a href="index.php">Overview</a></li>
                <li><a href="invoice.php">Invoice Generator</a></li>
                <li><a href="projects.php">Projects</a></li>
                <li><a href="TaskManagement.php">Task Management</a></li>
                <?php endif; ?>
                <li><a href="Tasks.php" class="active">My Tasks</a></li>
                <li><a href="#" data-page="chat">Team Chat</a></li>
            </ul>
        </nav>
        
        <main class="main-content">
            <header class="top-bar">
                <h1>My Tasks</h1>
                <div class="user-info">
                    <span class="user-role"><?php echo ucfirst($_SESSION['role']); ?></span>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                    <a href="../logout.php" class="logout-btn">Logout</a>
                </div>
            </header>

            <?php if (isset($message)): ?>
                <div class="alert <?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="tasks-container">
                <?php if (empty($tasks)): ?>
                    <p>No tasks assigned to you yet.</p>
                <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                        <div class="task-card">
                            <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                            
                            <div class="task-info">
                                <p>
                                    <strong>Project</strong>
                                    <span><?php echo htmlspecialchars($task['project_name']); ?></span>
                                </p>
                                <p>
                                    <strong>Description</strong>
                                    <span><?php echo htmlspecialchars($task['description']); ?></span>
                                </p>
                            </div>

                            <div class="task-deadline">
                                <strong>Deadline</strong>
                                <span><?php echo date('M d, Y', strtotime($task['deadline'])); ?></span>
                            </div>

                            <div class="current-status">
                                <strong>Current Status:</strong>
                                <span class="status-badge status-<?php echo $task['status']; ?>">
                                    <?php echo ucfirst($task['status']); ?>
                                </span>
                            </div>
                            
                            <form method="POST" class="status-controls">
                                <input type="hidden" name="task_id" value="<?php echo $task['id_task']; ?>">
                                <input type="hidden" name="update_status" value="1">
                                
                                <?php if ($task['status'] !== 'done'): ?>
                                    <?php if ($task['status'] === 'todo'): ?>
                                        <button type="submit" name="new_status" value="in_progress" class="status-btn status-in_progress">
                                            Move to In Progress
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($task['status'] === 'in_progress'): ?>
                                        <button type="submit" name="new_status" value="review" class="status-btn status-review">
                                            Move to Review
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($isAdmin && $task['status'] === 'review'): ?>
                                        <button type="submit" name="new_status" value="done" class="status-btn status-done">
                                            Mark as Done
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
