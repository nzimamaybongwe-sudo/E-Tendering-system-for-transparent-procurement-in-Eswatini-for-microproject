<?php
/**
 * AuditModel — unified audit log model.
 * Replaces the old split between AuditModel and AuditLogModel.
 */
class AuditModel extends BaseModel
{
    public function log(int $userId, string $actionType, string $targetTable, int $targetId, string $details): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO audit_logs (actor_user_id, action_type, target_table, target_id, details, created_at, immutable_flag)
             VALUES (:actor_user_id, :action_type, :target_table, :target_id, :details, NOW(), 1)'
        );
        return $stmt->execute([
            'actor_user_id' => $userId,
            'action_type'   => $this->sanitize($actionType),
            'target_table'  => $this->sanitize($targetTable),
            'target_id'     => $targetId,
            'details'       => $this->sanitize($details),
        ]);
    }

    /**
     * Paginated + filtered audit log query.
     * Returns ['logs' => [...], 'total' => int]
     */
    public function getFiltered(string $search, string $actionType, int $limit, int $offset): array
    {
        $where  = [];
        $params = [];

        if ($search !== '') {
            $where[]          = '(u.username LIKE :search OR a.details LIKE :search2 OR a.target_table LIKE :search3)';
            $params['search']  = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
            $params['search3'] = '%' . $search . '%';
        }
        if ($actionType !== '') {
            $where[]              = 'a.action_type = :action_type';
            $params['action_type'] = $actionType;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countSql = "SELECT COUNT(*) AS total FROM audit_logs a JOIN users u ON a.actor_user_id = u.user_id $whereClause";
        $stmt     = $this->pdo->prepare($countSql);
        $stmt->execute($params);
        $total    = (int)$stmt->fetchColumn();

        $params['limit']  = $limit;
        $params['offset'] = $offset;

        $dataSql = "SELECT a.*, u.username FROM audit_logs a JOIN users u ON a.actor_user_id = u.user_id
                    $whereClause
                    ORDER BY a.created_at DESC
                    LIMIT :limit OFFSET :offset";
        $stmt    = $this->pdo->prepare($dataSql);
        // Bind int params explicitly to avoid PDO treating them as strings
        foreach ($params as $key => $value) {
            if ($key === 'limit' || $key === 'offset') {
                $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
            }
        }
        $stmt->execute();
        $logs = $stmt->fetchAll();

        return ['logs' => $logs, 'total' => $total];
    }

    /** Get distinct action types for the filter dropdown */
    public function getDistinctActions(): array
    {
        $stmt = $this->pdo->query('SELECT DISTINCT action_type FROM audit_logs ORDER BY action_type ASC');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Kept for backward compatibility */
    public function getRecent(int $limit = 100): array
    {
        ['logs' => $logs] = $this->getFiltered('', '', $limit, 0);
        return $logs;
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM audit_logs ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }
}
