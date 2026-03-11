<?php
require_once 'config.php';

// ALL PHP LOGIC MUST BE AT THE TOP, BEFORE ANY HTML OUTPUT

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = getCurrentUser();

// Simple admin check - you can change this condition
$isAdmin = ($currentUser['email'] === 'm7.contact.us@gmail.com' || $currentUser['id'] == 1);

if (!$isAdmin) {
    header('Location: home.php');
    exit;
}

// Get statistics
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalSellers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='seller'")->fetchColumn();
$totalBuyers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='buyer'")->fetchColumn();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status='delivered'")->fetchColumn();

// Get all users with additional seller data for commission tracking
$users = $pdo->query("
    SELECT u.*, s.store_name,
           (SELECT COUNT(*) FROM products WHERE seller_id = u.id) as product_count,
           (SELECT COALESCE(SUM(sold * price), 0) FROM products WHERE seller_id = u.id) as total_revenue
    FROM users u
    LEFT JOIN seller_stores s ON u.id = s.seller_id
    ORDER BY u.registration_date DESC
")->fetchAll();

// Get all products with seller info
$products = $pdo->query("
    SELECT p.*, u.full_name as seller_name, c.name as category_name
    FROM products p
    JOIN users u ON p.seller_id = u.id
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
    LIMIT 50
")->fetchAll();

// Get all orders
$orders = $pdo->query("
    SELECT o.*, u.full_name as buyer_name
    FROM orders o
    LEFT JOIN users u ON o.buyer_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 50
")->fetchAll();

// Calculate commission summary
$now = new DateTime();
$totalCommissionDue = 0;
$sellersDue = 0;
$sellersInFreePeriod = 0;

foreach ($users as $user) {
    if ($user['role'] === 'seller') {
        $registrationDate = new DateTime($user['registration_date']);
        $monthsSince = $registrationDate->diff($now)->m + ($registrationDate->diff($now)->y * 12);
        
        if ($monthsSince >= 3) {
            $totalCommissionDue += round($user['total_revenue'] * 0.1);
            $sellersDue++;
        } else {
            $sellersInFreePeriod++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - M7 Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="M7shooping.css">
    <!-- Simple round favicon with M7 text -->
    <link rel="icon" type="image/x-icon" href="M7shooping.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
            color: white;
            font-family: 'Poppins', sans-serif;
        }
        
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            background: rgba(255,255,255,0.1);
            padding: 20px 30px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }
        
        .admin-header h1 {
            margin: 0;
            background: linear-gradient(135deg, #d96565, #4CAF50);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .admin-tabs {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .tab-btn {
            padding: 12px 25px;
            border-radius: 50px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .tab-btn:hover {
            background: rgba(217, 101, 101, 0.3);
            transform: translateY(-2px);
        }
        
        .tab-btn.active {
            background: linear-gradient(135deg, #d96565, #b84343);
            border-color: transparent;
        }
        
        .admin-section {
            background: rgba(255,255,255,0.05);
            border-radius: 30px;
            padding: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: rgba(255,255,255,0.1);
            padding: 25px;
            border-radius: 20px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: 800;
            color: #d96565;
        }
        
        /* Commission Summary Cards */
        .commission-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 30px 0;
        }
        
        .commission-card {
            padding: 25px;
            border-radius: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .commission-card.free {
            background: rgba(76, 175, 80, 0.1);
            border: 1px solid #4CAF50;
        }
        
        .commission-card.due {
            background: rgba(255, 152, 0, 0.1);
            border: 1px solid #FF9800;
        }
        
        .commission-card.total {
            background: rgba(217, 101, 101, 0.1);
            border: 1px solid #d96565;
        }
        
        .commission-number {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 5px;
        }
        
        .commission-label {
            font-size: 14px;
            opacity: 0.8;
        }
        
        .commission-free { color: #4CAF50; }
        .commission-due { color: #FF9800; }
        .commission-total { color: #d96565; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255,255,255,0.05);
            border-radius: 15px;
            overflow: hidden;
        }
        
        th {
            background: rgba(217, 101, 101, 0.3);
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        tr:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .action-btn {
            padding: 8px 15px;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 0 5px;
            text-decoration: none;
            display: inline-block;
        }
        
        .edit-btn { background: #4CAF50; color: white; }
        .delete-btn { background: #d96565; color: white; }
        .suspend-btn { background: #FF9800; color: white; }
        .view-btn { background: #2196F3; color: white; }
        .paid-btn { background: #4CAF50; color: white; }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .search-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .search-bar input {
            flex: 1;
            padding: 15px 20px;
            border-radius: 50px;
            border: none;
            background: rgba(255,255,255,0.1);
            color: white;
            font-size: 16px;
        }
        
        .search-bar input:focus {
            outline: 2px solid #d96565;
        }
        
        .filter-select {
            padding: 15px 25px;
            border-radius: 50px;
            background: rgba(255,255,255,0.1);
            color: white;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .export-btn {
            background: #4CAF50;
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            margin-right: 15px;
        }
        
        .danger-btn {
            background: #d96565;
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }
        
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background: linear-gradient(135deg, #1a1a1a, #2a2a2a);
            padding: 40px;
            border-radius: 30px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .badge-active {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }
        
        .badge-suspended {
            background: rgba(255, 152, 0, 0.2);
            color: #FF9800;
            border: 1px solid #FF9800;
        }
        
        .badge-admin {
            background: rgba(217, 101, 101, 0.2);
            color: #d96565;
            border: 1px solid #d96565;
        }
        
        .commission-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .commission-badge.free {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }
        
        .commission-badge.regular {
            background: rgba(255, 152, 0, 0.2);
            color: #FF9800;
            border: 1px solid #FF9800;
        }
        
        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255,255,255,0.1);
            border-top: 4px solid #d96565;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="admin-container">
        <div class="admin-header">
            <h1>🔐 M7 Marketplace Admin Panel</h1>
            <div>
                <span style="margin-right: 20px;">Welcome, <?php echo htmlspecialchars($currentUser['full_name']); ?></span>
                <a href="logout.php" class="action-btn" style="background: #666;">Logout</a>
                <a href="tax-dashboard.php" class="action-btn" style="background: #FF9800;">💰 Tax Dashboard</a>
                <a href="mark-paid.php" class="action-btn" style="background: #FF9800;">💰 Mark Payments</a>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="stats-grid" id="stats-container">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalUsers; ?></div>
                <div>Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalSellers; ?></div>
                <div>Sellers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalBuyers; ?></div>
                <div>Buyers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalProducts; ?></div>
                <div>Products</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalOrders; ?></div>
                <div>Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($totalRevenue); ?> DZD</div>
                <div>Revenue</div>
            </div>
        </div>

        <!-- Commission Summary Cards (NEW) -->
        <div class="commission-summary">
            <div class="commission-card free">
                <div class="commission-number commission-free"><?php echo $sellersInFreePeriod; ?></div>
                <div class="commission-label">Sellers in 0% period</div>
            </div>
            <div class="commission-card due">
                <div class="commission-number commission-due"><?php echo $sellersDue; ?></div>
                <div class="commission-label">Sellers with commission due</div>
            </div>
            <div class="commission-card total">
                <div class="commission-number commission-total"><?php echo number_format($totalCommissionDue); ?> DZD</div>
                <div class="commission-label">Total commission receivable</div>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="admin-tabs">
            <button class="tab-btn active" onclick="switchTab('users')">👥 Users</button>
            <button class="tab-btn" onclick="switchTab('products')">📦 Products</button>
            <button class="tab-btn" onclick="switchTab('orders')">📋 Orders</button>
            <button class="tab-btn" onclick="window.location.href='payment-tracker.php'">💰 Payments</button>
        </div>
        
        <!-- Search & Filters -->
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="🔍 Search by name, email, or username..." onkeyup="filterTable()">
            <select class="filter-select" id="roleFilter" onchange="filterTable()">
                <option value="all">All Roles</option>
                <option value="buyer">Buyers</option>
                <option value="seller">Sellers</option>
            </select>
        </div>
        
        <!-- Export Buttons -->
        <div style="margin-bottom: 20px; text-align: right;">
            <button onclick="exportData('csv')" class="export-btn">📥 Export to CSV</button>
            <button onclick="exportData('json')" class="export-btn">📥 Export to JSON</button>
        </div>
        
        <!-- Main Content Area - Users Tab (Default) -->
        <div class="admin-section" id="admin-content">
            <h2>👥 All Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Store</th>
                        <th>Joined</th>
                        <th>Commission Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): 
                        // Calculate commission status for sellers
                        $commissionStatus = '';
                        if ($user['role'] === 'seller') {
                            $registrationDate = new DateTime($user['registration_date']);
                            $now = new DateTime();
                            $monthsSince = $registrationDate->diff($now)->m + ($registrationDate->diff($now)->y * 12);
                            $isFreePeriod = $monthsSince < 3;
                            $freeEndDate = clone $registrationDate;
                            $freeEndDate->modify('+3 months');
                            $daysLeft = $now->diff($freeEndDate)->days;
                            $commissionAmount = round($user['total_revenue'] * 0.1);
                        }
                    ?>
                    <tr data-email="<?php echo $user['email']; ?>" data-name="<?php echo $user['full_name']; ?>" data-username="<?php echo $user['username']; ?>" data-role="<?php echo $user['role']; ?>">
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td>
                            <span class="badge" style="background: <?php echo $user['role'] === 'seller' ? 'rgba(76,175,80,0.2)' : 'rgba(33,150,243,0.2)'; ?>; color: <?php echo $user['role'] === 'seller' ? '#4CAF50' : '#2196F3'; ?>">
                                <?php echo $user['role']; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($user['store_name'] ?? '-'); ?></td>
                        <td><?php echo date('d M Y', strtotime($user['registration_date'])); ?></td>
                        <td>
                            <?php if ($user['role'] === 'seller'): ?>
                                <?php if ($isFreePeriod): ?>
                                    <div>
                                        <span class="commission-badge free">✅ 0% Commission</span><br>
                                        <small>Ends: <?php echo $freeEndDate->format('d M Y'); ?></small><br>
                                        <small><?php echo $daysLeft; ?> days left</small>
                                    </div>
                                <?php else: ?>
                                    <div>
                                        <span class="commission-badge regular">💰 10% Commission</span><br>
                                        <small>Due: <?php echo number_format($commissionAmount); ?> DZD</small><br>
                                        <small>Since: <?php echo date('d M Y', strtotime($user['registration_date'])); ?></small>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="opacity: 0.5;">-</span>
                            <?php endif; ?>
                        </td>
                        <!-- In the table header, add a new column -->
                        <th>Actions</th>

                        <!-- In the table body, replace your existing actions with this -->
                        <td>
                            <button class="action-btn view-btn" onclick="viewUser(<?php echo $user['id']; ?>)">👁️ View</button>
                            <a href="mailto:<?php echo $user['email']; ?>" class="action-btn edit-btn">✉️ Email</a>
                            
                            <?php if ($user['role'] !== 'admin'): // Don't block/delete other admins ?>
                                
                                <?php if ($user['is_suspended'] ?? false): ?>
                                    <button class="action-btn suspend-btn" onclick="toggleSuspend(<?php echo $user['id']; ?>, 'unsuspend')">▶️ Activate</button>
                                <?php else: ?>
                                    <button class="action-btn suspend-btn" onclick="toggleSuspend(<?php echo $user['id']; ?>, 'suspend')">⏸️ Block</button>
                                <?php endif; ?>
                                
                                <button class="action-btn delete-btn" onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo addslashes($user['full_name']); ?>')">🗑️ Delete</button>
                                
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    let currentTab = 'users';
    let allUsers = <?php echo json_encode($users); ?>;
    let allProducts = <?php echo json_encode($products); ?>;
    let allOrders = <?php echo json_encode($orders); ?>;

    function switchTab(tab) {
        currentTab = tab;
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        
        let content = document.getElementById('admin-content');
        
        if (tab === 'users') {
            displayUsers(content);
        } else if (tab === 'products') {
            displayProducts(content);
        } else if (tab === 'orders') {
            displayOrders(content);
        }
    }

    function displayUsers(container) {
        let html = '<h2>👥 All Users</h2>';
        html += '<table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Username</th><th>Role</th><th>Store</th><th>Joined</th><th>Actions</th></tr></thead><tbody>';
        
        allUsers.forEach(user => {
            html += `
                <tr>
                    <td>${user.id}</td>
                    <td>${escapeHtml(user.full_name)}</td>
                    <td>${escapeHtml(user.email)}</td>
                    <td>${escapeHtml(user.username)}</td>
                    <td><span class="badge" style="background: ${user.role === 'seller' ? 'rgba(76,175,80,0.2)' : 'rgba(33,150,243,0.2)'}; color: ${user.role === 'seller' ? '#4CAF50' : '#2196F3'}">${user.role}</span></td>
                    <td>${escapeHtml(user.store_name || '-')}</td>
                    <td>${new Date(user.registration_date).toLocaleDateString()}</td>
                    <td>
                        <button class="action-btn view-btn" onclick="viewUser(${user.id})">👁️ View</button>
                        <a href="mailto:${user.email}" class="action-btn edit-btn">✉️ Email</a>
                    </td>
                </tr>
            `;
        });
        
        html += '</tbody></table>';
        container.innerHTML = html;
    }

    function displayProducts(container) {
        let html = '<h2>📦 All Products</h2>';
        html += '<table><thead><tr><th>Product</th><th>Seller</th><th>Price</th><th>Stock</th><th>Sold</th><th>Category</th><th>Actions</th></tr></thead><tbody>';
        
        allProducts.forEach(product => {
            html += `
                <tr>
                    <td>${escapeHtml(product.name)}</td>
                    <td>${escapeHtml(product.seller_name)}</td>
                    <td>${Number(product.price).toLocaleString()} DZD</td>
                    <td>${product.quantity}</td>
                    <td>${product.sold || 0}</td>
                    <td>${escapeHtml(product.category_name || 'N/A')}</td>
                    <td>
                        <button class="action-btn view-btn" onclick="viewProduct(${product.id})">👁️ View</button>
                    </td>
                </tr>
            `;
        });
        
        html += '</tbody></table>';
        container.innerHTML = html;
    }

    function displayOrders(container) {
        let html = '<h2>📋 Recent Orders</h2>';
        html += '<table><thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead><tbody>';
        
        allOrders.forEach(order => {
            html += `
                <tr>
                    <td>${escapeHtml(order.order_number)}</td>
                    <td>${escapeHtml(order.buyer_name || 'N/A')}</td>
                    <td>${Number(order.total).toLocaleString()} DZD</td>
                    <td><span class="badge" style="background: rgba(255,193,7,0.2); color: #ffc107;">${order.status || 'pending'}</span></td>
                    <td>${new Date(order.created_at).toLocaleDateString()}</td>
                    <td>
                        <button class="action-btn view-btn" onclick="viewOrder(${order.id})">👁️ View</button>
                    </td>
                </tr>
            `;
        });
        
        html += '</tbody></table>';
        container.innerHTML = html;
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function filterTable() {
        let search = document.getElementById('searchInput').value.toLowerCase();
        let roleFilter = document.getElementById('roleFilter').value;
        
        let rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            let name = row.cells[1]?.textContent.toLowerCase() || '';
            let email = row.cells[2]?.textContent.toLowerCase() || '';
            let username = row.cells[3]?.textContent.toLowerCase() || '';
            let role = row.cells[4]?.textContent.toLowerCase() || '';
            
            let matchesSearch = name.includes(search) || email.includes(search) || username.includes(search);
            let matchesRole = roleFilter === 'all' || role.includes(roleFilter);
            
            row.style.display = matchesSearch && matchesRole ? '' : 'none';
        });
    }

    function exportData(format) {
        let data;
        switch(currentTab) {
            case 'users': data = allUsers; break;
            case 'products': data = allProducts; break;
            case 'orders': data = allOrders; break;
            default: data = allUsers;
        }
        
        let content = format === 'json' ? JSON.stringify(data, null, 2) : convertToCSV(data);
        let blob = new Blob([content], { type: format === 'json' ? 'application/json' : 'text/csv' });
        let url = URL.createObjectURL(blob);
        let a = document.createElement('a');
        a.href = url;
        a.download = `${currentTab}_${Date.now()}.${format}`;
        a.click();
    }

    function convertToCSV(data) {
        if (data.length === 0) return '';
        let headers = Object.keys(data[0]);
        let csv = headers.join(',') + '\n';
        data.forEach(row => {
            csv += headers.map(h => {
                let val = row[h] || '';
                if (typeof val === 'string') val = val.replace(/,/g, ';');
                return `"${val}"`;
            }).join(',') + '\n';
        });
        return csv;
    }

    function viewUser(userId) {
        let user = allUsers.find(u => u.id == userId);
        if (!user) return;
        
        let modal = document.createElement('div');
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content" style="max-width: 500px;">
                <h2 style="color: #d96565; margin-bottom: 20px;">👤 User Details</h2>
                
                <div style="margin-bottom: 20px;">
                    <p><strong>ID:</strong> ${user.id}</p>
                    <p><strong>Name:</strong> ${escapeHtml(user.full_name)}</p>
                    <p><strong>Email:</strong> ${escapeHtml(user.email)}</p>
                    <p><strong>Username:</strong> ${escapeHtml(user.username)}</p>
                    <p><strong>Role:</strong> ${user.role}</p>
                    <p><strong>Phone:</strong> ${escapeHtml(user.phone || 'N/A')}</p>
                    <p><strong>Joined:</strong> ${new Date(user.registration_date).toLocaleString()}</p>
                    ${user.store_name ? `<p><strong>Store:</strong> ${escapeHtml(user.store_name)}</p>` : ''}
                    ${user.role === 'seller' ? `<p><strong>Products:</strong> ${user.product_count}</p>` : ''}
                    ${user.role === 'seller' ? `<p><strong>Total Revenue:</strong> ${Number(user.total_revenue).toLocaleString()} DZD</p>` : ''}
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <a href="mailto:${user.email}" class="action-btn edit-btn" style="flex: 1; text-align: center;">✉️ Email</a>
                    <button onclick="this.closest('.modal').remove()" class="action-btn" style="flex: 1; background: #666;">Close</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }

    function viewProduct(productId) {
        let product = allProducts.find(p => p.id == productId);
        if (!product) return;
        
        let modal = document.createElement('div');
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content" style="max-width: 500px;">
                <h2 style="color: #d96565; margin-bottom: 20px;">📦 Product Details</h2>
                
                <div style="margin-bottom: 20px;">
                    <p><strong>Name:</strong> ${escapeHtml(product.name)}</p>
                    <p><strong>Seller:</strong> ${escapeHtml(product.seller_name)}</p>
                    <p><strong>Price:</strong> ${Number(product.price).toLocaleString()} DZD</p>
                    <p><strong>Stock:</strong> ${product.quantity}</p>
                    <p><strong>Sold:</strong> ${product.sold || 0}</p>
                    <p><strong>Category:</strong> ${escapeHtml(product.category_name || 'N/A')}</p>
                    <p><strong>Description:</strong> ${escapeHtml(product.description || 'No description')}</p>
                </div>
                
                <button onclick="this.closest('.modal').remove()" class="action-btn" style="width: 100%; background: #666;">Close</button>
            </div>
        `;
        
        document.body.appendChild(modal);
    }

    function viewOrder(orderId) {
        let order = allOrders.find(o => o.id == orderId);
        if (!order) return;
        
        let modal = document.createElement('div');
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content" style="max-width: 500px;">
                <h2 style="color: #d96565; margin-bottom: 20px;">📋 Order Details</h2>
                
                <div style="margin-bottom: 20px;">
                    <p><strong>Order #:</strong> ${escapeHtml(order.order_number)}</p>
                    <p><strong>Customer:</strong> ${escapeHtml(order.buyer_name || 'N/A')}</p>
                    <p><strong>Total:</strong> ${Number(order.total).toLocaleString()} DZD</p>
                    <p><strong>Status:</strong> ${order.status || 'pending'}</p>
                    <p><strong>Payment:</strong> ${order.payment_method}</p>
                    <p><strong>Date:</strong> ${new Date(order.created_at).toLocaleString()}</p>
                    <p><strong>Shipping Address:</strong> ${escapeHtml(order.shipping_address || 'N/A')}</p>
                    <p><strong>Phone:</strong> ${escapeHtml(order.shipping_phone || 'N/A')}</p>
                </div>
                
                <button onclick="this.closest('.modal').remove()" class="action-btn" style="width: 100%; background: #666;">Close</button>
            </div>
        `;
        
        document.body.appendChild(modal);
    }

    function markAsPaid(sellerId, amount) {
        if (confirm(`Mark ${amount} DZD as paid for this seller?`)) {
            alert('Payment recorded! You can implement a payment tracking system here.');
            // You can redirect to a payment tracking page
            // window.location.href = `mark-paid.php?seller_id=${sellerId}&amount=${amount}`;
        }
    }
    </script>
    <script>
    // Block/Unblock user function
    function toggleSuspend(userId, action) {
        let message = action === 'suspend' ? 'Block this user?' : 'Activate this user?';
        
        if (confirm(message)) {
            fetch('toggle-user-status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'user_id=' + userId + '&action=' + action
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    location.reload();
                } else {
                    alert('❌ ' + data.message);
                }
            });
        }
    }

    // Delete user function
    function confirmDelete(userId, userName) {
        if (confirm(`⚠️ Are you sure you want to DELETE ${userName}?\n\nThis will permanently remove:\n- User account\n- All products (if seller)\n- All orders\n- All messages\n\nThis action CANNOT be undone!`)) {
            
            let password = prompt('Enter your admin password to confirm deletion:');
            if (password) {
                fetch('delete-user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'user_id=' + userId + '&password=' + encodeURIComponent(password)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ ' + data.message);
                        location.reload();
                    } else {
                        alert('❌ ' + data.message);
                    }
                });
            }
        }
    }
    </script>

    <script src="script.js"></script>
</body>
</html>