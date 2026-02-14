-- E-Learning System Tables
-- KSP Samosir - Learning management and course delivery

CREATE TABLE learning_courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category ENUM('koperasi_dasar', 'manajemen', 'keuangan', 'hukum', 'teknologi', 'pengembangan_diri', 'lainnya') NOT NULL,
    difficulty_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    duration_hours INT NOT NULL,
    max_participants INT DEFAULT 0,
    enrollment_deadline DATE,
    course_content LONGTEXT,
    prerequisites TEXT,
    learning_objectives TEXT,
    status ENUM('draft', 'active', 'inactive', 'completed') DEFAULT 'draft',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE course_modules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    module_title VARCHAR(255) NOT NULL,
    module_description TEXT,
    module_content LONGTEXT,
    module_order INT NOT NULL,
    duration_minutes INT DEFAULT 0,
    resources TEXT, -- JSON array of resource files/URLs
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES learning_courses(id) ON DELETE CASCADE
);

CREATE TABLE learning_enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    student_id INT NOT NULL,
    enrollment_date DATE NOT NULL,
    completion_date DATE,
    progress_percentage DECIMAL(5,2) DEFAULT 0,
    status ENUM('active', 'completed', 'dropped', 'failed') DEFAULT 'active',
    certificate_issued TINYINT(1) DEFAULT 0,
    final_score DECIMAL(5,2),
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES learning_courses(id),
    FOREIGN KEY (student_id) REFERENCES users(id)
);

CREATE TABLE learning_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    enrollment_id INT NOT NULL,
    module_id INT NOT NULL,
    status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    completion_date DATE,
    time_spent_minutes INT DEFAULT 0,
    score DECIMAL(5,2),
    attempts INT DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollment_id) REFERENCES learning_enrollments(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE CASCADE
);

CREATE TABLE certificates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    enrollment_id INT NOT NULL UNIQUE,
    certificate_number VARCHAR(50) UNIQUE NOT NULL,
    issued_date DATE NOT NULL,
    expiry_date DATE,
    issued_by INT,
    verification_code VARCHAR(100) UNIQUE,
    status ENUM('active', 'revoked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollment_id) REFERENCES learning_enrollments(id),
    FOREIGN KEY (issued_by) REFERENCES users(id)
);

CREATE TABLE course_ratings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT NOT NULL,
    student_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES learning_courses(id),
    FOREIGN KEY (student_id) REFERENCES users(id),
    UNIQUE KEY unique_rating (course_id, student_id)
);

CREATE TABLE learning_analytics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT,
    enrollment_id INT,
    student_id INT,
    event_type VARCHAR(50) NOT NULL, -- login, module_start, module_complete, quiz_attempt, etc.
    event_data JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES learning_courses(id),
    FOREIGN KEY (enrollment_id) REFERENCES learning_enrollments(id),
    FOREIGN KEY (student_id) REFERENCES users(id)
);

-- Indexes
CREATE INDEX idx_learning_courses_code ON learning_courses(course_code);
CREATE INDEX idx_learning_courses_category ON learning_courses(category);
CREATE INDEX idx_learning_courses_status ON learning_courses(status);
CREATE INDEX idx_course_modules_course ON course_modules(course_id);
CREATE INDEX idx_course_modules_order ON course_modules(module_order);
CREATE INDEX idx_learning_enrollments_course ON learning_enrollments(course_id);
CREATE INDEX idx_learning_enrollments_student ON learning_enrollments(student_id);
CREATE INDEX idx_learning_enrollments_status ON learning_enrollments(status);
CREATE INDEX idx_learning_progress_enrollment ON learning_progress(enrollment_id);
CREATE INDEX idx_learning_progress_module ON learning_progress(module_id);
CREATE INDEX idx_learning_progress_status ON learning_progress(status);
CREATE INDEX idx_certificates_enrollment ON certificates(enrollment_id);
CREATE INDEX idx_certificates_number ON certificates(certificate_number);
CREATE INDEX idx_course_ratings_course ON course_ratings(course_id);
CREATE INDEX idx_course_ratings_student ON course_ratings(student_id);
CREATE INDEX idx_learning_analytics_course ON learning_analytics(course_id);
CREATE INDEX idx_learning_analytics_student ON learning_analytics(student_id);
CREATE INDEX idx_learning_analytics_event ON learning_analytics(event_type);
