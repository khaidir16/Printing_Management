<?php
// ========================================
// ENHANCED INDEX WITH LOGIN/PROFILE
// ========================================

session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$user = null;

if ($isLoggedIn) {
    try {
        require_once 'config/database.php';
        $database = new Database();
        $conn = $database->getConnection();
        
        $stmt = $conn->prepare("SELECT id, username, email, full_name, role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            // User not found, destroy session
            session_destroy();
            $isLoggedIn = false;
        }
    } catch (Exception $e) {
        // Database error, treat as not logged in
        $isLoggedIn = false;
    }
}

// Get role icon and color
function getRoleIcon($role) {
    switch ($role) {
        case 'admin': return '👑';
        case 'manager': return '👔';
        case 'staff': return '👷';
        default: return '👤';
    }
}

function getRoleColor($role) {
    switch ($role) {
        case 'admin': return 'bg-red-500 text-white';
        case 'manager': return 'bg-yellow-500 text-black';
        case 'staff': return 'bg-green-500 text-white';
        default: return 'bg-gray-500 text-white';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PrintPro Management - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .dropdown:hover .dropdown-menu {
            display: block;
        }
        .role-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-weight: 600;
        }
        .user-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
        }
        .mobile-menu {
            display: none;
        }
        .mobile-menu.active {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <!-- Logo & Title -->
                <div class="flex items-center space-x-3">
                    <i class="fas fa-print text-3xl text-blue-600"></i>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">PrintPro Management</h1>
                        <p class="text-sm text-gray-500">Sistem Manajemen Percetakan Profesional</p>
                    </div>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-4">
                    <?php if ($isLoggedIn && $user): ?>
                        <!-- Logged in user -->
                        <div class="flex items-center space-x-4">
                            <button class="flex items-center space-x-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-search text-gray-500"></i>
                                <span class="text-sm">Cari</span>
                            </button>
                            <button class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-plus"></i>
                                <span class="text-sm">Tambah Pesanan</span>
                            </button>
                            <button class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-bell text-gray-500"></i>
                            </button>
                        </div>

                        <!-- User Profile Dropdown -->
                        <div class="relative dropdown">
                            <button class="flex items-center space-x-3 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                <div class="user-avatar">
                                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                </div>
                                <div class="text-left">
                                    <div class="text-sm font-medium"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                    <div class="text-xs text-gray-500 flex items-center">
                                        <span class="mr-1"><?php echo getRoleIcon($user['role']); ?></span>
                                        <?php echo strtoupper($user['role']); ?>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </button>

                            <!-- Dropdown Menu -->
                            <div class="dropdown-menu absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border hidden">
                                <div class="p-4 border-b">
                                    <div class="flex items-center space-x-3">
                                        <div class="user-avatar" style="width: 3rem; height: 3rem; font-size: 1.25rem;">
                                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="font-medium"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></div>
                                            <span class="role-badge <?php echo getRoleColor($user['role']); ?> mt-1 inline-block">
                                                <?php echo getRoleIcon($user['role']) . ' ' . strtoupper($user['role']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="p-2">
                                    <a href="#" class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                                        <i class="fas fa-user w-4"></i>
                                        <span>Profil Saya</span>
                                    </a>
                                    <a href="#" class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                                        <i class="fas fa-cog w-4"></i>
                                        <span>Pengaturan</span>
                                    </a>
                                    <hr class="my-2">
                                    <button onclick="logout()" class="flex items-center space-x-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded w-full text-left">
                                        <i class="fas fa-sign-out-alt w-4"></i>
                                        <span>Keluar</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Not logged in -->
                        <div class="flex items-center space-x-4">
                            <div class="text-sm text-gray-600">
                                Belum login? Akses fitur lengkap dengan masuk
                            </div>
                            <a href="login.php" class="flex items-center space-x-2 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-sign-in-alt"></i>
                                <span>Masuk</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button onclick="toggleMobileMenu()" class="p-2 border border-gray-300 rounded-lg">
                        <i class="fas fa-bars text-gray-500"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobileMenu" class="mobile-menu md:hidden border-t bg-white py-4">
                <?php if ($isLoggedIn && $user): ?>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3 px-4">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <div class="font-medium"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></div>
                                <span class="role-badge <?php echo getRoleColor($user['role']); ?> mt-1 inline-block">
                                    <?php echo getRoleIcon($user['role']) . ' ' . strtoupper($user['role']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="space-y-2 px-4">
                            <button class="flex items-center space-x-2 w-full px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-search text-gray-500"></i>
                                <span>Cari</span>
                            </button>
                            <button class="flex items-center space-x-2 w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-plus"></i>
                                <span>Tambah Pesanan</span>
                            </button>
                            <a href="#" class="flex items-center space-x-2 w-full px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-user text-gray-500"></i>
                                <span>Profil Saya</span>
                            </a>
                            <a href="#" class="flex items-center space-x-2 w-full px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-cog text-gray-500"></i>
                                <span>Pengaturan</span>
                            </a>
                            <button onclick="logout()" class="flex items-center space-x-2 w-full px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Keluar</span>
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="px-4 space-y-4">
                        <div class="text-center text-gray-600">
                            Belum login? Akses fitur lengkap dengan masuk
                        </div>
                        <a href="login.php" class="flex items-center justify-center space-x-2 w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Masuk</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if (!$isLoggedIn): ?>
            <!-- Public Dashboard (Not Logged In) -->
            <div class="space-y-8">
                <!-- Welcome Section -->
                <div class="text-center py-12">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        Selamat Datang di PrintPro Management
                    </h2>
                    <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                        Sistem manajemen percetakan profesional untuk mengelola pelanggan, produk, dan pesanan dengan mudah dan efisien.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="login.php" class="inline-flex items-center justify-center space-x-2 px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-lg">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Masuk ke Sistem</span>
                        </a>
                        <button class="inline-flex items-center justify-center space-x-2 px-8 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-lg">
                            <i class="fas fa-eye"></i>
                            <span>Lihat Demo</span>
                        </button>
                    </div>
                </div>

                <!-- Features Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-sm border text-center">
                        <i class="fas fa-users text-4xl text-blue-600 mb-4"></i>
                        <h3 class="text-xl font-semibold mb-2">Manajemen Pelanggan</h3>
                        <p class="text-gray-600">
                            Kelola data pelanggan dengan mudah, termasuk informasi kontak dan riwayat pesanan.
                        </p>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border text-center">
                        <i class="fas fa-box text-4xl text-blue-600 mb-4"></i>
                        <h3 class="text-xl font-semibold mb-2">Katalog Produk</h3>
                        <p class="text-gray-600">
                            Atur produk dan layanan percetakan dengan harga, kategori, dan deskripsi lengkap.
                        </p>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border text-center">
                        <i class="fas fa-print text-4xl text-blue-600 mb-4"></i>
                        <h3 class="text-xl font-semibold mb-2">Tracking Pesanan</h3>
                        <p class="text-gray-600">
                            Pantau status pesanan dari pending hingga selesai dengan sistem tracking terintegrasi.
                        </p>
                    </div>
                </div>

                <!-- Stats Preview -->
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="p-6 text-center border-b">
                        <h3 class="text-xl font-semibold">Statistik Sistem</h3>
                        <p class="text-gray-600">Data real-time dari sistem PrintPro Management</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                            <div>
                                <div class="text-3xl font-bold text-blue-600" id="customerCount">...</div>
                                <div class="text-gray-600">Total Pelanggan</div>
                            </div>
                            <div>
                                <div class="text-3xl font-bold text-green-600" id="productCount">...</div>
                                <div class="text-gray-600">Produk Tersedia</div>
                            </div>
                            <div>
                                <div class="text-3xl font-bold text-purple-600" id="orderCount">...</div>
                                <div class="text-gray-600">Pesanan Aktif</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Authenticated Dashboard -->
            <div class="space-y-6">
                <!-- Welcome Message -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold mb-2">
                                Selamat datang, <?php echo htmlspecialchars($user['full_name']); ?>! 👋
                            </h3>
                            <p class="opacity-90">
                                Anda login sebagai <?php echo strtoupper($user['role']); ?> • 
                                <?php echo date('l, d F Y'); ?>
                            </p>
                        </div>
                        <div class="text-6xl opacity-20">
                            <?php echo getRoleIcon($user['role']); ?>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-sm border hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-medium text-gray-600">Total Pelanggan</h4>
                            <i class="fas fa-users text-gray-400"></i>
                        </div>
                        <div class="text-2xl font-bold" id="authCustomerCount">...</div>
                        <p class="text-xs text-gray-500">Pelanggan aktif</p>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-medium text-gray-600">Total Produk</h4>
                            <i class="fas fa-box text-gray-400"></i>
                        </div>
                        <div class="text-2xl font-bold" id="authProductCount">...</div>
                        <p class="text-xs text-gray-500">Jenis layanan</p>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-medium text-gray-600">Pesanan Bulan Ini</h4>
                            <i class="fas fa-print text-gray-400"></i>
                        </div>
                        <div class="text-2xl font-bold" id="authOrderCount">...</div>
                        <p class="text-xs text-gray-500">Pesanan aktif</p>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="p-6 border-b">
                        <h3 class="text-xl font-semibold">Pesanan Terbaru</h3>
                        <p class="text-gray-600">Daftar pesanan yang masuk dalam sistem</p>
                    </div>
                    <div class="p-6">
                        <div id="recentOrders">
                            <div class="text-center py-8">
                                <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                                <p class="text-gray-500">Memuat pesanan...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Toggle mobile menu
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('active');
        }

        // Logout function
        function logout() {
            if (confirm('Apakah Anda yakin ingin keluar?')) {
                fetch('api/auth.php?action=logout', {
                    method: 'POST'
                })
                .then(() => {
                    window.location.href = 'login.php';
                })
                .catch(error => {
                    console.error('Logout error:', error);
                    window.location.href = 'login.php';
                });
            }
        }

        // Load statistics
        async function loadStats() {
            try {
                // Load customers
                const customersResponse = await fetch('api/customers.php');
                const customersData = await customersResponse.json();
                const customerCount = customersData.success ? customersData.count || customersData.data?.length || 0 : 0;
                
                // Load products
                const productsResponse = await fetch('api/products.php');
                const productsData = await productsResponse.json();
                const productCount = productsData.success ? productsData.count || productsData.data?.length || 0 : 0;
                
                // Load orders
                const ordersResponse = await fetch('api/orders.php');
                const ordersData = await ordersResponse.json();
                const orderCount = ordersData.success ? ordersData.count || ordersData.data?.length || 0 : 0;

                // Update public stats
                const customerCountEl = document.getElementById('customerCount');
                const productCountEl = document.getElementById('productCount');
                const orderCountEl = document.getElementById('orderCount');
                
                if (customerCountEl) customerCountEl.textContent = customerCount;
                if (productCountEl) productCountEl.textContent = productCount;
                if (orderCountEl) orderCountEl.textContent = orderCount;

                // Update authenticated stats
                const authCustomerCountEl = document.getElementById('authCustomerCount');
                const authProductCountEl = document.getElementById('authProductCount');
                const authOrderCountEl = document.getElementById('authOrderCount');
                
                if (authCustomerCountEl) authCustomerCountEl.textContent = customerCount;
                if (authProductCountEl) authProductCountEl.textContent = productCount;
                if (authOrderCountEl) authOrderCountEl.textContent = orderCount;

                // Load recent orders for authenticated users
                <?php if ($isLoggedIn): ?>
                if (ordersData.success && ordersData.data) {
                    displayRecentOrders(ordersData.data.slice(0, 5));
                }
                <?php endif; ?>

            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        // Display recent orders
        function displayRecentOrders(orders) {
            const container = document.getElementById('recentOrders');
            if (!container) return;

            if (orders.length === 0) {
                container.innerHTML = '<div class="text-center py-8 text-gray-500">Belum ada pesanan</div>';
                return;
            }

            const formatCurrency = (amount) => {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(amount);
            };

            const getStatusColor = (status) => {
                switch (status) {
                    case 'Selesai': return 'bg-green-500 text-white';
                    case 'Proses': return 'bg-blue-500 text-white';
                    case 'Pending': return 'bg-yellow-500 text-black';
                    case 'Dibatalkan': return 'bg-red-500 text-white';
                    default: return 'bg-gray-500 text-white';
                }
            };

            const tableHTML = `
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${orders.map(order => `
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">#${order.id}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${order.customer || 'N/A'}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${order.product || 'N/A'}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${order.quantity}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">${formatCurrency(order.total_price || order.total || 0)}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(order.status)}">
                                            ${order.status}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${order.order_date || order.date || 'N/A'}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;

            container.innerHTML = tableHTML;
        }

        // Load stats on page load
        document.addEventListener('DOMContentLoaded', loadStats);
    </script>
</body>
</html>
