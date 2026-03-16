<?php
session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/services/CourseService.php';

$db = new Database();
$pdo = $db->connect();
$courseService = new CourseService($pdo);
$courses = $courseService->getAll();
$courseFetchError = $courseService->getLastError();

$success = $_SESSION['success'] ?? null;
$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];

unset($_SESSION['success'], $_SESSION['errors'], $_SESSION['old']);

$genderOptions = [
    'male' => 'Male',
    'female' => 'Female',
    'other' => 'Other',
];
$currentYear = (int) date('Y');
$maxAcademicYear = $currentYear + 1;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/register.css">
</head>

<body>
    <main class="registration-page">
        <section class="card">
            <header class="card__header">
                <div>
                    <p class="eyebrow">MKSU Student Portal</p>
                    <h1>Create your student profile</h1>
                    <p class="subtitle">Fill in the details below to activate your account and begin registering for
                        units.</p>
                </div>
            </header>

            <?php if (!empty($success)) : ?>
                <div class="alert alert--success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)) : ?>
                <div class="alert alert--error">
                    <p>Please fix the following issues:</p>
                    <ul>
                        <?php foreach ($errors as $error) : ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($courseFetchError)) : ?>
                <div class="alert alert--error">
                    <?= htmlspecialchars($courseFetchError) ?>
                </div>
            <?php endif; ?>

            <form class="form" action="handlers/AuthHandler.php" method="POST" novalidate>
                <input type="hidden" name="action" value="register">
                <div class="form__grid">
                    <label class="form__field">
                        <span>Admission Number *</span>
                        <input type="text" name="admission_number" maxlength="20" required
                            value="<?= htmlspecialchars($old['admission_number'] ?? '') ?>"
                            placeholder="MKSU/COM/001/2024">
                    </label>

                    <label class="form__field">
                        <span>First Name *</span>
                        <input type="text" name="first_name" maxlength="50" required
                            value="<?= htmlspecialchars($old['first_name'] ?? '') ?>">
                    </label>

                    <label class="form__field">
                        <span>Middle Name</span>
                        <input type="text" name="middle_name" maxlength="50"
                            value="<?= htmlspecialchars($old['middle_name'] ?? '') ?>">
                    </label>

                    <label class="form__field">
                        <span>Last Name *</span>
                        <input type="text" name="last_name" maxlength="50" required
                            value="<?= htmlspecialchars($old['last_name'] ?? '') ?>">
                    </label>

                    <label class="form__field">
                        <span>Gender *</span>
                        <select name="gender" required>
                            <option value="">Select gender</option>
                            <?php foreach ($genderOptions as $value => $label) : ?>
                                <option value="<?= $value ?>" <?= ($old['gender'] ?? '') === $value ? 'selected' : '' ?>>
                                    <?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="form__field">
                        <span>Email Address *</span>
                        <input type="email" name="email" required value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                            placeholder="student@mksu.ac.ke">
                    </label>

                    <label class="form__field">
                        <span>Phone Number *</span>
                        <input type="tel" name="phone_number" maxlength="20" required
                            value="<?= htmlspecialchars($old['phone_number'] ?? '') ?>" placeholder="0712 345 678">
                    </label>

                    <label class="form__field">
                        <span>Date of Birth *</span>
                        <input type="date" name="date_of_birth" required
                            value="<?= htmlspecialchars($old['date_of_birth'] ?? '') ?>">
                    </label>

                    <label class="form__field">
                        <span>Course *</span>
                        <select name="course_id" required <?= empty($courses) ? 'disabled' : '' ?>>
                            <option value="">Select course</option>
                            <?php foreach ($courses as $course) : ?>
                                <option value="<?= $course['id'] ?>"
                                    <?= ($old['course_id'] ?? '') == $course['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($course['course_code'] . ' — ' . $course['course_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="form__field">
                        <span>Year of Study (YYYY) *</span>
                        <input type="text" name="year_of_study" pattern="\d{4}" inputmode="numeric" maxlength="4"
                            placeholder="<?= $currentYear ?>" required
                            value="<?= htmlspecialchars($old['year_of_study'] ?? '') ?>">
                        <small class="form__hint">Enter a 4-digit academic year between 2000 and
                            <?= $maxAcademicYear ?>.</small>
                    </label>

                    <label class="form__field">
                        <span>Password *</span>
                        <input type="password" name="password" minlength="8" required
                            placeholder="Create a strong password">
                    </label>

                    <label class="form__field">
                        <span>Confirm Password *</span>
                        <input type="password" name="password_confirmation" minlength="8" required
                            placeholder="Repeat your password">
                    </label>
                </div>

                <div class="form__actions">
                    <button type="submit" class="btn btn--primary" <?= empty($courses) ? 'disabled' : '' ?>>Register
                        student</button>
                    <p class="form__helper">Already have an account? <a href="login.php">Sign in</a></p>
                </div>
            </form>
        </section>
    </main>
</body>

</html>