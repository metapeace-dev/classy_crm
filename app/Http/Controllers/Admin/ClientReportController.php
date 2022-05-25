<?php

namespace App\Http\Controllers\Admin;

use App\Client;
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

class ClientReportController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.clientReport');
        $this->pageIcon = 'ti-pie-chart';
    }

    public function index() {
        $this->fromDate = Carbon::today()->firstOfMonth();
        $this->toDate = Carbon::today();
//        $this->designers = User::allDesigners();
//        $this->installers = User::allInstallers();
//        $this->leadSources = LeadSource::all();
        $this->fieldsData = ReportSetting::where('type', 'client');
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $this->fieldsData = $this->fieldsData->where('role', 'admin');

        } elseif ($user->hasRole('designer')) {
            $this->fieldsData = $this->fieldsData->where('role', 'designer');

        } elseif ($user->hasRole('employee')) {
            $this->fieldsData = $this->fieldsData->where('role', 'employee');
        }
        $this->fieldsData = $this->fieldsData->select('field_name', 'status')->pluck('status', 'field_name')->toArray();

        return view('admin.reports.clients.index', $this->data);
    }

    public function store(Request $request){

    }

    public function data(Request $request) {
        $startDate = null; $endDate = null;
        $startDate = Carbon::createFromFormat($this->global->date_format, $request->get('startDate'))->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $request->get('endDate'))->toDateString();
        $clients = Client::select('*');

        if (!is_null($startDate)) {
            $clients->where(DB::raw('DATE(clients.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $clients->where(DB::raw('DATE(clients.`created_at`)'), '<=', $endDate);
        }

        $clients->orderBy('clients.created_at', 'DESC');
        $clients = $clients->get();
        $removeData = ['updated_at'];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'client');

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

        return DataTables::of($clients)
//            ->addColumn('action', function($row) {
//                $string = '<a href="javascript:void(0);" data-id="'.$row->id.'" class="btn btn-info btn-circle btn_send_email"
//                      data-toggle="tooltip" data-original-title="Send Email"><i class="fa fa-envelope-o" aria-hidden="true"></i></a>';
//                return $string;
//            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format($this->global->date_format);
            })
            ->rawColumns(['created_at', 'action'])
            ->removeColumn($removeData)
            ->make(true);

    }

    public function export(Request $request) {
        $startDate = null; $endDate = null;
        $startDate = Carbon::createFromFormat($this->global->date_format, $request->get('startDate'))->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $request->get('endDate'))->toDateString();
        $clients = Client::select('*');

        if (!is_null($startDate)) {
            $clients->where(DB::raw('DATE(clients.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $clients->where(DB::raw('DATE(clients.`created_at`)'), '<=', $endDate);
        }

        $clients->orderBy('clients.created_at', 'DESC');

        $title = 'Client Report';

        $clients = $clients->get();
        $activeData = ['id'];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'client');

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
        foreach ($clients as $row) {
            $data = [];
            $data['id'] = $row->id;
            foreach ($activeData as $active) {
                $data[$active] = $row->{$active};
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
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'client');

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
        $clients = Client::select('*');
        if (!is_null($startDate)) {
            $clients->where(DB::raw('DATE(clients.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $clients->where(DB::raw('DATE(clients.`created_at`)'), '<=', $endDate);
        }

        $clients->orderBy('clients.created_at', 'DESC');

        $title = 'Client Report';

        $clients = $clients->get();
        $activeData = ['id'];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'client');

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
        foreach ($clients as $row) {
            $data = [];
            $data['id'] = $row->id;
            foreach ($activeData as $active) {
                $data[$active] = $row->{$active};
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
        $pdf->loadView('admin.reports.clients.report-pdf', $this->data);
        return $pdf->download($title.'.pdf');
    }
}
