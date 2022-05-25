<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Reply;
use App\InterestArea;
use App\Lead;
use App\LeadSource;
use App\LeadStatus;
use App\Notifications\NewReportEmail;
use App\ReportSetting;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class LeadReportController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.leadReport');
        $this->pageIcon = 'ti-pie-chart';
    }

    public function index() {
        $this->fromDate = Carbon::today()->firstOfMonth();
        $this->toDate = Carbon::today();
        $this->designers = User::allDesigners();
        $this->allStatus = LeadStatus::all();
        $this->leadSources = LeadSource::all();
        $this->cities = Lead::select('id', 'city')->groupBy('city')->orderBy('id','ASC')->get()->toArray();
        $this->fieldsData = ReportSetting::where('type', 'lead');
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $this->fieldsData = $this->fieldsData->where('role', 'admin');

        } elseif ($user->hasRole('designer')) {
            $this->fieldsData = $this->fieldsData->where('role', 'designer');

        } elseif ($user->hasRole('employee')) {
            $this->fieldsData = $this->fieldsData->where('role', 'employee');
        }
        $this->fieldsData = $this->fieldsData->select('field_name', 'status')->pluck('status', 'field_name')->toArray();
        $aa = $this->test('aaa');
        return view('admin.reports.leads.index', $this->data);
    }

    public function conversionClient() {
        $this->fromDate = Carbon::today()->firstOfMonth();
        $this->toDate = Carbon::today();
        $this->designers = User::allDesigners();
        $this->allStatus = LeadStatus::all();
        $this->leadSources = LeadSource::all();
        $this->cities = Lead::select('id', 'city')->groupBy('city')->orderBy('id','ASC')->get()->toArray();
        $this->fieldsData = ReportSetting::where('type', 'lead');
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $this->fieldsData = $this->fieldsData->where('role', 'admin');

        } elseif ($user->hasRole('designer')) {
            $this->fieldsData = $this->fieldsData->where('role', 'designer');

        } elseif ($user->hasRole('employee')) {
            $this->fieldsData = $this->fieldsData->where('role', 'employee');
        }
        $this->fieldsData = $this->fieldsData->select('field_name', 'status')->pluck('status', 'field_name')->toArray();

        return view('admin.reports.leads.conversion-client', $this->data);
    }

    public function test($abc){

    }

    public function conversionProject() {
        $this->fromDate = Carbon::today()->firstOfMonth();
        $this->toDate = Carbon::today();
        $this->designers = User::allDesigners();
        $this->allStatus = LeadStatus::all();
        $this->leadSources = LeadSource::all();
        $this->cities = Lead::select('id', 'city')->groupBy('city')->orderBy('id','ASC')->get()->toArray();
        $this->fieldsData = ReportSetting::where('type', 'lead');
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $this->fieldsData = $this->fieldsData->where('role', 'admin');

        } elseif ($user->hasRole('designer')) {
            $this->fieldsData = $this->fieldsData->where('role', 'designer');

        } elseif ($user->hasRole('employee')) {
            $this->fieldsData = $this->fieldsData->where('role', 'employee');
        }
        $this->fieldsData = $this->fieldsData->select('field_name', 'status')->pluck('status', 'field_name')->toArray();

        return view('admin.reports.leads.conversion-project', $this->data);
    }

    public function designerPerformance() {
        $this->fromDate = Carbon::today()->firstOfMonth();
        $this->toDate = Carbon::today();
        $this->designers = User::allDesigners();
        $this->allStatus = LeadStatus::all();
        $this->leadSources = LeadSource::all();
        $this->cities = Lead::select('id', 'city')->groupBy('city')->orderBy('id','ASC')->get()->toArray();

        return view('admin.reports.leads.designer-performance', $this->data);
    }

    public function store(Request $request){

    }

    public function data(Request $request) { //$startDate = null, $endDate = null, $client = null, $project = null
        $startDate = null; $endDate = null; $client = null; $project = null;
        $designer_id = null; $status_id = null; $source_id = null; $city = null;
        if($request->has('startDate')){
            $startDate = $request->get('startDate');
        }

        if($request->has('endDate')){
            $endDate = $request->get('endDate');
        }

        if($request->has('client')){
            $client = $request->get('client');
        }

        if($request->has('project')){
            $project = $request->get('project');
        }

        if($request->has('designer_id')){
            $designer_id = $request->get('designer_id');
        }

        if($request->has('status_id')){
            $status_id = $request->get('status_id');
        }

        if($request->has('source_id')){
            $source_id = $request->get('source_id');
        }

        if($request->has('city')){
            $city = $request->get('city');
        }

        $leads = Lead::leftJoin('lead_sources', 'lead_sources.id', 'leads.source_id')
            ->leftJoin('lead_status', 'lead_status.id', 'leads.status_id')
            ->leftJoin('users', 'users.id', 'leads.user_id')
            ->leftJoin('clients', 'clients.id', 'leads.client_id')
            ->leftJoin('projects', 'projects.id', 'leads.project_id')
            ->select('leads.id', 'leads.created_at', 'leads.company_name','leads.first_name', 'leads.last_name', 'leads.phone', 'leads.ext',
                'leads.cell', 'leads.email', 'leads.fax', 'leads.ref', 'leads.address1', 'leads.address2', 'leads.state',
                'leads.city', 'leads.zip', 'leads.interest_areas', 'lead_status.type as status',
                'lead_sources.name as source', 'users.name as designer', 'clients.first_name as client_first_name', 'clients.last_name as client_last_name',
                'projects.project_name as project', 'projects.sales_price as sales_price');


        if(!is_null($client) && $client){
            $leads->whereNotNull('leads.client_id');
        }

        if(!is_null($project) && $project){
            $leads->whereNotNull('leads.project_id');
        }

        if (!is_null($startDate)) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
            $leads->where(DB::raw('DATE(leads.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
            $leads->where(DB::raw('DATE(leads.`created_at`)'), '<=', $endDate);
        }

        if(!is_null($designer_id)){
            $leads->where('leads.user_id', $designer_id);
        }

        if(!is_null($status_id)){
            $leads->where('leads.status_id', $status_id);
        }

        if(!is_null($source_id)){
            $leads->where('leads.source_id', $source_id);
        }

        if(!is_null($city)){
            $leads->where('leads.city', $city);
        }

        $leads->orderBy('leads.created_at', 'DESC');
        $leads = $leads->get();

        $areas = InterestArea::select('id', 'type')->pluck('type', 'id')->toArray();

        $removeData = ['first_name', 'last_name', 'client_first_name', 'client_last_name'];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'lead');
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

        return DataTables::of($leads)
            ->addColumn('action', function($row) {
                $string = '<a href="javascript:void(0);" data-id="'.$row->id.'" class="btn btn-info btn-circle btn_send_email"
                      data-toggle="tooltip" data-original-title="Send Email"><i class="fa fa-envelope-o" aria-hidden="true"></i></a>';
                return $string;
            })
            ->addColumn('date', function ($row) {
                return $row->created_at->format($this->global->date_format);
            })
            ->addColumn('full_name', function ($row) {
                return $row->full_name;
            })
            ->addColumn('client', function ($row) {
                return ucfirst($row->client_first_name) . ' ' . ucfirst($row->client_last_name);
            })
            ->editColumn('status', function ($row) {
                if ($row->status == 'Pending Lead') {
                    $status = '<label class="label label-info">' . $row->status . '</label>';
                } else if ($row->status == 'In Design Process') {
                    $status = '<label class="label label-warning">' . $row->status . '</label>';
                } else if ($row->status == 'Converted Sale') {
                    $status = '<label class="label label-success">' . $row->status . '</label>';
                } else if ($row->status == 'Dead') {
                    $status = '<label class="label label-danger">' . $row->status . '</label>';
                } else {
                    $status = '<label class="label label-info">' . $row->status . '</label>';
                }
                return $status;
            })
            ->editColumn('sales_price', function ($row) {
                return '$'.number_format($row->sales_price, 2, '.', ',');
            })
            ->editColumn('interest_areas', function ($row) use ($areas) {
                if($row->interest_areas != null )
                    $selected_areas = explode(',', $row->interest_areas);
                else{
                    $selected_areas = [];
                }
                $result = '';
                foreach ($selected_areas as $area){
                    $result .= $areas[$area] . ', ';
                }
                return substr($result,0, -2);
            })
            ->rawColumns(['full_name', 'date', 'status', 'action'])
            ->removeColumn($removeData)
            ->make(true);
    }

    public function performanceData(Request $request) { //$startDate = null, $endDate = null, $client = null, $project = null
        $startDate = null; $endDate = null;
        if($request->has('startDate')){
            $startDate = $request->get('startDate');
        }

        if($request->has('endDate')){
            $endDate = $request->get('endDate');
        }
        if (!is_null($startDate)) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
        }

        if (!is_null($endDate)) {
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
        }
        $status = LeadStatus::all();
        $pending = 0; $process = 0; $converted = 0; $dead = 0;
        foreach ($status as $st){
            if ($st->type == 'Pending Lead') {
                $pending = $st->id;
            } else if ($st->type == 'In Design Process') {
                $process = $st->id;
            } else if ($st->type == 'Converted Sale') {
                $converted = $st->id;
            } else if ($st->type == 'Dead') {
                $dead = $st->id;
            }
        }
        $designers = User::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->with(['leads' => function ($query) use ($startDate, $endDate) {
                if (!is_null($startDate)) {
                    $query->where(DB::raw('DATE(leads.`created_at`)'), '>=', $startDate);
                }
                if (!is_null($endDate)) {
                    $query->where(DB::raw('DATE(leads.`created_at`)'), '<=', $endDate);
                }
            }])
            ->select('users.id', 'users.name')
            ->where('roles.name', 'designer')
            ->orderBy('users.created_at', 'DESC');

        $designers->get();

        return DataTables::of($designers)
            ->addColumn('received', function ($row) {
                return count($row->leads);
            })
            ->addColumn('pending', function ($row) use ($pending) {
                $count = 0;
                foreach ($row->leads as $lead){
                    if($lead->status_id == $pending){
                        $count++;
                    }
                }
                return $count;
            })
            ->addColumn('design_process', function ($row) use ($process) {
                $count = 0;
                foreach ($row->leads as $lead){
                    if($lead->status_id == $process){
                        $count++;
                    }
                }
                return $count;
            })
            ->addColumn('converted', function ($row) use ($converted) {
                $count = 0;
                foreach ($row->leads as $lead){
                    if($lead->status_id == $converted){
                        $count++;
                    }
                }
                return $count;
            })
            ->addColumn('dead', function ($row) use ($dead) {
                $count = 0;
                foreach ($row->leads as $lead){
                    if($lead->status_id == $dead){
                        $count++;
                    }
                }
                return $count;
            })
            ->removeColumn('unreadNotifications', 'image_url')
            ->make(true);
    }

    public function performanceExport(Request $request) {
        $startDate = null; $endDate = null;
        if($request->has('startDate')){
            $startDate = $request->get('startDate');
        }

        if($request->has('endDate')){
            $endDate = $request->get('endDate');
        }
        if (!is_null($startDate)) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
        }

        if (!is_null($endDate)) {
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
        }
        $status = LeadStatus::all();
        $pending = 0; $process = 0; $converted = 0; $dead = 0;
        foreach ($status as $st){
            if ($st->type == 'Pending Lead') {
                $pending = $st->id;
            } else if ($st->type == 'In Design Process') {
                $process = $st->id;
            } else if ($st->type == 'Converted Sale') {
                $converted = $st->id;
            } else if ($st->type == 'Dead') {
                $dead = $st->id;
            }
        }
        $designers = User::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->with(['leads' => function ($query) use ($startDate, $endDate) {
                if (!is_null($startDate)) {
                    $query->where(DB::raw('DATE(leads.`created_at`)'), '>=', $startDate);
                }
                if (!is_null($endDate)) {
                    $query->where(DB::raw('DATE(leads.`created_at`)'), '<=', $endDate);
                }
            }])
            ->select('users.id', 'users.name')
            ->where('roles.name', 'designer')
            ->orderBy('users.created_at', 'DESC');

        $designers = $designers->get();

        $title = 'Designer Performance Report';

        // Initialize the array which will be passed into the Excel
        // generator.
        $exportArray = [];

        // Define the Excel spreadsheet headers
        $exportArray[] = ['ID', 'Designer Name', 'Received', 'Pending', 'In Design Process', 'Converted', 'Dead'];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($designers as $designer) {
            $data = [];
            $data['id'] = $designer->id;
            $data['name'] = $designer->name;
            $data['received'] = count($designer->leads);
            $data['pending'] = 0;
            $data['process'] = 0;
            $data['converted'] = 0;
            $data['dead'] = 0;
            foreach ($designer->leads as $lead){
                if($lead->status_id == $pending){
                    $data['pending']++;
                }
                if($lead->status_id == $process){
                    $data['process']++;
                }
                if($lead->status_id == $converted){
                    $data['converted']++;
                }
                if($lead->status_id == $dead){
                    $data['dead']++;
                }
            }
            $exportArray[] = $data;
        }

        Excel::create($title, function($excel) use ($exportArray, $title) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle($title);
            $excel->setCreator('Classy CRM')->setCompany('Classy Closet');
            $excel->setDescription($title);

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function($sheet) use ($exportArray) {
                $sheet->fromArray($exportArray, null, 'A1', true, false);
                $sheet->row(1, function($row) {

                    // call row manipulation methods
                    $row->setFont(array(
                        'bold'       =>  true
                    ));
                });

            });

        })->download('xlsx');
    }

    public function performancePDF(Request $request) {
        $startDate = null; $endDate = null;
        if($request->has('startDate')){
            $startDate = $request->get('startDate');
        }

        if($request->has('endDate')){
            $endDate = $request->get('endDate');
        }
        if (!is_null($startDate)) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
        }

        if (!is_null($endDate)) {
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
        }
        $status = LeadStatus::all();
        $pending = 0; $process = 0; $converted = 0; $dead = 0;
        foreach ($status as $st){
            if ($st->type == 'Pending Lead') {
                $pending = $st->id;
            } else if ($st->type == 'In Design Process') {
                $process = $st->id;
            } else if ($st->type == 'Converted Sale') {
                $converted = $st->id;
            } else if ($st->type == 'Dead') {
                $dead = $st->id;
            }
        }
        $designers = User::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->with(['leads' => function ($query) use ($startDate, $endDate) {
                if (!is_null($startDate)) {
                    $query->where(DB::raw('DATE(leads.`created_at`)'), '>=', $startDate);
                }
                if (!is_null($endDate)) {
                    $query->where(DB::raw('DATE(leads.`created_at`)'), '<=', $endDate);
                }
            }])
            ->select('users.id', 'users.name')
            ->where('roles.name', 'designer')
            ->orderBy('users.created_at', 'DESC');

        $designers = $designers->get();

        $title = 'Designer Performance Report';

        // Initialize the array which will be passed into the Excel
        // generator.
        $exportArray = [];

        // Define the Excel spreadsheet headers
//        $exportArray[] = ['ID', 'Designer Name', 'Received', 'Pending', 'In Design Process', 'Converted', 'Dead'];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($designers as $designer) {
            $data = [];
            $data['id'] = $designer->id;
            $data['name'] = $designer->name;
            $data['received'] = count($designer->leads);
            $data['pending'] = 0;
            $data['process'] = 0;
            $data['converted'] = 0;
            $data['dead'] = 0;
            foreach ($designer->leads as $lead){
                if($lead->status_id == $pending){
                    $data['pending']++;
                }
                if($lead->status_id == $process){
                    $data['process']++;
                }
                if($lead->status_id == $converted){
                    $data['converted']++;
                }
                if($lead->status_id == $dead){
                    $data['dead']++;
                }
            }
            $exportArray[] = $data;
        }

        $this->__set('exportData', $exportArray);
        $this->__set('title', $title);
        $this->__set('startDate', Carbon::createFromFormat($this->global->date_format, $request->get('startDate'))->format("d M Y"));
        $this->__set('endDate', Carbon::createFromFormat($this->global->date_format, $request->get('endDate'))->format("d M Y"));

//        return view('admin.reports.leads.report-pdf', $this->data);
        $pdf = app('dompdf.wrapper');
        $pdf->setPaper('A4', 'landscape');
        $pdf->loadView('admin.reports.leads.performance-pdf', $this->data);
        return $pdf->download($title.'.pdf');
    }

    public function export(Request $request) {
        $startDate = null; $endDate = null; $client = null; $project = null;
        $designer_id = null; $status_id = null; $source_id = null; $city = null;
        if($request->has('startDate')){
            $startDate = $request->get('startDate');
        }

        if($request->has('endDate')){
            $endDate = $request->get('endDate');
        }

        if($request->has('client')){
            $client = $request->get('client');
        }

        if($request->has('project')){
            $project = $request->get('project');
        }

        if($request->has('designer_id')){
            $designer_id = $request->get('designer_id');
        }

        if($request->has('status_id')){
            $status_id = $request->get('status_id');
        }

        if($request->has('source_id')){
            $source_id = $request->get('source_id');
        }

        if($request->has('city')){
            $city = $request->get('city');
        }

        $leads = Lead::leftJoin('lead_sources', 'lead_sources.id', 'leads.source_id')
            ->leftJoin('lead_status', 'lead_status.id', 'leads.status_id')
            ->leftJoin('users', 'users.id', 'leads.user_id')
            ->leftJoin('clients', 'clients.id', 'leads.client_id')
            ->leftJoin('projects', 'projects.id', 'leads.project_id')
            ->select('leads.id', 'leads.created_at', 'leads.company_name','leads.first_name', 'leads.last_name', 'leads.phone', 'leads.ext',
                'leads.cell', 'leads.email', 'leads.fax', 'leads.ref', 'leads.address1', 'leads.address2', 'leads.state',
                'leads.city', 'leads.zip', 'leads.interest_areas', 'lead_status.type as status',
                'lead_sources.name as source', 'users.name as designer', 'clients.first_name as client_first_name', 'clients.last_name as client_last_name',
                'projects.project_name as project', 'projects.sales_price as sales_price');

        if(!is_null($client) && $client){
            $leads->whereNotNull('leads.client_id');
        }

        if(!is_null($project) && $project){
            $leads->whereNotNull('leads.project_id');
        }

        if (!is_null($startDate)) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
            $leads->where(DB::raw('DATE(leads.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
            $leads->where(DB::raw('DATE(leads.`created_at`)'), '<=', $endDate);
        }

        if(!is_null($designer_id)){
            $leads->where('leads.user_id', $designer_id);
        }

        if(!is_null($status_id)){
            $leads->where('leads.status_id', $status_id);
        }

        if(!is_null($source_id)){
            $leads->where('leads.source_id', $source_id);
        }

        if(!is_null($city)){
            $leads->where('leads.city', $city);
        }

        $leads->orderBy('leads.created_at', 'DESC');
        $leads = $leads->get();

        $areas = InterestArea::select('id', 'type')->pluck('type', 'id')->toArray();

        $activeData = ['id', 'created_at'];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'lead');
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $fieldsData = $fieldsData->where('role', 'admin');

        } elseif ($user->hasRole('designer')) {
            $fieldsData = $fieldsData->where('role', 'designer');

        } elseif ($user->hasRole('employee')) {
            $fieldsData = $fieldsData->where('role', 'employee');
        }
        $fieldsData = $fieldsData->get();
        $exportArray[0] = ['ID', 'Date'];
        foreach ($fieldsData as $field){
            if($field->field_name == 'project'){
                if($field->status == 'active') {
                    $exportArray[0][] = 'Project';
                    $exportArray[0][] = 'Sales Price';
                    $activeData[] = $field->field_name;
                }
            }
            elseif($field->status == 'active'){
                $activeData[] = $field->field_name;
                $exportArray[0][] = ucfirst($field->field_name);
            }
        }

        $title = '';
        if(is_null($client) && is_null($project)) {
            $title = 'Lead Report';
            if (($key = array_search('Client', $exportArray[0])) !== false)
                unset($exportArray[0][$key]);
            if (($key = array_search('Project', $exportArray[0])) !== false)
                unset($exportArray[0][$key]);
            if (($key = array_search('Sales Price', $exportArray[0])) !== false)
                unset($exportArray[0][$key]);
            foreach ($leads as $row) {
                $data = [];
                $data['id'] = $row->id;
                $data['created_at'] = $row->created_at->format($this->global->date_format);
                foreach ($activeData as $active){
                    if($active == 'client'){
                    }
                    elseif($active == 'interest_areas'){
                        if($row->interest_areas != null )
                            $selected_areas = explode(',', $row->interest_areas);
                        else{
                            $selected_areas = [];
                        }
                        $result = '';
                        foreach ($selected_areas as $area){
                            $result .= $areas[$area] . ', ';
                        }
                        $data[$active] = substr($result,0, -2);
                    }
                    elseif($active == 'project'){
                    }
                    else{
                        $data[$active] = $row->{$active};
                    }
                }
                $exportArray[] = $data;
            }
        }
        if(!is_null($client) && $client){
            $title = 'Lead Conversion To Client Report';
            if (($key = array_search('Project', $exportArray[0])) !== false)
                unset($exportArray[0][$key]);
            if (($key = array_search('Sales Price', $exportArray[0])) !== false)
                unset($exportArray[0][$key]);
            foreach ($leads as $row) {
                $data = [];
                $data['id'] = $row->id;
                $data['created_at'] = $row->created_at->format($this->global->date_format);
                foreach ($activeData as $active){
                    if($active == 'client'){
                        $data[$active] = ucfirst($row->client_first_name) . ' ' . ucfirst($row->client_last_name);
                    }
                    elseif($active == 'interest_areas'){
                        if($row->interest_areas != null )
                            $selected_areas = explode(',', $row->interest_areas);
                        else{
                            $selected_areas = [];
                        }
                        $result = '';
                        foreach ($selected_areas as $area){
                            $result .= $areas[$area] . ', ';
                        }
                        $data[$active] = substr($result,0, -2);
                    }
                    elseif($active == 'project'){
                    }
                    else{
                        $data[$active] = $row->{$active};
                    }
                }
                $exportArray[] = $data;
            }
        }
        if(!is_null($project) && $project) {
            $title = 'Lead Conversion To Project Report';
            if (($key = array_search('Client', $exportArray[0])) !== false)
                unset($exportArray[0][$key]);
            foreach ($leads as $row) {
                $data = [];
                $data['id'] = $row->id;
                $data['created_at'] = $row->created_at->format($this->global->date_format);
                foreach ($activeData as $active){
                    if($active == 'client'){
                    }
                    elseif($active == 'interest_areas'){
                        if($row->interest_areas != null )
                            $selected_areas = explode(',', $row->interest_areas);
                        else{
                            $selected_areas = [];
                        }
                        $result = '';
                        foreach ($selected_areas as $area){
                            $result .= $areas[$area] . ', ';
                        }
                        $data[$active] = substr($result,0, -2);
                    }
                    elseif($active == 'project'){
                        $data[$active] = $row->project;
                        $data['sales_price'] = '$'.number_format($row->sales_price, 2, '.', ',');
                    }
                    else{
                        $data[$active] = $row->{$active};
                    }
                }
                $exportArray[] = $data;
            }
        }



        // Generate and return the spreadsheet
        Excel::create($title, function($excel) use ($exportArray, $title) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle($title);
            $excel->setCreator('Classy CRM')->setCompany('Classy Closet');
            $excel->setDescription('Lead Report file');

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

    public function sendEmail($lead_id = null, $type = null) {
        $lead = Lead::leftJoin('lead_sources', 'lead_sources.id', 'leads.source_id')
            ->leftJoin('lead_status', 'lead_status.id', 'leads.status_id')
            ->leftJoin('users', 'users.id', 'leads.user_id')
            ->leftJoin('clients', 'clients.id', 'leads.client_id')
            ->leftJoin('projects', 'projects.id', 'leads.project_id')
            ->select('leads.id', 'leads.created_at', 'leads.company_name','leads.first_name', 'leads.last_name', 'leads.phone', 'leads.ext',
                'leads.cell', 'leads.email', 'leads.fax', 'leads.ref', 'leads.address1', 'leads.address2', 'leads.state',
                'leads.city', 'leads.zip', 'leads.interest_areas', 'lead_status.type as status',
                'lead_sources.name as source', 'users.name as designer', 'clients.first_name as client_first_name', 'clients.last_name as client_last_name',
                'projects.project_name as project', 'projects.sales_price as sales_price', 'leads.user_id');

        $lead->where('leads.id', $lead_id);
        $lead = $lead->first();
        $areas = InterestArea::select('id', 'type')->pluck('type', 'id')->toArray();

        $activeData = [];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'lead');
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
            if($field->field_name == 'project'){
                if($field->status == 'active') {
                    $activeData[] = $field->field_name;
                }
            }
            elseif($field->status == 'active'){
                $activeData[] = $field->field_name;
            }
        }
        $data = [];
        if(!empty($lead)){
            if(is_null($type)) {
                $data['ID'] = $lead->id;
                $data['Created On'] = $lead->created_at->format($this->global->date_format);
                foreach ($activeData as $active){
                    $key = Lang::has('modules.lead.'.$active) ? __('modules.lead.'.$active) : __('modules.lead.'.Str::camel($active));
                    if($active == 'client' || $active == 'project'){
                    }
                    elseif($active == 'interest_areas'){
                        if($lead->interest_areas != null )
                            $selected_areas = explode(',', $lead->interest_areas);
                        else{
                            $selected_areas = [];
                        }
                        $result = '';
                        foreach ($selected_areas as $area){
                            $result .= $areas[$area] . ', ';
                        }
                        $data[$key] = substr($result,0, -2);
                    }
                    else{

                        $data[$key] = $lead->{$active};
                    }
                }
            }
            else if(!is_null($type) && $type == 'client'){
                $data['ID'] = $lead->id;
                $data['Created On'] = $lead->created_at->format($this->global->date_format);
                foreach ($activeData as $active){
                    $key = Lang::has('modules.lead.'.$active) ? __('modules.lead.'.$active) : __('modules.lead.'.Str::camel($active));
                    if($active == 'client'){
                        $data[$key] = ucfirst($lead->client_first_name) . ' ' . ucfirst($lead->client_last_name);
                    }
                    elseif($active == 'interest_areas'){
                        if($lead->interest_areas != null )
                            $selected_areas = explode(',', $lead->interest_areas);
                        else{
                            $selected_areas = [];
                        }
                        $result = '';
                        foreach ($selected_areas as $area){
                            $result .= $areas[$area] . ', ';
                        }
                        $data[$key] = substr($result,0, -2);
                    }
                    elseif($active == 'project'){
                    }
                    else{
                        $data[$key] = $lead->{$active};
                    }
                }
            }
            else if(!is_null($type) && $type == 'project') {
                $data['ID'] = $lead->id;
                $data['Created On'] = $lead->created_at->format($this->global->date_format);
                foreach ($activeData as $active){
                    $key = Lang::has('modules.lead.'.$active) ? __('modules.lead.'.$active) : __('modules.lead.'.Str::camel($active));
                    if($active == 'client'){
                    }
                    elseif($active == 'interest_areas'){
                        if($lead->interest_areas != null )
                            $selected_areas = explode(',', $lead->interest_areas);
                        else{
                            $selected_areas = [];
                        }
                        $result = '';
                        foreach ($selected_areas as $area){
                            $result .= $areas[$area] . ', ';
                        }
                        $data[$key] = substr($result,0, -2);
                    }
                    elseif($active == 'project'){
                        $data[$key] = $lead->project;
                        $data['Sales Price'] = '$'.number_format($lead->sales_price, 2, '.', ',');
                    }
                    else{
                        $data[$key] = $lead->{$active};
                    }
                }
            }

        }

        if(!empty($lead->user_id)){
            $notifyUser = User::withoutGlobalScope('active')->findOrFail($lead->user_id);
            $notifyUser->notify(new NewReportEmail($data, $notifyUser, 'lead'));
            return Reply::success(__('messages.emailSendSuccess'));
        }
        else
            return Reply::error(__('messages.emailSendError'));
    }

    public function exportPDF(Request $request) {
        $startDate = null; $endDate = null; $client = null; $project = null;
        $designer_id = null; $status_id = null; $source_id = null; $city = null;
        if($request->has('startDate')){
            $startDate = $request->get('startDate');
        }

        if($request->has('endDate')){
            $endDate = $request->get('endDate');
        }

        if($request->has('client')){
            $client = $request->get('client');
        }

        if($request->has('project')){
            $project = $request->get('project');
        }

        if($request->has('designer_id')){
            $designer_id = $request->get('designer_id');
        }

        if($request->has('status_id')){
            $status_id = $request->get('status_id');
        }

        if($request->has('source_id')){
            $source_id = $request->get('source_id');
        }

        if($request->has('city')){
            $city = $request->get('city');
        }

        $leads = Lead::leftJoin('lead_sources', 'lead_sources.id', 'leads.source_id')
            ->leftJoin('lead_status', 'lead_status.id', 'leads.status_id')
            ->leftJoin('users', 'users.id', 'leads.user_id')
            ->leftJoin('clients', 'clients.id', 'leads.client_id')
            ->leftJoin('projects', 'projects.id', 'leads.project_id')
            ->select('leads.id', 'leads.created_at', 'leads.company_name','leads.first_name', 'leads.last_name', 'leads.phone', 'leads.ext',
                'leads.cell', 'leads.email', 'leads.fax', 'leads.ref', 'leads.address1', 'leads.address2', 'leads.state',
                'leads.city', 'leads.zip', 'leads.interest_areas', 'lead_status.type as status',
                'lead_sources.name as source', 'users.name as designer', 'clients.first_name as client_first_name', 'clients.last_name as client_last_name',
                'projects.project_name as project', 'projects.sales_price as sales_price');

        if(!is_null($client) && $client){
            $leads->whereNotNull('leads.client_id');
        }

        if(!is_null($project) && $project){
            $leads->whereNotNull('leads.project_id');
        }

        if (!is_null($startDate)) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
            $leads->where(DB::raw('DATE(leads.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
            $leads->where(DB::raw('DATE(leads.`created_at`)'), '<=', $endDate);
        }

        if(!is_null($designer_id)){
            $leads->where('leads.user_id', $designer_id);
        }

        if(!is_null($status_id)){
            $leads->where('leads.status_id', $status_id);
        }

        if(!is_null($source_id)){
            $leads->where('leads.source_id', $source_id);
        }

        if(!is_null($city)){
            $leads->where('leads.city', $city);
        }

        $leads->orderBy('leads.created_at', 'DESC');
        $leads = $leads->get();

        $areas = InterestArea::select('id', 'type')->pluck('type', 'id')->toArray();

        $activeData = ['id', 'created_at'];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'lead');
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $fieldsData = $fieldsData->where('role', 'admin');

        } elseif ($user->hasRole('designer')) {
            $fieldsData = $fieldsData->where('role', 'designer');

        } elseif ($user->hasRole('employee')) {
            $fieldsData = $fieldsData->where('role', 'employee');
        }
        $fieldsData = $fieldsData->get();
        $exportArray[0] = ['ID', 'Date'];
        foreach ($fieldsData as $field){
            if($field->field_name == 'project'){
                if($field->status == 'active') {
                    $exportArray[0][] = 'Project';
                    $exportArray[0][] = 'Sales Price';
                    $activeData[] = $field->field_name;
                }
            }
            elseif($field->status == 'active'){
                $activeData[] = $field->field_name;
                $exportArray[0][] = ucfirst($field->field_name);
            }
        }

        if(is_null($client) && is_null($project)) {
            $title = 'Lead Report';
            if (($key = array_search('Client', $exportArray[0])) !== false)
                unset($exportArray[0][$key]);
            if (($key = array_search('Project', $exportArray[0])) !== false)
                unset($exportArray[0][$key]);
            if (($key = array_search('Sales Price', $exportArray[0])) !== false)
                unset($exportArray[0][$key]);
            foreach ($leads as $row) {
                $data = [];
                $data['id'] = $row->id;
                $data['created_at'] = $row->created_at->format($this->global->date_format);

                foreach ($activeData as $active){
                    if($active == 'client'){
                    }
                    elseif($active == 'interest_areas'){
                        if($row->interest_areas != null )
                            $selected_areas = explode(',', $row->interest_areas);
                        else{
                            $selected_areas = [];
                        }
                        $result = '';
                        foreach ($selected_areas as $area){
                            $result .= $areas[$area] . ', ';
                        }
                        $data[$active] = substr($result,0, -2);
                    }
                    elseif($active == 'project' || $active == 'created_at'){
                    }
                    else{
                        $data[$active] = $row->{$active};
                    }
                }

                $exportArray[] = $data;
            }
        }
        if(!is_null($client) && $client){
            $title = 'Lead Conversion To Client Report';
            if (($key = array_search('Project', $exportArray[0])) !== false)
                unset($exportArray[0][$key]);
            if (($key = array_search('Sales Price', $exportArray[0])) !== false)
                unset($exportArray[0][$key]);
            foreach ($leads as $row) {
                $data = [];
                $data['id'] = $row->id;
                $data['created_at'] = $row->created_at->format($this->global->date_format);
                foreach ($activeData as $active){
                    if($active == 'client'){
                        $data[$active] = ucfirst($row->client_first_name) . ' ' . ucfirst($row->client_last_name);
                    }
                    elseif($active == 'interest_areas'){
                        if($row->interest_areas != null )
                            $selected_areas = explode(',', $row->interest_areas);
                        else{
                            $selected_areas = [];
                        }
                        $result = '';
                        foreach ($selected_areas as $area){
                            $result .= $areas[$area] . ', ';
                        }
                        $data[$active] = substr($result,0, -2);
                    }
                    elseif($active == 'project' || $active == 'created_at'){
                    }
                    else{
                        $data[$active] = $row->{$active};
                    }
                }
                $exportArray[] = $data;
            }
        }
        if(!is_null($project) && $project) {
            $title = 'Lead Conversion To Project Report';
            if (($key = array_search('Client', $exportArray[0])) !== false)
                unset($exportArray[0][$key]);
            foreach ($leads as $row) {
                $data = [];
                $data['id'] = $row->id;
                $data['created_at'] = $row->created_at->format($this->global->date_format);
                foreach ($activeData as $active){
                    if($active == 'client' || $active == 'created_at'){
                    }
                    elseif($active == 'interest_areas'){
                        if($row->interest_areas != null )
                            $selected_areas = explode(',', $row->interest_areas);
                        else{
                            $selected_areas = [];
                        }
                        $result = '';
                        foreach ($selected_areas as $area){
                            $result .= $areas[$area] . ', ';
                        }
                        $data[$active] = substr($result,0, -2);
                    }
                    elseif($active == 'project'){
                        $data[$active] = $row->project;
                        $data['sales_price'] = '$'.number_format($row->sales_price, 2, '.', ',');
                    }
                    else{
                        $data[$active] = $row->{$active};
                    }
                }
                $exportArray[] = $data;
            }
        }

        $this->__set('exportData', $exportArray);
        $this->__set('title', $title);
        $this->__set('startDate', Carbon::createFromFormat($this->global->date_format, $request->get('startDate'))->format("d M Y"));
        $this->__set('endDate', Carbon::createFromFormat($this->global->date_format, $request->get('endDate'))->format("d M Y"));

//        return view('admin.reports.leads.report-pdf', $this->data);
        $pdf = app('dompdf.wrapper');
        $pdf->setPaper('A4', 'landscape');
        $pdf->loadView('admin.reports.leads.report-pdf', $this->data);
        return $pdf->download($title.'.pdf');
    }
}
