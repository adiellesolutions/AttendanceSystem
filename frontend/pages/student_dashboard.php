
<?php
session_start();
require_once "../../backend/db/db.php";


// Block access if not logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}


$sql = "
    SELECT s.full_name, s.student_id
    FROM students s
    WHERE s.user_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// initials (AM, JD, etc.)
$initials = "";
if ($student) {
    $names = explode(" ", trim($student['full_name']));
    foreach ($names as $n) {
        $initials .= strtoupper($n[0]);
        if (strlen($initials) === 2) break;
    }
}
?>


<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Student Dashboard - Track attendance records and daily check-ins">
    <title>Student Dashboard - RFID Attendance</title>
    <link rel="stylesheet" href="../css/main.css">
    <!-- External scripts -->
    <script type="module" async src="https://static.rocket.new/rocket-web.js?_cfg=https%3A%2F%2Frfidatten9359back.builtwithrocket.new&_be=https%3A%2F%2Fapplication.rocket.new&_v=0.1.12"></script>
    <script type="module" defer src="https://static.rocket.new/rocket-shot.js?v=0.0.2"></script>
</head>
<body class="bg-background">
    <!-- Navigation Header -->
    <header class="bg-surface border-b border-border sticky top-0 z-nav shadow-card">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <svg class="w-10 h-10" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="40" height="40" rx="8" fill="#1E3A5F"/>
                        <path d="M12 20L18 26L28 14" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="20" cy="20" r="12" stroke="#E67E22" stroke-width="2" stroke-dasharray="4 4"/>
                    </svg>
                    <div>
                        <h1 class="text-xl font-heading font-bold text-primary">RFID Attendance</h1>
                        <p class="text-xs text-text-secondary font-caption">Student Portal</p>
                    </div>
                </div>

                <!-- User Profile -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center border-2 border-primary">
    <span class="text-primary font-bold">
        <?= htmlspecialchars($initials) ?>
    </span>
</div>

<div class="hidden sm:block">
    <p class="font-semibold text-text-primary">
        <?= htmlspecialchars($student['full_name']) ?>
    </p>
    <p class="text-xs text-text-secondary">
        Student ID: <?= htmlspecialchars($student['student_id']) ?>
    </p>
</div>

                    </div>

                                        <!-- Logout Button -->
<button onclick="window.location.href='../../backend/api/logout.php'"
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
            </div>

        </nav>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Main Attendance Table -->
        <section id="attendance-table" class="card">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-heading font-semibold text-text-primary">Attendance Record</h3>
                <div class="flex items-center space-x-3">
                    <button class="text-sm text-primary hover:text-primary-600 font-medium transition-smooth flex items-center space-x-1">
                       
                    </button>
                </div>
            </div>

            <div class="hidden lg:block overflow-x-auto">
    <table class="table">
        <thead>
            <tr>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="attendanceTableBody">
                    <!-- JS injects rows here -->
                </tbody>
            </table>
        </div>

           
            <!-- Mobile Card View -->
<div class="lg:hidden space-y-4" id="attendanceMobileCards"></div>



           <!-- Pagination -->
<<div class="flex items-center justify-between mt-6 pt-6 border-t border-border">
    <div class="flex items-center space-x-2" id="paginationControls">

        <button id="prevPage"
            class="btn-outline h-10 w-10 flex items-center justify-center"
            aria-label="Previous page">
            ‹
        </button>

        <div id="pageNumbers" class="flex space-x-2"></div>

        <button id="nextPage"
            class="btn-outline h-10 w-10 flex items-center justify-center"
            aria-label="Next page">
            ›
        </button>

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
                    <h4 class="font-heading font-semibold text-text-primary mb-4">Need Help?</h4>
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
    <script src="../js/student_dashboard.js"></script>

    <script id="dhws-dataInjector" src="../public/dhws-data-injector.js"></script>
</body>
</html>