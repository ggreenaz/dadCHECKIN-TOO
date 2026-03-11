<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Auth\ProviderFactory;
use App\Models\OrganizationModel;

class AuthController extends Controller
{
    // ── Login form ───────────────────────────────────────────────

    public function loginForm(array $params): void
    {
        if (Auth::check()) {
            $this->redirect('/admin');
        }

        [$org, $settings, $providers] = $this->loadOrgAndProviders();

        $this->view->render('auth/login', [
            'title'     => 'Sign In',
            'flash'     => $this->flash(),
            'providers' => $providers,
            'settings'  => $settings,
            'org'       => $org,
        ]);
    }

    // ── Local / LDAP form submit ─────────────────────────────────

    public function login(array $params): void
    {
        [$org, $settings, $providers] = $this->loadOrgAndProviders();

        if (!$org) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Organization not configured.'];
            $this->redirect('/auth/login');
            return;
        }

        $providerName = $this->request->input('provider', 'local');
        $orgId        = (int)$org['organization_id'];

        $credentials = [
            'org_id'   => $orgId,
            'email'    => $this->request->clean('email'),
            'username' => $this->request->clean('username'),
            'password' => $this->request->input('password', ''),
        ];

        $user = Auth::attemptProvider($providerName, $credentials, $settings);

        if (!$user) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid credentials. Please try again.'];
            $this->redirect('/auth/login');
            return;
        }

        $this->redirect('/admin');
    }

    // ── OAuth redirect ───────────────────────────────────────────

    public function oauthRedirect(array $params): void
    {
        $providerName = $params['provider'] ?? '';
        $provider     = ProviderFactory::make($providerName);

        if (!$provider) {
            $this->redirect('/auth/login');
            return;
        }

        [, $settings] = $this->loadOrgAndProviders();

        if (!$provider->isConfigured($settings)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => ucfirst($providerName) . ' SSO is not configured.'];
            $this->redirect('/auth/login');
            return;
        }

        $state = Auth::generateState();
        $_SESSION['oauth_state']    = $state;
        $_SESSION['oauth_provider'] = $providerName;

        $url = $provider->getAuthUrl($settings, $this->baseUrl(), $state);
        header('Location: ' . $url);
        exit;
    }

    // ── OAuth callback ───────────────────────────────────────────

    public function oauthCallback(array $params): void
    {
        $providerName = $params['provider'] ?? '';

        // Validate state
        $state = $this->request->input('state', '');
        if (!$state || $state !== ($_SESSION['oauth_state'] ?? '')) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid OAuth state. Please try again.'];
            $this->redirect('/auth/login');
            return;
        }
        unset($_SESSION['oauth_state'], $_SESSION['oauth_provider']);

        $error = $this->request->input('error', '');
        if ($error) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Sign-in was cancelled or denied.'];
            $this->redirect('/auth/login');
            return;
        }

        [$org, $settings] = $this->loadOrgAndProviders();

        if (!$org) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Organization not found.'];
            $this->redirect('/auth/login');
            return;
        }

        $credentials = [
            'code'   => $this->request->input('code', ''),
            'org_id' => (int)$org['organization_id'],
        ];

        $user = Auth::attemptProvider($providerName, $credentials, $settings, $this->baseUrl());

        if (!$user) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Account not found. Contact your administrator.'];
            $this->redirect('/auth/login');
            return;
        }

        $this->redirect('/admin');
    }

    // ── Logout ───────────────────────────────────────────────────

    public function logout(array $params): void
    {
        Auth::logout();
        $this->redirect('/auth/login');
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function loadOrgAndProviders(): array
    {
        $cfg  = require BASE_PATH . '/config/app.php';
        $slug = $cfg['org_slug'] ?? '';
        $org  = $slug ? (new OrganizationModel())->findBySlug($slug) : null;

        if (!$org) return [null, ['auth_providers' => ['local']], ['local' => ProviderFactory::make('local')]];

        $settings  = Auth::getOrgSettings((int)$org['organization_id']);
        $providers = ProviderFactory::enabled($settings);

        return [$org, $settings, $providers];
    }

    private function baseUrl(): string
    {
        $cfg = require BASE_PATH . '/config/app.php';
        return rtrim($cfg['url'] ?? '', '/');
    }
}
