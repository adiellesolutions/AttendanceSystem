<?php
session_start();
require_once "../../backend/db/db.php";

// block non-teachers
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// get teacher info
$sql = "
    SELECT 
        t.full_name,
        u.profile_photo
    FROM teachers t
    JOIN users u ON u.id = t.user_id
    WHERE u.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();

$displayName = $teacher['full_name'] ?? "Teacher";

$photoUrl = !empty($teacher['profile_photo'])
    ? "../../uploads/" . $teacher['profile_photo']
    : "https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop";
?>


<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Teacher Dashboard - RFID Attendance System for classroom monitoring and student management">
    <title>Teacher Dashboard - RFID Attendance System</title>
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
                        <p class="text-xs text-text-secondary font-caption">Teacher Portal</p>
                    </div>
                </div>


                <!-- User Profile -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-3">
                       <img src="<?= htmlspecialchars($photoUrl) ?>"
     alt="Teacher profile photo"
     class="w-10 h-10 rounded-full object-cover border-2 border-primary"
     onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop'; this.onerror=null;">

<div class="hidden sm:block">
    <p class="text-sm font-semibold text-text-primary">
        <?= htmlspecialchars($displayName) ?>
    </p>
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
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header with Controls -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-3xl font-heading font-bold text-text-primary mb-2">Class Attendance Monitor</h2>
                    <p class="text-text-secondary">Real-time classroom attendance tracking and management</p>
                </div>
                
            </div>




                <!-- Real-time Scan Notifications -->
        <section id="realtime-updates" class="mb-8">
        <div class="card">
            <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-heading font-semibold text-text-primary">Recent RFID Scans</h3>
            <span class="flex items-center space-x-2 text-sm text-success">
                <span class="w-2 h-2 rounded-full bg-success animate-pulse"></span>
                <span>Live Updates</span>
            </span>
            </div>

            <!-- ✅ Dynamic list goes here -->
            <div id="recent-scans-list" class="space-y-3">
            <div class="text-sm text-text-secondary">Loading recent scans...</div>
            </div>
        </div>
        </section>

        <!-- Load JS -->
        <script src="../../assets/js/recent_scans.js"></script>


     <!-- Class Selector and Date Picker -->
<div class="flex justify-end mb-6">
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 w-full md:w-2/3 lg:w-1/2">
    <div>
      <label class="label">Select Date</label>
      <input type="date" id="date-picker" class="input text-sm">
    </div>

    <div>
      <label class="label">Search Student</label>
      <div class="relative">
        <input type="text"
          id="student-search"
          placeholder="Name or Student ID"
          class="input text-sm pl-10">
      </div>
    </div>
  </div>
</div>


        <!-- Main Attendance Table -->
        <section id="attendance-table" class="card">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-heading font-semibold text-text-primary">Today's Attendance Record</h3>
                <div class="flex items-center space-x-3">
                    <button class="text-sm text-primary hover:text-primary-600 font-medium transition-smooth flex items-center space-x-1">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_11d73560a-1766499825938.png" alt="Sort icon" class="w-4 h-4">
                        <span>Sort</span>
                    </button>
                </div>
            </div>

            <!-- Desktop Table View -->
<div class="hidden lg:block overflow-x-auto">
  <table class="table">
    <thead>
      <tr>
        <th class="w-12">
        </th>
        <th>Student Name</th>
        <th>Student ID</th>
        <th>Time In</th>
        <th>Time Out</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody id="today-attendance-tbody">
      <tr>
        <td colspan="6" class="text-center text-text-secondary">Loading...</td>
      </tr>
    </tbody>
  </table>
</div>

<!-- Mobile Card View -->
<div id="today-attendance-mobile" class="lg:hidden space-y-4">
  <div class="text-sm text-text-secondary">Loading...</div>
</div>


            <!-- Pagination -->
<div class="flex items-center justify-between mt-6 pt-6 border-t border-border">
  <p id="attendance-pagination-info" class="text-sm text-text-secondary">
    Loading...
  </p>

  <div id="attendance-pagination" class="flex items-center space-x-2">
    <!-- buttons injected by JS -->
  </div>
</div>

        </section>
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
                        <li><a href="javascript:void(0)" class="text-text-secondary hover:text-primary transition-smooth">Help Center</a></li>
                        <li><a href="javascript:void(0)" class="text-text-secondary hover:text-primary transition-smooth">Privacy Policy</a></li>
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
    <script src="../js/recent_scans.js"></script>
    <script src="../js/today_attendance.js"></script>

<script id="dhws-dataInjector" src="../public/dhws-data-injector.js"></script>
</body>
</html>