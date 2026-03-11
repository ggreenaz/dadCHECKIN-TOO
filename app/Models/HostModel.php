<?php
namespace App\Models;

use App\Core\Model;

class HostModel extends Model
{
    protected string $table      = 'hosts';
    protected string $primaryKey = 'host_id';

    public function getForOrg(int $orgId, ?int $locationId = null): array
    {
        $sql = "SELECT h.*, d.name AS department_name
                FROM hosts h
                LEFT JOIN departments d ON h.department_id = d.department_id
                WHERE h.organization_id = ? AND h.active = 1";
        $bindings = [$orgId];

        if ($locationId !== null) {
            $sql .= " AND (h.location_id = ? OR h.location_id IS NULL)";
            $bindings[] = $locationId;
        }

        $sql .= " ORDER BY h.name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }
}
