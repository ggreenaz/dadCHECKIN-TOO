<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class UserModel extends Model
{
    protected string $table      = 'users';
    protected string $primaryKey = 'user_id';

    /** All users for an organisation, ordered by name */
    public function getForOrg(int $orgId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE organization_id = ? ORDER BY name ASC"
        );
        $stmt->execute([$orgId]);
        return $stmt->fetchAll();
    }

    /** Find a single user scoped to an org */
    public function findById(int $userId, int $orgId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE user_id = ? AND organization_id = ? LIMIT 1"
        );
        $stmt->execute([$userId, $orgId]);
        return $stmt->fetch() ?: null;
    }

    /** Create a new user; returns the new user_id */
    public function create(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        return $this->insert($data);
    }

    /** Update a user scoped to an org */
    public function updateUser(int $userId, int $orgId, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $affected = parent::update($data, ['user_id' => $userId, 'organization_id' => $orgId]);
        return $affected > 0;
    }

    /** Deactivate a user */
    public function deactivate(int $userId, int $orgId): bool
    {
        $affected = parent::update(
            ['active' => 0, 'updated_at' => date('Y-m-d H:i:s')],
            ['user_id' => $userId, 'organization_id' => $orgId]
        );
        return $affected > 0;
    }

    /** Reactivate a user */
    public function reactivate(int $userId, int $orgId): bool
    {
        $affected = parent::update(
            ['active' => 1, 'updated_at' => date('Y-m-d H:i:s')],
            ['user_id' => $userId, 'organization_id' => $orgId]
        );
        return $affected > 0;
    }
}
