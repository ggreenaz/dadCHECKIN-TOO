<?php
namespace App\Core;

abstract class Controller
{
    protected Request $request;
    protected View    $view;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->view    = new View();
    }

    /** Redirect to a URL */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /** Redirect back with a flash message stored in session */
    protected function redirectBack(string $message = '', string $type = 'success'): void
    {
        if ($message) {
            $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        }
        $ref = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($ref);
    }

    /** Return JSON response */
    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /** Read and clear the flash message from session */
    protected function flash(): ?array
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    /** Require authentication — redirect to login if not authenticated */
    protected function requireAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->redirect('/auth/login');
        }
    }

    /** Require a minimum role level */
    protected function requireRole(string $minRole): void
    {
        $this->requireAuth();
        $hierarchy = ['staff' => 1, 'location_admin' => 2, 'org_admin' => 3, 'super_admin' => 4];
        $userRole  = $_SESSION['user_role'] ?? 'staff';
        if (($hierarchy[$userRole] ?? 0) < ($hierarchy[$minRole] ?? 99)) {
            http_response_code(403);
            $this->view->render('errors/403', ['title' => 'Access Denied']);
            exit;
        }
    }

    /** Shorthand for the current organization_id in session */
    protected function orgId(): int
    {
        return (int)($_SESSION['organization_id'] ?? 0);
    }
}
