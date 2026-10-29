<?php
/**
 * PortfolioModel — manages contractor past project history.
 * Fulfills the "portfolio showcasing" objective.
 */
class PortfolioModel extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO contractor_portfolio (contractor_id, project_name, client_name, project_value, completion_year, description)
             VALUES (:contractor_id, :project_name, :client_name, :project_value, :completion_year, :description)'
        );
        $stmt->execute([
            'contractor_id'   => $data['contractor_id'],
            'project_name'    => $this->sanitize($data['project_name']),
            'client_name'     => $this->sanitize($data['client_name']),
            'project_value'   => (float)$data['project_value'],
            'completion_year' => (int)$data['completion_year'],
            'description'     => $this->sanitize($data['description'] ?? ''),
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getByContractor(int $contractorId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM contractor_portfolio WHERE contractor_id = :id ORDER BY completion_year DESC');
        $stmt->execute(['id' => $contractorId]);
        return $stmt->fetchAll();
    }

    public function delete(int $projectId, int $contractorId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM contractor_portfolio WHERE project_id = :p_id AND contractor_id = :c_id');
        return $stmt->execute(['p_id' => $projectId, 'c_id' => $contractorId]);
    }

    public function createMedia(int $projectId, string $filePath, string $fileType): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO portfolio_media (project_id, file_path, file_type, created_at) VALUES (:project_id, :file_path, :file_type, NOW())');
        return $stmt->execute([
            'project_id' => $projectId,
            'file_path'  => $this->sanitize($filePath),
            'file_type'  => $fileType,
        ]);
    }

    public function getMediaByProjectIds(array $projectIds): array
    {
        if (empty($projectIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($projectIds), '?'));
        $stmt = $this->pdo->prepare('SELECT * FROM portfolio_media WHERE project_id IN (' . $placeholders . ') ORDER BY created_at DESC');
        $stmt->execute($projectIds);
        return $stmt->fetchAll();
    }

    public function getByContractorWithMedia(int $contractorId): array
    {
        $portfolio = $this->getByContractor($contractorId);
        if (empty($portfolio)) {
            return [];
        }

        $projectIds = array_column($portfolio, 'project_id');
        $mediaItems = $this->getMediaByProjectIds($projectIds);

        $mediaByProject = [];
        foreach ($mediaItems as $media) {
            $mediaByProject[$media['project_id']][] = $media;
        }

        foreach ($portfolio as &$item) {
            $item['media'] = $mediaByProject[$item['project_id']] ?? [];
        }
        unset($item);

        return $portfolio;
    }
}
