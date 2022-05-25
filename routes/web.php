<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::post('/consent/remove-lead-request', ['uses' => 'PublicLeadGdprController@removeLeadRequest'])->name('front.gdpr.remove-lead-request');
Route::post('/consent/l/update/{lead}', ['uses' => 'PublicLeadGdprController@updateConsent'])->name('front.gdpr.consent.update');
Route::post('/consent/l/update/{lead}', ['uses' => 'PublicLeadGdprController@updateConsent'])->name('front.gdpr.consent.update');
Route::get('/consent/l/{lead}', ['uses' => 'PublicLeadGdprController@consent'])->name('front.gdpr.consent');
Route::post('/forms/l/update/{lead}', ['uses' => 'PublicLeadGdprController@updateLead'])->name('front.gdpr.lead.update');
Route::get('/forms/l/{lead}', ['uses' => 'PublicLeadGdprController@lead'])->name('front.gdpr.lead');

Route::get('/invoice/{id}', ['uses' => 'HomeController@invoice'])->name('front.invoice');
Route::get('/', ['uses' => 'HomeController@login']);

// Paypal IPN
Route::post('verify-ipn', array('as' => 'verify-ipn','uses' => 'PaypalIPNController@verifyIPN'));
Route::post('/verify-webhook', ['as' => 'verify-webhook', 'uses' => 'StripeWebhookController@verifyStripeWebhook']);

Route::group(
    ['namespace' => 'Client', 'prefix' => 'client', 'as' => 'client.'], function () {

    Route::post('stripe/{invoiceId}', array('as' => 'stripe', 'uses' => 'StripeController@paymentWithStripe',));
    Route::post('stripe-public/{invoiceId}', array('as' => 'stripe-public', 'uses' => 'StripeController@paymentWithStripePublic',));
    // route for post request
    Route::get('paypal-public/{invoiceId}', array('as' => 'paypal-public', 'uses' => 'PaypalController@paymentWithpaypalPublic',));
    Route::get('paypal/{invoiceId}', array('as' => 'paypal', 'uses' => 'PaypalController@paymentWithpaypal',));
    // route for check status responce
    Route::get('paypal', array('as' => 'status', 'uses' => 'PaypalController@getPaymentStatus',));
    Route::get('paypal-recurring', array('as' => 'paypal-recurring','uses' => 'PaypalController@payWithPaypalRecurrring',));

    Route::post('pay-with-razorpay', array('as' => 'pay-with-razorpay','uses' => 'RazorPayController@payWithRazorPay',));
});

Auth::routes();

Route::group(['middleware' => 'auth'], function () {

    // Admin routes
    Route::group(
        ['namespace' => 'Admin', 'as' => 'admin.', 'middleware' => ['role:admin|employee']], function () {

        Route::get('/dashboard', 'AdminDashboardController@index')->name('dashboard');
        Route::post('/dashboard/widget', 'AdminDashboardController@widget')->name('dashboard.widget');
        Route::post('/dashboard/period', 'AdminDashboardController@period')->name('dashboard.period');
        Route::resource('profile', 'EmployeeProfileController');

        Route::get('leads/gdpr/{leadID}', ['uses' => 'LeadController@gdpr'])->name('leads.gdpr');
        Route::get('leads/export/{followUp?}/{client?}', ['uses' => 'LeadController@export'])->name('leads.export');
        Route::get('leads/data', ['uses' => 'LeadController@data'])->name('leads.data');
        Route::post('leads/change-status', ['uses' => 'LeadController@changeStatus'])->name('leads.change-status');
        Route::get('leads/follow-up/{leadID}', ['uses' => 'LeadController@followUpCreate'])->name('leads.follow-up');
        Route::get('leads/followup/{leadID}', ['uses' => 'LeadController@followUpShow'])->name('leads.followup');
        Route::post('leads/follow-up-store', ['uses' => 'LeadController@followUpStore'])->name('leads.follow-up-store');
        Route::get('leads/follow-up-edit/{id?}', ['uses' => 'LeadController@editFollow'])->name('leads.follow-up-edit');
        Route::post('leads/follow-up-update', ['uses' => 'LeadController@UpdateFollow'])->name('leads.follow-up-update');
        Route::get('leads/follow-up-sort', ['uses' => 'LeadController@followUpSort'])->name('leads.follow-up-sort');
        Route::post('leads/save-consent-purpose-data/{lead}', ['uses' => 'LeadController@saveConsentLeadData'])->name('leads.save-consent-purpose-data');
        Route::get('leads/consent-purpose-data/{lead}', ['uses' => 'LeadController@consentPurposeData'])->name('leads.consent-purpose-data');
        Route::post('leads/detail/{leadID}', ['uses' => 'LeadController@getDetail'])->name('leads.getdetail');
        Route::get('leads/create/{clientID?}', ['uses' => 'LeadController@create'])->name('leads.create');
        Route::resource('leads', 'LeadController');

        Route::post('leadSource/store-src', ['uses' => 'LeadSourceController@storeSrc'])->name('leadSource.store-src');
        Route::get('leadSource/create-src', ['uses' => 'LeadSourceController@createSrc'])->name('leadSource.create-src');
        Route::post('leadSource/update-src/{id}', ['uses' => 'LeadSourceController@updateSrc'])->name('leadSource.update-src');
        Route::get('leadSource/edit-src/{id}', ['uses' => 'LeadSourceController@editSrc'])->name('leadSource.edit-src');
        Route::resource('leadSource', 'LeadSourceController');

        Route::post('interest-area/store-area', ['uses' => 'InterestAreaController@storeArea'])->name('interest-area.store-area');
        Route::get('interest-area/create-area', ['uses' => 'InterestAreaController@createArea'])->name('interest-area.create-area');
        Route::post('interest-area/update-area/{id}', ['uses' => 'InterestAreaController@updateArea'])->name('interest-area.update-area');
        Route::get('interest-area/edit-area/{id}', ['uses' => 'InterestAreaController@editArea'])->name('interest-area.edit-area');
        Route::resource('interest-area', 'InterestAreaController');

        Route::get('note/lead/create-note/{id}', ['uses' => 'NoteController@createLeadNote'])->name('note.create-lead-note');
        Route::get('note/client/create-note/{id}', ['uses' => 'NoteController@createClientNote'])->name('note.create-client-note');
        Route::post('note/store-note', ['uses' => 'NoteController@storeNote'])->name('note.store-note');
        Route::resource('note', 'NoteController');


        // Lead Files
        Route::get('lead-files/download/{id}', ['uses' => 'LeadFilesController@download'])->name('lead-files.download');
        Route::get('lead-files/thumbnail', ['uses' => 'LeadFilesController@thumbnailShow'])->name('lead-files.thumbnail');
        Route::resource('lead-files', 'LeadFilesController');

        // Proposal routes
        Route::get('proposals/data/{id?}', ['uses' => 'ProposalController@data'])->name('proposals.data');
        Route::get('proposals/download/{id}', ['uses' => 'ProposalController@download'])->name('proposals.download');
        Route::get('proposals/create/{leadID?}', ['uses' => 'ProposalController@create'])->name('proposals.create');
        Route::resource('proposals', 'ProposalController' , ['expect' => ['create']]);

        // Holidays
        Route::get('holidays/calendar-month', 'HolidaysController@getCalendarMonth')->name('holidays.calendar-month');
        Route::get('holidays/view-holiday/{year?}', 'HolidaysController@viewHoliday')->name('holidays.view-holiday');
        Route::get('holidays/mark_sunday', 'HolidaysController@Sunday')->name('holidays.mark-sunday');
        Route::get('holidays/calendar/{year?}', 'HolidaysController@holidayCalendar')->name('holidays.calendar');
        Route::get('holidays/mark-holiday', 'HolidaysController@markHoliday')->name('holidays.mark-holiday');
        Route::post('holidays/mark-holiday-store', 'HolidaysController@markDayHoliday')->name('holidays.mark-holiday-store');
        Route::resource('holidays', 'HolidaysController');

        Route::group(
            ['prefix' => 'employees'], function () {

            Route::get('employees/free-employees', ['uses' => 'ManageEmployeesController@freeEmployees'])->name('employees.freeEmployees');
            Route::get('employees/docs-create/{id}', ['uses' => 'ManageEmployeesController@docsCreate'])->name('employees.docs-create');
            Route::get('employees/tasks/{userId}/{hideCompleted}', ['uses' => 'ManageEmployeesController@tasks'])->name('employees.tasks');
            Route::get('employees/time-logs/{userId}', ['uses' => 'ManageEmployeesController@timeLogs'])->name('employees.time-logs');
            Route::get('employees/data', ['uses' => 'ManageEmployeesController@data'])->name('employees.data');
            Route::get('employees/export/{status?}/{employee?}/{role?}', ['uses' => 'ManageEmployeesController@export'])->name('employees.export');
            Route::post('employees/assignRole', ['uses' => 'ManageEmployeesController@assignRole'])->name('employees.assignRole');
            Route::post('employees/assignProjectAdmin', ['uses' => 'ManageEmployeesController@assignProjectAdmin'])->name('employees.assignProjectAdmin');
            Route::resource('employees', 'ManageEmployeesController');

            Route::get('department/quick-create', ['uses' => 'ManageTeamsController@quickCreate'])->name('department.quick-create');
            Route::post('department/quick-store', ['uses' => 'ManageTeamsController@quickStore'])->name('department.quick-store');
            Route::resource('department', 'ManageTeamsController');

            Route::get('designations/quick-create', ['uses' => 'ManageDesignationController@quickCreate'])->name('designations.quick-create');
            Route::post('designations/quick-store', ['uses' => 'ManageDesignationController@quickStore'])->name('designations.quick-store');
            Route::resource('designations', 'ManageDesignationController');

            Route::resource('employee-teams', 'ManageEmployeeTeamsController');

            Route::get('employee-docs/download/{id}', ['uses' => 'EmployeeDocsController@download'])->name('employee-docs.download');
            Route::resource('employee-docs', 'EmployeeDocsController');
        });

        Route::post('projects/gantt-task-update/{id}', ['uses' => 'ManageProjectsController@updateTaskDuration'])->name('projects.gantt-task-update');
        Route::get('projects/ajaxCreate/{columnId}', ['uses' => 'ManageProjectsController@ajaxCreate'])->name('projects.ajaxCreate');
        Route::get('projects/archive-data', ['uses' => 'ManageProjectsController@archiveData'])->name('projects.archive-data');
        Route::get('projects/archive', ['uses' => 'ManageProjectsController@archive'])->name('projects.archive');
        Route::get('projects/archive-restore/{id?}', ['uses' => 'ManageProjectsController@archiveRestore'])->name('projects.archive-restore');
        Route::get('projects/archive-delete/{id?}', ['uses' => 'ManageProjectsController@archiveDestroy'])->name('projects.archive-delete');
        Route::get('projects/export/{status?}/{clientID?}', ['uses' => 'ManageProjectsController@export'])->name('projects.export');
        Route::get('projects/data', ['uses' => 'ManageProjectsController@data'])->name('projects.data');
        Route::get('projects/ganttData', ['uses' => 'ManageProjectsController@ganttData'])->name('projects.ganttData');
        Route::get('projects/gantt', ['uses' => 'ManageProjectsController@gantt'])->name('projects.gantt');
        Route::post('projects/updateStatus/{id}', ['uses' => 'ManageProjectsController@updateStatus'])->name('projects.updateStatus');
        Route::post('projects/detail/{id}', ['uses' => 'ManageProjectsController@getDetail'])->name('projects.getdetail');
        Route::get('projects/create-lead/{id}', ['uses' => 'ManageProjectsController@createFromLead'])->name('projects.create-lead');
        Route::get('projects/create-client/{id}', ['uses' => 'ManageProjectsController@createFromClient'])->name('projects.create-client');
        Route::resource('projects', 'ManageProjectsController');

        Route::get('project-template/data', ['uses' => 'ProjectTemplateController@data'])->name('project-template.data');
        Route::get('project-template-task/detail/{id?}', ['uses' => 'ProjectTemplateTaskController@taskDetail'])->name('project-template-task.detail');
        Route::resource('project-template', 'ProjectTemplateController');

        Route::post('project-template-members/save-group', ['uses' => 'ProjectMemberTemplateController@storeGroup'])->name('project-template-members.storeGroup');
        Route::resource('project-template-member', 'ProjectMemberTemplateController');

        Route::get('project-template-task/data/{templateId?}', ['uses' => 'ProjectTemplateTaskController@data'])->name('project-template-task.data');
        Route::resource('project-template-task', 'ProjectTemplateTaskController');

        Route::post('projectCategory/store-cat', ['uses' => 'ManageProjectCategoryController@storeCat'])->name('projectCategory.store-cat');
        Route::get('projectCategory/create-cat', ['uses' => 'ManageProjectCategoryController@createCat'])->name('projectCategory.create-cat');
        Route::resource('projectCategory', 'ManageProjectCategoryController');

        Route::post('taskCategory/store-cat', ['uses' => 'ManageTaskCategoryController@storeCat'])->name('taskCategory.store-cat');
        Route::get('taskCategory/create-cat', ['uses' => 'ManageTaskCategoryController@createCat'])->name('taskCategory.create-cat');
        Route::resource('taskCategory', 'ManageTaskCategoryController');

        Route::get('notices/data', ['uses' => 'ManageNoticesController@data'])->name('notices.data');
        Route::get('notices/export/{startDate}/{endDate}', ['uses' => 'ManageNoticesController@export'])->name('notices.export');
        Route::resource('notices', 'ManageNoticesController');

        Route::get('settings/change-language', ['uses' => 'OrganisationSettingsController@changeLanguage'])->name('settings.change-language');
        Route::resource('settings', 'OrganisationSettingsController', ['only' => ['edit', 'update', 'index', 'change-language']]);
        Route::group(
            ['prefix' => 'settings'], function () {
            Route::get('report-settings/client/admin', ['uses' => 'ReportSettingsController@clientAdminSetting'])->name('report-settings.client-admin');
            Route::get('report-settings/client/employee', ['uses' => 'ReportSettingsController@clientEmployeeSetting'])->name('report-settings.client-employee');
            Route::get('report-settings/client/designer', ['uses' => 'ReportSettingsController@clientDesignerSetting'])->name('report-settings.client-designer');
            Route::get('report-settings/lead/admin', ['uses' => 'ReportSettingsController@leadAdminSetting'])->name('report-settings.lead-admin');
            Route::get('report-settings/lead/employee', ['uses' => 'ReportSettingsController@leadEmployeeSetting'])->name('report-settings.lead-employee');
            Route::get('report-settings/lead/designer', ['uses' => 'ReportSettingsController@leadDesignerSetting'])->name('report-settings.lead-designer');
            Route::get('report-settings/project/admin', ['uses' => 'ReportSettingsController@projectAdminSetting'])->name('report-settings.project-admin');
            Route::get('report-settings/project/employee', ['uses' => 'ReportSettingsController@projectEmployeeSetting'])->name('report-settings.project-employee');
            Route::get('report-settings/project/designer', ['uses' => 'ReportSettingsController@projectDesignerSetting'])->name('report-settings.project-designer');
            Route::get('report-settings/appointment/admin', ['uses' => 'ReportSettingsController@appointmentAdminSetting'])->name('report-settings.appointment-admin');
            Route::get('report-settings/appointment/employee', ['uses' => 'ReportSettingsController@appointmentEmployeeSetting'])->name('report-settings.appointment-employee');
            Route::get('report-settings/appointment/designer', ['uses' => 'ReportSettingsController@appointmentDesignerSetting'])->name('report-settings.appointment-designer');
            Route::get('report-settings/payment/admin', ['uses' => 'ReportSettingsController@paymentAdminSetting'])->name('report-settings.payment-admin');
            Route::get('report-settings/payment/employee', ['uses' => 'ReportSettingsController@paymentEmployeeSetting'])->name('report-settings.payment-employee');
            Route::get('report-settings/payment/designer', ['uses' => 'ReportSettingsController@paymentDesignerSetting'])->name('report-settings.payment-designer');
            Route::get('report-settings/commission/admin', ['uses' => 'ReportSettingsController@commissionAdminSetting'])->name('report-settings.commission-admin');
            Route::get('report-settings/commission/employee', ['uses' => 'ReportSettingsController@commissionEmployeeSetting'])->name('report-settings.commission-employee');
            Route::get('report-settings/commission/designer', ['uses' => 'ReportSettingsController@commissionDesignerSetting'])->name('report-settings.commission-designer');
            Route::get('report-settings/install-schedule/admin', ['uses' => 'ReportSettingsController@installScheduleAdminSetting'])->name('report-settings.install-schedule-admin');
            Route::get('report-settings/install-schedule/employee', ['uses' => 'ReportSettingsController@installScheduleEmployeeSetting'])->name('report-settings.install-schedule-employee');
            Route::get('report-settings/install-schedule/designer', ['uses' => 'ReportSettingsController@installScheduleDesignerSetting'])->name('report-settings.install-schedule-designer');

            Route::resource('report-settings', 'ReportSettingsController');
            Route::get('email-settings/sent-test-email', ['uses' => 'EmailNotificationSettingController@sendTestEmail'])->name('email-settings.sendTestEmail');
            Route::post('email-settings/updateMailConfig', ['uses' => 'EmailNotificationSettingController@updateMailConfig'])->name('email-settings.updateMailConfig');
            Route::resource('email-settings', 'EmailNotificationSettingController');
            Route::resource('profile-settings', 'AdminProfileSettingsController');
            Route::resource('project-settings', 'ProjectSettingController');

            Route::get('currency/exchange-key', ['uses' => 'CurrencySettingController@currencyExchangeKey'])->name('currency.exchange-key');
            Route::post('currency/exchange-key-store', ['uses' => 'CurrencySettingController@currencyExchangeKeyStore'])->name('currency.exchange-key-store');
            Route::resource('currency', 'CurrencySettingController');
            Route::get('currency/exchange-rate/{currency}', ['uses' => 'CurrencySettingController@exchangeRate'])->name('currency.exchange-rate');
            Route::get('currency/update/exchange-rates', ['uses' => 'CurrencySettingController@updateExchangeRate'])->name('currency.update-exchange-rates');
            Route::resource('currency', 'CurrencySettingController');


            Route::post('theme-settings/activeTheme', ['uses' => 'ThemeSettingsController@activeTheme'])->name('theme-settings.activeTheme');
            Route::post('theme-settings/roundedTheme', ['uses' => 'ThemeSettingsController@roundedTheme'])->name('theme-settings.roundedTheme');
            Route::resource('theme-settings', 'ThemeSettingsController');


            // Log time
            Route::resource('log-time-settings', 'LogTimeSettingsController');

            Route::resource('task-settings', 'TaskSettingsController',  ['only' => ['index', 'store']]);

            Route::resource('payment-gateway-credential', 'PaymentGatewayCredentialController');
            Route::resource('invoice-settings', 'InvoiceSettingController');

            Route::get('slack-settings/sendTestNotification', ['uses' => 'SlackSettingController@sendTestNotification'])->name('slack-settings.sendTestNotification');
            Route::post('slack-settings/updateSlackNotification/{id}', ['uses' => 'SlackSettingController@updateSlackNotification'])->name('slack-settings.updateSlackNotification');
            Route::resource('slack-settings', 'SlackSettingController');

            Route::get('push-notification-settings/sendTestNotification', ['uses' => 'PushNotificationController@sendTestNotification'])->name('push-notification-settings.sendTestNotification');
            Route::post('push-notification-settings/updatePushNotification/{id}', ['uses' => 'PushNotificationController@updatePushNotification'])->name('push-notification-settings.updatePushNotification');
            Route::resource('push-notification-settings', 'PushNotificationController');

            Route::post('update-settings/deleteFile', ['uses' => 'UpdateDatabaseController@deleteFile'])->name('update-settings.deleteFile');
            Route::get('update-settings/install', ['uses' => 'UpdateDatabaseController@install'])->name('update-settings.install');
            Route::get('update-settings/manual-update', ['uses' => 'UpdateDatabaseController@manual'])->name('update-settings.manual');
            Route::resource('update-settings', 'UpdateDatabaseController');

            Route::post('ticket-agents/update-group/{id}', ['uses' => 'TicketAgentsController@updateGroup'])->name('ticket-agents.update-group');
            Route::resource('ticket-agents', 'TicketAgentsController');
            Route::resource('ticket-groups', 'TicketGroupsController');

            Route::get('ticketTypes/createModal', ['uses' => 'TicketTypesController@createModal'])->name('ticketTypes.createModal');
            Route::resource('ticketTypes', 'TicketTypesController');

            Route::get('lead-source-settings/createModal', ['uses' => 'LeadSourceSettingController@createModal'])->name('leadSetting.createModal');
            Route::resource('lead-source-settings', 'LeadSourceSettingController');

            Route::get('lead-status-settings/createModal', ['uses' => 'LeadStatusSettingController@createModal'])->name('leadSetting.createModal');
            Route::resource('lead-status-settings', 'LeadStatusSettingController');

            Route::get('offline-payment-setting/createModal', ['uses' => 'OfflinePaymentSettingController@createModal'])->name('offline-payment-setting.createModal');
            Route::resource('offline-payment-setting', 'OfflinePaymentSettingController');

            Route::get('ticketChannels/createModal', ['uses' => 'TicketChannelsController@createModal'])->name('ticketChannels.createModal');
            Route::resource('ticketChannels', 'TicketChannelsController');

            Route::post('replyTemplates/fetch-template', ['uses' => 'TicketReplyTemplatesController@fetchTemplate'])->name('replyTemplates.fetchTemplate');
            Route::resource('replyTemplates', 'TicketReplyTemplatesController');

            Route::resource('attendance-settings', 'AttendanceSettingController');

            Route::resource('leaves-settings', 'LeavesSettingController');

            Route::get('data', ['uses' => 'AdminCustomFieldsController@getFields'])->name('custom-fields.data');
            Route::resource('custom-fields', 'AdminCustomFieldsController');

            // Message settings
            Route::resource('message-settings', 'MessageSettingsController');

            // Storage settings
            Route::resource('storage-settings', 'StorageSettingsController');

            // Storage settings
            Route::post('language-settings/update-data/{id?}', ['uses' => 'LanguageSettingsController@updateData'])->name('language-settings.update-data');
            Route::resource('language-settings', 'LanguageSettingsController');

            // Module settings
            Route::resource('module-settings', 'ModuleSettingsController');


            Route::get('gdpr/lead/approve-reject/{id}/{type}', ['uses' => 'GdprSettingsController@approveRejectLead'])->name('gdpr.lead.approve-reject');
            Route::get('gdpr/approve-reject/{id}/{type}', ['uses' => 'GdprSettingsController@approveReject'])->name('gdpr.approve-reject');

            Route::get('gdpr/lead/removal-data', ['uses' => 'GdprSettingsController@removalLeadData'])->name('gdpr.lead.removal-data');
            Route::get('gdpr/removal-data', ['uses' => 'GdprSettingsController@removalData'])->name('gdpr.removal-data');
            Route::put('gdpr/update-consent/{id}', ['uses' => 'GdprSettingsController@updateConsent'])->name('gdpr.update-consent');
            Route::get('gdpr/edit-consent/{id}', ['uses' => 'GdprSettingsController@editConsent'])->name('gdpr.edit-consent');
            Route::delete('gdpr/purpose-delete/{id}', ['uses' => 'GdprSettingsController@purposeDelete'])->name('gdpr.purpose-delete');
            Route::get('gdpr/consent-data', ['uses' => 'GdprSettingsController@data'])->name('gdpr.purpose-data');
            Route::post('gdpr/store-consent', ['uses' => 'GdprSettingsController@storeConsent'])->name('gdpr.store-consent');
            Route::get('gdpr/add-consent', ['uses' => 'GdprSettingsController@AddConsent'])->name('gdpr.add-consent');
            Route::get('gdpr/consent', ['uses' => 'GdprSettingsController@consent'])->name('gdpr.consent');
            Route::get('gdpr/right-of-access', ['uses' => 'GdprSettingsController@rightOfAccess'])->name('gdpr.right-of-access');
            Route::get('gdpr/right-to-informed', ['uses' => 'GdprSettingsController@rightToInformed'])->name('gdpr.right-to-informed');
            Route::get('gdpr/right-to-data-portability', ['uses' => 'GdprSettingsController@rightToDataPortability'])->name('gdpr.right-to-data-portability');
            Route::get('gdpr/right-to-erasure', ['uses' => 'GdprSettingsController@rightToErasure'])->name('gdpr.right-to-erasure');
            Route::resource('gdpr', 'GdprSettingsController',['only' => ['index', 'store']]);
        });

        Route::group(
            ['prefix' => 'projects'], function () {
            Route::post('project-members/save-group', ['uses' => 'ManageProjectMembersController@storeGroup'])->name('project-members.storeGroup');
            Route::resource('project-members', 'ManageProjectMembersController');

            Route::post('tasks/data/{startDate?}/{endDate?}/{hideCompleted?}/{projectId?}', ['uses' => 'ManageTasksController@data'])->name('tasks.data');
            Route::get('tasks/export/{startDate?}/{endDate?}/{projectId?}/{hideCompleted?}', ['uses' => 'ManageTasksController@export'])->name('tasks.export');
            Route::post('tasks/sort', ['uses' => 'ManageTasksController@sort'])->name('tasks.sort');
            Route::post('tasks/change-status', ['uses' => 'ManageTasksController@changeStatus'])->name('tasks.changeStatus');
            Route::get('tasks/check-task/{taskID}', ['uses' => 'ManageTasksController@checkTask'])->name('tasks.checkTask');
            Route::resource('tasks', 'ManageTasksController');

            Route::post('files/store-link', ['uses' => 'ManageProjectFilesController@storeLink'])->name('files.storeLink');
            Route::get('files/download/{id}', ['uses' => 'ManageProjectFilesController@download'])->name('files.download');
            Route::get('files/thumbnail', ['uses' => 'ManageProjectFilesController@thumbnailShow'])->name('files.thumbnail');
            Route::resource('files', 'ManageProjectFilesController');

            Route::get('invoices/download/{id}', ['uses' => 'ManageInvoicesController@download'])->name('invoices.download');
            Route::get('invoices/create-invoice/{id}', ['uses' => 'ManageInvoicesController@createInvoice'])->name('invoices.createInvoice');
            Route::resource('invoices', 'ManageInvoicesController');

            Route::resource('issues', 'ManageIssuesController');

            Route::post('time-logs/stop-timer/{id}', ['uses' => 'ManageTimeLogsController@stopTimer'])->name('time-logs.stopTimer');
            Route::get('time-logs/data/{id}', ['uses' => 'ManageTimeLogsController@data'])->name('time-logs.data');
            Route::resource('time-logs', 'ManageTimeLogsController');

            Route::get('milestones/detail/{id}', ['uses' => 'ManageProjectMilestonesController@detail'])->name('milestones.detail');
            Route::get('milestones/data/{id}', ['uses' => 'ManageProjectMilestonesController@data'])->name('milestones.data');
            Route::resource('milestones', 'ManageProjectMilestonesController');


        });

        Route::group(
            ['prefix' => 'clients'], function() {
            Route::post('save-consent-purpose-data/{client}', ['uses' => 'ManageClientsController@saveConsentLeadData'])->name('clients.save-consent-purpose-data');
            Route::get('consent-purpose-data/{client}', ['uses' => 'ManageClientsController@consentPurposeData'])->name('clients.consent-purpose-data');
            Route::get('gdpr/{id}', ['uses' => 'ManageClientsController@gdpr'])->name('clients.gdpr');
            Route::get('projects/{id}', ['uses' => 'ManageClientsController@showProjects'])->name('clients.projects');
            Route::get('invoices/{id}', ['uses' => 'ManageClientsController@showInvoices'])->name('clients.invoices');
            Route::get('export/{status?}/{client?}', ['uses' => 'ManageClientsController@export'])->name('clients.export');
            Route::get('data', ['uses' => 'ManageClientsController@data'])->name('clients.data');
            Route::get('create/{clientID?}/{status?}', ['uses' => 'ManageClientsController@create'])->name('clients.create');
            Route::post('detail/{clientID}', ['uses' => 'ManageClientsController@getDetail'])->name('clients.getdetail');


            Route::get('contacts/data/{id}', ['uses' => 'ClientContactController@data'])->name('contacts.data');
            Route::resource('contacts', 'ClientContactController');
        });

        Route::resource('clients', 'ManageClientsController', ['expect' => ['create']]);

        Route::get('all-issues/data', ['uses' => 'ManageAllIssuesController@data'])->name('all-issues.data');
        Route::resource('all-issues', 'ManageAllIssuesController');

        Route::get('all-time-logs/show-active-timer', ['uses' => 'ManageAllTimeLogController@showActiveTimer'])->name('all-time-logs.show-active-timer');
        Route::get('all-time-logs/members/{projectId}', ['uses' => 'ManageAllTimeLogController@membersList'])->name('all-time-logs.members');
        Route::get('all-time-logs/export/{startDate?}/{endDate?}/{projectId?}/{employee?}', ['uses' => 'ManageAllTimeLogController@export'])->name('all-time-logs.export');
        Route::post('all-time-logs/data/{startDate?}/{endDate?}/{projectId?}/{employee?}', ['uses' => 'ManageAllTimeLogController@data'])->name('all-time-logs.data');
        Route::post('all-time-logs/stop-timer/{id}', ['uses' => 'ManageAllTimeLogController@stopTimer'])->name('all-time-logs.stopTimer');
        Route::resource('all-time-logs', 'ManageAllTimeLogController');


        // task routes
        Route::resource('task', 'ManageAllTasksController', ['only' => ['edit', 'update', 'index']]); // hack to make left admin menu item active
        Route::group(
            ['prefix' => 'task'], function () {

            Route::get('all-tasks/export/{startDate?}/{endDate?}/{projectId?}/{hideCompleted?}', ['uses' => 'ManageAllTasksController@export'])->name('all-tasks.export');
            Route::post('all-tasks/data/{startDate?}/{endDate?}/{hideCompleted?}/{projectId?}', ['uses' => 'ManageAllTasksController@data'])->name('all-tasks.data');
            Route::get('all-tasks/members/{projectId}', ['uses' => 'ManageAllTasksController@membersList'])->name('all-tasks.members');
            Route::get('all-tasks/ajaxCreate/{columnId}', ['uses' => 'ManageAllTasksController@ajaxCreate'])->name('all-tasks.ajaxCreate');
            Route::get('all-tasks/reminder/{taskid}', ['uses' => 'ManageAllTasksController@remindForTask'])->name('all-tasks.reminder');
            Route::get('all-tasks/email/{taskid}', ['uses' => 'ManageAllTasksController@sendEmail'])->name('all-tasks.email');
            Route::post('task-files/store-file', ['uses' => 'ManageAllTasksController@storeFile'])->name('task-files.storeFile');
            Route::post('task-files/delete-file', ['uses' => 'ManageAllTasksController@deleteFile'])->name('task-files.deleteFile');
            Route::post('task-files/store-link', ['uses' => 'ManageAllTasksController@storeLink'])->name('task-files.storeLink');
            Route::get('task-files/download/{id}', ['uses' => 'ManageAllTasksController@download'])->name('task-files.download');
            Route::get('task-files/thumbnail', ['uses' => 'ManageAllTasksController@thumbnailShow'])->name('task-files.thumbnail');
            Route::resource('all-tasks', 'ManageAllTasksController');



            // taskboard resource
            Route::post('taskboard/updateIndex', ['as' => 'taskboard.updateIndex', 'uses' => 'AdminTaskboardController@updateIndex']);
            Route::resource('taskboard', 'AdminTaskboardController');

            // task calendar routes
            Route::resource('task-calendar', 'AdminCalendarController');

        });

        Route::resource('sticky-note', 'ManageStickyNotesController');


        Route::resource('reports', 'TaskReportController', ['only' => ['edit', 'update', 'index']]); // hack to make left admin menu item active
        Route::group(
            ['prefix' => 'reports'], function () {

            Route::post('client-report/data', ['uses' => 'ClientReportController@data'])->name('client-report.data');
            Route::get('client-report/export', ['uses' => 'ClientReportController@export'])->name('client-report.export');
            Route::get('client-report/email/{id}', ['uses' => 'ClientReportController@sendEmail'])->name('client-report.email');
            Route::post('client-report/export-pdf', ['uses' => 'ClientReportController@exportPDF'])->name('client-report.export-pdf');
            Route::resource('client-report', 'ClientReportController');

            Route::get('lead-report/conversion-client', ['uses' => 'LeadReportController@conversionClient'])->name('lead-report.conversion-client');
            Route::get('lead-report/conversion-project', ['uses' => 'LeadReportController@conversionProject'])->name('lead-report.conversion-project');
            Route::get('lead-report/designer-performance', ['uses' => 'LeadReportController@designerPerformance'])->name('lead-report.designer-performance');
            Route::post('lead-report/performance-data', ['uses' => 'LeadReportController@performanceData'])->name('lead-report.performance-data');
            Route::get('lead-report/performance-export', ['uses' => 'LeadReportController@performanceExport'])->name('lead-report.performance-export');
            Route::post('lead-report/data', ['uses' => 'LeadReportController@data'])->name('lead-report.data');
            Route::get('lead-report/export', ['uses' => 'LeadReportController@export'])->name('lead-report.export');
            Route::get('lead-report/email/{id}/{type?}', ['uses' => 'LeadReportController@sendEmail'])->name('lead-report.email');
            Route::post('lead-report/export-pdf', ['uses' => 'LeadReportController@exportPDF'])->name('lead-report.export-pdf');
            Route::post('lead-report/performance-pdf', ['uses' => 'LeadReportController@performancePDF'])->name('lead-report.performance-pdf');
            Route::resource('lead-report', 'LeadReportController');

            Route::post('project-report/data', ['uses' => 'ProjectReportController@data'])->name('project-report.data');
            Route::get('project-report/export', ['uses' => 'ProjectReportController@export'])->name('project-report.export');
            Route::get('project-report/email/{id}', ['uses' => 'ProjectReportController@sendEmail'])->name('project-report.email');
            Route::post('project-report/export-pdf', ['uses' => 'ProjectReportController@exportPDF'])->name('project-report.export-pdf');
            Route::resource('project-report', 'ProjectReportController');

            Route::get('appointment-report/data/{startDate?}/{endDate?}/{type?}', ['uses' => 'EventReportController@data'])->name('appointment-report.data');
            Route::get('appointment-report/export/{startDate?}/{endDate?}/{type?}', ['uses' => 'EventReportController@export'])->name('appointment-report.export');
            Route::get('appointment-report/email/{id}', ['uses' => 'EventReportController@sendEmail'])->name('appointment-report.email');
            Route::get('appointment-report/export-pdf/{startDate?}/{endDate?}/{type?}', ['uses' => 'EventReportController@exportPDF'])->name('appointment-report.export-pdf');
            Route::resource('appointment-report', 'EventReportController');

            Route::post('payment-report/data', ['uses' => 'PaymentReportController@data'])->name('payment-report.data');
            Route::get('payment-report/export', ['uses' => 'PaymentReportController@export'])->name('payment-report.export');
            Route::get('payment-report/email/{id}', ['uses' => 'PaymentReportController@sendEmail'])->name('payment-report.email');
            Route::post('payment-report/export-pdf', ['uses' => 'PaymentReportController@exportPDF'])->name('payment-report.export-pdf');
            Route::resource('payment-report', 'PaymentReportController');

            Route::get('commission-report/export-pdf/{projectID?}', ['uses' => 'CommissionReportController@exportPDF'])->name('commission-report.export-pdf');
            Route::post('commission-report/individual-pdf', ['uses' => 'CommissionReportController@individualPDF'])->name('commission-report.individual-pdf');
            Route::post('commission-report/individual-data', ['uses' => 'CommissionReportController@individualData'])->name('commission-report.individual-data');
            Route::get('commission-report/individual-export', ['uses' => 'CommissionReportController@individualExport'])->name('commission-report.individual-export');
            Route::get('commission-report/individual', ['uses' => 'CommissionReportController@individualReport'])->name('commission-report.individual');
            Route::post('commission-report/data', ['uses' => 'CommissionReportController@data'])->name('commission-report.data');
            Route::get('commission-report/export', ['uses' => 'CommissionReportController@export'])->name('commission-report.export');
            Route::get('commission-report/email/{id}', ['uses' => 'CommissionReportController@sendEmail'])->name('commission-report.email');
            Route::post('commission-report/common-pdf', ['uses' => 'CommissionReportController@commonPDF'])->name('commission-report.common-pdf');
            Route::resource('commission-report', 'CommissionReportController');

            Route::get('lead-source-report/data/{startDate?}/{endDate?}', ['uses' => 'LeadSourceReportController@data'])->name('lead-source-report.data');
            Route::get('lead-source-report/export/{startDate?}/{endDate?}', ['uses' => 'LeadSourceReportController@export'])->name('lead-source-report.export');
            Route::get('lead-source-report/email/{id}', ['uses' => 'LeadSourceReportController@sendEmail'])->name('lead-source-report.email');
            Route::post('lead-source-report/export-pdf', ['uses' => 'LeadSourceReportController@exportPDF'])->name('lead-source-report.export-pdf');
            Route::resource('lead-source-report', 'LeadSourceReportController');

            Route::post('install-schedule-report/data', ['uses' => 'InstallScheduleReportController@data'])->name('install-schedule-report.data');
            Route::get('install-schedule-report/export', ['uses' => 'InstallScheduleReportController@export'])->name('install-schedule-report.export');
            Route::get('install-schedule-report/email/{id}', ['uses' => 'InstallScheduleReportController@sendEmail'])->name('install-schedule-report.email');
            Route::post('install-schedule-report/export-pdf', ['uses' => 'InstallScheduleReportController@exportPDF'])->name('install-schedule-report.export-pdf');
            Route::resource('install-schedule-report', 'InstallScheduleReportController');

            Route::get('task-report/data/{startDate?}/{endDate?}/{employeeId?}/{projectId?}', ['uses' => 'TaskReportController@data'])->name('task-report.data');
            Route::get('task-report/export/{startDate?}/{endDate?}/{employeeId?}/{projectId?}', ['uses' => 'TaskReportController@export'])->name('task-report.export');
            Route::resource('task-report', 'TaskReportController');
            Route::resource('time-log-report', 'TimeLogReportController');
            Route::resource('finance-report', 'FinanceReportController');
            Route::resource('income-expense-report', 'IncomeVsExpenseReportController');
            //region Leave Report routes
            Route::get('leave-report/data/{startDate?}/{endDate?}/{employeeId?}', ['uses' => 'LeaveReportController@data'])->name('leave-report.data');
            Route::get('leave-report/export/{id?}/{startDate?}/{endDate?}', 'LeaveReportController@export')->name('leave-report.export');
            Route::get('leave-report/pending-leaves/{id?}', 'LeaveReportController@pendingLeaves')->name('leave-report.pending-leaves');
            Route::get('leave-report/upcoming-leaves/{id?}', 'LeaveReportController@upcomingLeaves')->name('leave-report.upcoming-leaves');
            Route::resource('leave-report', 'LeaveReportController');
           
            Route::get('attendance-report/{startDate}/{endDate}/{employee}', ['uses' => 'AttendanceReportController@report'])->name('attendance-report.report');
            Route::get('attendance-report/export/{startDate}/{endDate}/{employee}', ['uses' => 'AttendanceReportController@reportExport'])->name('attendance-report.reportExport');
            Route::resource('attendance-report', 'AttendanceReportController');
            //endregion
        });

        Route::resource('search', 'AdminSearchController');



        Route::resource('finance', 'ManageEstimatesController', ['only' => ['edit', 'update', 'index']]); // hack to make left admin menu item active
        Route::group(
            ['prefix' => 'finance'], function () {

            // Estimate routes
            Route::get('estimates/data', ['uses' => 'ManageEstimatesController@data'])->name('estimates.data');
            Route::get('estimates/download/{id}', ['uses' => 'ManageEstimatesController@download'])->name('estimates.download');
            Route::get('estimates/export/{startDate}/{endDate}/{status}', ['uses' => 'ManageEstimatesController@export'])->name('estimates.export');
            Route::resource('estimates', 'ManageEstimatesController');

            //Expenses routes
            Route::get('expenses/data', ['uses' => 'ManageExpensesController@data'])->name('expenses.data');
            Route::get('expenses/export/{startDate}/{endDate}/{status}/{employee}', ['uses' => 'ManageExpensesController@export'])->name('expenses.export');
            Route::resource('expenses', 'ManageExpensesController');

            // All invoices list routes
            Route::post('file/store', ['uses' => 'ManageAllInvoicesController@storeFile'])->name('invoiceFile.store');
            Route::delete('file/destroy', ['uses' => 'ManageAllInvoicesController@destroyFile'])->name('invoiceFile.destroy');
            Route::get('all-invoices/data', ['uses' => 'ManageAllInvoicesController@data'])->name('all-invoices.data');
            Route::get('all-invoices/download/{id}', ['uses' => 'ManageAllInvoicesController@download'])->name('all-invoices.download');
            Route::get('all-invoices/export/{startDate}/{endDate}/{status}/{projectID}', ['uses' => 'ManageAllInvoicesController@export'])->name('all-invoices.export');
            Route::get('all-invoices/convert-estimate/{id}', ['uses' => 'ManageAllInvoicesController@convertEstimate'])->name('all-invoices.convert-estimate');
            Route::get('all-invoices/convert-milestone/{id}', ['uses' => 'ManageAllInvoicesController@convertMilestone'])->name('all-invoices.convert-milestone');
            Route::get('all-invoices/convert-proposal/{id}', ['uses' => 'ManageAllInvoicesController@convertProposal'])->name('all-invoices.convert-proposal');
            Route::get('all-invoices/update-item', ['uses' => 'ManageAllInvoicesController@addItems'])->name('all-invoices.update-item');
            Route::get('all-invoices/payment-detail/{invoiceID}', ['uses' => 'ManageAllInvoicesController@paymentDetail'])->name('all-invoices.payment-detail');
            Route::get('all-invoices/get-client/{projectID}', ['uses' => 'ManageAllInvoicesController@getClient'])->name('all-invoices.get-client');
            Route::resource('all-invoices', 'ManageAllInvoicesController');

            // All Credit Note routes
            Route::post('credit-file/store', ['uses' => 'ManageAllCreditNotesController@storeFile'])->name('creditNoteFile.store');
            Route::delete('credit-file/destroy', ['uses' => 'ManageAllCreditNotesController@destroyFile'])->name('creditNoteFile.destroy');
            Route::get('all-credit-notes/data', ['uses' => 'ManageAllCreditNotesController@data'])->name('all-credit-notes.data');
            Route::get('all-credit-notes/download/{id}', ['uses' => 'ManageAllCreditNotesController@download'])->name('all-credit-notes.download');
            Route::get('all-credit-notes/export/{startDate}/{endDate}/{projectID}', ['uses' => 'ManageAllCreditNotesController@export'])->name('all-credit-notes.export');
            Route::get('all-credit-notes/convert-invoice/{id}', ['uses' => 'ManageAllCreditNotesController@convertInvoice'])->name('all-credit-notes.convert-invoice');
            Route::get('all-credit-notes/update-item', ['uses' => 'ManageAllCreditNotesController@addItems'])->name('all-credit-notes.update-item');
            Route::get('all-credit-notes/payment-detail/{creditNoteID}', ['uses' => 'ManageAllCreditNotesController@paymentDetail'])->name('all-credit-notes.payment-detail');
            Route::resource('all-credit-notes', 'ManageAllCreditNotesController');

            //Payments routes
            Route::get('payments/export/{startDate}/{endDate}/{status}/{payment}', ['uses' => 'ManagePaymentsController@export'])->name('payments.export');
            Route::get('payments/data', ['uses' => 'ManagePaymentsController@data'])->name('payments.data');
            Route::get('payments/pay-invoice/{invoiceId}', ['uses' => 'ManagePaymentsController@payInvoice'])->name('payments.payInvoice');
            Route::get('payments/download', ['uses' => 'ManagePaymentsController@downloadSample'])->name('payments.downloadSample');
            Route::post('payments/import', ['uses' => 'ManagePaymentsController@importExcel'])->name('payments.importExcel');
            Route::resource('payments', 'ManagePaymentsController');

            //commissions routes
            Route::get('commissions/create/{paymentID?}', ['uses' => 'ManageCommissionsController@create'])->name('commissions.create');
            Route::get('commissions/data', ['uses' => 'ManageCommissionsController@data'])->name('commissions.data');
            Route::resource('commissions', 'ManageCommissionsController');
        });

        //Ticket routes
        Route::get('tickets/export/{startDate?}/{endDate?}/{agentId?}/{status?}/{priority?}/{channelId?}/{typeId?}', ['uses' => 'ManageTicketsController@export'])->name('tickets.export');
        Route::get('tickets/data/{startDate?}/{endDate?}/{agentId?}/{status?}/{priority?}/{channelId?}/{typeId?}', ['uses' => 'ManageTicketsController@data'])->name('tickets.data');
        Route::get('tickets/refresh-count/{startDate?}/{endDate?}/{agentId?}/{status?}/{priority?}/{channelId?}/{typeId?}', ['uses' => 'ManageTicketsController@refreshCount'])->name('tickets.refreshCount');
        Route::resource('tickets', 'ManageTicketsController');


        // User message
        Route::post('message-submit', ['as' => 'user-chat.message-submit', 'uses' => 'AdminChatController@postChatMessage']);
        Route::get('user-search', ['as' => 'user-chat.user-search', 'uses' => 'AdminChatController@getUserSearch']);
        Route::resource('user-chat', 'AdminChatController');

        // attendance
        Route::get('attendances/export/{startDate?}/{endDate?}/{employee?}', ['uses' => 'ManageAttendanceController@export'])->name('attendances.export');

        Route::get('attendances/detail', ['uses' => 'ManageAttendanceController@attendanceDetail'])->name('attendances.detail');
        Route::get('attendances/data', ['uses' => 'ManageAttendanceController@data'])->name('attendances.data');
        Route::get('attendances/check-holiday', ['uses' => 'ManageAttendanceController@checkHoliday'])->name('attendances.check-holiday');
        Route::post('attendances/employeeData/{startDate?}/{endDate?}/{userId?}', ['uses' => 'ManageAttendanceController@employeeData'])->name('attendances.employeeData');
        Route::post('attendances/refresh-count/{startDate?}/{endDate?}/{userId?}', ['uses' => 'ManageAttendanceController@refreshCount'])->name('attendances.refreshCount');
        Route::get('attendances/attendance-by-date', ['uses' => 'ManageAttendanceController@attendanceByDate'])->name('attendances.attendanceByDate');
        Route::get('attendances/byDateData', ['uses' => 'ManageAttendanceController@byDateData'])->name('attendances.byDateData');
        Route::post('attendances/dateAttendanceCount', ['uses' => 'ManageAttendanceController@dateAttendanceCount'])->name('attendances.dateAttendanceCount');
        Route::get('attendances/info/{id}', ['uses' => 'ManageAttendanceController@detail'])->name('attendances.info');
        Route::get('attendances/summary', ['uses' => 'ManageAttendanceController@summary'])->name('attendances.summary');
        Route::post('attendances/summaryData', ['uses' => 'ManageAttendanceController@summaryData'])->name('attendances.summaryData');
        Route::resource('attendances', 'ManageAttendanceController');

        //Event Calendar
        Route::post('events/removeAttendee', ['as' => 'events.removeAttendee', 'uses' => 'AdminEventCalendarController@removeAttendee']);
        Route::post('events/move_event/{id}', ['as' => 'events.move_event', 'uses' => 'AdminEventCalendarController@moveEvent']);
        Route::get('events/lead/{id}', ['uses' => 'AdminEventCalendarController@leadAppt'])->name('events.lead');
        Route::get('events/project/{id}', ['uses' => 'AdminEventCalendarController@projectAppt'])->name('events.project');
        Route::get('events/client/{id}', ['uses' => 'AdminEventCalendarController@clientAppt'])->name('events.client');
        Route::get('events/create-pdf/{id}', ['uses' => 'AdminEventCalendarController@createPDF'])->name('events.create-pdf');
        Route::get('events/email/{id}', ['uses' => 'AdminEventCalendarController@sendEmail'])->name('events.email');
        Route::resource('events', 'AdminEventCalendarController');

        //Install Schedule
        Route::post('install_schedules/removeAttendee', ['as' => 'install_schedules.removeAttendee', 'uses' => 'AdminInstallScheduleController@removeAttendee']);
        Route::post('install_schedules/move_event/{id}', ['as' => 'install_schedules.move_event', 'uses' => 'AdminInstallScheduleController@moveEvent']);
//        Route::get('events/lead/{id}', ['uses' => 'AdminEventCalendarController@leadAppt'])->name('events.lead');
//        Route::get('events/project/{id}', ['uses' => 'AdminEventCalendarController@projectAppt'])->name('events.project');
//        Route::get('events/client/{id}', ['uses' => 'AdminEventCalendarController@clientAppt'])->name('events.client');
//        Route::get('events/create-pdf/{id}', ['uses' => 'AdminEventCalendarController@createPDF'])->name('events.create-pdf');
//        Route::get('events/email/{id}', ['uses' => 'AdminEventCalendarController@sendEmail'])->name('events.email');
        Route::resource('install_schedules', 'AdminInstallScheduleController');

        // Role permission routes
        Route::post('role-permission/assignAllPermission', ['as' => 'role-permission.assignAllPermission', 'uses' => 'ManageRolePermissionController@assignAllPermission']);
        Route::post('role-permission/removeAllPermission', ['as' => 'role-permission.removeAllPermission', 'uses' => 'ManageRolePermissionController@removeAllPermission']);
        Route::post('role-permission/assignRole', ['as' => 'role-permission.assignRole', 'uses' => 'ManageRolePermissionController@assignRole']);
        Route::post('role-permission/detachRole', ['as' => 'role-permission.detachRole', 'uses' => 'ManageRolePermissionController@detachRole']);
        Route::post('role-permission/storeRole', ['as' => 'role-permission.storeRole', 'uses' => 'ManageRolePermissionController@storeRole']);
        Route::post('role-permission/deleteRole', ['as' => 'role-permission.deleteRole', 'uses' => 'ManageRolePermissionController@deleteRole']);
        Route::get('role-permission/showMembers/{id}', ['as' => 'role-permission.showMembers', 'uses' => 'ManageRolePermissionController@showMembers']);
        Route::resource('role-permission', 'ManageRolePermissionController');

        //Leaves
        Route::post('leaves/leaveAction', ['as' => 'leaves.leaveAction', 'uses' => 'ManageLeavesController@leaveAction']);
        Route::get('leaves/show-reject-modal', ['as' => 'leaves.show-reject-modal', 'uses' => 'ManageLeavesController@rejectModal']);
        Route::get('leaves/all-leaves', ['as' => 'leave.all-leaves', 'uses' => 'ManageLeavesController@allLeaves']);
        Route::get('leaves/data/{startDate?}/{endDate?}/{employeeId?}', ['as' => 'leaves.data', 'uses' => 'ManageLeavesController@data']);
        Route::get('leaves/pending', ['as' => 'leaves.pending', 'uses' => 'ManageLeavesController@pendingLeaves']);
        Route::resource('leaves', 'ManageLeavesController');

        // LeaveType Resource
        Route::resource('leaveType', 'ManageLeaveTypesController');

        
        //sub task routes
        Route::post('sub-task/changeStatus', ['as' => 'sub-task.changeStatus', 'uses' => 'ManageSubTaskController@changeStatus']);
        Route::resource('sub-task', 'ManageSubTaskController');

        //task comments
        Route::resource('task-comment', 'AdminTaskCommentController');

        //taxes
        Route::resource('taxes', 'TaxSettingsController');

        //region Products Routes
        Route::get('products/data', ['uses' => 'AdminProductController@data'])->name('products.data');
        Route::get('products/export', ['uses' => 'AdminProductController@export'])->name('products.export');
        Route::resource('products', 'AdminProductController');
        //endregion

    }
    );

    // Designer routes
    Route::group(
       ['namespace' => 'Designer', 'prefix' => 'designer', 'as' => 'designer.', 'middleware' => ['role:designer']], function () {

       Route::get('dashboard', ['uses' => 'DesignerDashboardController@index'])->name('dashboard');

       Route::post('profile/updateOneSignalId', ['uses' => 'DesignerProfileController@updateOneSignalId'])->name('profile.updateOneSignalId');
       Route::resource('profile', 'DesignerProfileController');

       Route::get('projects/data', ['uses' => 'DesignerProjectsController@data'])->name('projects.data');
       Route::resource('projects', 'DesignerProjectsController');

       Route::get('project-template/data', ['uses' => 'ProjectTemplateController@data'])->name('project-template.data');
       Route::resource('project-template', 'ProjectTemplateController');

       Route::post('project-template-members/save-group', ['uses' => 'ProjectDesignerTemplateController@storeGroup'])->name('project-template-members.storeGroup');
       Route::resource('project-template-member', 'ProjectDesignerTemplateController');

       Route::resource('project-template-task', 'ProjectTemplateTaskController');

       Route::get('note/lead/create-note/{id}', ['uses' => 'DesignerNoteController@createLeadNote'])->name('note.create-lead-note');
       Route::get('note/client/create-note/{id}', ['uses' => 'DesignerNoteController@createClientNote'])->name('note.create-client-note');
       Route::post('note/store-note', ['uses' => 'DesignerNoteController@storeNote'])->name('note.store-note');
       Route::resource('note', 'DesignerNoteController');

       Route::get('leads/data', ['uses' => 'DesignerLeadController@data'])->name('leads.data');
       Route::post('leads/change-status', ['uses' => 'DesignerLeadController@changeStatus'])->name('leads.change-status');
       Route::get('leads/follow-up/{leadID}', ['uses' => 'DesignerLeadController@followUpCreate'])->name('leads.follow-up');
       Route::get('leads/followup/{leadID}', ['uses' => 'DesignerLeadController@followUpShow'])->name('leads.followup');
       Route::post('leads/follow-up-store', ['uses' => 'DesignerLeadController@followUpStore'])->name('leads.follow-up-store');
       Route::get('leads/follow-up-edit/{id?}', ['uses' => 'DesignerLeadController@editFollow'])->name('leads.follow-up-edit');
       Route::post('leads/follow-up-update', ['uses' => 'DesignerLeadController@UpdateFollow'])->name('leads.follow-up-update');
       Route::get('leads/follow-up-sort', ['uses' => 'DesignerLeadController@followUpSort'])->name('leads.follow-up-sort');
       Route::resource('leads', 'DesignerLeadController');

       // Lead Files
       Route::get('lead-files/download/{id}', ['uses' => 'LeadFilesController@download'])->name('lead-files.download');
       Route::get('lead-files/thumbnail', ['uses' => 'LeadFilesController@thumbnailShow'])->name('lead-files.thumbnail');
       Route::resource('lead-files', 'LeadFilesController');

       // Proposal routes
       Route::get('proposals/data/{id?}', ['uses' => 'DesignerProposalController@data'])->name('proposals.data');
       Route::get('proposals/download/{id}', ['uses' => 'DesignerProposalController@download'])->name('proposals.download');
       Route::get('proposals/create/{leadID?}', ['uses' => 'DesignerProposalController@create'])->name('proposals.create');
       Route::resource('proposals', 'DesignerProposalController' , ['expect' => ['create']]);

       Route::group(
           ['prefix' => 'projects'], function () {
           Route::resource('project-members', 'DesignerProjectsMemberController');

           Route::post('tasks/data/{startDate?}/{endDate?}/{hideCompleted?}/{projectId?}', ['uses' => 'DesignerTasksController@data'])->name('tasks.data');
           Route::post('tasks/sort', ['uses' => 'DesignerTasksController@sort'])->name('tasks.sort');
           Route::post('tasks/change-status', ['uses' => 'DesignerTasksController@changeStatus'])->name('tasks.changeStatus');
           Route::get('tasks/check-task/{taskID}', ['uses' => 'DesignerTasksController@checkTask'])->name('tasks.checkTask');
           Route::resource('tasks', 'DesignerTasksController');

           Route::get('files/download/{id}', ['uses' => 'DesignerProjectFilesController@download'])->name('files.download');
           Route::get('files/thumbnail', ['uses' => 'DesignerProjectFilesController@thumbnailShow'])->name('files.thumbnail');
           Route::resource('files', 'DesignerProjectFilesController');

           Route::get('time-log/show-log/{id}', ['uses' => 'DesignerTimeLogController@showTomeLog'])->name('time-log.show-log');
           Route::get('time-log/data/{id}', ['uses' => 'DesignerTimeLogController@data'])->name('time-log.data');
           Route::post('time-log/store-time-log', ['uses' => 'DesignerTimeLogController@storeTimeLog'])->name('time-log.store-time-log');
           Route::post('time-log/update-time-log/{id}', ['uses' => 'DesignerTimeLogController@updateTimeLog'])->name('time-log.update-time-log');
           Route::resource('time-log', 'DesignerTimeLogController');
       });

       //sticky note
       Route::resource('sticky-note', 'DesignerStickyNoteController');

       // User message
       Route::post('message-submit', ['as' => 'user-chat.message-submit', 'uses' => 'DesignerChatController@postChatMessage']);
       Route::get('user-search', ['as' => 'user-chat.user-search', 'uses' => 'DesignerChatController@getUserSearch']);
       Route::resource('user-chat', 'DesignerChatController');

       //Notice
       Route::get('notices/data', ['uses' => 'DesignerNoticesController@data'])->name('notices.data');
       Route::resource('notices', 'DesignerNoticesController');

       // task routes
       Route::resource('task', 'DesignerAllTasksController', ['only' => ['edit', 'update', 'index']]); // hack to make left admin menu item active
       Route::group(
           ['prefix' => 'task'], function () {

           Route::post('all-tasks/data/{startDate?}/{endDate?}/{hideCompleted?}/{projectId?}', ['uses' => 'DesignerAllTasksController@data'])->name('all-tasks.data');
           Route::get('all-tasks/designers/{projectId}', ['uses' => 'DesignerAllTasksController@designersList'])->name('all-tasks.designers');
           Route::get('all-tasks/ajaxCreate/{columnId}', ['uses' => 'DesignerAllTasksController@ajaxCreate'])->name('all-tasks.ajaxCreate');
           Route::get('all-tasks/reminder/{taskid}', ['uses' => 'DesignerAllTasksController@remindForTask'])->name('all-tasks.reminder');
           Route::post('task-files/store-file', ['uses' => 'DesignerAllTasksController@storeFile'])->name('task-files.storeFile');
           Route::post('task-files/delete-file', ['uses' => 'DesignerAllTasksController@deleteFile'])->name('task-files.deleteFile');
           Route::post('task-files/store-link', ['uses' => 'DesignerAllTasksController@storeLink'])->name('task-files.storeLink');
           Route::get('task-files/download/{id}', ['uses' => 'DesignerAllTasksController@download'])->name('task-files.download');
           Route::get('task-files/thumbnail', ['uses' => 'DesignerAllTasksController@thumbnailShow'])->name('task-files.thumbnail');
           Route::resource('all-tasks', 'DesignerAllTasksController');

           // taskboard resource
           Route::post('taskboard/updateIndex', ['as' => 'taskboard.updateIndex', 'uses' => 'DesignerTaskboardController@updateIndex']);
           Route::resource('taskboard', 'DesignerTaskboardController');

           // task calendar routes
           Route::resource('task-calendar', 'DesignerCalendarController');

       });

       Route::resource('finance', 'DesignerEstimatesController', ['only' => ['edit', 'update', 'index']]); // hack to make left admin menu item active
       Route::group(
           ['prefix' => 'finance'], function () {

           // Estimate routes
           Route::get('estimates/data', ['uses' => 'DesignerEstimatesController@data'])->name('estimates.data');
           Route::get('estimates/download/{id}', ['uses' => 'DesignerEstimatesController@download'])->name('estimates.download');
           Route::resource('estimates', 'DesignerEstimatesController');

           //Expenses routes
           Route::get('expenses/data', ['uses' => 'DesignerExpensesController@data'])->name('expenses.data');
           Route::resource('expenses', 'DesignerExpensesController');

           // All invoices list routes
           Route::post('file/store', ['uses' => 'DesignerAllInvoicesController@storeFile'])->name('invoiceFile.store');
           Route::delete('file/destroy', ['uses' => 'DesignerAllInvoicesController@destroyFile'])->name('invoiceFile.destroy');
           Route::get('all-invoices/data', ['uses' => 'DesignerAllInvoicesController@data'])->name('all-invoices.data');
           Route::get('all-invoices/download/{id}', ['uses' => 'DesignerAllInvoicesController@download'])->name('all-invoices.download');
           Route::get('all-invoices/convert-estimate/{id}', ['uses' => 'DesignerAllInvoicesController@convertEstimate'])->name('all-invoices.convert-estimate');
           Route::get('all-invoices/update-item', ['uses' => 'DesignerAllInvoicesController@addItems'])->name('all-invoices.update-item');
           Route::get('all-invoices/payment-detail/{invoiceID}', ['uses' => 'DesignerAllInvoicesController@paymentDetail'])->name('all-invoices.payment-detail');
           Route::resource('all-invoices', 'DesignerAllInvoicesController');

           // All Credit Note routes
           Route::post('credit-file/store', ['uses' => 'DesignerAllCreditNotesController@storeFile'])->name('creditNoteFile.store');
           Route::delete('credit-file/destroy', ['uses' => 'DesignerAllCreditNotesController@destroyFile'])->name('creditNoteFile.destroy');
           Route::get('all-credit-notes/data', ['uses' => 'DesignerAllCreditNotesController@data'])->name('all-credit-notes.data');
           Route::get('all-credit-notes/download/{id}', ['uses' => 'DesignerAllCreditNotesController@download'])->name('all-credit-notes.download');
           Route::get('all-credit-notes/convert-invoice/{id}', ['uses' => 'DesignerAllCreditNotesController@convertInvoice'])->name('all-credit-notes.convert-invoice');
           Route::get('all-credit-notes/update-item', ['uses' => 'DesignerAllCreditNotesController@addItems'])->name('all-credit-notes.update-item');
           Route::get('all-credit-notes/payment-detail/{creditNoteID}', ['uses' => 'DesignerAllCreditNotesController@paymentDetail'])->name('all-credit-notes.payment-detail');
           Route::resource('all-credit-notes', 'DesignerAllCreditNotesController');

           //Payments routes
           Route::get('payments/data', ['uses' => 'DesignerPaymentsController@data'])->name('payments.data');
           Route::get('payments/pay-invoice/{invoiceId}', ['uses' => 'DesignerPaymentsController@payInvoice'])->name('payments.payInvoice');
           Route::resource('payments', 'DesignerPaymentsController');
       });

       // Ticket reply template routes
       Route::post('replyTemplates/fetch-template', ['uses' => 'DesignerTicketReplyTemplatesController@fetchTemplate'])->name('replyTemplates.fetchTemplate');

       //Tickets routes
       Route::get('tickets/data', ['uses' => 'DesignerTicketsController@data'])->name('tickets.data');
       Route::post('tickets/storeAdmin', ['uses' => 'DesignerTicketsController@storeAdmin'])->name('tickets.storeAdmin');
       Route::post('tickets/updateAdmin/{id}', ['uses' => 'DesignerTicketsController@updateAdmin'])->name('tickets.updateAdmin');
       Route::post('tickets/close-ticket/{id}', ['uses' => 'DesignerTicketsController@closeTicket'])->name('tickets.closeTicket');
       Route::post('tickets/open-ticket/{id}', ['uses' => 'DesignerTicketsController@reopenTicket'])->name('tickets.reopenTicket');
       Route::get('tickets/admin-data/{startDate?}/{endDate?}/{agentId?}/{status?}/{priority?}/{channelId?}/{typeId?}', ['uses' => 'DesignerTicketsController@adminData'])->name('tickets.adminData');
       Route::get('tickets/refresh-count/{startDate?}/{endDate?}/{agentId?}/{status?}/{priority?}/{channelId?}/{typeId?}', ['uses' => 'DesignerTicketsController@refreshCount'])->name('tickets.refreshCount');
       Route::resource('tickets', 'DesignerTicketsController');

       //Ticket agent routes
       Route::get('ticket-agent/data/{startDate?}/{endDate?}/{status?}/{priority?}/{channelId?}/{typeId?}', ['uses' => 'DesignerTicketsAgentController@data'])->name('ticket-agent.data');
       Route::get('ticket-agent/refresh-count/{startDate?}/{endDate?}/{status?}/{priority?}/{channelId?}/{typeId?}', ['uses' => 'DesignerTicketsAgentController@refreshCount'])->name('ticket-agent.refreshCount');
       Route::post('ticket-agent/fetch-template', ['uses' => 'DesignerTicketsAgentController@fetchTemplate'])->name('ticket-agent.fetchTemplate');
       Route::resource('ticket-agent', 'DesignerTicketsAgentController');

       // attendance
       Route::get('attendances/detail', ['uses' => 'DesignerAttendanceController@attendanceDetail'])->name('attendances.detail');
       Route::get('attendances/data', ['uses' => 'DesignerAttendanceController@data'])->name('attendances.data');
       Route::get('attendances/check-holiday', ['uses' => 'DesignerAttendanceController@checkHoliday'])->name('attendances.check-holiday');
       Route::post('attendances/storeAttendance', ['uses' => 'DesignerAttendanceController@storeAttendance'])->name('attendances.storeAttendance');
       Route::post('attendances/employeeData/{startDate?}/{endDate?}/{userId?}', ['uses' => 'DesignerAttendanceController@employeeData'])->name('attendances.employeeData');
       Route::post('attendances/refresh-count/{startDate?}/{endDate?}/{userId?}', ['uses' => 'DesignerAttendanceController@refreshCount'])->name('attendances.refreshCount');
       Route::resource('attendances', 'DesignerAttendanceController');


       // Holidays
       Route::get('holidays/view-holiday/{year?}', 'DesignerHolidaysController@viewHoliday')->name('holidays.view-holiday');
       Route::get('holidays/calendar-month', 'DesignerHolidaysController@getCalendarMonth')->name('holidays.calendar-month');
       Route::get('holidays/mark_sunday', 'DesignerHolidaysController@Sunday')->name('holidays.mark-sunday');
       Route::get('holidays/calendar/{year?}', 'DesignerHolidaysController@holidayCalendar')->name('holidays.calendar');
       Route::get('holidays/mark-holiday', 'DesignerHolidaysController@markHoliday')->name('holidays.mark-holiday');
       Route::post('holidays/mark-holiday-store', 'DesignerHolidaysController@markDayHoliday')->name('holidays.mark-holiday-store');
       Route::resource('holidays', 'DesignerHolidaysController');

       // events
       Route::post('events/removeAttendee', ['as' => 'events.removeAttendee', 'uses' => 'DesignerEventController@removeAttendee']);
       Route::post('events/move_event/{id}', ['as' => 'events.move_event', 'uses' => 'DesignerEventController@moveEvent']);
       Route::get('events/create-pdf/{id}', ['uses' => 'DesignerEventController@createPDF'])->name('events.create-pdf');
       Route::resource('events', 'DesignerEventController');

       // clients
       Route::group(
           ['prefix' => 'clients'], function() {
           Route::get('projects/{id}', ['uses' => 'DesignerClientsController@showProjects'])->name('clients.projects');
           Route::get('invoices/{id}', ['uses' => 'DesignerClientsController@showInvoices'])->name('clients.invoices');

           Route::get('contacts/data/{id}', ['uses' => 'DesignerClientContactController@data'])->name('contacts.data');
           Route::resource('contacts', 'DesignerClientContactController');
       });

       Route::get('clients/data', ['uses' => 'DesignerClientsController@data'])->name('clients.data');
       Route::resource('clients', 'DesignerClientsController');

       Route::get('employees/docs-create/{id}', ['uses' => 'DesignerEmployeesController@docsCreate'])->name('employees.docs-create');
       Route::get('employees/tasks/{userId}/{hideCompleted}', ['uses' => 'DesignerEmployeesController@tasks'])->name('employees.tasks');
       Route::get('employees/time-logs/{userId}', ['uses' => 'DesignerEmployeesController@timeLogs'])->name('employees.time-logs');
       Route::get('employees/data', ['uses' => 'DesignerEmployeesController@data'])->name('employees.data');
       Route::get('employees/export', ['uses' => 'DesignerEmployeesController@export'])->name('employees.export');
       Route::post('employees/assignRole', ['uses' => 'DesignerEmployeesController@assignRole'])->name('employees.assignRole');
       Route::post('employees/assignProjectAdmin', ['uses' => 'DesignerEmployeesController@assignProjectAdmin'])->name('employees.assignProjectAdmin');
       Route::resource('employees', 'DesignerEmployeesController');

       Route::get('employee-docs/download/{id}', ['uses' => 'DesignerEmployeeDocsController@download'])->name('employee-docs.download');
       Route::resource('employee-docs', 'DesignerEmployeeDocsController');


       Route::get('all-time-logs/show-active-timer', ['uses' => 'DesignerAllTimeLogController@showActiveTimer'])->name('all-time-logs.show-active-timer');
       Route::post('all-time-logs/stop-timer/{id}', ['uses' => 'DesignerAllTimeLogController@stopTimer'])->name('all-time-logs.stopTimer');
       Route::post('all-time-logs/data/{startDate?}/{endDate?}/{projectId?}/{employee?}', ['uses' => 'DesignerAllTimeLogController@data'])->name('all-time-logs.data');
       Route::get('all-time-logs/designers/{projectId}', ['uses' => 'DesignerAllTimeLogController@designersList'])->name('all-time-logs.designers');
       Route::resource('all-time-logs', 'DesignerAllTimeLogController');

       Route::post('leaves/leaveAction', ['as' => 'leaves.leaveAction', 'uses' => 'DesignerLeavesController@leaveAction']);
       Route::get('leaves/data', ['as' => 'leaves.data', 'uses' => 'DesignerLeavesController@data']);
       Route::resource('leaves', 'DesignerLeavesController');

       Route::post('leaves-dashboard/leaveAction', ['as' => 'leaves-dashboard.leaveAction', 'uses' => 'DesignerLeaveDashboardController@leaveAction']);
       Route::resource('leaves-dashboard', 'DesignerLeaveDashboardController');

       //sub task routes
       Route::post('sub-task/changeStatus', ['as' => 'sub-task.changeStatus', 'uses' => 'DesignerSubTaskController@changeStatus']);
       Route::resource('sub-task', 'DesignerSubTaskController');

       //task comments
       Route::resource('task-comment', 'DesignerTaskCommentController');

       //region Products Routes
       Route::get('products/data', ['uses' => 'DesignerProductController@data'])->name('products.data');
       Route::resource('products', 'DesignerProductController');


       Route::group(
           ['prefix' => 'reports'], function () {
           Route::get('lead-report/conversion-client', ['uses' => 'DesignerLeadReportController@conversionClient'])->name('lead-report.conversion-client');
           Route::get('lead-report/conversion-project', ['uses' => 'DesignerLeadReportController@conversionProject'])->name('lead-report.conversion-project');
           Route::post('lead-report/data', ['uses' => 'DesignerLeadReportController@data'])->name('lead-report.data');
           Route::get('lead-report/export/{startDate?}/{endDate?}/{client?}/{project?}', ['uses' => 'DesignerLeadReportController@export'])->name('lead-report.export');
           Route::resource('lead-report', 'DesignerLeadReportController');

           Route::get('project-report/data/{startDate?}/{endDate?}', ['uses' => 'DesignerProjectReportController@data'])->name('project-report.data');
           Route::get('project-report/export/{startDate?}/{endDate?}', ['uses' => 'DesignerProjectReportController@export'])->name('project-report.export');
           Route::resource('project-report', 'DesignerProjectReportController');

           Route::get('appointment-report/data/{startDate?}/{endDate?}/{type?}', ['uses' => 'DesignerEventReportController@data'])->name('appointment-report.data');
           Route::get('appointment-report/export/{startDate?}/{endDate?}/{type?}', ['uses' => 'DesignerEventReportController@export'])->name('appointment-report.export');
           Route::resource('appointment-report', 'DesignerEventReportController');

           Route::get('commission-report/export-pdf/{projectID?}', ['uses' => 'DesignerCommissionReportController@exportPDF'])->name('commission-report.export-pdf');
           Route::post('commission-report/individual-data', ['uses' => 'DesignerCommissionReportController@individualData'])->name('commission-report.individual-data');
           Route::get('commission-report/individual-export', ['uses' => 'DesignerCommissionReportController@individualExport'])->name('commission-report.individual-export');
           Route::get('commission-report/individual', ['uses' => 'DesignerCommissionReportController@individualReport'])->name('commission-report.individual');
           Route::post('commission-report/data', ['uses' => 'DesignerCommissionReportController@data'])->name('commission-report.data');
           Route::get('commission-report/export', ['uses' => 'DesignerCommissionReportController@export'])->name('commission-report.export');
           Route::get('commission-report/email/{id}', ['uses' => 'DesignerCommissionReportController@sendEmail'])->name('commission-report.email');
           Route::resource('commission-report', 'DesignerCommissionReportController');
       });
       //endregion

   });
    //Client routes
    Route::group(
        ['namespace' => 'Client', 'prefix' => 'client', 'as' => 'client.', 'middleware' => ['role:client']], function () {

        Route::resource('dashboard', 'ClientDashboardController');

        Route::resource('profile', 'ClientProfileController');

        // Project section
        Route::get('projects/data', ['uses' => 'ClientProjectsController@data'])->name('projects.data');
        Route::resource('projects', 'ClientProjectsController');

        Route::group(
            ['prefix' => 'projects'], function () {

            Route::resource('project-members', 'ClientProjectMembersController');

            Route::resource('tasks', 'ClientTasksController');

            Route::get('files/download/{id}', ['uses' => 'ClientFilesController@download'])->name('files.download');
            Route::get('files/thumbnail', ['uses' => 'ClientFilesController@thumbnailShow'])->name('files.thumbnail');
            Route::resource('files', 'ClientFilesController');

            Route::get('time-log/data/{id}', ['uses' => 'ClientTimeLogController@data'])->name('time-log.data');
            Route::resource('time-log', 'ClientTimeLogController');

            Route::get('project-invoice/download/{id}', ['uses' => 'ClientProjectInvoicesController@download'])->name('project-invoice.download');
            Route::resource('project-invoice', 'ClientProjectInvoicesController');

        });

        //sticky note
        Route::resource('sticky-note', 'ClientStickyNoteController');

        // Invoice Section
        Route::get('invoices/download/{id}', ['uses' => 'ClientInvoicesController@download'])->name('invoices.download');
        Route::resource('invoices', 'ClientInvoicesController');

        // Estimate Section
        Route::get('estimates/download/{id}', ['uses' => 'ClientEstimateController@download'])->name('estimates.download');
        Route::resource('estimates', 'ClientEstimateController');
       
        //Payments section
        Route::get('payments/data', ['uses' => 'ClientPaymentsController@data'])->name('payments.data');
        Route::resource('payments', 'ClientPaymentsController');


        // Issues section
        Route::get('my-issues/data', ['uses' => 'ClientMyIssuesController@data'])->name('my-issues.data');
        Route::resource('my-issues', 'ClientMyIssuesController');

        // route for view/blade file
        Route::get('paywithpaypal', array('as' => 'paywithpaypal','uses' => 'PaypalController@payWithPaypal',));


        // change language
        Route::get('language/change-language', ['uses' => 'ClientProfileController@changeLanguage'])->name('language.change-language');


        //Tickets routes
        Route::get('tickets/data', ['uses' => 'ClientTicketsController@data'])->name('tickets.data');
        Route::post('tickets/close-ticket/{id}', ['uses' => 'ClientTicketsController@closeTicket'])->name('tickets.closeTicket');
        Route::post('tickets/open-ticket/{id}', ['uses' => 'ClientTicketsController@reopenTicket'])->name('tickets.reopenTicket');
        Route::resource('tickets', 'ClientTicketsController');

        Route::resource('events', 'ClientEventController');

        Route::post('gdpr/update-consent', ['uses' => 'ClientGdprController@updateConsent'])->name('gdpr.update-consent');
        Route::get('gdpr/consent', ['uses' => 'ClientGdprController@consent'])->name('gdpr.consent');
        Route::get('gdpr/download', ['uses' => 'ClientGdprController@downloadJSON'])->name('gdpr.download-json');
        Route::post('gdpr/remove-request', ['uses' => 'ClientGdprController@removeRequest'])->name('gdpr.remove-request');
        Route::get('privacy-policy', ['uses' => 'ClientGdprController@privacy'])->name('gdpr.privacy');
        Route::get('terms-and-condition', ['uses' => 'ClientGdprController@terms'])->name('gdpr.terms');
        Route::resource('gdpr', 'ClientGdprController');

        Route::resource('leaves', 'LeaveSettingController');


        // User message
        Route::post('message-submit', ['as' => 'user-chat.message-submit', 'uses' => 'ClientChatController@postChatMessage']);
        Route::get('user-search', ['as' => 'user-chat.user-search', 'uses' => 'ClientChatController@getUserSearch']);
        Route::resource('user-chat', 'ClientChatController');

        //task comments
        Route::resource('task-comment', 'ClientTaskCommentController');


    });

    Route::group(
        ['namespace' => 'Installer', 'prefix' => 'installer', 'as' => 'installer.', 'middleware' => ['role:installer']], function () {

        Route::get('dashboard', ['uses' => 'InstallerDashboardController@index'])->name('dashboard');

        Route::post('profile/updateOneSignalId', ['uses' => 'InstallerProfileController@updateOneSignalId'])->name('profile.updateOneSignalId');
        Route::resource('profile', 'InstallerProfileController');

        Route::get('projects/data', ['uses' => 'InstallerProjectsController@data'])->name('projects.data');
        Route::resource('projects', 'InstallerProjectsController');

        Route::get('project-template/data', ['uses' => 'ProjectTemplateController@data'])->name('project-template.data');
        Route::resource('project-template', 'ProjectTemplateController');

        Route::post('project-template-members/save-group', ['uses' => 'ProjectDesignerTemplateController@storeGroup'])->name('project-template-members.storeGroup');
        Route::resource('project-template-member', 'ProjectDesignerTemplateController');

        Route::resource('project-template-task', 'ProjectTemplateTaskController');

        // Proposal routes
        Route::get('proposals/data/{id?}', ['uses' => 'InstallerProposalController@data'])->name('proposals.data');
        Route::get('proposals/download/{id}', ['uses' => 'InstallerProposalController@download'])->name('proposals.download');
        Route::get('proposals/create/{leadID?}', ['uses' => 'InstallerProposalController@create'])->name('proposals.create');
        Route::resource('proposals', 'InstallerProposalController' , ['expect' => ['create']]);

        Route::group(
            ['prefix' => 'projects'], function () {
            Route::resource('project-members', 'InstallerProjectsMemberController');

            Route::post('tasks/data/{startDate?}/{endDate?}/{hideCompleted?}/{projectId?}', ['uses' => 'InstallerTasksController@data'])->name('tasks.data');
            Route::post('tasks/sort', ['uses' => 'InstallerTasksController@sort'])->name('tasks.sort');
            Route::post('tasks/change-status', ['uses' => 'InstallerTasksController@changeStatus'])->name('tasks.changeStatus');
            Route::get('tasks/check-task/{taskID}', ['uses' => 'InstallerTasksController@checkTask'])->name('tasks.checkTask');
            Route::resource('tasks', 'InstallerTasksController');

            Route::get('files/download/{id}', ['uses' => 'InstallerProjectFilesController@download'])->name('files.download');
            Route::get('files/thumbnail', ['uses' => 'InstallerProjectFilesController@thumbnailShow'])->name('files.thumbnail');
            Route::resource('files', 'InstallerProjectFilesController');
        });

        //sticky note
        Route::resource('sticky-note', 'InstallerStickyNoteController');

        // User message
        Route::post('message-submit', ['as' => 'user-chat.message-submit', 'uses' => 'InstallerChatController@postChatMessage']);
        Route::get('user-search', ['as' => 'user-chat.user-search', 'uses' => 'InstallerChatController@getUserSearch']);
        Route::resource('user-chat', 'InstallerChatController');

        //Notice
        Route::get('notices/data', ['uses' => 'InstallerNoticesController@data'])->name('notices.data');
        Route::resource('notices', 'InstallerNoticesController');

        // task routes
        Route::resource('task', 'InstallerAllTasksController', ['only' => ['edit', 'update', 'index']]); // hack to make left admin menu item active
        Route::group(
            ['prefix' => 'task'], function () {

            Route::post('all-tasks/data/{startDate?}/{endDate?}/{hideCompleted?}/{projectId?}', ['uses' => 'InstallerAllTasksController@data'])->name('all-tasks.data');
            Route::get('all-tasks/designers/{projectId}', ['uses' => 'InstallerAllTasksController@designersList'])->name('all-tasks.designers');
            Route::get('all-tasks/ajaxCreate/{columnId}', ['uses' => 'InstallerAllTasksController@ajaxCreate'])->name('all-tasks.ajaxCreate');
            Route::get('all-tasks/reminder/{taskid}', ['uses' => 'InstallerAllTasksController@remindForTask'])->name('all-tasks.reminder');
            Route::resource('all-tasks', 'InstallerAllTasksController');

            // taskboard resource
            Route::post('taskboard/updateIndex', ['as' => 'taskboard.updateIndex', 'uses' => 'InstallerTaskboardController@updateIndex']);
            Route::resource('taskboard', 'InstallerTaskboardController');

            // task calendar routes
            Route::resource('task-calendar', 'InstallerCalendarController');

        });

        // Ticket reply template routes
        Route::post('replyTemplates/fetch-template', ['uses' => 'InstallerTicketReplyTemplatesController@fetchTemplate'])->name('replyTemplates.fetchTemplate');

        //Tickets routes
        Route::get('tickets/data', ['uses' => 'InstallerTicketsController@data'])->name('tickets.data');
        Route::post('tickets/storeAdmin', ['uses' => 'InstallerTicketsController@storeAdmin'])->name('tickets.storeAdmin');
        Route::post('tickets/updateAdmin/{id}', ['uses' => 'InstallerTicketsController@updateAdmin'])->name('tickets.updateAdmin');
        Route::post('tickets/close-ticket/{id}', ['uses' => 'InstallerTicketsController@closeTicket'])->name('tickets.closeTicket');
        Route::post('tickets/open-ticket/{id}', ['uses' => 'InstallerTicketsController@reopenTicket'])->name('tickets.reopenTicket');
        Route::get('tickets/admin-data/{startDate?}/{endDate?}/{agentId?}/{status?}/{priority?}/{channelId?}/{typeId?}', ['uses' => 'InstallerTicketsController@adminData'])->name('tickets.adminData');
        Route::get('tickets/refresh-count/{startDate?}/{endDate?}/{agentId?}/{status?}/{priority?}/{channelId?}/{typeId?}', ['uses' => 'InstallerTicketsController@refreshCount'])->name('tickets.refreshCount');
        Route::resource('tickets', 'InstallerTicketsController');

        // attendance
        Route::get('attendances/detail', ['uses' => 'InstallerAttendanceController@attendanceDetail'])->name('attendances.detail');
        Route::get('attendances/data', ['uses' => 'InstallerAttendanceController@data'])->name('attendances.data');
        Route::get('attendances/check-holiday', ['uses' => 'InstallerAttendanceController@checkHoliday'])->name('attendances.check-holiday');
        Route::post('attendances/storeAttendance', ['uses' => 'InstallerAttendanceController@storeAttendance'])->name('attendances.storeAttendance');
        Route::post('attendances/employeeData/{startDate?}/{endDate?}/{userId?}', ['uses' => 'InstallerAttendanceController@employeeData'])->name('attendances.employeeData');
        Route::post('attendances/refresh-count/{startDate?}/{endDate?}/{userId?}', ['uses' => 'InstallerAttendanceController@refreshCount'])->name('attendances.refreshCount');
        Route::resource('attendances', 'InstallerAttendanceController');

        // events
        Route::post('events/removeAttendee', ['as' => 'events.removeAttendee', 'uses' => 'InstallerEventController@removeAttendee']);
        Route::post('events/move_event/{id}', ['as' => 'events.move_event', 'uses' => 'InstallerEventController@moveEvent']);
        Route::get('events/create-pdf/{id}', ['uses' => 'InstallerEventController@createPDF'])->name('events.create-pdf');
        Route::resource('events', 'InstallerEventController');

        // clients
        Route::group(
            ['prefix' => 'clients'], function() {
            Route::get('projects/{id}', ['uses' => 'InstallerClientsController@showProjects'])->name('clients.projects');
            Route::get('invoices/{id}', ['uses' => 'InstallerClientsController@showInvoices'])->name('clients.invoices');

            Route::get('contacts/data/{id}', ['uses' => 'InstallerClientContactController@data'])->name('contacts.data');
            Route::resource('contacts', 'InstallerClientContactController');
        });

        Route::get('clients/data', ['uses' => 'InstallerClientsController@data'])->name('clients.data');
        Route::resource('clients', 'InstallerClientsController');

        Route::get('all-time-logs/show-active-timer', ['uses' => 'InstallerAllTimeLogController@showActiveTimer'])->name('all-time-logs.show-active-timer');
        Route::post('all-time-logs/stop-timer/{id}', ['uses' => 'InstallerAllTimeLogController@stopTimer'])->name('all-time-logs.stopTimer');
        Route::post('all-time-logs/data/{startDate?}/{endDate?}/{projectId?}/{employee?}', ['uses' => 'InstallerAllTimeLogController@data'])->name('all-time-logs.data');
        Route::get('all-time-logs/designers/{projectId}', ['uses' => 'InstallerAllTimeLogController@designersList'])->name('all-time-logs.designers');
        Route::resource('all-time-logs', 'InstallerAllTimeLogController');

        //sub task routes
        Route::post('sub-task/changeStatus', ['as' => 'sub-task.changeStatus', 'uses' => 'InstallerSubTaskController@changeStatus']);
        Route::resource('sub-task', 'InstallerSubTaskController');

        //task comments
        Route::resource('task-comment', 'InstallerTaskCommentController');

        //region Products Routes
        Route::get('products/data', ['uses' => 'InstallerProductController@data'])->name('products.data');
        Route::resource('products', 'InstallerProductController');


        Route::group(
            ['prefix' => 'reports'], function () {
            Route::get('lead-report/conversion-client', ['uses' => 'InstallerLeadReportController@conversionClient'])->name('lead-report.conversion-client');
            Route::get('lead-report/conversion-project', ['uses' => 'InstallerLeadReportController@conversionProject'])->name('lead-report.conversion-project');
            Route::post('lead-report/data', ['uses' => 'InstallerLeadReportController@data'])->name('lead-report.data');
            Route::get('lead-report/export/{startDate?}/{endDate?}/{client?}/{project?}', ['uses' => 'InstallerLeadReportController@export'])->name('lead-report.export');
            Route::resource('lead-report', 'InstallerLeadReportController');

            Route::get('project-report/data/{startDate?}/{endDate?}', ['uses' => 'InstallerProjectReportController@data'])->name('project-report.data');
            Route::get('project-report/export/{startDate?}/{endDate?}', ['uses' => 'InstallerProjectReportController@export'])->name('project-report.export');
            Route::resource('project-report', 'InstallerProjectReportController');

            Route::get('appointment-report/data/{startDate?}/{endDate?}/{type?}', ['uses' => 'InstallerEventReportController@data'])->name('appointment-report.data');
            Route::get('appointment-report/export/{startDate?}/{endDate?}/{type?}', ['uses' => 'InstallerEventReportController@export'])->name('appointment-report.export');
            Route::resource('appointment-report', 'InstallerEventReportController');

            Route::get('commission-report/export-pdf/{projectID?}', ['uses' => 'InstallerCommissionReportController@exportPDF'])->name('commission-report.export-pdf');
            Route::post('commission-report/individual-data', ['uses' => 'InstallerCommissionReportController@individualData'])->name('commission-report.individual-data');
            Route::get('commission-report/individual-export', ['uses' => 'InstallerCommissionReportController@individualExport'])->name('commission-report.individual-export');
            Route::get('commission-report/individual', ['uses' => 'InstallerCommissionReportController@individualReport'])->name('commission-report.individual');
            Route::post('commission-report/data', ['uses' => 'InstallerCommissionReportController@data'])->name('commission-report.data');
            Route::get('commission-report/export', ['uses' => 'InstallerCommissionReportController@export'])->name('commission-report.export');
            Route::get('commission-report/email/{id}', ['uses' => 'InstallerCommissionReportController@sendEmail'])->name('commission-report.email');
            Route::resource('commission-report', 'InstallerCommissionReportController');
        });
        //endregion

    });

    // Mark all notifications as readu
    Route::post('mark-notification-read', ['uses' => 'NotificationController@markAllRead'])->name('mark-notification-read');
    Route::get('show-all-member-notifications', ['uses' => 'NotificationController@showAllMemberNotifications'])->name('show-all-member-notifications');
    Route::get('show-all-client-notifications', ['uses' => 'NotificationController@showAllClientNotifications'])->name('show-all-client-notifications');
    Route::get('show-all-admin-notifications', ['uses' => 'NotificationController@showAllAdminNotifications'])->name('show-all-admin-notifications');

});
