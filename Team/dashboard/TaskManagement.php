<?php
session_start();
require_once('../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$isAdmin = $_SESSION['role'] === 'admin';
if (!$isAdmin) {
    header('Location: index.php');
    exit();
}

$stmt = $pdo->query("
    SELECT id_project, title 
    FROM Project 
    WHERE status IN ('not_started', 'in_progress')
    ORDER BY id_project DESC
");
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);


$stmt = $pdo->query("
    SELECT id_member, name, role 
    FROM Team_member 
    ORDER BY name
");
$team_members = $stmt->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_task'])) {
    $project_id = $_POST['project_id'];
    $member_id = $_POST['member_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $deadline = $_POST['deadline'];
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO Task (title, description, deadline, id_project, id_member, status) 
            VALUES (?, ?, ?, ?, ?, 'todo')
        ");
        $result = $stmt->execute([$title, $description, $deadline, $project_id, $member_id]);
        
        if ($result) {
            $message = "Task assigned successfully!";
            $message_type = "success";
        }
    } catch (PDOException $e) {
        $message = "Error creating task: " . $e->getMessage();
        $message_type = "error";
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_done'])) {
    $task_id = $_POST['task_id'];
    
    try {
        $stmt = $pdo->prepare("
            UPDATE Task 
            SET status = 'done' 
            WHERE id_task = ? 
            AND status = 'review'
        ");
        $result = $stmt->execute([$task_id]);
        
        if ($result) {
            $message = "Task marked as done successfully!";
            $message_type = "success";
        }
    } catch (PDOException $e) {
        $message = "Error updating task: " . $e->getMessage();
        $message_type = "error";
    }
}


$stmt = $pdo->query("
    SELECT t.*, 
           p.title as project_name, 
           tm.name as member_name
    FROM Task t
    JOIN Project p ON t.id_project = p.id_project
    LEFT JOIN Team_member tm ON t.id_member = tm.id_member
    ORDER BY t.deadline ASC
");
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management - SITEVAA</title>
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
                <li><a href="projects.php" data-page="projects">Projects</a></li>
                <li><a href="TaskManagement.php" class="active" data-page="task-management">Task Management</a></li>
                <?php endif; ?>
                <li><a href="Tasks.php" data-page="tasks">My Tasks</a></li>
                <li><a href="chat.php" data-page="chat">Team Chat</a></li>
            </ul>
        </nav>
        
        <main class="main-content">
            <header class="top-bar">
                <h1>Task Management</h1>
                <div class="user-info">
                    <span class="user-role"><?php echo ucfirst($_SESSION['role']); ?></span>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                    <a href="../logout.php" class="logout-btn">Logout</a>
                </div>
            </header>

            <?php if (isset($message)): ?>
                <div class="alert <?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="task-form-container">
                <h2>Assign New Task</h2>
                <form method="POST" class="task-form">
                    <input type="hidden" name="create_task" value="1">
                    
                    <div class="form-group">
                        <label for="project_id">Project</label>
                        <select name="project_id" id="project_id" required>
                            <option value="">Select Project</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?php echo $project['id_project']; ?>">
                                    <?php echo htmlspecialchars($project['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="member_id">Team Member</label>
                        <select name="member_id" id="member_id" required>
                            <option value="">Select Team Member</option>
                            <?php foreach ($team_members as $member): ?>
                                <option value="<?php echo $member['id_member']; ?>">
                                    <?php echo htmlspecialchars($member['name']) . ' (' . htmlspecialchars($member['role']) . ')'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="title">Task Title</label>
                        <input type="text" name="title" id="title" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" rows="3" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="deadline">Deadline</label>
                        <input type="date" name="deadline" id="deadline" required>
                    </div>

                    <button type="submit" class="submit-btn">Assign Task</button>
                </form>
            </div>

            <div class="tasks-list">
                <h2>Current Tasks</h2>
                <?php foreach ($tasks as $task): ?>
                    <div class="task-card">
                        <div class="task-info">
                            <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                            <p><strong>Project:</strong> <?php echo htmlspecialchars($task['project_name']); ?></p>
                            <p><strong>Assigned to:</strong> <?php echo htmlspecialchars($task['member_name']); ?></p>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($task['description']); ?></p>
                        </div>
                        <div class="task-deadline">
                            <strong>Deadline</strong><br>
                            <?php echo date('M d, Y', strtotime($task['deadline'])); ?>
                        </div>
                        <div class="task-status">
                            <span class="status-badge status-<?php echo $task['status']; ?>">
                                <?php echo ucfirst($task['status']); ?>
                            </span>
                            <?php if ($isAdmin && $task['status'] === 'review'): ?>
                                <form method="POST">
                                    <input type="hidden" name="task_id" value="<?php echo $task['id_task']; ?>">
                                    <button type="submit" name="mark_done" class="action-btn">
                                        Mark as Done
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html>
