<?php
use App\Core\Router;
use App\Controllers\CheckinController;
use App\Controllers\BoardController;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\ImportController;
use App\Controllers\SettingsController;
use App\Controllers\InstallController;
use App\Controllers\SetupController;
use App\Controllers\UserController;

/** @var Router $router */

// ── Install wizard ───────────────────────────────────────────────
$router->get( '/install',              [InstallController::class, 'choosePath']);
$router->get( '/install/guided',       [InstallController::class, 'guidedInfo']);
$router->get( '/install/upgrade',      [InstallController::class, 'upgradePage']);
$router->post('/install/upgrade/run',  [InstallController::class, 'upgradeRun']);
$router->get( '/install/upgrade-path', [InstallController::class, 'upgradePathPage']);
$router->post('/install/upgrade-prepare',   [InstallController::class, 'upgradePrepare']);
$router->post('/install/create-database',   [InstallController::class, 'createDatabase']);
$router->post('/install/check-db-available', [InstallController::class, 'checkDbAvailable']);
$router->post('/install/upgrade/quick',[InstallController::class, 'quickUpgradeRun']);

// Guided upgrade — specific POST routes before generic :step
$router->post('/install/guided-upgrade/start',                    [InstallController::class, 'guidedUpgradeStart']);
$router->post('/install/guided-upgrade/organization/save',        [InstallController::class, 'guidedUpgradeOrgSave']);
$router->post('/install/guided-upgrade/migration/run',            [InstallController::class, 'guidedUpgradeMigration']);
$router->get( '/install/guided-upgrade/migration/stream',         [InstallController::class, 'guidedUpgradeMigrationStream']);
$router->post('/install/guided-upgrade/departments/save',         [InstallController::class, 'guidedUpgradeDeptsSave']);
$router->post('/install/guided-upgrade/auth/save',                [InstallController::class, 'guidedUpgradeAuthSave']);
$router->post('/install/guided-upgrade/kiosk/save',               [InstallController::class, 'guidedUpgradeKioskSave']);
$router->post('/install/guided-upgrade/notifications/save',       [InstallController::class, 'guidedUpgradeNotificationsSave']);
$router->post('/install/guided-upgrade/finish',                   [InstallController::class, 'guidedUpgradeFinish']);
$router->get( '/install/guided-upgrade/:step',                    [InstallController::class, 'guidedUpgradeStep']);
$router->get( '/install/abort',                                   [InstallController::class, 'abortConfirm']);
$router->post('/install/abort',                                   [InstallController::class, 'abortUpgrade']);

$router->get( '/install/:step',        [InstallController::class, 'step']);
$router->post('/install/1/check',      [InstallController::class, 'requirements']);
$router->post('/install/test-db',      [InstallController::class, 'testConnection']);
$router->post('/install/2/save',       [InstallController::class, 'saveDatabase']);
$router->post('/install/3/save',       [InstallController::class, 'saveSetup']);

// ── Auth ─────────────────────────────────────────────────────────
$router->get( '/auth/login',                [AuthController::class, 'loginForm']);
$router->post('/auth/login',                [AuthController::class, 'login']);
$router->get( '/auth/logout',               [AuthController::class, 'logout']);
$router->get( '/auth/redirect/:provider',   [AuthController::class, 'oauthRedirect']);
$router->get( '/auth/callback/:provider',   [AuthController::class, 'oauthCallback']);

// ── Public kiosk ─────────────────────────────────────────────────
$router->get( '/',                  [CheckinController::class, 'index']);
$router->get( '/checkin',           [CheckinController::class, 'index']);
$router->post('/checkin/auth',      [CheckinController::class, 'kioskAuth']);
$router->post('/checkin/checkout',  [CheckinController::class, 'kioskCheckout']);
$router->get( '/checkin/cancel',    [CheckinController::class, 'kioskCancel']);
$router->post('/checkin',           [CheckinController::class, 'store']);

// ── Live board ───────────────────────────────────────────────────
$router->get('/board',      [BoardController::class, 'index']);
$router->get('/board/poll', [BoardController::class, 'poll']);

// ── Log Hub ───────────────────────────────────────────────────────
$router->get('/logs',            [AdminController::class, 'logHub']);
$router->get('/admin/analytics', [AdminController::class, 'analytics']);

// ── Admin — dashboard & visits ───────────────────────────────────
$router->get('/admin',               [AdminController::class, 'dashboard']);
$router->get( '/admin/live',              [AdminController::class, 'live']);
$router->post('/admin/live/bulk-checkout',[AdminController::class, 'bulkCheckout']);
$router->get('/admin/live/poll',     [AdminController::class, 'livePoll']);
$router->get('/admin/live/demo',     [AdminController::class, 'liveDemo']);
$router->get('/admin/history',      [AdminController::class, 'history']);
$router->get('/admin/visitor/:id',  [AdminController::class, 'visitorProfile']);

// ── Admin — hosts ────────────────────────────────────────────────
$router->get( '/admin/hosts',            [AdminController::class, 'hosts']);
$router->post('/admin/hosts',            [AdminController::class, 'storeHost']);
$router->post('/admin/hosts/:id/delete', [AdminController::class, 'deleteHost']);

// ── Admin — reasons ──────────────────────────────────────────────
$router->get( '/admin/reasons',            [AdminController::class, 'reasons']);
$router->post('/admin/reasons',            [AdminController::class, 'storeReason']);
$router->post('/admin/reasons/:id/delete', [AdminController::class, 'deleteReason']);

// ── Admin — custom fields ────────────────────────────────────────
$router->get( '/admin/fields',            [AdminController::class, 'fields']);
$router->post('/admin/fields',            [AdminController::class, 'storeField']);
$router->post('/admin/fields/:id/delete', [AdminController::class, 'deleteField']);

// ── Admin — CSV import ───────────────────────────────────────────
$router->get( '/admin/import',          [ImportController::class, 'index']);
$router->post('/admin/import/hosts',    [ImportController::class, 'importHosts']);
$router->post('/admin/import/reasons',  [ImportController::class, 'importReasons']);
$router->post('/admin/import/visitors', [ImportController::class, 'importVisitors']);

// ── User management ───────────────────────────────────────────────
$router->get( '/admin/users',                  [UserController::class, 'index']);
$router->get( '/admin/users/new',              [UserController::class, 'create']);
$router->post('/admin/users',                  [UserController::class, 'store']);
$router->post('/admin/users/ldap-mode',        [UserController::class, 'saveLdapMode']);
$router->get( '/admin/users/:id/edit',         [UserController::class, 'edit']);
$router->post('/admin/users/:id',              [UserController::class, 'update']);
$router->post('/admin/users/:id/deactivate',   [UserController::class, 'deactivate']);
$router->post('/admin/users/:id/reactivate',   [UserController::class, 'reactivate']);

// ── Admin — docs ─────────────────────────────────────────────────
$router->get('/admin/docs',             [AdminController::class, 'docsIndex']);
$router->get('/admin/docs/search',      [AdminController::class, 'docsSearch']);
$router->get('/admin/docs/userguide',      [AdminController::class, 'userGuide']);
$router->get('/admin/docs/installguide',   [AdminController::class, 'installGuide']);
$router->get('/admin/docs/:page',       [AdminController::class, 'docs']);

// ── Admin — settings ─────────────────────────────────────────────
$router->get( '/admin/settings',               [SettingsController::class, 'index']);
$router->post('/admin/settings',               [SettingsController::class, 'save']);
$router->post('/admin/settings/auto-checkout', [SettingsController::class, 'saveAutoCheckout']);

// ── Guided Setup (timeline) ──────────────────────────────────────
// Specific GET routes must come before generic :stage
$router->get( '/admin/setup',                          [SetupController::class, 'index']);
$router->get( '/admin/setup/template/:stage',          [SetupController::class, 'downloadTemplate']);
$router->get( '/admin/setup/:stage',                   [SetupController::class, 'stage']);

// POST — specific routes before the generic upload catch-all
$router->post('/admin/setup/organization/save',        [SetupController::class, 'saveOrg']);
$router->post('/admin/setup/departments/save',         [SetupController::class, 'saveDepartment']);
$router->post('/admin/setup/departments/upload',       [SetupController::class, 'uploadDepartments']);
$router->post('/admin/setup/departments/:id/delete',   [SetupController::class, 'deleteDepartment']);
$router->post('/admin/setup/hosts/save',               [SetupController::class, 'saveHost']);
$router->post('/admin/setup/hosts/:id/delete',         [SetupController::class, 'deleteHost']);
$router->post('/admin/setup/reasons/save',             [SetupController::class, 'saveReason']);
$router->post('/admin/setup/reasons/:id/delete',       [SetupController::class, 'deleteReason']);
$router->post('/admin/setup/fields/save',              [SetupController::class, 'saveField']);
$router->post('/admin/setup/fields/:id/delete',        [SetupController::class, 'deleteField']);
$router->post('/admin/setup/notifications/save',       [SetupController::class, 'saveNotification']);
$router->post('/admin/setup/notifications/:id/delete', [SetupController::class, 'deleteNotification']);
$router->post('/admin/setup/kiosk/save',               [SetupController::class, 'saveKioskSettings']);
$router->post('/admin/setup/auth/test-ldap',           [SetupController::class, 'testLdapConnection']);
$router->post('/admin/setup/auth/save',                [SetupController::class, 'saveAuthProviders']);
$router->post('/admin/setup/users/save',               [SetupController::class, 'saveUser']);
$router->post('/admin/setup/users/upload',             [SetupController::class, 'uploadUsers']);
$router->post('/admin/setup/users/:id/delete',         [SetupController::class, 'deleteUser']);
// Generic CSV upload for hosts, reasons, fields (stage derived from :stage param)
$router->post('/admin/setup/:stage/upload',            [SetupController::class, 'uploadCSV']);
