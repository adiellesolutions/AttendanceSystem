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
                
                <div class="space-y-3">
                    <!-- Recent Scan 1 -->
                    <div class="flex items-center justify-between p-3 rounded-xl bg-success-50 border border-success-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-success flex items-center justify-center">
                                <img src="https://img.rocket.new/generatedImages/rocket_gen_img_10c798953-1767645351700.png" alt="Entry icon" class="w-4 h-4">
                            </div>
                            <div>
                                <p class="font-semibold text-text-primary text-sm">Emma Rodriguez</p>
                                <p class="text-xs text-text-secondary">STU-2024-1156</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-success text-xs">Entry</span>
                            <p class="text-xs text-text-secondary mt-1">6:17 AM</p>
                        </div>
                    </div>

                    <!-- Recent Scan 2 -->
                    <div class="flex items-center justify-between p-3 rounded-xl bg-warning-50 border border-warning-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-warning flex items-center justify-center">
                                <img src="https://img.rocket.new/generatedImages/rocket_gen_img_1b5bc9f42-1766986569025.png" alt="Late icon" class="w-4 h-4">
                            </div>
                            <div>
                                <p class="font-semibold text-text-primary text-sm">James Wilson</p>
                                <p class="text-xs text-text-secondary">STU-2024-0745</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-warning text-xs">Late Entry</span>
                            <p class="text-xs text-text-secondary mt-1">6:15 AM</p>
                        </div>
                    </div>

                    <!-- Recent Scan 3 -->
                    <div class="flex items-center justify-between p-3 rounded-xl bg-primary-50 border border-primary-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-error flex items-center justify-center">
                                <img src="https://img.rocket.new/generatedImages/rocket_gen_img_136d1b5bb-1768717525448.png" alt="Exit icon" class="w-4 h-4">
                            </div>
                            <div>
                                <p class="font-semibold text-text-primary text-sm">Aisha Patel</p>
                                <p class="text-xs text-text-secondary">STU-2024-1423</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-error text-xs">Exit</span>
                            <p class="text-xs text-text-secondary mt-1">6:10 AM</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Class Selector and Date Picker -->
        <div class="flex justify-end mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 w-full md:w-2/3 lg:w-1/2">
                <div>
                    <label class="label">Select Date</label>
                    <input type="date" id="date-picker" class="input text-sm" value="2026-01-18">
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
                                <input type="checkbox" class="w-4 h-4 rounded border-border text-primary focus:ring-accent" aria-label="Select all students">
                            </th>
                            <th>Student Name</th>
                            <th>Student ID</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Student Row 1 - Present -->
                        <tr>
                            <td>
                                <input type="checkbox" class="w-4 h-4 rounded border-border text-primary focus:ring-accent" aria-label="Select Emma Rodriguez">
                            </td>
                            <td>
                                <div class="flex items-center space-x-3">
                                    <img src="https://img.rocket.new/generatedImages/rocket_gen_img_13be3c843-1763299967170.png" 
                                         alt="Emma Rodriguez student profile photo" 
                                         class="w-10 h-10 rounded-full object-cover"
                                         onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;">
                                    <span class="font-semibold text-text-primary">Emma Rodriguez</span>
                                </div>
                            </td>
                            <td class="data-text text-text-secondary">STU-2024-1156</td>
                            <td class="data-text text-text-primary">08:15 AM</td>
                            <td class="data-text text-text-secondary">—</td>
                            <td><span class="badge badge-success">Present</span></td>
                        </tr>

                        <!-- Student Row 2 - Present -->
                        <tr>
                            <td>
                                <input type="checkbox" class="w-4 h-4 rounded border-border text-primary focus:ring-accent" aria-label="Select Michael Chen">
                            </td>
                            <td>
                                <div class="flex items-center space-x-3">
                                    <img src="https://img.rocket.new/generatedImages/rocket_gen_img_16e9d8251-1763294843980.png" 
                                         alt="Michael Chen student profile photo" 
                                         class="w-10 h-10 rounded-full object-cover"
                                         onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;">
                                    <span class="font-semibold text-text-primary">Michael Chen</span>
                                </div>
                            </td>
                            <td class="data-text text-text-secondary">STU-2024-0892</td>
                            <td class="data-text text-text-primary">08:12 AM</td>
                            <td class="data-text text-text-secondary">—</td>
                            <td><span class="badge badge-success">Present</span></td>
                        </tr>

                        <!-- Student Row 3 - Late -->
                        <tr>
                            <td>
                                <input type="checkbox" class="w-4 h-4 rounded border-border text-primary focus:ring-accent" aria-label="Select James Wilson">
                            </td>
                            <td>
                                <div class="flex items-center space-x-3">
                                    <img src="https://img.rocket.new/generatedImages/rocket_gen_img_16e9d8251-1763294843980.png" 
                                         alt="James Wilson student profile photo" 
                                         class="w-10 h-10 rounded-full object-cover"
                                         onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;">
                                    <span class="font-semibold text-text-primary">James Wilson</span>
                                </div>
                            </td>
                            <td class="data-text text-text-secondary">STU-2024-0745</td>
                            <td class="data-text text-warning">08:35 AM</td>
                            <td class="data-text text-text-secondary">—</td>
                            <td><span class="badge badge-warning">Late</span></td>
                        </tr>

                        <!-- Student Row 4 - Present -->
                        <tr>
                            <td>
                                <input type="checkbox" class="w-4 h-4 rounded border-border text-primary focus:ring-accent" aria-label="Select Aisha Patel">
                            </td>
                            <td>
                                <div class="flex items-center space-x-3">
                                    <img src="https://img.rocket.new/generatedImages/rocket_gen_img_13be3c843-1763299967170.png" 
                                         alt="Aisha Patel student profile photo" 
                                         class="w-10 h-10 rounded-full object-cover"
                                         onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;">
                                    <span class="font-semibold text-text-primary">Aisha Patel</span>
                                </div>
                            </td>
                            <td class="data-text text-text-secondary">STU-2024-1423</td>
                            <td class="data-text text-text-primary">08:10 AM</td>
                            <td class="data-text text-text-secondary">—</td>
                            <td><span class="badge badge-success">Present</span></td>
                        </tr>

                        <!-- Student Row 5 - Absent -->
                        <tr>
                            <td>
                                <input type="checkbox" class="w-4 h-4 rounded border-border text-primary focus:ring-accent" aria-label="Select Sofia Martinez">
                            </td>
                            <td>
                                <div class="flex items-center space-x-3">
                                    <img src="https://img.rocket.new/generatedImages/rocket_gen_img_1c40f9683-1763301361423.png" 
                                         alt="Sofia Martinez student profile photo" 
                                         class="w-10 h-10 rounded-full object-cover"
                                         onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;">
                                    <span class="font-semibold text-text-primary">Sofia Martinez</span>
                                </div>
                            </td>
                            <td class="data-text text-text-secondary">STU-2024-0634</td>
                            <td class="data-text text-text-secondary">—</td>
                            <td class="data-text text-text-secondary">—</td>
                            <td><span class="badge badge-error">Absent</span></td>
                        </tr>

                        <!-- Student Row 6 - Present -->
                        <tr>
                            <td>
                                <input type="checkbox" class="w-4 h-4 rounded border-border text-primary focus:ring-accent" aria-label="Select David Kim">
                            </td>
                            <td>
                                <div class="flex items-center space-x-3">
                                    <img src="https://img.rocket.new/generatedImages/rocket_gen_img_16e9d8251-1763294843980.png" 
                                         alt="David Kim student profile photo" 
                                         class="w-10 h-10 rounded-full object-cover"
                                         onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;">
                                    <span class="font-semibold text-text-primary">David Kim</span>
                                </div>
                            </td>
                            <td class="data-text text-text-secondary">STU-2024-0987</td>
                            <td class="data-text text-text-primary">08:08 AM</td>
                            <td class="data-text text-text-secondary">—</td>
                            <td><span class="badge badge-success">Present</span></td>
                        </tr>

                        <!-- Student Row 7 - Late -->
                        <tr>
                            <td>
                                <input type="checkbox" class="w-4 h-4 rounded border-border text-primary focus:ring-accent" aria-label="Select Olivia Thompson">
                            </td>
                            <td>
                                <div class="flex items-center space-x-3">
                                    <img src="https://img.rocket.new/generatedImages/rocket_gen_img_13eda94e2-1763299483841.png" 
                                         alt="Olivia Thompson student profile photo" 
                                         class="w-10 h-10 rounded-full object-cover"
                                         onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;">
                                    <span class="font-semibold text-text-primary">Olivia Thompson</span>
                                </div>
                            </td>
                            <td class="data-text text-text-secondary">STU-2024-1289</td>
                            <td class="data-text text-warning">08:32 AM</td>
                            <td class="data-text text-text-secondary">—</td>
                            <td><span class="badge badge-warning">Late</span></td>
                        </tr>

                        <!-- Student Row 8 - Present -->
                        <tr>
                            <td>
                                <input type="checkbox" class="w-4 h-4 rounded border-border text-primary focus:ring-accent" aria-label="Select Liam Johnson">
                            </td>
                            <td>
                                <div class="flex items-center space-x-3">
                                    <img src="https://img.rocket.new/generatedImages/rocket_gen_img_1ece17484-1763301747443.png" 
                                         alt="Liam Johnson student profile photo" 
                                         class="w-10 h-10 rounded-full object-cover"
                                         onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;">
                                    <span class="font-semibold text-text-primary">Liam Johnson</span>
                                </div>
                            </td>
                            <td class="data-text text-text-secondary">STU-2024-0521</td>
                            <td class="data-text text-text-primary">08:14 AM</td>
                            <td class="data-text text-text-secondary">—</td>
                            <td><span class="badge badge-success">Present</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="lg:hidden space-y-4">
                <!-- Student Card 1 - Present -->
                <div class="card p-4 hover:shadow-card-hover">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <img src="https://img.rocket.new/generatedImages/rocket_gen_img_13be3c843-1763299967170.png" 
                                 alt="Emma Rodriguez student profile photo" 
                                 class="w-12 h-12 rounded-full object-cover"
                                 onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;">
                            <div>
                                <p class="font-semibold text-text-primary">Emma Rodriguez</p>
                                <p class="text-sm data-text text-text-secondary">STU-2024-1156</p>
                            </div>
                        </div>
                        <span class="badge badge-success">Present</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-text-secondary mb-1">Time In</p>
                            <p class="data-text font-semibold text-text-primary">08:15 AM</p>
                        </div>
                        <div>
                            <p class="text-text-secondary mb-1">Time Out</p>
                            <p class="data-text text-text-secondary">—</p>
                        </div>
                    </div>
                    <button class="btn btn-outline w-full mt-4 h-10 text-sm">
                        View Details
                    </button>
                </div>

                <!-- Student Card 2 - Late -->
                <div class="card p-4 hover:shadow-card-hover">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <img src="https://img.rocket.new/generatedImages/rocket_gen_img_16e9d8251-1763294843980.png" 
                                 alt="James Wilson student profile photo" 
                                 class="w-12 h-12 rounded-full object-cover"
                                 onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;">
                            <div>
                                <p class="font-semibold text-text-primary">James Wilson</p>
                                <p class="text-sm data-text text-text-secondary">STU-2024-0745</p>
                            </div>
                        </div>
                        <span class="badge badge-warning">Late</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-text-secondary mb-1">Time In</p>
                            <p class="data-text font-semibold text-warning">08:35 AM</p>
                        </div>
                        <div>
                            <p class="text-text-secondary mb-1">Time Out</p>
                            <p class="data-text text-text-secondary">—</p>
                        </div>
                    </div>

                </div>

                <!-- Student Card 3 - Absent -->
                <div class="card p-4 hover:shadow-card-hover">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <img src="https://img.rocket.new/generatedImages/rocket_gen_img_1c40f9683-1763301361423.png" 
                                 alt="Sofia Martinez student profile photo" 
                                 class="w-12 h-12 rounded-full object-cover"
                                 onerror="this.src='https://images.unsplash.com/photo-1584824486509-112e4181ff6b?q=80&w=2940&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'; this.onerror=null;">
                            <div>
                                <p class="font-semibold text-text-primary">Sofia Martinez</p>
                                <p class="text-sm data-text text-text-secondary">STU-2024-0634</p>
                            </div>
                        </div>
                        <span class="badge badge-error">Absent</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-text-secondary mb-1">Time In</p>
                            <p class="data-text text-text-secondary">—</p>
                        </div>
                        <div>
                            <p class="text-text-secondary mb-1">Time Out</p>
                            <p class="data-text text-text-secondary">—</p>
                        </div>
                    </div>
                    <button class="btn btn-outline w-full mt-4 h-10 text-sm">
                        View Details
                    </button>
                </div>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between mt-6 pt-6 border-t border-border">
                <p class="text-sm text-text-secondary">Showing 8 of 32 students</p>
                <div class="flex items-center space-x-2">
                    <button class="btn-outline h-10 w-10 flex items-center justify-center" aria-label="Previous page">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_1af02fb73-1768427135094.png" alt="Previous icon" class="w-5 h-5">
                    </button>
                    <button class="btn btn-primary h-10 w-10 flex items-center justify-center">1</button>
                    <button class="btn-outline h-10 w-10 flex items-center justify-center">2</button>
                    <button class="btn-outline h-10 w-10 flex items-center justify-center">3</button>
                    <button class="btn-outline h-10 w-10 flex items-center justify-center">4</button>
                    <button class="btn-outline h-10 w-10 flex items-center justify-center" aria-label="Next page">
                        <img src="https://img.rocket.new/generatedImages/rocket_gen_img_125f820bf-1766427641023.png" alt="Next icon" class="w-5 h-5">
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

<script id="dhws-dataInjector" src="../public/dhws-data-injector.js"></script>
</body>
</html>