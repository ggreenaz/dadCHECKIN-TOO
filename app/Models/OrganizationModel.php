<?php
namespace App\Models;

use App\Core\Model;

class OrganizationModel extends Model
{
    protected string $table      = 'organizations';
    protected string $primaryKey = 'organization_id';

    public function findBySlug(string $slug): ?array
    {
        return $this->findWhere(['slug' => $slug, 'active' => 1]);
    }
}
