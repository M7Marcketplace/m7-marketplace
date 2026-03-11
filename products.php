<?php
require_once 'config.php';

// Get search query from URL
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : 'all';

// Get all categories for filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Build the products query with search
$query = "
    SELECT p.*, u.full_name as seller_name, s.store_name, c.name as category_name, c.icon as category_icon
    FROM products p
    JOIN users u ON p.seller_id = u.id
    LEFT JOIN seller_stores s ON p.seller_id = s.seller_id
    JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1
";

// Add category filter
if ($selectedCategory !== 'all') {
    $query .= " AND c.slug = " . $pdo->quote($selectedCategory);
}

// Add search filter
if (!empty($search)) {
    $query .= " AND (p.name LIKE " . $pdo->quote('%' . $search . '%') . 
              " OR p.description LIKE " . $pdo->quote('%' . $search . '%') . ")";
}

$query .= " ORDER BY p.created_at DESC";

$products = $pdo->query($query)->fetchAll();

// Get counts for each category
$counts = [];
foreach ($categories as $cat) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products p WHERE p.category_id = ? AND p.is_active = 1");
    $stmt->execute([$cat['id']]);
    $counts[$cat['slug']] = $stmt->fetchColumn();
}
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products - M7 Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="M7shooping.css">
    <!-- Simple round favicon with M7 text -->
    <link rel="icon" type="image/x-icon" href="M7shooping.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 600px;
            margin: 40px auto;
            animation: fadeInUp 0.6s ease-out;
        }
        
        .empty-icon {
            font-size: 120px;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }
        
        .empty-state h1 {
            font-size: 42px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #fff 0%, #d96565 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .empty-state p {
            font-size: 18px;
            margin: 20px auto;
            max-width: 500px;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            padding: 20px 0;
            animation: fadeIn 0.6s ease-out;
        }
        
        .product-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #d96565 0%, #4CAF50 100%);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .product-card:hover::before {
            transform: scaleX(1);
        }
        
        .product-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }
        
        .product-card:hover img {
            transform: scale(1.05);
        }
        
        .product-card h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #d96565;
        }
        
        .product-card .seller {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-bottom: 10px;
        }
        
        .product-card .price {
            font-size: 1.8rem;
            font-weight: 700;
            color: #4CAF50;
            margin: 15px 0;
        }
        
        .product-card .stock {
            font-size: 0.9rem;
            margin-bottom: 15px;
            opacity: 0.7;
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .product-actions .btn {
            flex: 1;
            padding: 12px;
            font-size: 1rem;
        }
        
        /* Search Bar Styles */
        .search-section {
            margin: 30px 0 20px;
            text-align: center;
        }
        
        .search-form {
            display: flex;
            max-width: 600px;
            margin: 0 auto;
            gap: 10px;
        }
        
        .search-input {
            flex: 1;
            padding: 15px 20px;
            border-radius: 50px;
            border: 2px solid transparent;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #d96565;
            box-shadow: 0 0 0 4px rgba(217, 101, 101, 0.2);
        }
        
        .search-input:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .search-btn {
            padding: 15px 30px;
            background: linear-gradient(135deg, #d96565 0%, #b84343 100%);
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .search-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(217, 101, 101, 0.4);
        }
        
        .clear-btn {
            padding: 15px 30px;
            background: #666;
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .clear-btn:hover {
            background: #555;
            transform: translateY(-3px);
        }
        
        /* Category Filter Styles */
        .filter-section {
            margin: 30px 0 20px;
            text-align: center;
        }
        
        .filter-title {
            font-size: 1.1rem;
            color: #d96565;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .category-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            margin-bottom: 20px;
            min-height: 50px;
        }
        
        .filter-btn {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }
        
        .filter-btn:hover {
            background: rgba(217, 101, 101, 0.3);
            transform: translateY(-2px);
        }
        
        .filter-btn.active {
            background: linear-gradient(135deg, #d96565 0%, #4CAF50 100%);
            border-color: transparent;
            box-shadow: 0 5px 15px rgba(217, 101, 101, 0.3);
        }
        
        .filter-btn .count {
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 8px;
            border-radius: 50px;
            font-size: 0.75rem;
            margin-left: 5px;
        }
        
        .results-count {
            text-align: center;
            font-size: 0.9rem;
            opacity: 0.7;
            margin-top: 15px;
            padding: 5px;
        }
        
        .results-count strong {
            color: #d96565;
            font-weight: 600;
        }
        
        .no-products-message {
            text-align: center;
            padding: 60px 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            margin: 20px 0;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<main>
    <div id="products-container">
        <?php if (empty($products) && empty($search)): ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h1>No Products Yet</h1>
                <p>Be the first seller to add a product to our marketplace!</p>
                <a href="register.php?role=seller" class="btn btn-success">Become a Seller</a>
            </div>
        <?php else: ?>
            
            <h1 class="text-center">Our Products</h1>
            
            <!-- Search Bar Section -->
            <div class="search-section">
                <form method="GET" action="products.php" class="search-form">
                    <input type="text" 
                           name="search" 
                           class="search-input"
                           placeholder="🔍 Search products by name or description..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    
                    <?php if ($selectedCategory !== 'all'): ?>
                        <input type="hidden" name="category" value="<?php echo $selectedCategory; ?>">
                    <?php endif; ?>
                    
                    <button type="submit" class="search-btn">Search</button>
                    
                    <?php if (!empty($search)): ?>
                        <a href="products.php<?php echo $selectedCategory !== 'all' ? '?category='.$selectedCategory : ''; ?>" class="clear-btn">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Filter Section -->
            <div class="filter-section">
                <div class="filter-title">
                    <span>🔍 Filter by Category</span>
                </div>
                
                <div class="category-filter" id="category-filter">
                    <a href="products.php<?php echo !empty($search) ? '?search='.urlencode($search) : ''; ?>" 
                       class="filter-btn <?php echo $selectedCategory === 'all' ? 'active' : ''; ?>">
                        📋 All <span class="count"><?php echo $totalProducts; ?></span>
                    </a>
                    
                    <?php foreach ($categories as $cat): ?>
                        <?php if (($counts[$cat['slug']] ?? 0) > 0): ?>
                        <a href="products.php?category=<?php echo $cat['slug']; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" 
                           class="filter-btn <?php echo $selectedCategory === $cat['slug'] ? 'active' : ''; ?>">
                            <?php echo $cat['icon']; ?> <?php echo $cat['name']; ?> 
                            <span class="count"><?php echo $counts[$cat['slug']] ?? 0; ?></span>
                        </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                
                <div class="results-count">
                    <?php if (!empty($search)): ?>
                        Showing <strong><?php echo count($products); ?></strong> result<?php echo count($products) != 1 ? 's' : ''; ?> for "<?php echo htmlspecialchars($search); ?>"
                    <?php else: ?>
                        Showing <strong><?php echo count($products); ?></strong> of <?php echo $totalProducts; ?> products
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Products Grid -->
            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🔍</div>
                    <h1>No Products Found</h1>
                    <p>No products match your search "<?php echo htmlspecialchars($search); ?>"</p>
                    <a href="products.php<?php echo $selectedCategory !== 'all' ? '?category='.$selectedCategory : ''; ?>" class="btn">Clear Search</a>
                </div>
            <?php else: ?>
                <div class="products-grid" id="products-grid">
                    <?php foreach ($products as $product): ?>
                    <div class="product-card" data-category="<?php echo $product['category_name']; ?>">
                        <img src="<?php echo $product['image_url'] ?? 'https://via.placeholder.com/300x300?text=No+Image'; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             onerror="this.src='https://via.placeholder.com/300x300?text=No+Image'">
                        <h3><?php echo htmlspecialchars($product['name']); ?> <?php echo $product['category_icon']; ?></h3>
                        <p class="seller">by <?php echo htmlspecialchars($product['store_name'] ?? $product['seller_name']); ?></p>
                        <p class="price"><?php echo number_format($product['price']); ?> DZD</p>
                        <p class="stock">📦 <?php echo $product['quantity']; ?> left</p>
                        <div class="product-actions">
                            <button onclick="viewProductDetails(<?php echo $product['id']; ?>)" class="btn btn-secondary">View</button>
                            <button onclick="addToCart(
                                <?php echo $product['id']; ?>,
                                '<?php echo addslashes($product['name']); ?>',
                                <?php echo $product['price']; ?>,
                                '<?php echo addslashes($product['image_url'] ?? 'https://via.placeholder.com/300'); ?>',
                                <?php echo $product['seller_id']; ?>,
                                '<?php echo addslashes($product['store_name'] ?? $product['seller_name']); ?>'
                            )" class="btn btn-success">Add to Cart</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
    </div>
</main>

<footer>
    <p>© 2026 M7 Marketplace. All rights reserved. | <a href="about.php">About</a> | <a href="contact.php">Contact</a> | <a href="terms.php">Terms of Service</a> | <a href="privacy.php">Privacy Policy</a></p>
</footer>

<script src="script.js"></script>
</body>
</html>