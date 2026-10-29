<?php
class AuditLogModel extends BaseModel
{
    public function getRecent(int $limit = 100): array
    {
        $stmt = $this->pdo->prepare('SELECT a.*, u.username FROM audit_logs a JOIN users u ON a.actor_user_id = u.user_id ORDER BY a.created_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
