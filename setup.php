<?php
// Simple setup script for FutureLaunch

// Check if already set up
if (file_exists('data/setup_complete.flag') && !isset($_GET['force'])) {
    header('Location: dashboard.php');
    exit();
}

$status = 'start';
$message = '';

// Process setup request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create necessary directories
    $dirs = ['data', 'config', 'logs'];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    
    // Create initial subscriber data file
    if (!file_exists('data/subscribers.json')) {
        file_put_contents('data/subscribers.json', json_encode([]));
    }
    
    // Create log files
    touch('logs/system.log');
    touch('logs/newsletter.log');
    
    // Check if homepage exists
    if (!file_exists('index.html')) {
        // Create basic homepage if it doesn't exist
        $homepage = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FutureLaunch - Coming Soon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .hero-section {
            padding: 4rem 0;
        }
        .signup-form {
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row hero-section align-items-center">
            <div class="col-lg-7 mb-5 mb-lg-0">
                <h1 class="display-4 fw-bold mb-4">FutureLaunch</h1>
                <p class="lead mb-4">We\'re launching soon. Sign up for our newsletter to get notified!</p>
                <div class="d-flex gap-3">
                    <a href="#subscribe" class="btn btn-light btn-lg">
                        <i class="fas fa-envelope me-2"></i> Subscribe
                    </a>
                </div>
            </div>
            <div class="col-lg-5" id="subscribe">
                <div class="signup-form">
                    <h3 class="mb-4">Newsletter</h3>
                    <form action="subscribe.php" method="post" id="newsletter-form">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <button type="submit" class="btn btn-light w-100">Subscribe</button>
                    </form>
                    <div id="success-message" class="alert alert-success mt-3" style="display: none;">
                        Thank you for subscribing!
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById("newsletter-form").addEventListener("submit", function(e) {
        e.preventDefault();
        
        // Get email value
        const email = document.getElementById("email").value;
        
        // Create form data
        const formData = new FormData();
        formData.append("email", email);
        
        // Send AJAX request
        fetch("subscribe.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById("success-message").style.display = "block";
                document.getElementById("newsletter-form").reset();
            } else {
                alert(data.message || "An error occurred. Please try again.");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred. Please try again.");
        });
    });
    </script>
</body>
</html>';
        file_put_contents('index.html', $homepage);
    }
    
    // Create basic subscribe.php if it doesn't exist
    if (!file_exists('subscribe.php')) {
        $subscribe_script = '<?php
header("Content-Type: application/json");

// Function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to log errors
function logError($message) {
    $log = date("[Y-m-d H:i:s]") . " Error: " . $message . PHP_EOL;
    file_put_contents("logs/newsletter.log", $log, FILE_APPEND);
}

// Process subscription
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $response = ["success" => false];
    
    // Get and validate email
    $email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
    
    if (empty($email)) {
        $response["message"] = "Email is required";
        logError("Empty email submitted");
    } elseif (!isValidEmail($email)) {
        $response["message"] = "Invalid email format";
        logError("Invalid email format: " . $email);
    } else {
        // Load existing subscribers
        $data_file = "data/subscribers.json";
        $subscribers = [];
        
        if (file_exists($data_file)) {
            $subscribers = json_decode(file_get_contents($data_file), true) ?: [];
        }
        
        // Check if email already exists
        $exists = false;
        foreach ($subscribers as $sub) {
            if ($sub["email"] === $email) {
                $exists = true;
                break;
            }
        }
        
        if ($exists) {
            $response["message"] = "This email is already subscribed";
        } else {
            // Add new subscriber
            $subscribers[] = [
                "email" => $email,
                "date" => date("Y-m-d H:i:s"),
                "status" => "active"
            ];
            
            // Save updated list
            file_put_contents($data_file, json_encode($subscribers, JSON_PRETTY_PRINT));
            
            $response["success"] = true;
            $response["message"] = "Subscription successful!";
        }
    }
    
    echo json_encode($response);
    exit;
}

// If not a POST request, redirect to homepage
header("Location: index.html");
exit;';
        file_put_contents('subscribe.php', $subscribe_script);
    }
    
    // Create setup complete flag
    file_put_contents('data/setup_complete.flag', date('Y-m-d H:i:s'));
    
    // Success
    $status = 'complete';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FutureLaunch Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .setup-container {
            max-width: 600px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .setup-header {
            background: #4e73df;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .setup-body {
            padding: 30px;
        }
        
        .icon-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 80px;
            width: 80px;
            border-radius: 50%;
            background-color: #f8f9fc;
            margin-bottom: 20px;
        }
        
        .icon-circle i {
            font-size: 40px;
            color: #4e73df;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <h2>FutureLaunch Setup</h2>
        </div>
        
        <div class="setup-body text-center">
            <?php if ($status === 'start'): ?>
                <div class="icon-circle">
                    <i class="fas fa-rocket"></i>
                </div>
                <h3 class="mb-3">Welcome to FutureLaunch</h3>
                <p class="mb-4">Let's get your newsletter system set up in just a few seconds.</p>
                
                <form method="post" action="">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-magic me-2"></i> Set Up Now
                    </button>
                </form>
                
                <div class="mt-4 text-muted small">
                    This will create necessary folders and files for your newsletter system.
                </div>
            <?php else: ?>
                <div class="icon-circle">
                    <i class="fas fa-check"></i>
                </div>
                <h3 class="mb-3">Setup Complete!</h3>
                <p class="mb-4">Your FutureLaunch newsletter system is now ready to use.</p>
                
                <div class="d-grid gap-3">
                    <a href="index.html" class="btn btn-outline-primary">
                        <i class="fas fa-home me-2"></i> Go to Homepage
                    </a>
                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="fas fa-tachometer-alt me-2"></i> Go to Dashboard
                    </a>
                </div>
                
                <div class="mt-4">
                    <div class="alert alert-info text-start">
                        <h5><i class="fas fa-info-circle me-2"></i> Login Details</h5>
                        <p class="mb-2">Use these credentials to access your dashboard:</p>
                        <ul class="mb-0 text-start">
                            <li><strong>Username:</strong> admin</li>
                            <li><strong>Password:</strong> admin123</li>
                        </ul>
                        <hr>
                        <p class="mb-0"><strong>Important:</strong> Please change your password after login for security.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
