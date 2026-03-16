<?php

class User
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function admissionExists(string $admissionNumber): bool
    {
        $query = 'SELECT COUNT(*) FROM students WHERE admission_number = :admission_number';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['admission_number' => $admissionNumber]);
        return (bool) $stmt->fetchColumn();
    }

    public function emailExists(string $email): bool
    {
        $query = 'SELECT COUNT(*) FROM students WHERE email = :email';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['email' => $email]);
        return (bool) $stmt->fetchColumn();
    }

    public function createStudent(array $data): int
    {
        $sql = 'INSERT INTO students (
			admission_number,
			first_name,
			middle_name,
			last_name,
			gender,
			email,
			default_password,
			phone_number,
			date_of_birth,
			course_id,
			year_of_study
        ) VALUES (
            :admission_number,
            :first_name,
            :middle_name,
            :last_name,
            :gender,
            :email,
            :default_password,
            :phone_number,
            :date_of_birth,
            :course_id,
            :year_of_study
        )';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'admission_number' => $data['admission_number'],
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'gender' => $data['gender'],
            'email' => $data['email'],
            'default_password' => $data['password_hash'],
            'phone_number' => $data['phone_number'],
            'date_of_birth' => $data['date_of_birth'],
            'course_id' => $data['course_id'],
            'year_of_study' => $data['year_of_study'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findByIdentifier(string $identifier): ?array
    {
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'admission_number';
        $stmt = $this->pdo->prepare("SELECT * FROM students WHERE {$field} = :identifier LIMIT 1");
        $stmt->execute(['identifier' => $identifier]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        return $student ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM students WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        return $student ?: null;
    }

    public function getProfileById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT s.*, c.course_name, c.course_code, c.faculty
            FROM students s
            LEFT JOIN courses c ON c.id = s.course_id
            WHERE s.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        return $profile ?: null;
    }
}
