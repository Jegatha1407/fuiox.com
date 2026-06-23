

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\EmbeddedSignupController;
use App\Http\Controllers\MessengerController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\AppsController;
use App\Http\Controllers\AppointmentsController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AutomationController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BulkTemplateController;
use App\Http\Controllers\FlowController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReviewController;


Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');



Route::post('/reviews', 
[ReviewController::class, 'store'])
->name('reviews.store');


Route::get('/reviews', 
[ReviewController::class, 'index'])
->name('reviews.index');

// Auth
// Channel Webhooks (public - no auth needed)
Route::get('/webhook/messenger',  [MessengerController::class, 'webhook'])->name('webhook.messenger.verify');
Route::post('/webhook/messenger', [MessengerController::class, 'webhook'])->name('webhook.messenger');
Route::get('/webhook/instagram',  [MessengerController::class, 'webhook'])->name('webhook.instagram.verify');
Route::post('/webhook/instagram', [MessengerController::class, 'webhook'])->name('webhook.instagram');
Route::post('/webhook/telegram/{userId}', [MessengerController::class, 'telegramWebhook'])->name('webhook.telegram');

Route::get('/auto-login', function(){
    $userId = session('post_verify_user');
    if(!$userId) return redirect()->route('login');
    $user = App\Models\User::find($userId);
    if(!$user) return redirect()->route('login');
    session()->forget('post_verify_user');
    session(['auth_user' => $user->id, 'auth_role' => $user->role]);
    session()->save();
    return redirect()->route('setup')->with('success', '✅ Email verified! Connect your WhatsApp to get started.');
})->name('auto.login');

Route::get('/login',              [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',             [AuthController::class, 'loginPost'])->name('login.post');
Route::post('/logout',            [AuthController::class, 'logout'])->name('logout');
Route::get('/register',           [AuthController::class, 'showRegister'])->name('register');

Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/otp',                [AuthController::class, 'showOtp'])->name('otp.show');
Route::post('/otp/verify',        [AuthController::class, 'verifyOtp'])->name('otp.verify');
Route::post('/otp/resend',        [AuthController::class, 'resendOtp'])->name('otp.resend');

// Login 2FA OTP
Route::post('/login/send-otp',    [AuthController::class, 'sendLoginOtp'])->name('login.send.otp');
Route::post('/login/verify-otp',  [AuthController::class, 'verifyLoginOtp'])->name('login.verify.otp');

// WhatsApp Webhook
Route::post('/embedded-signup/exchange-token', [EmbeddedSignupController::class, 'exchangeToken'])->name('embedded.exchange');
Route::post('/embedded-signup/fetch-details',  [EmbeddedSignupController::class, 'fetchDetails'])->name('embedded.fetch');
Route::post('/embedded-signup/connect',        [EmbeddedSignupController::class, 'connect'])->name('embedded.connect');
Route::post('/embedded-signup/select-phone',   [EmbeddedSignupController::class, 'selectPhone'])->name('embedded.select.phone');
Route::any('/webhook',            [ChatController::class, 'webhook'])->name('webhook');

  Route::get('/setup',      [UserController::class, 'setup'])->name('setup');
    Route::post('/setup',     [UserController::class, 'updateSetup'])->name('setup.update');
    Route::post('/setup/fetch-from-token', [UserController::class, 'fetchFromToken'])->name('setup.fetch');

    // Channels (Messenger, Instagram, Telegram)
    // AI Chat
    Route::get('/ai/settings',  [AiChatController::class, 'settings'])->name('ai.settings');
    Route::post('/ai/settings', [AiChatController::class, 'saveSettings'])->name('ai.settings.save');
    Route::post('/ai/test',     [AiChatController::class, 'test'])->name('ai.test');
    Route::post('/chat/toggle', [ChatController::class, 'toggleBot'])->name('chat.toggle');
    Route::post('/ai/toggle-global',       [AiChatController::class, 'toggleGlobal'])->name('ai.toggle.global');
    Route::post('/ai/toggle-conversation', [AiChatController::class, 'toggleConversation'])->name('ai.toggle.conversation');
    Route::get('/ai/conversation-state',   [AiChatController::class, 'getConversationState'])->name('ai.conversation.state');

    // Apps
    Route::get('/apps',                    [AppsController::class, 'index'])->name('apps');
    Route::post('/apps/install',           [AppsController::class, 'install'])->name('apps.install');
    Route::post('/apps/deactivate',        [AppsController::class, 'deactivate'])->name('apps.deactivate');
    Route::get('/apps/{appType}/builder',  [AppsController::class, 'builder'])->name('apps.builder');
    Route::post('/apps/{appType}/ai-generate-flow', [App\Http\Controllers\AiFlowController::class, 'generate'])->name('apps.ai.generate');
    Route::post('/flows/ai-generate', [App\Http\Controllers\AiFlowController::class, 'generateOutside'])->name('flows.ai.generate');
    Route::post('/apps/{appType}/flow',    [AppsController::class, 'saveFlow'])->name('apps.flow.save');
    Route::get('/apps/{appType}/flow',     [AppsController::class, 'getFlow'])->name('apps.flow.get');

    // App Resources (Doctors / Tables / Stylists depending on app)
    Route::get('/apps/{appType}/resources',           [AppsController::class, 'resources'])->name('apps.resources');
    Route::post('/apps/{appType}/resources',           [AppsController::class, 'addResource'])->name('apps.resources.add');
    Route::post('/apps/{appType}/resources/{id}',      [AppsController::class, 'updateResource'])->name('apps.resources.update');
    Route::post('/apps/{appType}/resources/{id}/toggle',[AppsController::class, 'toggleResource'])->name('apps.resources.toggle');
    Route::delete('/apps/{appType}/resources/{id}',     [AppsController::class, 'deleteResource'])->name('apps.resources.delete');
    Route::get('/apps/{appType}/resources-list',        [AppsController::class, 'listResources'])->name('apps.resources.list');
    Route::get('/apps/{appType}/access',                 [AppsController::class, 'accessPage'])->name('apps.access');
    Route::post('/apps/{appType}/access/toggle',          [AppsController::class, 'toggleAccess'])->name('apps.access.toggle');
    Route::post('/apps/{appType}/access/permission',       [AppsController::class, 'updatePagePermission'])->name('apps.access.permission');
    Route::post('/apps/{appType}/access/add-employee',     [AppsController::class, 'addEmployee'])->name('apps.access.add');
    Route::post('/apps/{appType}/access/employee/{id}',   [AppsController::class, 'updateEmployee'])->name('apps.access.update');
    Route::delete('/apps/{appType}/access/employee/{id}', [AppsController::class, 'deleteEmployee'])->name('apps.access.delete');
    Route::post('/apps/{appType}/toggle-bot',           [AppsController::class, 'toggleBot'])->name('apps.toggle.bot');

    // Appointments / Bookings
    Route::get('/apps/{appType}/appointments',           [AppointmentsController::class, 'index'])->name('apps.appointments');
    Route::get('/apps/{appType}/available-slots',         [AppointmentsController::class, 'availableSlots'])->name('apps.slots');
    Route::post('/apps/{appType}/book',                   [AppointmentsController::class, 'book'])->name('apps.book');
    Route::post('/apps/{appType}/appointments/{id}/cancel',[AppointmentsController::class, 'cancel'])->name('apps.appointments.cancel');
    Route::post('/apps/{appType}/appointments/{id}/complete',[AppointmentsController::class, 'complete'])->name('apps.appointments.complete');
    Route::get('/apps/{appType}/records',                [AppsController::class, 'records'])->name('apps.records');

    Route::get('/channels',                    [MessengerController::class, 'index'])->name('channels');
    Route::get('/channels/pages',              [MessengerController::class, 'getPages'])->name('channels.pages');
    Route::post('/channels/connect/messenger', [MessengerController::class, 'connectMessenger'])->name('channels.connect.messenger');
    Route::post('/channels/connect/instagram', [MessengerController::class, 'connectInstagram'])->name('channels.connect.instagram');
    Route::post('/channels/connect/telegram',  [MessengerController::class, 'connectTelegram'])->name('channels.connect.telegram');
    Route::post('/channels/send/messenger',    [MessengerController::class, 'sendMessenger'])->name('channels.send.messenger');
    Route::post('/channels/send/telegram',     [MessengerController::class, 'sendTelegram'])->name('channels.send.telegram');
    Route::post('/channels/disconnect',        [MessengerController::class, 'disconnect'])->name('channels.disconnect');

Route::middleware(['auth.custom', 'user.only'])->group(function () {

    // ── Dashboard
    Route::get('/dashboard',  [UserController::class, 'dashboard'])->name('dashboard');

    // ── Setup / Settings
  
    Route::put('/settings/profile', [UserController::class, 'updateSettings'])->name('profile.update');
    Route::get('/settings',   [UserController::class, 'settings'])->name('settings');
    Route::post('/settings',  [UserController::class, 'updateSettings'])->name('settings.update');

    // ── API Docs
    Route::get('/api-docs',   [UserController::class, 'apiDocs'])->name('api.docs');

    // ── API Key
    Route::post('/api/generate-key', [UserController::class, 'generateApiKey'])->name('api.generate.key');

    // ── Chat (page)
    Route::get('/chat',       [ChatController::class, 'chat'])->name('chat');

    // ── Chat (API endpoints)
    Route::get('/chat/users',              [ChatController::class, 'getUsers'])->name('chat.users');
    Route::get('/chat/messages/{phone}',   [ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/send',              [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::post('/chat/send-template',     [ChatController::class, 'sendTemplate'])->name('chat.send.template');
    Route::get('/chat/window/{phone}',     [ChatController::class, 'checkWindow'])->name('chat.window');
    Route::post('/chat/assign',            [ChatController::class, 'assignConversation'])->name('chat.assign');
    Route::get('/chat/wallet-balance',     [ChatController::class, 'walletBalance'])->name('chat.wallet');
    Route::get('/chat/team-members',       [ChatController::class, 'teamMembers'])->name('chat.team.members');
    Route::post('/chat/change-password',   [ChatController::class, 'changePassword'])->name('chat.change.password');
    Route::post('/chat/save-contact',      [ChatController::class, 'saveContact'])->name('chat.save.contact');
    Route::post('/chat/react',             [ChatController::class, 'react'])->name('chat.react');
    Route::delete('/chat/message/{id}',    [ChatController::class, 'deleteMessage'])->name('chat.delete.message');
    Route::get('/chat/profile/{phone}',    [ChatController::class, 'getProfile'])->name('chat.profile');
    Route::post('/chat/forward',           [ChatController::class, 'forwardMessage'])->name('chat.forward');
    Route::post('/chat/upload-media',      [ChatController::class, 'uploadMedia'])->name('chat.upload.media');
    Route::get('/chat/media/{mediaId}',    [ChatController::class, 'getMedia'])->name('chat.media');
    Route::get('/chat/last-update',           [ChatController::class, 'lastUpdate'])->name('chat.last.update');

    // ── Contacts (page)
    Route::get('/contacts',              [ContactController::class, 'contacts'])->name('contacts');

    // ── Contacts (API)
    Route::get('/contacts/stats',        [ContactController::class, 'stats'])->name('contacts.stats');
    Route::get('/contacts/list',         [ContactController::class, 'list'])->name('contacts.list');
    Route::get('/contacts/groups',       [ContactController::class, 'groups'])->name('contacts.groups');
    Route::get('/contacts/export',       [ContactController::class, 'export'])->name('contacts.export');
    Route::post('/contacts/import',      [ContactController::class, 'import'])->name('contacts.import');
    Route::post('/contacts',             [ContactController::class, 'store'])->name('contacts.store');
    Route::put('/contacts/{id}',         [ContactController::class, 'update'])->name('contacts.update');
    Route::delete('/contacts/{id}',      [ContactController::class, 'destroy'])->name('contacts.destroy');

    // ── Campaigns (page)
    Route::get('/campaigns',             [CampaignController::class, 'campaigns'])->name('campaigns');

    // ── Campaigns (API)
    Route::get('/campaigns/stats',       [CampaignController::class, 'stats'])->name('campaigns.stats');
    Route::get('/campaigns/list',        [CampaignController::class, 'list'])->name('campaigns.list');
    Route::post('/campaigns',            [CampaignController::class, 'store'])->name('campaigns.store');
    Route::delete('/campaigns/{id}',     [CampaignController::class, 'destroy'])->name('campaigns.destroy');

    // ── Bulk Send
    Route::get('/bulk-template',          [BulkTemplateController::class, 'index'])->name('bulk.template');
    Route::post('/bulk-template/preview', [BulkTemplateController::class, 'preview'])->name('bulk.template.preview');
    Route::post('/bulk-template/send',    [BulkTemplateController::class, 'send'])->name('bulk.template.send');

    // ── Templates (page + API)
    Route::get('/templates',                  [TemplateController::class, 'manager'])->name('templates.manager');
    Route::get('/api/templates/list',         [TemplateController::class, 'listAll'])->name('api.templates.list');
    Route::get('/templates/meta',            [TemplateController::class, 'getMetaTemplates'])->name('templates.meta');
    Route::post('/api/templates/create',      [TemplateController::class, 'createTemplate'])->name('api.templates.create');
    Route::post('/api/templates/upload-media',  [TemplateController::class, 'uploadMedia'])->name('api.templates.upload.media');
    Route::delete('/api/templates/{name}',    [TemplateController::class, 'deleteTemplate'])->name('api.templates.delete');

    // ── Reports (page)
    Route::get('/reports',  [ReportController::class, 'reports'])->name('reports');

    // ── Team (page)
    Route::get('/team',                 [TeamController::class, 'team'])->name('team');
    Route::get('/team/dashboard',       [TeamController::class, 'teamDashboard'])->name('team.dashboard');
    Route::get('/team/change-password', [TeamController::class, 'changePasswordPage'])->name('team.change.password');
    Route::post('/team/change-password',[TeamController::class, 'changePassword'])->name('team.change.password.post');

    // ── Team (API)
    Route::get('/team/stats',       [TeamController::class, 'stats'])->name('team.stats');
    Route::get('/team/list',        [TeamController::class, 'list'])->name('team.list');
    Route::post('/team',            [TeamController::class, 'store'])->name('team.store');
    Route::put('/team/{id}',        [TeamController::class, 'update'])->name('team.update');
    Route::delete('/team/{id}',     [TeamController::class, 'destroy'])->name('team.destroy');

    // ── Flow Builder (page)
    Route::get('/flows/builder',    [FlowController::class, 'builder'])->name('flows.builder');

    // ── Flows (API)
    Route::get('/flows',            [FlowController::class, 'index'])->name('flows.index');
    Route::post('/flows',           [FlowController::class, 'store'])->name('flows.store');
    Route::put('/flows/{id}',       [FlowController::class, 'update'])->name('flows.update');
    Route::delete('/flows/{id}',    [FlowController::class, 'destroy'])->name('flows.destroy');

    // ── Automation (page)
    Route::get('/automation',               [AutomationController::class, 'automation'])->name('automation');

    // ── Automation (API)
    Route::get('/automations/stats',        [AutomationController::class, 'stats'])->name('automations.stats');
    Route::get('/automations/list',         [AutomationController::class, 'list'])->name('automations.list');
    Route::post('/automations',             [AutomationController::class, 'store'])->name('automations.store');
    Route::put('/automations/{id}',         [AutomationController::class, 'update'])->name('automations.update');
    Route::delete('/automations/{id}',      [AutomationController::class, 'destroy'])->name('automations.destroy');
    Route::post('/automations/{id}/toggle', [AutomationController::class, 'toggle'])->name('automations.toggle');

    // ── Billing (page)
    Route::get('/billing',                  [BillingController::class, 'billing'])->name('billing');

    // ── Billing (API)
    Route::get('/billing/plans',            [BillingController::class, 'plans'])->name('billing.plans');
    Route::get('/billing/current',          [BillingController::class, 'current'])->name('billing.current');
    Route::get('/billing/invoices',         [BillingController::class, 'invoices'])->name('billing.invoices');
    Route::post('/billing/create-order',    [BillingController::class, 'createOrder'])->name('billing.create.order');
    Route::post('/billing/verify-payment',  [BillingController::class, 'verifyPayment'])->name('billing.verify');
    Route::post('/billing/cancel',          [BillingController::class, 'cancel'])->name('billing.cancel');

    // ── Notifications (API)
    Route::get('/notifications',              [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count',        [NotificationController::class, 'count'])->name('notifications.count');
    Route::post('/notifications/{id}/read',   [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all',    [NotificationController::class, 'markAllRead'])->name('notifications.read.all');

    // ── Agent
    Route::get('/agent/dashboard', [TeamController::class, 'teamDashboard'])->name('agent.dashboard');
    Route::get('/agent/password',  [TeamController::class, 'changePasswordPage'])->name('agent.password');
    Route::post('/agent/password', [TeamController::class, 'changePassword'])->name('agent.password.post');

    // ── Credential Requests (user side)
    Route::get('/credential-request',       [UserController::class, 'credentialRequest'])->name('credential.request');
    Route::post('/credential-request',      [UserController::class, 'submitCredentialRequest'])->name('credential.request.submit');
});

// APP EMPLOYEES
Route::middleware(['auth.custom'])->group(function () {
    Route::get('/app-employee/{appType}/dashboard',    [App\Http\Controllers\AppEmployeeController::class, 'dashboard'])->name('apps.employee.dashboard');
    Route::get('/app-employee/{appType}/flow-builder', [App\Http\Controllers\AppEmployeeController::class, 'flowBuilder'])->name('apps.employee.flow-builder');
    Route::get('/app-employee/{appType}/resources',    [App\Http\Controllers\AppEmployeeController::class, 'resources'])->name('apps.employee.resources');
    Route::get('/app-employee/{appType}/records',      [App\Http\Controllers\AppEmployeeController::class, 'records'])->name('apps.employee.records');
    Route::get('/app-employee/change-password',        [App\Http\Controllers\AppEmployeeController::class, 'changePassword'])->name('apps.employee.change-password');
    Route::post('/app-employee/change-password',       [App\Http\Controllers\AppEmployeeController::class, 'updatePassword'])->name('apps.employee.update-password');
    Route::get('/app-employee/no-access',              [App\Http\Controllers\AppEmployeeController::class, 'noAccess'])->name('apps.employee.no-access');
});



// ── ADMIN ─────────────────────────────────────────────────────────────────────
Route::middleware(['auth.custom', 'admin.only'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    // ── Dashboard
    Route::get('/dashboard',    [AdminController::class, 'dashboard'])->name('dashboard');

    // ── Credential Requests
    Route::get('/credential-requests',               [AdminController::class, 'credentialRequests'])->name('credential.requests');
    Route::post('/credential-requests/{id}/accept',  [AdminController::class, 'acceptRequest'])->name('credential.accept');
    Route::post('/credential-requests/{id}/reject',  [AdminController::class, 'rejectRequest'])->name('credential.reject');

    // ── User Management
    Route::get('/users/{id}/edit',   [AdminController::class, 'editUser'])->name('users.edit');
    Route::post('/users/{id}/update',[AdminController::class, 'updateUser'])->name('users.update');
    Route::post('/users/{id}/trial', [AdminController::class, 'toggleTrial'])->name('users.trial');
    Route::post('/users/{id}/active',[AdminController::class, 'toggleActive'])->name('users.active');
    Route::post('/users/{id}/block', [AdminController::class, 'toggleBlock'])->name('users.block');

    // ── Packages
    Route::get('/packages',              [AdminController::class, 'packages'])->name('packages');
    Route::post('/packages/store',       [AdminController::class, 'storePackage'])->name('packages.store');
    Route::post('/packages/{id}/toggle', [AdminController::class, 'togglePackage'])->name('packages.toggle');
    Route::post('/packages/{id}/delete', [AdminController::class, 'deletePackage'])->name('packages.delete');
});
// Legal pages
Route::get('/privacy', fn() => view('legal.privacy'))->name('privacy');
Route::get('/terms', fn() => view('legal.terms'))->name('terms');
Route::get('/data-deletion', fn() => view('legal.data-deletion'))->name('data.deletion');

