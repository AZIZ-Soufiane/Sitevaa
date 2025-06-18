<?php
require_once('../config/db.php');

// Assume user is logged in and their name and role are stored in session
session_start();
$currentUser = isset($_SESSION['name']) ? $_SESSION['name'] : 'You';
$currentRole = isset($_SESSION['role']) ? $_SESSION['role'] : 'Member';
$isAdmin = $_SESSION['role'] === 'admin';

$stmt = $pdo->prepare("SELECT name FROM Team_member WHERE name != :currentUser ORDER BY name ASC");
$stmt->execute(['currentUser' => $currentUser]);
$teamMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tab = isset($_GET['tab']) && $_GET['tab'] === 'private' ? 'private' : 'team';
$privateUser = isset($_GET['user']) ? $_GET['user'] : ($teamMembers[0]['name'] ?? '');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Chat - SITEVAA</title>
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
                <li><a href="TaskManagement.php" data-page="task-management">Task Management</a></li>
                <?php endif; ?>
                <li><a href="Tasks.php" data-page="tasks">My Tasks</a></li>
                <li><a href="chat.php" class="active" data-page="chat">Team Chat</a></li>
            </ul>
        </nav>
        <main class="main-content">
            <header class="top-bar">
                <h1 id="page-title"><?php echo $tab === 'team' ? 'Team Chat' : 'Private Chat'; ?></h1>
                <div class="user-info">
                    <span class="user-role"><?php echo htmlspecialchars(ucfirst($currentRole)); ?></span>
                    <span class="user-name"><?php echo htmlspecialchars($currentUser); ?></span>
                    <a href="../logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            <div class="chat-container" style="background:rgba(255,255,255,0.03);border-radius:12px;padding:30px;max-width:700px;margin:0 auto;">
                <div style="margin-bottom:24px;">
                    <h2 style="color:var(--accent-1);margin-bottom:10px;">Chat Room</h2>
                    <p style="color:var(--text-gray);">Switch between Team Chat and Private Chat. This is a demo; sending messages is disabled.</p>
                </div>
                <div class="chat-tabs">
                    <a href="chat.php?tab=team" class="chat-tab-btn<?php if($tab==='team') echo ' active'; ?>">Team Chat</a>
                    <a href="chat.php?tab=private" class="chat-tab-btn<?php if($tab==='private') echo ' active'; ?>">Private Chat</a>
                </div>
                <?php if ($tab === 'private' && !empty($teamMembers)): ?>
                <div id="private-users" class="private-users-list">
                    <?php foreach ($teamMembers as $member): ?>
                        <a href="chat.php?tab=private&user=<?php echo urlencode($member['name']); ?>"
                           class="private-user-btn<?php if($privateUser === $member['name']) echo ' active'; ?>">
                            <?php echo htmlspecialchars($member['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <div class="chat-messages" id="chat-messages" style="max-height:350px;overflow-y:auto;padding:10px 0 20px 0;">
                    <div style="color:var(--text-gray);text-align:center;">
                        <?php if ($tab === 'team'): ?>
                            No messages in team chat yet.
                        <?php elseif ($tab === 'private' && $privateUser): ?>
                            No messages with <?php echo htmlspecialchars($privateUser); ?> yet.
                        <?php else: ?>
                            No team members found for private chat.
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>