<?php
namespace App\Models;

use App\Core\Model;

class DepartmentModel extends Model
{
    protected string $table      = 'departments';
    protected string $primaryKey = 'department_id';

    public function getForOrg(int $orgId): array
    {
        return $this->where(['organization_id' => $orgId, 'active' => 1], 'sort_order, name');
    }

    public function findByName(int $orgId, string $name): ?array
    {
        return $this->findWhere(['organization_id' => $orgId, 'name' => $name]);
    }
}
