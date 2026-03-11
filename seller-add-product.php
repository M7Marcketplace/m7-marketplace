<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product - M7 Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="M7shooping.css">
    <!-- Simple round favicon with M7 text -->
    <link rel="icon" type="image/x-icon" href="M7shooping.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        .add-product-wrapper {
            min-height: calc(100vh - 200px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .add-product-container {
            max-width: 800px;
            width: 100%;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 50px 40px;
            border-radius: 40px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.6s ease-out;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-header h1 {
            font-size: 48px;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #fff 0%, #4CAF50 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .progress-bar {
            height: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            margin: 30px 0;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #4CAF50 0%, #d96565 100%);
            width: 25%;
            transition: width 0.3s ease;
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 10px;
        }
        
        .step {
            flex: 1;
            text-align: center;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .step.active {
            background: linear-gradient(135deg, #4CAF50 0%, #d96565 100%);
        }
        
        .form-section {
            animation: fadeIn 0.5s ease;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #4CAF50;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 14px 18px;
            border-radius: 15px;
            border: 2px solid transparent;
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            font-size: 16px;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.2);
        }
        
        .required-star {
            color: #d96565;
            margin-left: 4px;
        }
        
        .price-input {
            position: relative;
        }
        
        .price-input::after {
            content: 'DZD';
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-weight: 600;
        }
        
        .image-upload-area {
            border: 3px dashed #4CAF50;
            border-radius: 30px;
            padding: 50px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .image-upload-area:hover {
            background: rgba(76, 175, 80, 0.1);
        }
        
        .image-upload-area .upload-icon {
            font-size: 80px;
            margin-bottom: 15px;
        }
        
        .image-optional-badge {
            display: inline-block;
            background: rgba(76, 175, 80, 0.2);
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 12px;
            margin-top: 10px;
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }
        
        .image-preview-container {
            text-align: center;
            margin: 20px 0;
        }
        
        .image-preview {
            max-width: 100%;
            max-height: 300px;
            border-radius: 20px;
            border: 3px solid #4CAF50;
            display: none;
            margin: 0 auto;
        }
        
        .skip-image-btn {
            background: transparent;
            border: 1px solid #4CAF50;
            color: #4CAF50;
            padding: 12px 25px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 14px;
            margin: 10px 0;
            width: 100%;
        }
        
        .skip-image-btn:hover {
            background: #4CAF50;
            color: white;
        }
        
        .review-box {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 25px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .review-content {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 15px;
            padding: 20px;
        }
        
        .review-content p {
            margin: 10px 0;
        }
        
        .review-content strong {
            color: #4CAF50;
            min-width: 120px;
            display: inline-block;
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 14px 30px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(76, 175, 80, 0.4);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
        }
        
        .cancel-btn {
            background: #666;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
        }
        
        .cancel-btn:hover {
            background: #555;
        }
        
        #message-container {
            margin-bottom: 20px;
        }
        
        .error-message {
            background: rgba(217, 101, 101, 0.2);
            border: 1px solid #d96565;
            color: #d96565;
            padding: 12px 20px;
            border-radius: 50px;
            text-align: center;
        }
        
        .success-message {
            background: rgba(76, 175, 80, 0.2);
            border: 1px solid #4CAF50;
            color: #4CAF50;
            padding: 12px 20px;
            border-radius: 50px;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .step-indicator {
                flex-direction: column;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<?php
require_once 'config.php';

// Check if user is logged in and is a seller
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = getCurrentUser();
if ($currentUser['role'] !== 'seller') {
    header('Location: auth.php');
    exit;
}

// Get all categories for dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $price = $_POST['price'] ?? '';
    $quantity = $_POST['quantity'] ?? 1;
    $condition = $_POST['condition'] ?? 'new';
    $phone = $_POST['phone'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if (empty($name) || empty($category_id) || empty($price) || empty($phone)) {
        $error = 'Please fill all required fields';
    } elseif ($price <= 0) {
        $error = 'Price must be greater than 0';
    } else {
        // Handle image upload
        $image_url = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $image_url = $targetPath;
            }
        }
        
        // Insert product
        $stmt = $pdo->prepare("
            INSERT INTO products (seller_id, category_id, name, price, quantity, `condition`, phone_contact, description, image_url, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        if ($stmt->execute([$currentUser['id'], $category_id, $name, $price, $quantity, $condition, $phone, $description, $image_url])) {
            $success = 'Product added successfully!';
            // Clear form or redirect
            echo "<script>setTimeout(function() { window.location.href = 'my-products.php'; }, 2000);</script>";
        } else {
            $error = 'Failed to add product';
        }
    }
}
?>

<?php include 'navbar.php'; ?>

<main>
    <div class="add-product-wrapper">
        <div class="add-product-container">
            <div class="page-header">
                <h1>📦 Add New Product</h1>
                <p>Fill in the details below to list your product for sale</p>
            </div>
            
            <div id="message-container">
                <?php if ($error): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>
            </div>
            
            <!-- Progress Bar -->
            <div class="progress-bar">
                <div class="progress-fill" id="progress-fill" style="width: 25%;"></div>
            </div>
            
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step active" id="step1">1. Basic Info</div>
                <div class="step" id="step2">2. Details</div>
                <div class="step" id="step3">3. Images</div>
                <div class="step" id="step4">4. Review</div>
            </div>
            
            <form id="add-product-form" method="POST" enctype="multipart/form-data">
                <!-- Step 1: Basic Info -->
                <div id="step1-content" class="form-section">
                    <div class="form-group">
                        <label>📝 Product Name <span class="required-star">*</span></label>
                        <input type="text" name="name" id="productName" placeholder="e.g. iPhone 13, Nike Air Max" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>📂 Category <span class="required-star">*</span></label>
                            <select name="category_id" id="productCategory" required>
                                <option value="">Select category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo $cat['icon']; ?> <?php echo $cat['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>🏷️ Condition</label>
                            <select name="condition" id="productCondition">
                                <option value="new">🆕 New</option>
                                <option value="like-new">✨ Like New</option>
                                <option value="good">👍 Good</option>
                                <option value="fair">🔄 Fair</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <div></div>
                        <button type="button" class="btn btn-primary" onclick="nextStep(2)">Next →</button>
                    </div>
                </div>

                <!-- Step 2: Details -->
                <div id="step2-content" class="form-section" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>💰 Price (DZD) <span class="required-star">*</span></label>
                            <div class="price-input">
                                <input type="number" name="price" id="productPrice" placeholder="e.g. 15000" min="1" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>📦 Quantity</label>
                            <input type="number" name="quantity" id="productQuantity" placeholder="e.g. 10" min="1" value="1">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>📞 Contact Phone Number <span class="required-star">*</span></label>
                        <input type="tel" name="phone" id="productPhone" placeholder="e.g. 0550 12 34 56 or +213 550 12 34 56" required>
                        <small style="color: #4CAF50; display: block; margin-top: 5px;">
                            Buyers will use this number to contact you via call, SMS, or WhatsApp
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label>📝 Description</label>
                        <textarea name="description" id="productDescription" rows="5" placeholder="Describe your product... (optional)"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="prevStep(1)">← Previous</button>
                        <button type="button" class="btn btn-primary" onclick="nextStep(3)">Next →</button>
                    </div>
                </div>

                <!-- Step 3: Images - OPTIONAL -->
                <div id="step3-content" class="form-section" style="display: none;">
                    <div class="image-upload-area" onclick="document.getElementById('productImage').click()">
                        <div class="upload-icon">📸</div>
                        <p>Click to upload product images</p>
                        <small>Supported: JPG, PNG, GIF (max 5MB)</small>
                        <div class="image-optional-badge">✨ Optional - You can skip</div>
                    </div>
                    
                    <input type="file" name="image" id="productImage" accept="image/*" style="display: none;" onchange="previewImage(this)">
                    
                    <div class="image-preview-container">
                        <img id="imagePreview" class="image-preview">
                    </div>
                    
                    <button type="button" class="skip-image-btn" onclick="skipImage()">⏭️ Skip Image (continue without image)</button>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="prevStep(2)">← Previous</button>
                        <button type="button" class="btn btn-primary" onclick="nextStep(4)">Next →</button>
                    </div>
                </div>

                <!-- Step 4: Review -->
                <div id="step4-content" class="form-section" style="display: none;">
                    <div class="review-box">
                        <h3 style="color: #4CAF50; text-align: center;">🔍 Review Your Product</h3>
                        <div id="reviewContent" class="review-content">
                            <!-- Review content will be filled by JavaScript -->
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="prevStep(3)">← Previous</button>
                        <button type="submit" class="btn btn-success">✅ List Product</button>
                    </div>
                </div>
            </form>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="seller-dashboard.php" class="cancel-btn">Cancel</a>
            </div>
        </div>
    </div>
</main>

<footer>
    <p>© 2026 M7 Marketplace. All rights reserved. | <a href="about.php">About</a> | <a href="contact.php">Contact</a> | <a href="terms.php">Terms of Service</a> | <a href="privacy.php">Privacy Policy</a></p>
</footer>

<script src="script.js"></script>
</body>
</html>