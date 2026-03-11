<?php
namespace App\Models;

use App\Core\Model;

class VisitorModel extends Model
{
    protected string $table      = 'visitors';
    protected string $primaryKey = 'visitor_id';

    /** Find visitor by phone; returns row or null */
    public function findByPhone(string $phone): ?array
    {
        return $this->findWhere(['phone' => $phone]);
    }

    /** Find visitor by email; returns row or null */
    public function findByEmail(string $email): ?array
    {
        return $this->findWhere(['email' => $email]);
    }

    /**
     * Find or create a visitor record.
     * Looks up by email first (if provided), then phone.
     * Returns ['visitor' => row, 'created' => bool]
     */
    public function findOrCreate(array $data): array
    {
        $email = $data['email'] ?? null;
        $phone = $data['phone'] ?? null;

        // Prefer email lookup (stable, unique identity)
        if ($email) {
            $existing = $this->findByEmail($email);
            if ($existing) {
                return ['visitor' => $existing, 'created' => false];
            }
        }

        // Fall back to phone lookup
        if ($phone) {
            $existing = $this->findByPhone($phone);
            if ($existing) {
                return ['visitor' => $existing, 'created' => false];
            }
        }

        $id      = $this->insert($data);
        $visitor = $this->find($id);
        return ['visitor' => $visitor, 'created' => true];
    }
}
