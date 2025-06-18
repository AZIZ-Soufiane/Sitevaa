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

$stmt = $pdo->query("SELECT Client_Id, name FROM Client ORDER BY name");
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission for new invoice
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_invoice'])) {
        $client_id = $_POST['client_id'];
        $services = $_POST['services'];
        $amounts = $_POST['amounts'];
        $total = array_sum(array_map('floatval', $amounts));
        
        try {
            $stmt = $pdo->prepare("SELECT id_request FROM Project_request WHERE Client_id = ? ORDER BY id_request DESC LIMIT 1");
            $stmt->execute([$client_id]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($request) {
                $stmt = $pdo->prepare("INSERT INTO Invoice (amount, status, id_request) VALUES (?, 'sent', ?)");
                $stmt->execute([$total, $request['id_request']]);
                $message = "Invoice created successfully!";
            } else {
                $message = "Error: No project request found for this client.";
            }
        } catch (PDOException $e) {
            $message = "Error creating invoice: " . $e->getMessage();
        }
    }
    
    // Handle status update
    elseif (isset($_POST['update_status'])) {
        $new_status = $_POST['status'];
        $invoice_id = $_POST['invoice_id'];
        
        $stmt = $pdo->prepare("UPDATE Invoice SET status = ? WHERE id_invoice = ?");
        $result = $stmt->execute([$new_status, $invoice_id]);
        
        if ($result) {
            $stmt = $pdo->prepare("SELECT i.id_request, i.amount, pr.description 
                          FROM Invoice i 
                          JOIN Project_request pr ON i.id_request = pr.id_request 
                          WHERE i.id_invoice = ?");
            $stmt->execute([$invoice_id]);
            $row = $stmt->fetch();
            
            if ($row) {
                $request_id = $row['id_request'];
                
                $request_status = 'pending';
                if ($new_status == 'approved') {
                    $request_status = 'approved';
                    
                    // Create new project when invoice is approved
                    $stmt = $pdo->prepare("
                        INSERT INTO Project (title, description, status, id_invoice) 
                        VALUES (?, ?, 'not_started', ?)
                    ");
                    $title = "Project #" . $row['id_request'];
                    $description = $row['description'] . "\n\nProject Budget: $" . number_format($row['amount'], 2);
                    $stmt->execute([$title, $description, $invoice_id]);
                }
                if ($new_status == 'rejected') {
                    $request_status = 'rejected';
                }
                
                $stmt = $pdo->prepare("UPDATE Project_request SET status = ? WHERE id_request = ?");
                $stmt->execute([$request_status, $request_id]);
            }
            $message = "Status updated successfully!";
        } else {
            $message = "Error updating status";
        }
    }
}

$stmt = $pdo->query("
    SELECT 
        i.id_invoice,
        i.amount,
        i.status,
        c.name as client_name,
        pr.description
    FROM Invoice i
    JOIN Project_request pr ON i.id_request = pr.id_request 
    JOIN Client c ON pr.Client_id = c.Client_Id
    ORDER BY i.id_invoice DESC
");
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Generator - SITEVAA</title>
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
                <li><a href="index.php"  data-page="overview">Overview</a></li>
                <li><a href="invoice.php" class="active" data-page="invoice-generator">Invoice Generator</a></li>
                <li><a href="projects.php" data-page="projects">Projects</a></li>
                <li><a href="TaskManagement.php" data-page="task-management">Task Management</a></li>
                <?php endif; ?>
                <li><a href="Tasks.php" data-page="tasks">My Tasks</a></li>
                <li><a href="chat.php" data-page="chat">Team Chat</a></li>
            </ul>
        </nav>
        
        <main class="main-content">
            <header class="top-bar">
                <h1>Invoice Generator</h1>
                <div class="user-info">
                    <span class="user-role"><?php echo ucfirst($_SESSION['role']); ?></span>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                    <a href="../logout.php" class="logout-btn">Logout</a>
                </div>
            </header>

            <?php if (isset($message)): ?>
                <div class="alert success"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="invoice-container">
                <div class="invoice-form-wrapper">
                    <form method="POST" class="invoice-form">
                        <input type="hidden" name="create_invoice" value="1">
                        <div class="form-group">
                            <select name="client_id" required>
                                <option value="">Choose a client</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?php echo $client['Client_Id']; ?>">
                                        <?php echo htmlspecialchars($client['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="services">
                            <div class="service-row">
                                <input type="text" name="services[]" placeholder="Service Description" required>
                                <input type="number" name="amounts[]" placeholder="Amount" required>
                                <button type="button" class="remove-btn" onclick="removeService(this)">Remove</button>
                            </div>
                        </div>

                        <button type="button" class="add-btn" onclick="addService()">Add Service</button>
                        <button type="submit" class="submit-btn">Create Invoice</button>
                    </form>
                </div>
            </div>

            <div class="recent-invoices">
                <h2>Recent Invoices</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Invoice ID</th>
                            <th>Client</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td>#<?php echo $invoice['id_invoice']; ?></td>
                                <td><?php echo htmlspecialchars($invoice['client_name']); ?></td>
                                <td>$<?php echo number_format($invoice['amount'], 2); ?></td>
                                <td>
                                    <form method="POST" class="status-form">
                                        <input type="hidden" name="invoice_id" value="<?php echo $invoice['id_invoice']; ?>">
                                        <input type="hidden" name="update_status" value="1">
                                        <select name="status" onchange="this.form.submit()" class="status-select">
                                            <option value="sent" <?php echo $invoice['status'] === 'sent' ? 'selected' : ''; ?>>Sent</option>
                                            <option value="approved" <?php echo $invoice['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                            <option value="rejected" <?php echo $invoice['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <div id="invoiceModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Invoice Details</h2>
            <div id="invoiceDetails"></div>
       </div>
    </div>

    <script>
        function addService() {
            const div = document.createElement('div');
            div.className = 'service-row';
            div.innerHTML = `
                <input type="text" name="services[]" placeholder="Service Description" required>
                <input type="number" name="amounts[]" placeholder="Amount" required>
                <button type="button" class="remove-btn" onclick="removeService(this)">Remove</button>
            `;
            document.getElementById('services').appendChild(div);
        }

        function removeService(button) {
            button.parentElement.remove();
        }

        document.querySelectorAll('.status-select').forEach(select => {
        select.dataset.status = select.value;
        select.addEventListener('change', function() {
            this.dataset.status = this.value;
        });
    });
    </script>
</body>
</html>
