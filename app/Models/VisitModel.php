<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class VisitModel extends Model
{
    protected string $table      = 'visits';
    protected string $primaryKey = 'visit_id';

    /**
     * Get all active (not checked-out) visits for an org,
     * joined with visitor, host, and reason details.
     */
    public function getActiveVisits(int $orgId, ?int $locationId = null): array
    {
        $sql = "
            SELECT
                vi.visit_id,
                vi.status,
                vi.check_in_time,
                v.first_name,
                v.last_name,
                v.phone,
                v.email,
                h.name       AS host_name,
                d.name       AS host_department,
                vr.label     AS reason_label
            FROM visits vi
            JOIN visitors      v  ON vi.visitor_id  = v.visitor_id
            LEFT JOIN hosts    h  ON vi.host_id     = h.host_id
            LEFT JOIN departments d ON h.department_id = d.department_id
            LEFT JOIN visit_reasons vr ON vi.reason_id = vr.reason_id
            WHERE vi.organization_id = ?
              AND vi.check_out_time IS NULL
              AND vi.status NOT IN ('completed','no_show','cancelled')
        ";
        $bindings = [$orgId];

        if ($locationId !== null) {
            $sql       .= ' AND vi.location_id = ?';
            $bindings[] = $locationId;
        }

        $sql .= ' ORDER BY vi.check_in_time ASC';

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }

    /**
     * Check out a visit by setting checkout time and status to 'completed'.
     */
    public function checkOut(int $visitId, int $orgId): bool
    {
        $stmt = Database::getInstance()->prepare(
            "UPDATE visits
             SET check_out_time = NOW(), status = 'completed', updated_at = NOW()
             WHERE visit_id = ? AND organization_id = ? AND check_out_time IS NULL"
        );
        $stmt->execute([$visitId, $orgId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Check out by visitor phone within an org (matches the most recent open visit).
     */
    public function checkOutByPhone(string $phone, int $orgId): ?array
    {
        $db   = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT vi.visit_id, vi.visitor_id
             FROM visits vi
             JOIN visitors v ON vi.visitor_id = v.visitor_id
             WHERE v.phone = ?
               AND vi.organization_id = ?
               AND vi.check_out_time IS NULL
               AND vi.status NOT IN ('completed','no_show','cancelled')
             ORDER BY vi.check_in_time DESC
             LIMIT 1"
        );
        $stmt->execute([$phone, $orgId]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $this->checkOut((int)$row['visit_id'], $orgId);
        return $this->find((int)$row['visit_id']);
    }

    /**
     * Get visit history for an org with full join details.
     */
    public function getHistory(int $orgId, array $filters = [], int $limit = 200): array
    {
        $sql = "
            SELECT
                vi.visit_id,
                vi.visitor_id,
                vi.status,
                vi.check_in_time,
                vi.check_out_time,
                vi.notes,
                v.first_name,
                v.last_name,
                v.phone,
                v.email,
                l.name        AS location_name,
                h.name        AS host_name,
                vr.label      AS reason_label,
                TIMESTAMPDIFF(MINUTE, vi.check_in_time,
                    COALESCE(vi.check_out_time, NOW())) AS duration_min
            FROM visits vi
            JOIN visitors          v  ON vi.visitor_id  = v.visitor_id
            LEFT JOIN locations    l  ON vi.location_id = l.location_id
            LEFT JOIN hosts        h  ON vi.host_id     = h.host_id
            LEFT JOIN visit_reasons vr ON vi.reason_id  = vr.reason_id
            WHERE vi.organization_id = ?
        ";
        $bindings = [$orgId];

        if (!empty($filters['date_from'])) {
            $sql       .= ' AND DATE(vi.check_in_time) >= ?';
            $bindings[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql       .= ' AND DATE(vi.check_in_time) <= ?';
            $bindings[] = $filters['date_to'];
        }
        if (!empty($filters['host_id'])) {
            $sql       .= ' AND vi.host_id = ?';
            $bindings[] = (int)$filters['host_id'];
        }
        if (!empty($filters['status'])) {
            $sql       .= ' AND vi.status = ?';
            $bindings[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $like       = '%' . $filters['search'] . '%';
            $sql       .= ' AND (v.first_name LIKE ? OR v.last_name LIKE ?
                               OR CONCAT(v.first_name," ",v.last_name) LIKE ?
                               OR v.phone LIKE ? OR v.email LIKE ?)';
            $bindings[] = $like;
            $bindings[] = $like;
            $bindings[] = $like;
            $bindings[] = $like;
            $bindings[] = $like;
        }

        $sql .= ' ORDER BY vi.check_in_time DESC LIMIT ' . (int)$limit;

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }
}
