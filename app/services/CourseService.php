<?php

class CourseService
{
    private PDO $pdo;
    private ?string $lastError = null;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->bootstrap();
    }

    public function getAll(): array
    {
        try {
            $stmt = $this->pdo->query('SELECT id, course_code, course_name FROM courses ORDER BY course_name ASC');
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            $this->lastError = 'Unable to load courses right now. Please try again later.';
            error_log('CourseService::getAll failed: ' . $e->getMessage());
            return [];
        }
    }

    public function exists(?int $courseId): bool
    {
        if (empty($courseId)) {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM courses WHERE id = :id');
            $stmt->execute(['id' => $courseId]);
            return (bool) $stmt->fetchColumn();
        } catch (Throwable $e) {
            $this->lastError = 'We could not verify the selected course.';
            error_log('CourseService::exists failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    private function bootstrap(): void
    {
        try {
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS courses (
                id INT AUTO_INCREMENT PRIMARY KEY,
                course_code VARCHAR(10) UNIQUE,
                course_name VARCHAR(100),
                faculty VARCHAR(100)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

            $count = (int) $this->pdo->query('SELECT COUNT(*) FROM courses')->fetchColumn();
            if ($count === 0) {
                $this->seedDefaults();
            }
        } catch (Throwable $e) {
            $this->lastError = 'Unable to prepare courses data. Please contact support.';
            error_log('CourseService::bootstrap failed: ' . $e->getMessage());
        }
    }

    private function seedDefaults(): void
    {
        $courses = [
            ['BCS', 'Bachelor of Computer Science', 'Computing & Informatics'],
            ['BIT', 'Bachelor of Information Technology', 'Computing & Informatics'],
            ['BCOM', 'Bachelor of Commerce', 'Business & Economics'],
        ];

        $stmt = $this->pdo->prepare('INSERT INTO courses (course_code, course_name, faculty) VALUES (:code, :name, :faculty)');
        foreach ($courses as $course) {
            $stmt->execute([
                'code' => $course[0],
                'name' => $course[1],
                'faculty' => $course[2],
            ]);
        }
    }
}
