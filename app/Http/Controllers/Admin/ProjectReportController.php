<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Reply;
use App\LeadSource;
use App\Notifications\NewReportEmail;
use App\Project;
use App\Lead;
use App\ReportSetting;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ProjectReportController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.projectReport');
        $this->pageIcon = 'ti-pie-chart';
    }

    public function index() {
        $this->fromDate = Carbon::today()->firstOfMonth();
        $this->toDate = Carbon::today();
        $this->designers = User::allDesigners();
        $this->installers = User::allInstallers();
        $this->leadSources = LeadSource::all();
        $this->fieldsData = ReportSetting::where('type', 'project');
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $this->fieldsData = $this->fieldsData->where('role', 'admin');

        } elseif ($user->hasRole('designer')) {
            $this->fieldsData = $this->fieldsData->where('role', 'designer');

        } elseif ($user->hasRole('employee')) {
            $this->fieldsData = $this->fieldsData->where('role', 'employee');
        }
        $this->fieldsData = $this->fieldsData->select('field_name', 'status')->pluck('status', 'field_name')->toArray();

        return view('admin.reports.projects.index', $this->data);
    }

    public function store(Request $request){

    }

    public function data(Request $request) {
        $startDate = null; $endDate = null;
        $startDate = Carbon::createFromFormat($this->global->date_format, $request->get('startDate'))->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $request->get('endDate'))->toDateString();
        $projects = Project::leftJoin('users', 'users.id', 'projects.user_id')
                ->leftJoin('leads', 'leads.project_id', 'projects.id')
                ->leftJoin('lead_sources', 'lead_sources.id', 'leads.source_id')
                ->leftJoin('clients', 'clients.id', 'projects.client_id')
                ->leftJoin('project_installers', 'project_installers.project_id', 'projects.id')
                ->select('projects.*', 'clients.first_name as client_first_name', 'clients.last_name as client_last_name', 'users.name as designer');

        if (!is_null($startDate)) {
            $projects->where(DB::raw('DATE(projects.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $projects->where(DB::raw('DATE(projects.`created_at`)'), '<=', $endDate);
        }

        if($request->has('designer_id') && !empty($request->get('designer_id'))){
            $designer_id = $request->get('designer_id');
            $projects->where('projects.user_id', $designer_id);
        }

        if($request->has('installer_id') && !empty($request->get('installer_id'))){
            $installer_id = $request->get('installer_id');
            $projects->where('project_installers.user_id', $installer_id);
        }

        if($request->has('source_id') && !empty($request->get('source_id'))){
            $source_id = $request->get('source_id');
            $projects->where('lead_sources.id', $source_id);
        }

        $projects->orderBy('projects.created_at', 'DESC');
        $projects = $projects->get();

        $removeData = ['updated_at', 'isProjectAdmin', 'client_first_name', 'client_last_name', 'client_id', 'user_id'];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'project');

        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $fieldsData = $fieldsData->where('role', 'admin');

        } elseif ($user->hasRole('designer')) {
            $fieldsData = $fieldsData->where('role', 'designer');

        } elseif ($user->hasRole('employee')) {
            $fieldsData = $fieldsData->where('role', 'employee');
        }
        $fieldsData = $fieldsData->get();

        foreach ($fieldsData as $field){
            if($field->status == 'deactive'){
                $removeData[] = $field->field_name;
            }
        }

        return DataTables::of($projects)
            ->addColumn('action', function($row) {
                $string = '<a href="javascript:void(0);" data-id="'.$row->id.'" class="btn btn-info btn-circle btn_send_email"
                      data-toggle="tooltip" data-original-title="Send Email"><i class="fa fa-envelope-o" aria-hidden="true"></i></a>';
                return $string;
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format($this->global->date_format);
            })
            ->editColumn('status', function ($row) {
                if ($row->status == 'canceled') {
                    $status = '<label class="label label-danger">' . $row->status . '</label>';
                } else if ($row->status == 'completed') {
                    $status = '<label class="label label-success">' . $row->status . '</label>';
                } else if ($row->status == 'in progress') {
                    $status = '<label class="label label-info">' . $row->status . '</label>';
                } else if ($row->status == 'not started') {
                    $status = '<label class="label label-inverse">' . $row->status . '</label>';
                } else if ($row->status == 'on hold') {
                    $status = '<label class="label label-warning">' . $row->status . '</label>';
                } else {
                    $status = '<label class="label label-info">' . $row->status . '</label>';
                }
                return $status;
            })
            ->editColumn('sales_price', function ($row) {
                return '$'.number_format($row->sales_price, 2, '.', ',');
            })
            ->addColumn('client', function($row){
                return ucfirst($row->client_first_name). ' ' . ucfirst($row->client_last_name);
            })
            ->rawColumns(['created_at', 'client', 'status', 'project_name', 'action'])
            ->removeColumn($removeData)
            ->make(true);

    }

    public function export(Request $request) {
        $startDate = null; $endDate = null;
        $startDate = Carbon::createFromFormat($this->global->date_format, $request->get('startDate'))->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $request->get('endDate'))->toDateString();
        $projects = Project::leftJoin('users', 'users.id', 'projects.user_id')
            ->leftJoin('leads', 'leads.project_id', 'projects.id')
            ->leftJoin('lead_sources', 'lead_sources.id', 'leads.source_id')
            ->leftJoin('clients', 'clients.id', 'projects.client_id')
            ->leftJoin('project_installers', 'project_installers.project_id', 'projects.id')
            ->select('projects.*', 'clients.first_name as client_first_name', 'clients.last_name as client_last_name', 'users.name as designer');

        if (!is_null($startDate)) {
            $projects->where(DB::raw('DATE(projects.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $projects->where(DB::raw('DATE(projects.`created_at`)'), '<=', $endDate);
        }

        if($request->has('designer_id') && !empty($request->get('designer_id'))){
            $designer_id = $request->get('designer_id');
            $projects->where('projects.user_id', $designer_id);
        }

        if($request->has('installer_id') && !empty($request->get('installer_id'))){
            $installer_id = $request->get('installer_id');
            $projects->where('project_installers.user_id', $installer_id);
        }

        if($request->has('source_id') && !empty($request->get('source_id'))){
            $source_id = $request->get('source_id');
            $projects->where('lead_sources.id', $source_id);
        }

        $projects->orderBy('projects.created_at', 'DESC');

        $title = 'Project Report';
        $attributes = ['client_first_name', 'client_last_name'];

        $projects = $projects->get()->makeHidden($attributes);
        $activeData = ['id'];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'project');

        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $fieldsData = $fieldsData->where('role', 'admin');

        } elseif ($user->hasRole('designer')) {
            $fieldsData = $fieldsData->where('role', 'designer');

        } elseif ($user->hasRole('employee')) {
            $fieldsData = $fieldsData->where('role', 'employee');
        }
        $fieldsData = $fieldsData->get();

        $exportArray[0] = ['ID'];
        foreach ($fieldsData as $field){
            if($field->status == 'active'){
                $activeData[] = $field->field_name;
                $exportArray[0][] = ucfirst($field->field_name);
            }
        }
        $exportArray[0][] = 'Created On';

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($projects as $row) {
            $data = [];
            $data['id'] = $row->id;
            foreach ($activeData as $active) {
                if($active == 'client'){
                    $data['client'] = ucfirst($row->client_first_name). ' ' . ucfirst($row->client_last_name);
                }
                else if($active == 'sales_price'){
                    $data['sales_price'] = '$'.number_format($row->sales_price, 2, '.', ',');
                }
                else{
                    $data[$active] = $row->{$active};
                }
            }
            $data['created_at'] = $row->created_at->format($this->global->date_format);
            $exportArray[] = $data;
        }

        // Generate and return the spreadsheet
        Excel::create($title, function($excel) use ($exportArray, $title) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle($title);
            $excel->setCreator('Classy CRM')->setCompany('Classy Closet');
            $excel->setDescription('Project Report file');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($exportArray) {
                $sheet->fromArray($exportArray, null, 'A1', false, false);

                $sheet->row(1, function($row) {

                    // call row manipulation methods
                    $row->setFont(array(
                        'bold'       =>  true
                    ));

                });

            });

        })->download('xlsx');
    }

    public function sendEmail($project_id = null) {

        $project = Project::leftJoin('users', 'users.id', 'projects.user_id')
            ->leftJoin('clients', 'clients.id', 'projects.client_id')
            ->select('projects.*','clients.first_name as client_first_name', 'clients.last_name as client_last_name', 'users.name as designer');

        $project->where('projects.id', $project_id);

        $project = $project->first();

        $activeData = [];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'project');

        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $fieldsData = $fieldsData->where('role', 'admin');

        } elseif ($user->hasRole('designer')) {
            $fieldsData = $fieldsData->where('role', 'designer');

        } elseif ($user->hasRole('employee')) {
            $fieldsData = $fieldsData->where('role', 'employee');
        }
        $fieldsData = $fieldsData->get();

        foreach ($fieldsData as $field){
            if($field->status == 'active'){
                $activeData[] = $field->field_name;
            }
        }

        $data = [];
        $data['ID'] = $project_id;
        foreach ($activeData as $active) {
            $key = Lang::has('modules.projects.'.$active) ? __('modules.projects.'.$active) : __('modules.projects.'.Str::camel($active));
            if($active == 'client'){
                $data[$key] = ucfirst($project->client_first_name). ' ' . ucfirst($project->client_last_name);
            }
            else if($active == 'sales_price'){
                $data[$key] = '$'.number_format($project->sales_price, 2, '.', ',');
            }
            else{
                $data[$key] = $project->{$active};
            }
        }
        $data['Created On'] = $project->created_at->format($this->global->date_format);
        if(!empty($project->user_id)){
            $notifyUser = User::withoutGlobalScope('active')->findOrFail($project->user_id);
            $notifyUser->notify(new NewReportEmail($data, $notifyUser, 'project'));
            return Reply::success(__('messages.emailSendSuccess'));
        }
        else
            return Reply::error(__('messages.emailSendError'));
    }

    public function exportPDF(Request $request) {
        $startDate = null; $endDate = null;
        $startDate = Carbon::createFromFormat($this->global->date_format, $request->get('startDate'))->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $request->get('endDate'))->toDateString();
        $projects = Project::leftJoin('users', 'users.id', 'projects.user_id')
            ->leftJoin('leads', 'leads.project_id', 'projects.id')
            ->leftJoin('lead_sources', 'lead_sources.id', 'leads.source_id')
            ->leftJoin('clients', 'clients.id', 'projects.client_id')
            ->leftJoin('project_installers', 'project_installers.project_id', 'projects.id')
            ->select('projects.*', 'clients.first_name as client_first_name', 'clients.last_name as client_last_name', 'users.name as designer');

        if (!is_null($startDate)) {
            $projects->where(DB::raw('DATE(projects.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $projects->where(DB::raw('DATE(projects.`created_at`)'), '<=', $endDate);
        }

        if($request->has('designer_id') && !empty($request->get('designer_id'))){
            $designer_id = $request->get('designer_id');
            $projects->where('projects.user_id', $designer_id);
        }

        if($request->has('installer_id') && !empty($request->get('installer_id'))){
            $installer_id = $request->get('installer_id');
            $projects->where('project_installers.user_id', $installer_id);
        }

        if($request->has('source_id') && !empty($request->get('source_id'))){
            $source_id = $request->get('source_id');
            $projects->where('lead_sources.id', $source_id);
        }

        $projects->orderBy('projects.created_at', 'DESC');

        $title = 'Project Report';
        $attributes = ['client_first_name', 'client_last_name'];

        $projects = $projects->get()->makeHidden($attributes);
        $activeData = ['id'];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'project');

        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $fieldsData = $fieldsData->where('role', 'admin');

        } elseif ($user->hasRole('designer')) {
            $fieldsData = $fieldsData->where('role', 'designer');

        } elseif ($user->hasRole('employee')) {
            $fieldsData = $fieldsData->where('role', 'employee');
        }
        $fieldsData = $fieldsData->get();

        $exportArray[0] = ['ID'];
        foreach ($fieldsData as $field){
            if($field->status == 'active'){
                $activeData[] = $field->field_name;
                $exportArray[0][] = ucfirst($field->field_name);
            }
        }
        $exportArray[0][] = 'Created On';

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($projects as $row) {
            $data = [];
            $data['id'] = $row->id;
            foreach ($activeData as $active) {
                if($active == 'client'){
                    $data['client'] = ucfirst($row->client_first_name). ' ' . ucfirst($row->client_last_name);
                }
                else if($active == 'sales_price'){
                    $data['sales_price'] = '$'.number_format($row->sales_price, 2, '.', ',');
                }
                else{
                    $data[$active] = $row->{$active};
                }
            }
            $data['created_at'] = $row->created_at->format($this->global->date_format);
            $exportArray[] = $data;
        }

        $this->__set('exportData', $exportArray);
        $this->__set('title', $title);
        $this->__set('startDate', Carbon::createFromFormat($this->global->date_format, $request->get('startDate'))->format("d M Y"));
        $this->__set('endDate', Carbon::createFromFormat($this->global->date_format, $request->get('endDate'))->format("d M Y"));

//        return view('admin.reports.leads.report-pdf', $this->data);
        $pdf = app('dompdf.wrapper');
        $pdf->setPaper('A4', 'landscape');
        $pdf->loadView('admin.reports.projects.report-pdf', $this->data);
        return $pdf->download($title.'.pdf');
    }
}
