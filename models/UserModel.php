<?php
class UserModel extends BaseModel
{
    public function findByEmail(string $email)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function findByLogin(string $identifier)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email OR username = :username LIMIT 1');
        $stmt->execute([
            'email' => $identifier,
            'username' => $identifier,
        ]);
        return $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (username, email, password_hash, role, status, created_at) VALUES (:username, :email, :password_hash, :role, :status, NOW())');
        $stmt->execute([
            'username' => $this->sanitize($data['username']),
            'email' => trim($data['email']),
            'password_hash' => $data['password_hash'],
            'role' => $data['role'],
            'status' => $data['status'] ?? 'active',
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function updateStatus(int $userId, string $status): bool
    {
        $stmt = $this->pdo->prepare('UPDATE users SET status = :status WHERE user_id = :user_id');
        return $stmt->execute(['status' => $status, 'user_id' => $userId]);
    }

    public function getActiveUsers(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM users WHERE status = "active"');
        return $stmt->fetchAll();
    }

    public function getById(int $userId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch();
    }

    public function countRole(string $role): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) AS total FROM users WHERE role = :role');
        $stmt->execute(['role' => $role]);
        $row = $stmt->fetch();
        return (int)$row['total'];
    }

    public function countActiveRole(string $role): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) AS total FROM users WHERE role = :role AND status = "active"');
        $stmt->execute(['role' => $role]);
        $row = $stmt->fetch();
        return (int)$row['total'];
    }

    public function getByRole(string $role): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE role = :role');
        $stmt->execute(['role' => $role]);
        return $stmt->fetchAll();
    }

    public function getActiveByRole(string $role): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE role = :role AND status = "active"');
        $stmt->execute(['role' => $role]);
        return $stmt->fetchAll();
    }

    /** Alias for getById — used by controllers that call findById() */
    public function findById(int $userId)
    {
        return $this->getById($userId);
    }

    public function updatePassword(int $userId, string $passwordHash): bool
    {
        $stmt = $this->pdo->prepare('UPDATE users SET password_hash = :hash WHERE user_id = :user_id');
        return $stmt->execute(['hash' => $passwordHash, 'user_id' => $userId]);
    }
}
