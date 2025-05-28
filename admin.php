<?php
session_start();

// Simple login check
if (isset($_POST['login'])) {
    if ($_POST['username'] == 'admin' && $_POST['password'] == 'admin123') {
        $_SESSION['logged_in'] = true;
    }
}

// Logout handling
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Check login status
$logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// Newsletter subscribers function
function getSubscribers() {
    $file = 'data/subscribers.json';
    if (file_exists($file)) {
        $json = file_get_contents($file);
        $data = json_decode($json, true);
        return $data ?? [];
    }
    return [];
}

// Handle delete action
if ($logged_in && isset($_GET['delete'])) {
    $email = $_GET['delete'];
    $subscribers = getSubscribers();
    
    // Remove the subscriber with the matching email
    foreach ($subscribers as $key => $subscriber) {
        if ($subscriber['email'] == $email) {
            unset($subscribers[$key]);
            break;
        }
    }
    
    // Re-index array and save
    $subscribers = array_values($subscribers);
    file_put_contents('data/subscribers.json', json_encode($subscribers, JSON_PRETTY_PRINT));
    
    // Redirect to avoid resubmission
    header('Location: admin.php?deleted=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <?php if (!$logged_in): ?>
            <!-- Login Form -->
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">Admin Login</h4>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
                                </div>
                                <button type="submit" name="login" class="btn btn-primary">Login</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Admin Dashboard -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Newsletter Subscribers</h2>
                <div>
                    <a href="index.php" class="btn btn-sm btn-outline-primary me-2">Homepage</a>
                    <a href="admin.php?logout=1" class="btn btn-sm btn-danger">Logout</a>
                </div>
            </div>
            
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success">
                    Subscriber has been deleted successfully.
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <?php 
                    $subscribers = getSubscribers();
                    if (empty($subscribers)): 
                    ?>
                        <div class="text-center py-5">
                            <p class="text-muted">No subscribers found.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Email</th>
                                        <th>Date Added</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($subscribers as $subscriber): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($subscriber['email']); ?></td>
                                            <td><?php echo htmlspecialchars($subscriber['date'] ?? 'N/A'); ?></td>
                                            <td>
                                                <a href="admin.php?delete=<?php echo urlencode($subscriber['email']); ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Are you sure you want to delete this subscriber?')">
                                                    Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
