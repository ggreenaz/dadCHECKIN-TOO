<?php
namespace App\Models;

use App\Core\Model;

class VisitReasonModel extends Model
{
    protected string $table      = 'visit_reasons';
    protected string $primaryKey = 'reason_id';

    public function getForOrg(int $orgId, ?int $locationId = null): array
    {
        if ($locationId !== null) {
            $stmt = $this->db->prepare(
                "SELECT * FROM visit_reasons
                 WHERE organization_id = ? AND active = 1
                   AND (location_id = ? OR location_id IS NULL)
                 ORDER BY sort_order, label"
            );
            $stmt->execute([$orgId, $locationId]);
            return $stmt->fetchAll();
        }
        return $this->where(
            ['organization_id' => $orgId, 'active' => 1],
            'sort_order, label'
        );
    }
}
