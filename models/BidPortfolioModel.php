<?php
/**
 * BidPortfolioModel
 *
 * Manages media (images/videos) uploaded specifically for a bid.
 */
class BidPortfolioModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS bid_portfolio_media (
                media_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                bid_id     INT UNSIGNED NOT NULL,
                file_path  VARCHAR(500) NOT NULL,
                file_type  VARCHAR(50)  NOT NULL DEFAULT 'image',
                created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_bid_id (bid_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    public function createMedia(int $bidId, string $filePath, string $fileType): bool
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO bid_portfolio_media (bid_id, file_path, file_type, created_at)
            VALUES (:bid_id, :file_path, :file_type, NOW())
        ');
        return $stmt->execute([
            'bid_id'    => $bidId,
            'file_path' => $this->sanitize($filePath),
            'file_type' => $fileType,
        ]);
    }

    public function getMediaByBid(int $bidId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM bid_portfolio_media 
            WHERE bid_id = :bid_id 
            ORDER BY created_at ASC
        ');
        $stmt->execute(['bid_id' => $bidId]);
        return $stmt->fetchAll();
    }
}
