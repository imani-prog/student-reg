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

    /**
     * @param array{
     *     admission_number:string,
     *     first_name:string,
     *     middle_name:?string,
     *     last_name:string,
     *     gender:string,
     *     email:string,
     *     password_hash:string,
     *     phone_number:string,
     *     date_of_birth:string,
     *     course_id:int,
     *     year_of_study:int
     * } $data
     */
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
}
