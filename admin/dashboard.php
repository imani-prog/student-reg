<?php
session_start();

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$pdo = $database->connect();

function fetchScalar(PDO $pdo, string $sql, array $params = []): int
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function fetchRecentStudents(PDO $pdo, int $limit = 5): array
{
    $stmt = $pdo->prepare(
        'SELECT s.first_name, s.last_name, s.admission_number, s.email, s.created_at, s.year_of_study, c.course_code
		FROM students s
		LEFT JOIN courses c ON c.id = s.course_id
		ORDER BY s.created_at DESC
		LIMIT :limit'
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$stats = [
    'students' => 0,
    'courses' => 0,
    'units' => 0,
    'registrations' => 0,
    'students_this_month' => 0,
    'current_year_students' => 0,
    'female_students' => 0,
    'male_students' => 0,
];

$recentStudents = [];
$error = null;
$currentYear = (int) date('Y');
$startOfMonth = date('Y-m-01 00:00:00');

try {
    $stats['students'] = fetchScalar($pdo, 'SELECT COUNT(*) FROM students');
    $stats['courses'] = fetchScalar($pdo, 'SELECT COUNT(*) FROM courses');
    $stats['units'] = fetchScalar($pdo, 'SELECT COUNT(*) FROM units');
    $stats['registrations'] = fetchScalar($pdo, 'SELECT COUNT(*) FROM registrations');
    $stats['students_this_month'] = fetchScalar($pdo, 'SELECT COUNT(*) FROM students WHERE created_at >= :start', ['start' => $startOfMonth]);
    $stats['current_year_students'] = fetchScalar($pdo, 'SELECT COUNT(*) FROM students WHERE year_of_study = :year', ['year' => $currentYear]);
    $stats['female_students'] = fetchScalar($pdo, "SELECT COUNT(*) FROM students WHERE gender = 'female'");
    $stats['male_students'] = fetchScalar($pdo, "SELECT COUNT(*) FROM students WHERE gender = 'male'");

    $recentStudents = fetchRecentStudents($pdo);
} catch (Throwable $e) {
    $error = 'Unable to load analytics right now. Please refresh in a moment.';
    error_log('Admin dashboard error: ' . $e->getMessage());
}

$statCards = [
    [
        'label' => 'Total students',
        'value' => $stats['students'],
        'meta' => 'New this month: ' . number_format($stats['students_this_month']),
    ],
    [
        'label' => 'Total courses',
        'value' => $stats['courses'],
        'meta' => 'Units: ' . number_format($stats['units']),
    ],
    [
        'label' => 'Registrations',
        'value' => $stats['registrations'],
        'meta' => 'Year ' . $currentYear . ': ' . number_format($stats['current_year_students']),
    ],
    [
        'label' => 'Female students',
        'value' => $stats['female_students'],
        'meta' => 'Male: ' . number_format($stats['male_students']),
    ],
];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard • MKSU</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>
    <main class="admin-dashboard">
        <section class="panel">
            <header class="panel__header">
                <div class="panel__header-content">
                    <img src="../assets/images/logo.png" alt="MKSU Logo">
                    <p class="eyebrow">Portal analytics</p>
                    <h1>Admin overview</h1>
                    <p class="subtitle">Live snapshot of students, courses, and registrations across the portal.</p>
                </div>
                <div class="panel__actions">
                    <a class="btn" href="../register.php">Register student</a>
                    <a class="btn btn--ghost" href="../login.php">Login a student</a>
                </div>
            </header>

            <?php if ($error) : ?>
                <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
            <?php else : ?>
                <div class="stats-grid">
                    <?php foreach ($statCards as $card) : ?>
                        <article class="stat-card">
                            <p class="stat-card__label"><?= htmlspecialchars($card['label']) ?></p>
                            <h2><?= number_format($card['value']) ?></h2>
                            <span class="stat-card__meta"><?= htmlspecialchars($card['meta']) ?></span>
                        </article>
                    <?php endforeach; ?>
                </div>

                <section class="recent">
                    <div class="recent__header">
                        <div class="recent__header-content">
                            <p class="eyebrow">Most recent registrations</p>
                            <h2>Latest students</h2>
                        </div>
                    </div>

                    <?php if (empty($recentStudents)) : ?>
                        <p class="helper">No students have been registered yet.</p>
                    <?php else : ?>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Admission #</th>
                                        <th>Course</th>
                                        <th>Year</th>
                                        <th>Registered on</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentStudents as $student) : ?>
                                        <tr>
                                            <td><?= htmlspecialchars(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''))) ?>
                                            </td>
                                            <td><?= htmlspecialchars($student['admission_number']) ?></td>
                                            <td><?= htmlspecialchars($student['course_code'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($student['year_of_study'] ?? '—') ?></td>
                                            <td><?= htmlspecialchars($student['created_at'] ? date('d M Y', strtotime($student['created_at'])) : '—') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </section>
    </main>
</body>

</html>