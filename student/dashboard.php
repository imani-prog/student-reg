<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    $_SESSION['login_errors'] = ['Please sign in to access the dashboard.'];
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/User.php';

$database = new Database();
$pdo = $database->connect();
$userModel = new User($pdo);

$studentId = (int) $_SESSION['student_id'];
$student = $userModel->getProfileById($studentId);

if (!$student) {
    unset($_SESSION['student_id'], $_SESSION['student_name']);
    $_SESSION['login_errors'] = ['We could not load your profile. Please sign in again.'];
    header('Location: ../login.php');
    exit;
}

$fullName = trim(
    implode(' ', array_filter([
        $student['first_name'] ?? '',
        $student['middle_name'] ?? '',
        $student['last_name'] ?? '',
    ]))
);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/student.css">
</head>

<body>
    <main class="dashboard">
        <section class="card">
            <div class="card__header">
                <div>
                    <p class="eyebrow">Student snapshot</p>
                    <h1><?= htmlspecialchars($fullName) ?></h1>
                    <p class="subtitle">Admission #<?= htmlspecialchars($student['admission_number']) ?></p>
                </div>
                <a class="btn btn--ghost" href="../handlers/logout.php">Logout</a>
            </div>

            <div class="card__grid">
                <article>
                    <span class="label">Course</span>
                    <p>
                        <?= htmlspecialchars($student['course_code'] ?? 'N/A') ?> —
                        <?= htmlspecialchars($student['course_name'] ?? 'Not assigned') ?>
                    </p>
                </article>
                <article>
                    <span class="label">Email</span>
                    <p><?= htmlspecialchars($student['email']) ?></p>
                </article>
                <article>
                    <span class="label">Phone</span>
                    <p><?= htmlspecialchars($student['phone_number']) ?></p>
                </article>
                <article>
                    <span class="label">Year of study</span>
                    <p><?= htmlspecialchars($student['year_of_study']) ?></p>
                </article>
                <article>
                    <span class="label">Gender</span>
                    <p><?= htmlspecialchars(ucfirst($student['gender'])) ?></p>
                </article>
                <article>
                    <span class="label">Date of birth</span>
                    <p><?= htmlspecialchars($student['date_of_birth']) ?></p>
                </article>
            </div>
        </section>
    </main>
</body>

</html>