<?php
session_start();
require_once "../../backend/db/db.php";

// block non-admin users
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// get admin info from users table
$sql = "
    SELECT username, profile_photo
    FROM users
    WHERE id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

// fallback image if none
$profilePhoto = $admin['profile_photo']
    ? "../../uploads/" . $admin['profile_photo']
    : "https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop";
?>



<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin Dashboard - RFID Attendance System comprehensive oversight and management">
    <title>Admin Dashboard - RFID Attendance System</title>
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
                        <p class="text-xs text-text-secondary font-caption">Admin Portal</p>
                    </div>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="admin_dashboard.php" class="px-4 py-2 rounded-xl bg-primary-50 text-primary font-medium transition-smooth">
                        Dashboard
                    </a>
                    <a href="user_management.php" class="px-4 py-2 rounded-xl text-text-secondary hover:bg-primary-50 hover:text-primary font-medium transition-smooth">
                        Users
                    </a>
                    <a href="rfid_system_settings.php" class="px-4 py-2 rounded-xl text-text-secondary hover:bg-primary-50 hover:text-primary font-medium transition-smooth">
                        Settings
                    </a>
                </div>

                <div class="flex items-center space-x-3">
    <img src="<?= htmlspecialchars($profilePhoto) ?>"
        alt="Admin profile photo"
        class="w-10 h-10 rounded-full object-cover border-2 border-primary"
        onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop'; this.onerror=null;">

    <div class="hidden sm:block">
        <p class="text-sm font-semibold text-text-primary">
            <?= htmlspecialchars($admin['username']) ?>
        </p>
        <p class="text-xs text-text-secondary">Administrator</p>
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
                <a href="admin_dashboard.html" class="block px-4 py-3 rounded-xl bg-primary-50 text-primary font-medium">
                    Dashboard
                </a>
                <a href="user_management.php" class="block px-4 py-3 rounded-xl text-text-secondary hover:bg-primary-50 hover:text-primary font-medium transition-smooth">
                    Users
                </a>
                <a href="rfid_system_settings.php" class="block px-4 py-3 rounded-xl text-text-secondary hover:bg-primary-50 hover:text-primary font-medium transition-smooth">
                    Settings
                </a>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">


        <!-- Summary Metrics Panel -->
        <section id="metrics-panel" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
           <!-- Total Students Card -->
                <!-- Total Students Card -->
        <div class="card hover:shadow-card-hover">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-caption text-text-secondary mb-2">Total Students</p>
                    <h3
                        id="total-students-count"
                        class="text-3xl font-heading font-bold text-primary mb-1">
                        —
                    </h3>
                </div>
            </div>
        </div>


        </section>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Real-time Feed & Quick Actions -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Real-time Attendance Feed -->
                <section id="attendance-feed" class="card">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-heading font-semibold text-text-primary mb-1">Real-time Attendance Feed</h3>
                            <p class="text-sm text-text-secondary">Recent RFID scan events</p>
                        </div>
                        <button class="btn-outline h-10 px-4 text-sm" onclick="loadAttendanceFeed()">
                            Refresh
                        </button>

                    </div>
 
                    <!-- Feed Items -->
                    <div id="attendance-feed-body" class="space-y-4">
                        <!-- populated by JS -->
                    </div>

                    <div class="mt-6 text-center">
                        <a href="#" class="text-primary font-medium hover:text-primary-600 transition-smooth inline-flex items-center space-x-2">
                            <span>View Complete Attendance Log</span>
                        </a>
                    </div>
                </section>
            </div>

            <!-- Right Column: Attendance Trends & Filters -->
            <div class="space-y-8">
                <!-- Attendance Filter Panel -->
                <section id="filter-panel" class="card">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-heading font-semibold text-text-primary">Filter Attendance</h3>
                        <button class="text-sm text-primary hover:text-primary-600 font-medium transition-smooth flex items-center space-x-1">
                        </button>
                    </div>

                    <div class="space-y-4">
                        <!-- Date Range Filter -->
                        <div>
                            <label class="label">Date Range</label>
                            <div class="grid grid-cols-2 gap-3">
                                <input type="date" id="filter-from" class="input text-sm">
                                <input type="date" id="filter-to" class="input text-sm">
                            </div>
                        </div>

                        <!-- Student Search -->
                        <div>
                            <label class="label">Search Student</label>
                            <div class="relative">
                                <input
                                    type="text"
                                    id="filter-student"
                                    placeholder="Enter student name or ID"
                                    class="input text-sm">
                            </div>
                        </div>

                        <!-- Apply Filter Button -->
                        <button id="apply-filters" class="btn btn-primary w-full">
                            Apply Filters
                        </button>

                        <!-- Reset Button -->
                        <button
                        type="button"
                        id="reset-filters"
                        class="text-sm text-primary hover:text-primary-600 font-medium transition-smooth flex items-center space-x-1">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_18c5374aa-1768717524587.png"
                            alt="Reset filter icon"
                            class="w-4 h-4">
                        <span>Reset</span>
                    </button>

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
<script src="../js/admin_dashboard.js"></script>

<script id="dhws-dataInjector" src="../public/dhws-data-injector.js"></script>
</body>
</html>