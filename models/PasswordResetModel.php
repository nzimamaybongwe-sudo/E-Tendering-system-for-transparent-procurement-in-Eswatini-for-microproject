<?php
/**
 * PasswordResetModel — manages password reset tokens.
 */
class PasswordResetModel extends BaseModel
{
    public function createToken(int $userId, string $token, string $expiresAt): void
    {
        // Invalidate any existing token for this user first
        $stmt = $this->pdo->prepare('DELETE FROM password_resets WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);

        $stmt = $this->pdo->prepare(
            'INSERT INTO password_resets (user_id, token, expires_at, created_at) VALUES (:user_id, :token, :expires_at, NOW())'
        );
        $stmt->execute([
            'user_id'    => $userId,
            'token'      => $token,
            'expires_at' => $expiresAt,
        ]);
    }

    public function findValidToken(string $token): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW() AND used = 0 LIMIT 1'
        );
        $stmt->execute(['token' => $token]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function invalidateToken(string $token): void
    {
        $stmt = $this->pdo->prepare('UPDATE password_resets SET used = 1 WHERE token = :token');
        $stmt->execute(['token' => $token]);
    }
}
