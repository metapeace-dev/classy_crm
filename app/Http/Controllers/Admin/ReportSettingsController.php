<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Reply;
use App\Http\Requests\CommonRequest;
use App\ReportSetting;
use App\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportSettingsController extends AdminBaseController
{

    public function __construct() {
        parent:: __construct();
        $this->pageTitle = __('app.menu.reportSettings');
        $this->pageIcon = 'icon-settings';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.report-settings.index', $this->data);
    }

    public function clientAdminSetting(){
        $this->fieldsData = ReportSetting::where('role', 'admin')->where('type', 'client')->get();
        $this->type = 'client';
        $this->role = 'admin';

        return view('admin.report-settings.client', $this->data);
    }

    public function clientEmployeeSetting(){
        $this->fieldsData = ReportSetting::where('role', 'employee')->where('type', 'client')->get();
        $this->type = 'client';
        $this->role = 'employee';

        return view('admin.report-settings.client', $this->data);
    }

    public function clientDesignerSetting(){
        $this->fieldsData = ReportSetting::where('role', 'designer')->where('type', 'client')->get();
        $this->type = 'client';
        $this->role = 'designer';

        return view('admin.report-settings.client', $this->data);
    }

    public function leadAdminSetting(){
        $this->fieldsData = ReportSetting::where('role', 'admin')->where('type', 'lead')->get();
        $this->type = 'lead';
        $this->role = 'admin';

        return view('admin.report-settings.lead', $this->data);
    }

    public function leadEmployeeSetting(){
        $this->fieldsData = ReportSetting::where('role', 'employee')->where('type', 'lead')->get();
        $this->type = 'lead';
        $this->role = 'employee';

        return view('admin.report-settings.lead', $this->data);
    }

    public function leadDesignerSetting(){
        $this->fieldsData = ReportSetting::where('role', 'designer')->where('type', 'lead')->get();
        $this->type = 'lead';
        $this->role = 'designer';

        return view('admin.report-settings.lead', $this->data);
    }

    public function projectAdminSetting(){
        $this->fieldsData = ReportSetting::where('role', 'admin')->where('type', 'project')->get();
        $this->type = 'project';
        $this->role = 'admin';
        return view('admin.report-settings.project', $this->data);
    }

    public function projectEmployeeSetting(){
        $this->fieldsData = ReportSetting::where('role', 'employee')->where('type', 'project')->get();
        $this->type = 'project';
        $this->role = 'employee';
        return view('admin.report-settings.project', $this->data);
    }

    public function projectDesignerSetting(){
        $this->fieldsData = ReportSetting::where('role', 'designer')->where('type', 'project')->get();
        $this->type = 'project';
        $this->role = 'designer';
        return view('admin.report-settings.project', $this->data);
    }

    public function appointmentAdminSetting(){
        $this->fieldsData = ReportSetting::where('role', 'admin')->where('type', 'appointment')->get();
        $this->type = 'appointment';
        $this->role = 'admin';

        return view('admin.report-settings.appointment', $this->data);
    }

    public function appointmentEmployeeSetting(){
        $this->fieldsData = ReportSetting::where('role', 'employee')->where('type', 'appointment')->get();
        $this->type = 'appointment';
        $this->role = 'employee';

        return view('admin.report-settings.appointment', $this->data);
    }

    public function appointmentDesignerSetting(){
        $this->fieldsData = ReportSetting::where('role', 'designer')->where('type', 'appointment')->get();
        $this->type = 'appointment';
        $this->role = 'designer';

        return view('admin.report-settings.appointment', $this->data);
    }

    public function paymentAdminSetting(){
        $this->fieldsData = ReportSetting::where('role', 'admin')->where('type', 'payment')->get();
        $this->type = 'payment';
        $this->role = 'admin';

        return view('admin.report-settings.payment', $this->data);
    }

    public function paymentEmployeeSetting(){
        $this->fieldsData = ReportSetting::where('role', 'employee')->where('type', 'payment')->get();
        $this->type = 'payment';
        $this->role = 'employee';

        return view('admin.report-settings.payment', $this->data);
    }

    public function paymentDesignerSetting(){
        $this->fieldsData = ReportSetting::where('role', 'designer')->where('type', 'payment')->get();
        $this->type = 'payment';
        $this->role = 'designer';

        return view('admin.report-settings.payment', $this->data);
    }

    public function commissionAdminSetting(){
        $this->fieldsData = ReportSetting::where('role', 'admin')->where('type', 'commission')->get();
        $this->type = 'commission';
        $this->role = 'admin';

        return view('admin.report-settings.commission', $this->data);
    }

    public function commissionEmployeeSetting(){
        $this->fieldsData = ReportSetting::where('role', 'employee')->where('type', 'commission')->get();
        $this->type = 'commission';
        $this->role = 'employee';

        return view('admin.report-settings.commission', $this->data);
    }

    public function commissionDesignerSetting(){
        $this->fieldsData = ReportSetting::where('role', 'designer')->where('type', 'commission')->get();
        $this->type = 'commission';
        $this->role = 'designer';

        return view('admin.report-settings.commission', $this->data);
    }

    public function installScheduleAdminSetting(){
        $this->fieldsData = ReportSetting::where('role', 'admin')->where('type', 'install_schedule')->get();
        $this->type = 'install_schedule';
        $this->role = 'admin';

        return view('admin.report-settings.install_schedule', $this->data);
    }

    public function installScheduleEmployeeSetting(){
        $this->fieldsData = ReportSetting::where('role', 'employee')->where('type', 'install_schedule')->get();
        $this->type = 'install_schedule';
        $this->role = 'employee';

        return view('admin.report-settings.install_schedule', $this->data);
    }

    public function installScheduleDesignerSetting(){
        $this->fieldsData = ReportSetting::where('role', 'designer')->where('type', 'install_schedule')->get();
        $this->type = 'install_schedule';
        $this->role = 'designer';

        return view('admin.report-settings.install_schedule', $this->data);
    }

    public function update(Request $request){
        $setting = ReportSetting::findOrFail($request->id);
        $setting->status = $request->status;
        $setting->save();

        return Reply::success(__('messages.settingsUpdated'));
    }
}
