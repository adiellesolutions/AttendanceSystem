<?php
session_start();

// Protect page: only admin can access
//if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
 // header("Location: login.html");
 // exit;
//}

$displayName = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Admin';
$displayRole = $_SESSION['role'] ?? 'admin';
$photoPath   = $_SESSION['profile_photo_path'] ?? null;

// photoPath is stored like: uploads/profile/xxx.jpg
// Current file is: frontend/pages/user_management.php
// So to reach project root: ../../
$photoUrl = $photoPath ? "../../" . ltrim($photoPath, "/") : "https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop";
?>


<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="User Management - RFID Attendance System comprehensive user account administration">
    <title>User Management - RFID Attendance System</title>
    <link rel="stylesheet" href="../css/main.css">
  
   <!-- <script type="module" async src="https://static.rocket.new/rocket-web.js?_cfg=https%3A%2F%2Frfidatten9359back.builtwithrocket.new&_be=https%3A%2F%2Fapplication.rocket.new&_v=0.1.12"></script>
 <script type="module" defer src="https://static.rocket.new/rocket-shot.js?v=0.0.2"></script>-->
  
<style>
 .modal-overlay { 
    display: none; 
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    align-items: center;
    justify-content: center;
    padding: 16px;
    z-index: 9999;
  }
  .modal-overlay:not(.hidden) { display: flex; }

  /* ✅ Scrollable modal */
  .modal-content {
    width: 100%;
    max-width: 48rem;     /* ~ max-w-3xl */
    max-height: 90vh;     /* limit modal height */
    overflow: hidden;     /* prevent double scroll */
    background: white;    /* or your surface color */
    border-radius: 16px;  /* optional */
  }

  .modal-scroll {
    max-height: 90vh;     /* same as modal */
    overflow-y: auto;     /* ✅ enables scrolling */
  }

</style>

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
                    <a href="admin_dashboard.html" class="px-4 py-2 rounded-xl text-text-secondary hover:bg-primary-50 hover:text-primary font-medium transition-smooth">
                        Dashboard
                    </a>
                    <a href="user_management.html" class="px-4 py-2 rounded-xl bg-primary-50 text-primary font-medium transition-smooth">
                        Users
                    </a>
                    <a href="rfid_system_settings.html" class="px-4 py-2 rounded-xl text-text-secondary hover:bg-primary-50 hover:text-primary font-medium transition-smooth">
                        Settings
                    </a>
                </div>

                <!-- User Profile -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-3">
                    <img src="<?= htmlspecialchars($photoUrl) ?>"
                        alt="Admin profile photo"
                        class="w-10 h-10 rounded-full object-cover border-2 border-primary"
                        onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop'; this.onerror=null;">

                    <div class="hidden sm:block">
                    <p class="text-sm font-semibold text-text-primary"><?= htmlspecialchars($displayName) ?></p>
                    <p class="text-xs text-text-secondary"><?= htmlspecialchars(ucfirst($displayRole)) ?></p>
                    </div>

                    </div>
                    
                    <!-- Logout Button -->
                    <button onclick="logout()"
                        class="btn btn-outline h-10 px-4 text-sm flex items-center space-x-2
                            hover:bg-error hover:text-white hover:border-error transition-smooth"
                        aria-label="Logout"
                        title="Logout from system">

                        <!-- Logout Icon -->
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
                <a href="user_management.html" class="block px-4 py-3 rounded-xl bg-primary-50 text-primary font-medium">
                    Users
                </a>
                <a href="rfid_system_settings.html" class="block px-4 py-3 rounded-xl text-text-secondary hover:bg-primary-50 hover:text-primary font-medium transition-smooth">
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
                    <h2 class="text-3xl font-heading font-bold text-text-primary mb-2">User Management</h2>
                    <p class="text-text-secondary">Account administration and role-based access control</p>
                </div>
                <button id="add-user-btn" class="btn btn-primary inline-flex items-center space-x-2 w-full sm:w-auto justify-center">
                    <span>Add New User</span>
                </button>
            </div>
        </div>



        <!-- Filter and Search Section -->
        <section id="filter-section" class="card mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Search Input -->
                <div class="lg:col-span-2">
                    <label class="label">Search Users</label>
                    <div class="relative">
                        <input type="text" id="search-input" placeholder="Search by name, email, or ID..." class="input w-full">
                    </div>
                </div>

                <!-- Role Filter -->
                <div>
                    <label class="label">Role</label>
                    <select id="role-filter" class="input w-full">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="teacher">Teacher</option>
                        <option value="student">Student</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="label">Status</label>
                    <select id="status-filter" class="input w-full">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="locked">Locked</option>
                    </select>
                </div>
            </div>

            <!-- Action Toolbar -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 pt-6 border-t border-border">
                <div class="flex items-center space-x-2 text-sm text-text-secondary">
                    <img src="https://img.rocket.new/generatedImages/rocket_gen_img_11f5df0ae-1767626778469.png" alt="Information icon" class="w-4 h-4">
                    <span id="user-count">Showing 142 users</span>
                </div>
            </div>
        </section>

        <!-- User Directory Table - Desktop View -->
        <section id="user-table-section" class="card hidden lg:block overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-12">
                                <input type="checkbox" id="select-all" class="w-5 h-5 rounded border-border text-primary focus:ring-accent cursor-pointer">
                            </th>
                            <th class="cursor-pointer hover:text-primary transition-smooth">
                                <div class="flex items-center space-x-2">
                                    <span>Name</span>
                                </div>
                            </th>
                            <th class="cursor-pointer hover:text-primary transition-smooth">
                                <div class="flex items-center space-x-2">
                                    <span>Email</span>
                                </div>
                            </th>
                            <th class="cursor-pointer hover:text-primary transition-smooth">
                                <div class="flex items-center space-x-2">
                                    <span>Role</span>
                                </div>
                            </th>
                            <th>Status</th>
                            <th class="cursor-pointer hover:text-primary transition-smooth">
                                <div class="flex items-center space-x-2">
                                    <span>Last Login</span>
                                </div>
                            </th>
                            <th>Associated</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="user-table-body">
                       
                    </tbody>
                </table>
            </div>

           <!-- Pagination -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6 pt-6 border-t border-border">
            <p id="pagination-info" class="text-sm text-text-secondary">Showing 0-0 of 0 users</p>

            <div id="pagination-controls" class="flex items-center space-x-2">
                <!-- JS will render buttons here -->
            </div>
            </div>

            </div>
        </section>

        <!-- User Directory Cards - Mobile View -->
        <section id="user-cards-section" class="lg:hidden space-y-4">
            <!-- User Card 1 - Admin -->
            <div class="card">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <input type="checkbox" class="user-checkbox w-5 h-5 rounded border-border text-primary focus:ring-accent cursor-pointer">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_155748a5d-1763296653785.png" 
                             alt="Profile photo of Dr. Sarah Johnson" 
                             class="w-12 h-12 rounded-full object-cover"
                             onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;">
                        <div>
                            <p class="font-semibold text-text-primary">Dr. Sarah Johnson</p>
                            <p class="text-sm text-text-secondary">ID: ADM-2024-001</p>
                        </div>
                    </div>
                    <span class="badge bg-primary-100 text-primary-700">Admin</span>
                </div>

                <div class="space-y-3 mb-4">
                    <div class="flex items-center space-x-2 text-sm">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_1175352d5-1767644430280.png" alt="Email icon" class="w-4 h-4">
                        <span class="text-text-secondary">sarah.johnson@school.edu</span>
                    </div>
                    <div class="flex items-center space-x-2 text-sm">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_175bcfdbd-1764676194744.png" alt="Clock icon" class="w-4 h-4">
                        <span class="text-text-secondary">Last login: 01/18/2026 6:15 AM</span>
                    </div>
                    <div class="flex items-center space-x-2 text-sm">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_1eacfd15a-1768717524572.png" alt="School icon" class="w-4 h-4">
                        <span class="text-text-secondary">All Departments</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="badge badge-success">Active</span>
                    </div>
                </div>

                <div class="flex items-center space-x-2 pt-4 border-t border-border">
                    <button class="btn-outline h-10 flex-1 text-sm inline-flex items-center justify-center space-x-2">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_104f87139-1768530273062.png" alt="Edit icon" class="w-4 h-4">
                        <span>Edit</span>
                    </button>
                    <button class="btn-outline h-10 px-4 text-sm touch-target" aria-label="View activity logs">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_1f7049645-1768717526136.png" alt="History icon" class="w-5 h-5">
                    </button>
                    <button class="btn-outline h-10 px-4 text-sm touch-target" aria-label="Reset password">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_11eb2cbb3-1768717540728.png" alt="Reset password icon" class="w-5 h-5">
                    </button>
                </div>
            </div>

            <!-- User Card 2 - Teacher -->
            <div class="card">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <input type="checkbox" class="user-checkbox w-5 h-5 rounded border-border text-primary focus:ring-accent cursor-pointer">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_12dac66d7-1763294317993.png" 
                             alt="Profile photo of Prof. Michael Chen" 
                             class="w-12 h-12 rounded-full object-cover"
                             onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;">
                        <div>
                            <p class="font-semibold text-text-primary">Prof. Michael Chen</p>
                            <p class="text-sm text-text-secondary">ID: TCH-2024-042</p>
                        </div>
                    </div>
                    <span class="badge bg-secondary-100 text-secondary-700">Teacher</span>
                </div>

                <div class="space-y-3 mb-4">
                    <div class="flex items-center space-x-2 text-sm">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_1175352d5-1767644430280.png" alt="Email icon" class="w-4 h-4">
                        <span class="text-text-secondary">michael.chen@school.edu</span>
                    </div>
                    <div class="flex items-center space-x-2 text-sm">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_175bcfdbd-1764676194744.png" alt="Clock icon" class="w-4 h-4">
                        <span class="text-text-secondary">Last login: 01/18/2026 5:42 AM</span>
                    </div>
                    <div class="flex items-center space-x-2 text-sm">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_1eacfd15a-1768717524572.png" alt="School icon" class="w-4 h-4">
                        <span class="text-text-secondary">Grade 12</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="badge badge-success">Active</span>
                    </div>
                </div>

                <div class="flex items-center space-x-2 pt-4 border-t border-border">
                    <button class="btn-outline h-10 flex-1 text-sm inline-flex items-center justify-center space-x-2">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_104f87139-1768530273062.png" alt="Edit icon" class="w-4 h-4">
                        <span>Edit</span>
                    </button>
                    <button class="btn-outline h-10 px-4 text-sm touch-target" aria-label="View activity logs">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_1f7049645-1768717526136.png" alt="History icon" class="w-5 h-5">
                    </button>
                    <button class="btn-outline h-10 px-4 text-sm touch-target" aria-label="Reset password">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_11eb2cbb3-1768717540728.png" alt="Reset password icon" class="w-5 h-5">
                    </button>
                </div>
            </div>

            <!-- User Card 3 - Student -->
            <div class="card">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <input type="checkbox" class="user-checkbox w-5 h-5 rounded border-border text-primary focus:ring-accent cursor-pointer">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_10458bf2c-1763293523905.png" 
                             alt="Profile photo of Emma Rodriguez" 
                             class="w-12 h-12 rounded-full object-cover"
                             onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;">
                        <div>
                            <p class="font-semibold text-text-primary">Emma Rodriguez</p>
                            <p class="text-sm text-text-secondary">ID: STU-2024-1156</p>
                        </div>
                    </div>
                    <span class="badge bg-success-100 text-success-700">Student</span>
                </div>

                <div class="space-y-3 mb-4">
                    <div class="flex items-center space-x-2 text-sm">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_1175352d5-1767644430280.png" alt="Email icon" class="w-4 h-4">
                        <span class="text-text-secondary">emma.rodriguez@student.edu</span>
                    </div>
                    <div class="flex items-center space-x-2 text-sm">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_175bcfdbd-1764676194744.png" alt="Clock icon" class="w-4 h-4">
                        <span class="text-text-secondary">Last login: 01/18/2026 6:12 AM</span>
                    </div>
                    <div class="flex items-center space-x-2 text-sm">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_1eacfd15a-1768717524572.png" alt="School icon" class="w-4 h-4">
                        <span class="text-text-secondary">Grade 12</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="badge badge-success">Active</span>
                    </div>
                </div>

                <div class="flex items-center space-x-2 pt-4 border-t border-border">
                    <button class="btn-outline h-10 flex-1 text-sm inline-flex items-center justify-center space-x-2">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_104f87139-1768530273062.png" alt="Edit icon" class="w-4 h-4">
                        <span>Edit</span>
                    </button>
                    <button class="btn-outline h-10 px-4 text-sm touch-target" aria-label="View activity logs">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_1f7049645-1768717526136.png" alt="History icon" class="w-5 h-5">
                    </button>
                    <button class="btn-outline h-10 px-4 text-sm touch-target" aria-label="Reset password">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_11eb2cbb3-1768717540728.png" alt="Reset password icon" class="w-5 h-5">
                    </button>
                </div>
            </div>

            <!-- Pagination for Mobile -->
            <div class="flex flex-col items-center gap-4 mt-6">
                <p class="text-sm text-text-secondary">Showing 1-3 of 142 users</p>
                <div class="flex items-center space-x-2">
                    <button class="btn-outline h-10 px-4 text-sm" disabled>
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_1af02fb73-1768427135094.png" alt="Previous page" class="w-5 h-5">
                    </button>
                    <button class="h-10 px-4 rounded-xl bg-primary text-white text-sm font-medium">1</button>
                    <button class="btn-outline h-10 px-4 text-sm">2</button>
                    <button class="btn-outline h-10 px-4 text-sm">3</button>
                    <button class="btn-outline h-10 px-4 text-sm">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_125f820bf-1766427641023.png" alt="Next page" class="w-5 h-5">
                    </button>
                </div>
            </div>
        </section>
    </main>

 <!-- Add User Modal (DB-aligned) -->
 <div id="add-user-modal" class="modal-overlay hidden">
  <div class="modal-content">
    <div class="p-8 modal-scroll">

      <!-- Modal Header -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-2xl font-heading font-bold text-text-primary mb-1">Add New User</h3>
          <p class="text-sm text-text-secondary">Create a new account with role-based permissions</p>
        </div>

        <button
          id="close-modal-btn"
          class="p-2 rounded-xl hover:bg-primary-50 transition-smooth touch-target"
          aria-label="Close modal"
          type="button">
          ✕
        </button>
      </div>

      <!-- Modal Form -->
      <form id="add-user-form" class="space-y-6" novalidate>

        <!-- Account (users table) -->
        <div>
          <h4 class="text-lg font-heading font-semibold text-text-primary mb-4 pb-2 border-b border-border">
            Account
          </h4>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="label" for="um_username">Username *</label>
              <input
                id="um_username"
                name="username"
                type="text"
                required
                class="input w-full"
                placeholder="email or school ID"
                autocomplete="username">
              <p class="text-xs text-text-secondary mt-1">Must be unique (stored in users.username)</p>
            </div>

            <div>
              <label class="label" for="um_role">User Role *</label>
              <select id="um_role" name="role" required class="input w-full">
                <option value="">Select role</option>
                <option value="admin">Admin</option>
                <option value="teacher">Teacher</option>
                <option value="student">Student</option>
              </select>
            </div>

            <div>
              <label class="label" for="um_status">Account Status *</label>
              <select id="um_status" name="status" required class="input w-full">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            <div class="md:col-span-2">
              <label class="label" for="um_password">Temporary Password *</label>
              <input
                id="um_password"
                name="password"
                type="password"
                required
                class="input w-full"
                placeholder="Enter temporary password"
                autocomplete="new-password">
            </div>
          </div>
        </div>

        <div class="md:col-span-2">
        <label class="label" for="um_profile_photo">Profile Picture (optional)</label>
        <input
            id="um_profile_photo"
            name="profile_photo"
            type="file"
            accept="image/*"
          >
        <p class="text-xs text-text-secondary mt-1">JPG/PNG/WEBP, max 2MB.</p>
        </div>


        <!-- Role specific -->
        <div id="role-specific-section" class="hidden">
          <h4 class="text-lg font-heading font-semibold text-text-primary mb-4 pb-2 border-b border-border">
            Role-Specific
          </h4>

          <!-- STUDENT (students + guardians + rfid_cards) -->
          <div id="student-section" class="hidden space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="label" for="um_student_id">Student ID *</label>
                <input
                  id="um_student_id"
                  name="student_id"
                  type="text"
                  class="input w-full"
                  placeholder="STU-2024-1156">
              </div>

              <div>
                <label class="label" for="um_student_email">Student Email (optional)</label>
                <input
                  id="um_student_email"
                  name="student_email"
                  type="email"
                  class="input w-full"
                  placeholder="student@email.com"
                  autocomplete="email">
              </div>

              <div class="md:col-span-2">
                <label class="label" for="um_student_fullname">Student Full Name *</label>
                <input
                  id="um_student_fullname"
                  name="student_full_name"
                  type="text"
                  class="input w-full"
                  placeholder="Student full name">
              </div>
            </div>

            <div class="pt-4 border-t border-border"></div>

            <div>
              <h5 class="font-semibold text-text-primary mb-3">Guardian Information</h5>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                  <label class="label" for="um_guardian_fullname">Guardian Full Name *</label>
                  <input
                    id="um_guardian_fullname"
                    name="guardian_full_name"
                    type="text"
                    class="input w-full"
                    placeholder="Guardian full name">
                </div>

                <div>
                  <label class="label" for="um_guardian_email">Guardian Email *</label>
                  <input
                    id="um_guardian_email"
                    name="guardian_email"
                    type="email"
                    class="input w-full"
                    placeholder="guardian@email.com"
                    autocomplete="email">
                </div>

                <div>
                  <label class="label" for="um_guardian_contact">Guardian Contact No. (optional)</label>
                  <input
                    id="um_guardian_contact"
                    name="guardian_contact_no"
                    type="text"
                    class="input w-full"
                    placeholder="09xxxxxxxxx">
                </div>
              </div>
            </div>

            <div class="pt-4 border-t border-border"></div>

            <div>
              <h5 class="font-semibold text-text-primary mb-3">RFID Card</h5>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="label" for="um_card_uid">RFID UID *</label>
                  <input
                    id="um_card_uid"
                    name="card_uid"
                    type="text"
                    class="input w-full"
                    placeholder="CARD_UID_12345">
                </div>

                <div>
                  <label class="label" for="um_card_status">Card Status *</label>
                  <select id="um_card_status" name="card_status" class="input w-full">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="lost">Lost</option>
                  </select>
                </div>
              </div>
            </div>

          </div>

          <!-- TEACHER (teachers) -->
          <div id="teacher-section" class="hidden space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="label" for="um_teacher_id">Teacher ID *</label>
                <input
                  id="um_teacher_id"
                  name="teacher_id"
                  type="text"
                  class="input w-full"
                  placeholder="TCH-2024-042">
              </div>

              <div>
                <label class="label" for="um_teacher_email">Teacher Email (optional)</label>
                <input
                  id="um_teacher_email"
                  name="teacher_email"
                  type="email"
                  class="input w-full"
                  placeholder="teacher@email.com"
                  autocomplete="email">
              </div>

              <div class="md:col-span-2">
                <label class="label" for="um_teacher_fullname">Teacher Full Name *</label>
                <input
                  id="um_teacher_fullname"
                  name="teacher_full_name"
                  type="text"
                  class="input w-full"
                  placeholder="Teacher full name">
              </div>
            </div>
          </div>

        </div>

        <!-- Message -->
        <div id="um_msg" class="text-sm"></div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-6 border-t border-border">
          <button type="button" id="cancel-btn" class="btn-outline h-12 px-6">Cancel</button>
          <button type="submit" class="btn btn-primary h-12 px-6">Create User Account</button>
        </div>

      </form>
    </div>
  </div>
</div>





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

 
<script src="../../js/user_management.js"></script>
<script src="/AttendanceSystem/js/user_management_list.js"></script>

</body>
</html>