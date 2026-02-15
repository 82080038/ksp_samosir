<?php
require_once __DIR__ . '/BaseController.php';

/**
 * LearningController handles e-learning and training modules.
 * Manages courses, enrollments, progress tracking, and certifications.
 */
class LearningController extends BaseController {
    /**
     * Display learning management dashboard.
     */
    public function index() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $stats = $this->getLearningStats();
        $recent_courses = $this->getRecentCourses();
        $popular_courses = $this->getPopularCourses();

        $this->render('learning/index', [
            'stats' => $stats,
            'recent_courses' => $recent_courses,
            'popular_courses' => $popular_courses
        ]);
    }

    /**
     * Display courses management.
     */
    public function courses() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        $page = intval($_GET['page'] ?? 1);
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $total = (fetchRow("SELECT COUNT(*) as count FROM learning_courses") ?? [])['count'] ?? 0;
        $totalPages = ceil($total / $perPage);

        $courses = fetchAll("SELECT lc.*, u.full_name as created_by_name FROM learning_courses lc LEFT JOIN users u ON lc.created_by = u.id ORDER BY lc.created_at DESC LIMIT ? OFFSET ?", [$perPage, $offset], 'ii');

        $this->render('learning/courses', [
            'courses' => $courses,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Create new course.
     */
    public function createCourse() {
        // $this->ensureLoginAndRole(['admin', 'pengurus']); // DISABLED for development

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->collectCourseInput();
            $error = $this->validateCourseInput($data);
            if ($error) {
                flashMessage('error', $error);
                redirect('learning/createCourse');
            }

            runInTransaction(function($conn) use ($data) {
                $stmt = $conn->prepare("INSERT INTO learning_courses (course_code, title, description, category, difficulty_level, duration_hours, max_participants, enrollment_deadline, course_content, prerequisites, learning_objectives, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $created_by = $_SESSION['user']['id'] ?? 1;
                $stmt->bind_param('sssssiissssi', 
                    $data['course_code'], $data['title'], $data['description'], $data['category'], 
                    $data['difficulty_level'], $data['duration_hours'], $data['max_participants'], 
                    $data['enrollment_deadline'], $data['course_content'], $data['prerequisites'], 
                    $data['learning_objectives'], $created_by);
                $stmt->execute();
                $course_id = $stmt->insert_id;
                $stmt->close();

                // Create initial modules if provided
                if (!empty($data['modules'])) {
                    foreach ($data['modules'] as $module) {
                        $stmt2 = $conn->prepare("INSERT INTO course_modules (course_id, module_title, module_description, module_order, duration_minutes) VALUES (?, ?, ?, ?, ?)");
                        $stmt2->bind_param('issii', $course_id, $module['title'], $module['description'], $module['order'], $module['duration']);
                        $stmt2->execute();
                        $stmt2->close();
                    }
                }
            });

            flashMessage('success', 'Kursus berhasil dibuat');
            redirect('learning/courses');
        }

        $this->render('learning/create_course');
    }

    /**
     * Display course details and enrollment.
     */
    public function courseDetail($course_id) {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $course = fetchRow("SELECT lc.*, u.full_name as created_by_name FROM learning_courses lc LEFT JOIN users u ON lc.created_by = u.id WHERE lc.id = ?", [$course_id], 'i');
        if (!$course) {
            flashMessage('error', 'Kursus tidak ditemukan');
            redirect('learning/courses');
        }

        $modules = fetchAll("SELECT * FROM course_modules WHERE course_id = ? ORDER BY module_order", [$course_id], 'i');
        $enrollments = fetchAll("SELECT le.*, u.full_name as student_name FROM learning_enrollments le LEFT JOIN users u ON le.student_id = u.id WHERE le.course_id = ?", [$course_id], 'i');

        $this->render('learning/course_detail', [
            'course' => $course,
            'modules' => $modules,
            'enrollments' => $enrollments
        ]);
    }

    /**
     * Enroll in a course.
     */
    public function enroll($course_id) {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $student_id = $_SESSION['user']['id'] ?? 1;

        // Check if already enrolled
        $existing = fetchRow("SELECT id FROM learning_enrollments WHERE course_id = ? AND student_id = ?", [$course_id, $student_id], 'ii');
        if ($existing) {
            flashMessage('error', 'Anda sudah terdaftar di kursus ini');
            redirect('learning/courseDetail/' . $course_id);
        }

        // Check enrollment deadline
        $course = fetchRow("SELECT enrollment_deadline, max_participants FROM learning_courses WHERE id = ?", [$course_id], 'i');
        if ($course['enrollment_deadline'] && strtotime($course['enrollment_deadline']) < time()) {
            flashMessage('error', 'Batas waktu pendaftaran sudah lewat');
            redirect('learning/courseDetail/' . $course_id);
        }

        // Check max participants
        $current_enrollments = (fetchRow("SELECT COUNT(*) as count FROM learning_enrollments WHERE course_id = ?", [$course_id], 'i') ?? [])['count'] ?? 0;
        if ($course['max_participants'] && $current_enrollments >= $course['max_participants']) {
            flashMessage('error', 'Kursus sudah penuh');
            redirect('learning/courseDetail/' . $course_id);
        }

        runInTransaction(function($conn) use ($course_id, $student_id) {
            $stmt = $conn->prepare("INSERT INTO learning_enrollments (course_id, student_id, enrollment_date, status) VALUES (?, ?, CURDATE(), 'active')");
            $stmt->bind_param('ii', $course_id, $student_id);
            $stmt->execute();
            $enrollment_id = $stmt->insert_id;
            $stmt->close();

            // Initialize progress for each module
            $modules = fetchAll("SELECT id FROM course_modules WHERE course_id = ?", [$course_id], 'i');
            foreach ($modules as $module) {
                $stmt2 = $conn->prepare("INSERT INTO learning_progress (enrollment_id, module_id, status) VALUES (?, ?, 'not_started')");
                $stmt2->bind_param('ii', $enrollment_id, $module['id']);
                $stmt2->execute();
                $stmt2->close();
            }
        });

        flashMessage('success', 'Berhasil mendaftar kursus');
        redirect('learning/myCourses');
    }

    /**
     * Display user's enrolled courses.
     */
    public function myCourses() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $user_id = $_SESSION['user']['id'] ?? 1;

        $enrollments = fetchAll("SELECT le.*, lc.title, lc.course_code, lc.category, lc.duration_hours FROM learning_enrollments le LEFT JOIN learning_courses lc ON le.course_id = lc.id WHERE le.student_id = ? AND le.status = 'active' ORDER BY le.enrollment_date DESC", [$user_id], 'i');

        // Get progress for each enrollment
        foreach ($enrollments as &$enrollment) {
            $progress = $this->getEnrollmentProgress($enrollment['id']);
            $enrollment['progress'] = $progress;
        }

        $this->render('learning/my_courses', [
            'enrollments' => $enrollments
        ]);
    }

    /**
     * Update learning progress.
     */
    public function updateProgress($enrollment_id, $module_id) {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $user_id = $_SESSION['user']['id'] ?? 1;

        // Verify enrollment belongs to user
        $enrollment = fetchRow("SELECT id FROM learning_enrollments WHERE id = ? AND student_id = ?", [$enrollment_id, $user_id], 'ii');
        if (!$enrollment) {
            flashMessage('error', 'Akses tidak diizinkan');
            redirect('learning/myCourses');
        }

        $status = $_POST['status'] ?? 'completed';
        $notes = sanitize($_POST['notes'] ?? '');

        runInTransaction(function($conn) use ($enrollment_id, $module_id, $status, $notes) {
            $stmt = $conn->prepare("UPDATE learning_progress SET status = ?, completion_date = CURDATE(), notes = ? WHERE enrollment_id = ? AND module_id = ?");
            $stmt->bind_param('ssii', $status, $notes, $enrollment_id, $module_id);
            $stmt->execute();
            $stmt->close();

            // Update enrollment progress percentage
            $this->updateEnrollmentProgress($conn, $enrollment_id);
        });

        flashMessage('success', 'Progress berhasil diperbarui');
        redirect('learning/courseLearning/' . $enrollment_id);
    }

    /**
     * Display course learning interface.
     */
    public function courseLearning($enrollment_id) {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $user_id = $_SESSION['user']['id'] ?? 1;

        // Verify enrollment belongs to user
        $enrollment = fetchRow("SELECT le.*, lc.title, lc.description FROM learning_enrollments le LEFT JOIN learning_courses lc ON le.course_id = lc.id WHERE le.id = ? AND le.student_id = ?", [$enrollment_id, $user_id], 'ii');
        if (!$enrollment) {
            flashMessage('error', 'Akses tidak diizinkan');
            redirect('learning/myCourses');
        }

        $modules = fetchAll("SELECT cm.*, lp.status, lp.completion_date, lp.notes FROM course_modules cm LEFT JOIN learning_progress lp ON cm.id = lp.module_id AND lp.enrollment_id = ? WHERE cm.course_id = ? ORDER BY cm.module_order", [$enrollment_id, $enrollment['course_id']], 'ii');

        $progress = $this->getEnrollmentProgress($enrollment_id);

        $this->render('learning/course_learning', [
            'enrollment' => $enrollment,
            'modules' => $modules,
            'progress' => $progress
        ]);
    }

    /**
     * Generate certificates.
     */
    public function generateCertificate($enrollment_id) {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $user_id = $_SESSION['user']['id'] ?? 1;

        // Check if course is completed
        $progress = $this->getEnrollmentProgress($enrollment_id);
        if ($progress['percentage'] < 100) {
            flashMessage('error', 'Kursus belum selesai 100%');
            redirect('learning/courseLearning/' . $enrollment_id);
        }

        // Generate certificate
        runInTransaction(function($conn) use ($enrollment_id, $user_id) {
            $certificate_number = 'CERT-' . date('Y') . '-' . str_pad($enrollment_id, 6, '0', STR_PAD_LEFT);

            $stmt = $conn->prepare("INSERT INTO certificates (enrollment_id, certificate_number, issued_date, issued_by) VALUES (?, ?, CURDATE(), ?) ON DUPLICATE KEY UPDATE issued_date = CURDATE()");
            $stmt->bind_param('isi', $enrollment_id, $certificate_number, $user_id);
            $stmt->execute();
            $stmt->close();
        });

        flashMessage('success', 'Sertifikat berhasil dibuat');
        redirect('learning/myCertificates');
    }

    /**
     * Display user's certificates.
     */
    public function myCertificates() {
        // $this->ensureLoginAndRole(['admin', 'pengurus', 'staff']); // DISABLED for development

        $user_id = $_SESSION['user']['id'] ?? 1;

        $certificates = fetchAll("SELECT c.*, lc.title as course_title, lc.course_code FROM certificates c LEFT JOIN learning_enrollments le ON c.enrollment_id = le.id LEFT JOIN learning_courses lc ON le.course_id = lc.id WHERE le.student_id = ? ORDER BY c.issued_date DESC", [$user_id], 'i');

        $this->render('learning/my_certificates', [
            'certificates' => $certificates
        ]);
    }

    /**
     * Get learning statistics.
     */
    private function getLearningStats() {
        $stats = [];

        $stats['total_courses'] = (fetchRow("SELECT COUNT(*) as total FROM learning_courses") ?? [])['total'] ?? 0;
        $stats['active_courses'] = (fetchRow("SELECT COUNT(*) as total FROM learning_courses WHERE status = 'active'") ?? [])['total'] ?? 0;
        $stats['total_enrollments'] = (fetchRow("SELECT COUNT(*) as total FROM learning_enrollments") ?? [])['total'] ?? 0;
        $stats['total_certificates'] = (fetchRow("SELECT COUNT(*) as total FROM certificates") ?? [])['total'] ?? 0;
        $stats['completion_rate'] = $this->calculateCompletionRate();

        return $stats;
    }

    /**
     * Get recent courses.
     */
    private function getRecentCourses() {
        return fetchAll("SELECT lc.*, u.full_name as created_by_name FROM learning_courses lc LEFT JOIN users u ON lc.created_by = u.id ORDER BY lc.created_at DESC LIMIT 5") ?? [];
    }

    /**
     * Get popular courses.
     */
    private function getPopularCourses() {
        return fetchAll("SELECT lc.title, lc.category, COUNT(le.id) as enrollment_count FROM learning_courses lc LEFT JOIN learning_enrollments le ON lc.id = le.course_id GROUP BY lc.id ORDER BY enrollment_count DESC LIMIT 5") ?? [];
    }

    /**
     * Get enrollment progress.
     */
    private function getEnrollmentProgress($enrollment_id) {
        $total_modules = (fetchRow("SELECT COUNT(*) as total FROM learning_progress WHERE enrollment_id = ?", [$enrollment_id], 'i') ?? [])['total'] ?? 0;
        $completed_modules = (fetchRow("SELECT COUNT(*) as total FROM learning_progress WHERE enrollment_id = ? AND status = 'completed'", [$enrollment_id], 'i') ?? [])['total'] ?? 0;

        $percentage = $total_modules > 0 ? round(($completed_modules / $total_modules) * 100, 1) : 0;

        return [
            'total_modules' => $total_modules,
            'completed_modules' => $completed_modules,
            'percentage' => $percentage
        ];
    }

    /**
     * Update enrollment progress percentage.
     */
    private function updateEnrollmentProgress($conn, $enrollment_id) {
        $progress = $this->getEnrollmentProgress($enrollment_id);

        $stmt = $conn->prepare("UPDATE learning_enrollments SET progress_percentage = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param('di', $progress['percentage'], $enrollment_id);
        $stmt->execute();
        $stmt->close();

        // Mark as completed if 100%
        if ($progress['percentage'] >= 100) {
            $stmt2 = $conn->prepare("UPDATE learning_enrollments SET completion_date = CURDATE() WHERE id = ?");
            $stmt2->bind_param('i', $enrollment_id);
            $stmt2->execute();
            $stmt2->close();
        }
    }

    /**
     * Calculate completion rate.
     */
    private function calculateCompletionRate() {
        $completed_enrollments = (fetchRow("SELECT COUNT(*) as total FROM learning_enrollments WHERE progress_percentage >= 100") ?? [])['total'] ?? 0;
        $total_enrollments = (fetchRow("SELECT COUNT(*) as total FROM learning_enrollments") ?? [])['total'] ?? 0;

        return $total_enrollments > 0 ? round(($completed_enrollments / $total_enrollments) * 100, 1) : 0;
    }

    /**
     * Collect course input data.
     */
    private function collectCourseInput() {
        return [
            'course_code' => sanitize($_POST['course_code']),
            'title' => sanitize($_POST['title']),
            'description' => sanitize($_POST['description']),
            'category' => sanitize($_POST['category']),
            'difficulty_level' => sanitize($_POST['difficulty_level'] ?? 'beginner'),
            'duration_hours' => intval($_POST['duration_hours']),
            'max_participants' => intval($_POST['max_participants'] ?? 0),
            'enrollment_deadline' => sanitize($_POST['enrollment_deadline']),
            'course_content' => sanitize($_POST['course_content']),
            'prerequisites' => sanitize($_POST['prerequisites']),
            'learning_objectives' => sanitize($_POST['learning_objectives']),
            'modules' => $_POST['modules'] ?? []
        ];
    }

    /**
     * Validate course input.
     */
    private function validateCourseInput($data) {
        if (empty($data['course_code'])) return 'Kode kursus wajib diisi';
        if (empty($data['title'])) return 'Judul kursus wajib diisi';
        if ($data['duration_hours'] <= 0) return 'Durasi kursus harus lebih dari 0';

        // Check if course_code already exists
        $existing = fetchRow("SELECT id FROM learning_courses WHERE course_code = ?", [$data['course_code']], 's');
        if ($existing) return 'Kode kursus sudah ada';

        return null;
    }
}
