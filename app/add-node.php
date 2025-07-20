<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once "../model/NodeModel.php";

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code_node = trim($_POST['code_node'] ?? '');
    $name_node = trim($_POST['name_node'] ?? '');
    $location_node = trim($_POST['location_node'] ?? '');
    
    if (empty($code_node) || empty($name_node)) {
        $error_message = 'Node code and name are required.';
    } else {
        try {
            $nodeModel = new NodeModel();
            
            // Check if code already exists
            $existingNode = $nodeModel->getNodeByCode($code_node);
            if ($existingNode) {
                $error_message = 'Node code already exists. Please choose a different code.';
            } else {
                $data = [
                    'id_user' => $_SESSION['user_id'],
                    'code_node' => $code_node,
                    'name_node' => $name_node,
                    'location_node' => $location_node
                ];
                
                $result = $nodeModel->addNode($data);
                
                if ($result > 0) {
                    $success_message = 'Node added successfully!';
                    // Clear form
                    $_POST = [];
                } else {
                    $error_message = 'Failed to add node. Please try again.';
                }
            }
        } catch (Exception $e) {
            $error_message = 'Server error occurred. Please try again later.';
            error_log("Add node error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Node - Tilapia Farm</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            width: 500px;
            max-width: 90vw;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-header h1 {
            color: #333;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .form-header p {
            color: #666;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            flex: 1;
            padding: 1rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            text-align: center;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .form-help {
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.25rem;
        }

        @media (max-width: 480px) {
            .form-container {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }
            
            .form-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h1>
                <i class="fas fa-plus-circle"></i>
                Add New Node
            </h1>
            <p>Register a new IoT sensor node for your farm</p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="addNodeForm">
            <div class="form-group">
                <label for="code_node" class="form-label">
                    <i class="fas fa-barcode"></i> Node Code *
                </label>
                <input 
                    type="text" 
                    id="code_node" 
                    name="code_node"
                    class="form-input" 
                    placeholder="e.g., NODE001, SENSOR_A1"
                    value="<?php echo htmlspecialchars($_POST['code_node'] ?? ''); ?>"
                    required
                    pattern="[A-Za-z0-9_-]+"
                    title="Only letters, numbers, underscore and dash allowed"
                >
                <div class="form-help">Unique identifier for this node (letters, numbers, _ and - only)</div>
            </div>

            <div class="form-group">
                <label for="name_node" class="form-label">
                    <i class="fas fa-tag"></i> Node Name *
                </label>
                <input 
                    type="text" 
                    id="name_node" 
                    name="name_node"
                    class="form-input" 
                    placeholder="e.g., Pond 1 Sensor, Main Tank Monitor"
                    value="<?php echo htmlspecialchars($_POST['name_node'] ?? ''); ?>"
                    required
                    maxlength="100"
                >
                <div class="form-help">Descriptive name for easy identification</div>
            </div>

            <div class="form-group">
                <label for="location_node" class="form-label">
                    <i class="fas fa-map-marker-alt"></i> Location
                </label>
                <textarea 
                    id="location_node" 
                    name="location_node"
                    class="form-input form-textarea" 
                    placeholder="e.g., North Pond, Tank A - Section 2, GPS: 13.7563, 100.5018"
                    maxlength="255"
                ><?php echo htmlspecialchars($_POST['location_node'] ?? ''); ?></textarea>
                <div class="form-help">Physical location or description of where this node is installed</div>
            </div>

            <div class="form-buttons">
                <a href="node-management.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Node
                </button>
            </div>
        </form>
    </div>

    <script>
        // Auto-generate node code based on name
        document.getElementById('name_node').addEventListener('input', function() {
            const codeInput = document.getElementById('code_node');
            if (!codeInput.value) {
                const name = this.value.toUpperCase();
                const code = name.replace(/[^A-Z0-9]/g, '_').substring(0, 10);
                if (code) {
                    codeInput.value = 'NODE_' + code;
                }
            }
        });

        // Form validation
        document.getElementById('addNodeForm').addEventListener('submit', function(e) {
            const code = document.getElementById('code_node').value.trim();
            const name = document.getElementById('name_node').value.trim();
            
            if (!code || !name) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return;
            }
            
            // Validate code format
            if (!/^[A-Za-z0-9_-]+$/.test(code)) {
                e.preventDefault();
                alert('Node code can only contain letters, numbers, underscore and dash.');
                return;
            }
        });

        // Auto-focus on first input
        window.addEventListener('load', function() {
            document.getElementById('code_node').focus();
        });

        <?php if (!empty($success_message)): ?>
        // Auto-redirect after success
        setTimeout(function() {
            window.location.href = 'node-management.php';
        }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>
