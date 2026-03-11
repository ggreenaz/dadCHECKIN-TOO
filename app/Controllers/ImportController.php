<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class ImportController extends Controller
{
    public function index(array $params): void
    {
        $this->requireAuth();
        $this->view->render('admin/import', [
            'title'    => 'CSV Import',
            'helpSlug' => 'import',
            'flash'    => $this->flash(),
        ]);
    }

    public function importHosts(array $params): void
    {
        $this->requireRole('org_admin');
        $orgId = $this->orgId();
        $file  = $this->request->file('csv_file');

        [$rows, $error] = $this->parseCSV($file);
        if ($error) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $error];
            $this->redirect('/admin/import');
            return;
        }

        $db      = Database::getInstance();
        $stmt    = $db->prepare(
            "INSERT IGNORE INTO hosts (organization_id, name, email, phone, department)
             VALUES (?, ?, ?, ?, ?)"
        );
        $imported = 0;
        foreach ($rows as $i => $row) {
            if ($i === 0 && $this->isHeader($row, ['name'])) continue;
            $name = trim($row[0] ?? '');
            if (!$name) continue;
            $stmt->execute([
                $orgId,
                $name,
                trim($row[1] ?? '') ?: null,
                trim($row[2] ?? '') ?: null,
                trim($row[3] ?? '') ?: null,
            ]);
            $imported++;
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => "{$imported} host(s) imported."];
        $this->redirect('/admin/import');
    }

    public function importReasons(array $params): void
    {
        $this->requireRole('org_admin');
        $orgId = $this->orgId();
        $file  = $this->request->file('csv_file');

        [$rows, $error] = $this->parseCSV($file);
        if ($error) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $error];
            $this->redirect('/admin/import');
            return;
        }

        $db       = Database::getInstance();
        $stmt     = $db->prepare(
            "INSERT INTO visit_reasons (organization_id, label, sort_order) VALUES (?, ?, ?)"
        );
        $imported = 0;
        $order    = (int)$db->query(
            "SELECT COALESCE(MAX(sort_order),0) FROM visit_reasons WHERE organization_id = {$orgId}"
        )->fetchColumn();

        foreach ($rows as $i => $row) {
            if ($i === 0 && $this->isHeader($row, ['label', 'reason'])) continue;
            $label = trim($row[0] ?? '');
            if (!$label) continue;
            $stmt->execute([$orgId, $label, ++$order]);
            $imported++;
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => "{$imported} reason(s) imported."];
        $this->redirect('/admin/import');
    }

    public function importVisitors(array $params): void
    {
        $this->requireRole('org_admin');
        $file = $this->request->file('csv_file');

        [$rows, $error] = $this->parseCSV($file);
        if ($error) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $error];
            $this->redirect('/admin/import');
            return;
        }

        $db       = Database::getInstance();
        $stmt     = $db->prepare(
            "INSERT INTO visitors (first_name, last_name, phone, email)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
               first_name = VALUES(first_name),
               last_name  = VALUES(last_name),
               email      = VALUES(email)"
        );
        $imported = 0;
        foreach ($rows as $i => $row) {
            if ($i === 0 && $this->isHeader($row, ['first', 'last', 'name'])) continue;
            $first = trim($row[0] ?? '');
            $last  = trim($row[1] ?? '');
            $phone = trim($row[2] ?? '');
            if (!$first || !$phone) continue;
            $stmt->execute([
                $first,
                $last,
                $phone,
                trim($row[3] ?? '') ?: null,
            ]);
            $imported++;
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => "{$imported} visitor(s) imported/updated."];
        $this->redirect('/admin/import');
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function parseCSV(?array $file): array
    {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return [[], 'No file uploaded or upload error.'];
        }

        $mime    = mime_content_type($file['tmp_name']);
        $allowed = ['text/plain', 'text/csv', 'application/csv', 'application/octet-stream'];
        if (!in_array($mime, $allowed, true)) {
            return [[], 'Invalid file type. Please upload a CSV file.'];
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            return [[], 'Could not read the uploaded file.'];
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        if (empty($rows)) {
            return [[], 'The CSV file is empty.'];
        }

        return [$rows, null];
    }

    private function isHeader(array $row, array $keywords): bool
    {
        $first = strtolower(trim($row[0] ?? ''));
        foreach ($keywords as $kw) {
            if (str_contains($first, $kw)) return true;
        }
        return false;
    }
}
