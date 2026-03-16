<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/services/CourseService.php';

class AuthHandler
{
    private PDO $pdo;
    private User $userModel;
    private CourseService $courseService;

    public function __construct()
    {
        $database = new Database();
        $this->pdo = $database->connect();
        $this->userModel = new User($this->pdo);
        $this->courseService = new CourseService($this->pdo);
    }

    public function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('../register.php');
        }

        $action = $_POST['action'] ?? null;

        switch ($action) {
            case 'register':
                $this->register();
                break;
            case 'login':
                $this->login();
                break;
            default:
                $this->redirect('../register.php');
        }
    }

    private function register(): void
    {
        $input = $this->sanitizeInput($_POST);
        $errors = $this->validate($input);

        if (!empty($errors)) {
            $this->flashErrors($errors, $input);
            return;
        }

        try {
            $studentId = $this->userModel->createStudent([
                'admission_number' => $input['admission_number'],
                'first_name' => $input['first_name'],
                'middle_name' => $input['middle_name'] ?: null,
                'last_name' => $input['last_name'],
                'gender' => $input['gender'],
                'email' => $input['email'],
                'password_hash' => password_hash($input['password'], PASSWORD_DEFAULT),
                'phone_number' => $input['phone_number'],
                'date_of_birth' => $input['date_of_birth'],
                'course_id' => $input['course_id'],
                'year_of_study' => (int) $input['year_of_study'],
            ]);

            if ($studentId > 0) {
                $_SESSION['success'] = 'Student registered successfully. You can now log in.';
                unset($_SESSION['old']);
            } else {
                $_SESSION['errors'] = ['Unable to complete registration. Please try again.'];
            }
        } catch (Throwable $e) {
            error_log('Student registration failed: ' . $e->getMessage());
            $_SESSION['errors'] = ['We could not complete your registration due to a server error. Please try again.'];
            $this->persistOldInput($input);
        }

        $this->redirect('../register.php');
    }

    private function login(): void
    {
        $input = $this->sanitizeLoginInput($_POST);
        $errors = [];

        if ($input['identifier'] === '') {
            $errors[] = 'Enter your admission number or email.';
        }

        if ($input['password'] === '') {
            $errors[] = 'Enter your password.';
        }

        $student = null;
        if (empty($errors)) {
            $student = $this->userModel->findByIdentifier($input['identifier']);
            if (!$student) {
                $errors[] = 'We could not find an account with those credentials.';
            } elseif (!password_verify($input['password'], $student['default_password'])) {
                $errors[] = 'Incorrect password. Please try again.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['login_errors'] = $errors;
            $_SESSION['login_old'] = ['identifier' => $input['identifier']];
            $this->redirect('../login.php');
        }

        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_name'] = $student['first_name'];
        unset($_SESSION['login_errors'], $_SESSION['login_old']);

        $this->redirect('../student/dashboard.php');
    }

    private function sanitizeInput(array $data): array
    {
        return [
            'admission_number' => strtoupper(trim($data['admission_number'] ?? '')),
            'first_name' => $this->titleCase($data['first_name'] ?? ''),
            'middle_name' => $this->titleCase($data['middle_name'] ?? ''),
            'last_name' => $this->titleCase($data['last_name'] ?? ''),
            'gender' => strtolower(trim($data['gender'] ?? '')),
            'email' => strtolower(trim($data['email'] ?? '')),
            'phone_number' => $this->normalizePhone($data['phone_number'] ?? ''),
            'date_of_birth' => trim($data['date_of_birth'] ?? ''),
            'course_id' => isset($data['course_id']) ? (int) $data['course_id'] : null,
            'year_of_study' => trim($data['year_of_study'] ?? ''),
            'password' => $data['password'] ?? '',
            'password_confirmation' => $data['password_confirmation'] ?? '',
        ];
    }

    private function sanitizeLoginInput(array $data): array
    {
        return [
            'identifier' => trim($data['identifier'] ?? ''),
            'password' => $data['password'] ?? '',
        ];
    }

    private function validate(array $input): array
    {
        $errors = [];

        if (empty($input['admission_number']) || strlen($input['admission_number']) < 5) {
            $errors[] = 'Admission number is required and must be at least 5 characters.';
        } elseif ($this->userModel->admissionExists($input['admission_number'])) {
            $errors[] = 'That admission number is already registered.';
        }

        if (empty($input['first_name'])) {
            $errors[] = 'First name is required.';
        }

        if (empty($input['last_name'])) {
            $errors[] = 'Last name is required.';
        }

        $allowedGender = ['male', 'female', 'other'];
        if (empty($input['gender']) || !in_array($input['gender'], $allowedGender, true)) {
            $errors[] = 'Please select a valid gender option.';
        }

        if (empty($input['email']) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        } elseif ($this->userModel->emailExists($input['email'])) {
            $errors[] = 'That email is already in use.';
        }

        if (empty($input['phone_number']) || !preg_match('/^\+?[0-9]{9,15}$/', $input['phone_number'])) {
            $errors[] = 'Enter a valid phone number (9-15 digits).';
        }

        if (empty($input['date_of_birth'])) {
            $errors[] = 'Date of birth is required.';
        } else {
            $dob = DateTime::createFromFormat('Y-m-d', $input['date_of_birth']);
            $dobErrors = DateTime::getLastErrors();
            $hasDobErrors = is_array($dobErrors) && ($dobErrors['warning_count'] > 0 || $dobErrors['error_count'] > 0);
            if (!$dob || $hasDobErrors) {
                $errors[] = 'Provide a valid date of birth.';
            } elseif ($dob > new DateTime('now')) {
                $errors[] = 'Date of birth cannot be in the future.';
            }
        }

        if (empty($input['course_id']) || !$this->courseService->exists($input['course_id'])) {
            $errors[] = 'Select a valid course.';
        }

        $yearValue = $input['year_of_study'];
        if ($yearValue === '' || !preg_match('/^\d{4}$/', $yearValue)) {
            $errors[] = 'Year of study must be a 4-digit year (e.g., 2024).';
        } else {
            $yearInt = (int) $yearValue;
            $currentYear = (int) date('Y') + 1;
            if ($yearInt < 2000 || $yearInt > $currentYear) {
                $errors[] = 'Year of study must be between 2000 and ' . $currentYear . '.';
            }
        }

        if (strlen($input['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }

        if ($input['password'] !== $input['password_confirmation']) {
            $errors[] = 'Password confirmation does not match.';
        }

        return $errors;
    }

    private function flashErrors(array $errors, array $input): void
    {
        $_SESSION['errors'] = $errors;
        $this->persistOldInput($input);
        $this->redirect('../register.php');
    }

    private function persistOldInput(array $input): void
    {
        $old = $input;
        unset($old['password'], $old['password_confirmation']);
        $_SESSION['old'] = $old;
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    private function titleCase(string $value): string
    {
        $value = trim($value);
        return $value === '' ? '' : ucwords(strtolower($value));
    }

    private function normalizePhone(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $digitsOnly = preg_replace('/[^0-9]/', '', $value);
        return strpos($value, '+') === 0 ? '+' . $digitsOnly : $digitsOnly;
    }
}

(new AuthHandler())->handle();
