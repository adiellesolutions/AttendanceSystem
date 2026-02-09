<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../backend/db/db.php";

// allow only admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// fetch admin info
$sql = "SELECT username, profile_photo FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

$displayName = $admin['username'];
$displayRole = "Administrator";

$photoUrl = !empty($admin['profile_photo'])
    ? "../../uploads/" . $admin['profile_photo']
    : "https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop";
?>


<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="RFID System Settings - Configure hardware integration, manage card assignments, and monitor scanning device connectivity">
    <title>RFID System Settings - RFID Attendance System</title>
    <link rel="stylesheet" href="../css/main.css">
  
  <script type="module" async src="https://static.rocket.new/rocket-web.js?_cfg=https%3A%2F%2Frfidatten9359back.builtwithrocket.new&_be=https%3A%2F%2Fapplication.rocket.new&_v=0.1.12"></script>
  <script type="module" defer src="https://static.rocket.new/rocket-shot.js?v=0.0.2"></script>
  </head>
<body class="bg-background">
    <!-- Navigation Header -->
    <header class="bg-surface border-b border-border sticky top-0 z-nav shadow-card">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo and Brand -->
                <div class="flex items-center space-x-3">
                    <svg class="w-10 h-10" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="40" height="40" rx="8" fill="#1E3A5F"/>
                        <path d="M12 20L18 26L28 14" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="20" cy="20" r="12" stroke="#E67E22" stroke-width="2" stroke-dasharray="4 4"/>
                    </svg>
                    <div>
                        <h1 class="text-xl font-heading font-bold text-primary">RFID Attendance</h1>
                        <p class="text-xs text-text-secondary font-caption">System Settings</p>
                    </div>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="admin_dashboard.php" class="px-4 py-2 rounded-xl text-text-secondary hover:bg-primary-50 hover:text-primary font-medium transition-smooth">
                        Dashboard
                    </a>
                    <a href="user_management.php" class="px-4 py-2 rounded-xl text-text-secondary hover:bg-primary-50 hover:text-primary font-medium transition-smooth">
                        Users
                    </a>
                    <a href="rfid_system_settings.php" class="px-4 py-2 rounded-xl bg-primary-50 text-primary font-medium transition-smooth">
                        Settings
                    </a>
                </div>

                <!-- Admin User Profile -->
<div class="flex items-center space-x-4">
    <div class="flex items-center space-x-3">
        <img src="<?= htmlspecialchars($photoUrl) ?>"
            alt="Admin profile photo"
            class="w-10 h-10 rounded-full object-cover border-2 border-primary"
            onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop'; this.onerror=null;">

        <div class="hidden sm:block">
            <p class="text-sm font-semibold text-text-primary">
                <?= htmlspecialchars($displayName) ?>
            </p>
            <p class="text-xs text-text-secondary">
                <?= $displayRole ?>
            </p>
        </div>
    </div>
</div>

                    <!-- Logout Button -->
                    <button
    onclick="window.location.href='../../backend/api/logout.php'"
    class="btn btn-outline h-10 px-4 text-sm flex items-center space-x-2
           hover:bg-error hover:text-white hover:border-error transition-smooth"
    aria-label="Logout"
    title="Logout from system"
>
    <!-- icon -->
    <svg xmlns="http://www.w3.org/2000/svg"
        class="w-4 h-4"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
        stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1
               a2 2 0 01-2 2H5a2 2 0 01-2-2V7
               a2 2 0 012-2h6a2 2 0 012 2v1" />
    </svg>

    <span class="hidden sm:inline">Logout</span>
</button>


                </div>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-xl hover:bg-primary-50 transition-smooth touch-target" aria-label="Toggle mobile menu">
                    <img src="https://img.rocket.new/generatedImages/rocket_gen_img_1b5957374-1766482728093.png" alt="Menu icon" class="w-6 h-6">
                </button>
            </div>

            <!-- Mobile Navigation -->
            <div id="mobile-menu" class="hidden md:hidden pb-4 space-y-2">
                <a href="admin_dashboard.html" class="block px-4 py-3 rounded-xl text-text-secondary hover:bg-primary-50 hover:text-primary font-medium transition-smooth">
                    Dashboard
                </a>
                <a href="user_management.html" class="block px-4 py-3 rounded-xl text-text-secondary hover:bg-primary-50 hover:text-primary font-medium transition-smooth">
                    Users
                </a>
                <a href="rfid_system_settings.html" class="block px-4 py-3 rounded-xl bg-primary-50 text-primary font-medium">
                    Settings
                </a>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-heading font-bold text-text-primary mb-2">RFID System Settings</h2>
                    <p class="text-text-secondary">Manage card assignments</p>
                </div>
               
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="space-y-8">
            <!-- Left Column: RFID Readers & Configuration -->
            <div class="lg:col-span-3">     

                <!-- RFID Card Management -->
                <section id="card-management" class="card">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-heading font-semibold text-text-primary mb-1">RFID Card Management</h3>
                            <p class="text-sm text-text-secondary">Manage student card assignments and activation status</p>
                        </div>
                    </div>

                    <!-- Search and Filter -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="md:col-span-2">
                            <div class="relative">
                                <input type="text" placeholder="Search by student name or card ID" class="input text-sm pl-10 w-full" id="card-search">
                                <img src="https://img.rocket.new/generatedImages/rocket_gen_img_1b7fbd49e-1766036505327.png" alt="Search icon" class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2">
                            </div>
                        </div>
                        <div>
                            <select class="input text-sm w-full" id="card-status-filter">
                                <option value="all">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="lost">Lost/Deactivated</option>
                            </select>
                        </div>
                    </div>

                    <!-- Cards Table -->
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>
                                        
                            </th>
                            <th>Student Name</th>
                            <th>Card ID</th>
                            <th>Status</th>
                            <th>Issue Date</th>
                        </tr>
                    </thead>

                            <tbody id="rfid-table-body">
                                <!-- populated by JS -->
                            </tbody>

                        </table>
                    </div>

                    <!-- Bulk Actions -->
                    <div class="text-sm text-text-secondary" id="card-count">
                        Loading cards...
                    </div>

                </section>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-surface border-t border-border mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- About Section -->
                <div>
                    <h4 class="font-heading font-semibold text-text-primary mb-4">RFID Attendance System</h4>
                    <p class="text-sm text-text-secondary mb-4">Automated classroom attendance tracking through RFID technology for modern educational institutions.</p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="font-heading font-semibold text-text-primary mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="admin_dashboard.html" class="text-text-secondary hover:text-primary transition-smooth">Dashboard</a></li>
                        <li><a href="user_management.html" class="text-text-secondary hover:text-primary transition-smooth">User Management</a></li>
                        <li><a href="rfid_system_settings.html" class="text-text-secondary hover:text-primary transition-smooth">System Settings</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h4 class="font-heading font-semibold text-text-primary mb-4">Contact Support</h4>
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-start space-x-2">
                            <img src="https://img.rocket.new/generatedImages/rocket_gen_img_1175352d5-1767644430280.png" alt="Email icon" class="w-4 h-4 mt-0.5">
                            <span class="text-text-secondary">support@rfidattendance.edu</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <img src="https://img.rocket.new/generatedImages/rocket_gen_img_13361d1fe-1768398985241.png" alt="Phone icon" class="w-4 h-4 mt-0.5">
                            <span class="text-text-secondary">+1 (555) 123-4567</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-border mt-8 pt-8 text-center">
                <p class="text-sm text-text-secondary">© 2026 RFID Attendance System. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
<script src="../js/rfid_system_settings.js"></script>

<script id="dhws-dataInjector" src="../public/dhws-data-injector.js"></script>
</body>
</html>