<?php

namespace App\Http\Controllers\Designer;

use App\InterestArea;
use App\Lead;
use App\LeadSource;
use App\LeadStatus;
use App\ReportSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class DesignerLeadReportController extends DesignerBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.leadReport');
        $this->pageIcon = 'ti-pie-chart';
    }

    public function index() {
        $this->fromDate = Carbon::today()->firstOfMonth();
        $this->toDate = Carbon::today();
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

        return view('designer.reports.leads.index', $this->data);
    }

    public function store(Request $request){

    }

    public function data(Request $request) {
        $this->userDetail = auth()->user();
        $startDate = null; $endDate = null;
        $status_id = null; $source_id = null; $city = null;
        if($request->has('startDate')){
            $startDate = $request->get('startDate');
        }

        if($request->has('endDate')){
            $endDate = $request->get('endDate');
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
            ->leftJoin('clients', 'clients.id', 'leads.client_id')
            ->leftJoin('projects', 'projects.id', 'leads.project_id')
            ->select('leads.id', 'leads.created_at', 'leads.company_name','leads.first_name', 'leads.last_name', 'leads.phone', 'leads.ext',
                'leads.cell', 'leads.email', 'leads.fax', 'leads.ref', 'leads.address1', 'leads.address2', 'leads.state',
                'leads.city', 'leads.zip', 'leads.interest_areas', 'lead_status.type as status',
                'lead_sources.name as source', 'clients.first_name as client_first_name', 'clients.last_name as client_last_name',
                'projects.project_name as project', 'projects.sales_price as sales_price');
        if (!is_null($startDate)) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
            $leads->where(DB::raw('DATE(leads.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
            $leads->where(DB::raw('DATE(leads.`created_at`)'), '<=', $endDate);
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

        $leads->where('leads.user_id', '=', $this->userDetail->id);
        $leads->orderBy('leads.created_at', 'DESC');
        $leads = $leads->get();

        $areas = InterestArea::select('id', 'type')->pluck('type', 'id')->toArray();

        $removeData = ['first_name', 'last_name', 'client_first_name', 'client_last_name', 'created_at'];
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
            ->rawColumns(['full_name', 'date', 'status'])
            ->removeColumn($removeData)
            ->make(true);

    }

    public function export(Request $request) {

        $this->userDetail = auth()->user();
        $startDate = null; $endDate = null;
        $status_id = null; $source_id = null; $city = null;
        if($request->has('startDate')){
            $startDate = $request->get('startDate');
        }

        if($request->has('endDate')){
            $endDate = $request->get('endDate');
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
            ->leftJoin('clients', 'clients.id', 'leads.client_id')
            ->leftJoin('projects', 'projects.id', 'leads.project_id')
            ->select('leads.id', 'leads.created_at', 'leads.company_name','leads.first_name', 'leads.last_name', 'leads.phone', 'leads.ext',
                'leads.cell', 'leads.email', 'leads.fax', 'leads.ref', 'leads.address1', 'leads.address2', 'leads.state',
                'leads.city', 'leads.zip', 'leads.interest_areas', 'lead_status.type as status',
                'lead_sources.name as source', 'clients.first_name as client_first_name', 'clients.last_name as client_last_name',
                'projects.project_name as project', 'projects.sales_price as sales_price');
        if (!is_null($startDate)) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
            $leads->where(DB::raw('DATE(leads.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
            $leads->where(DB::raw('DATE(leads.`created_at`)'), '<=', $endDate);
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

        $leads->where('leads.user_id', '=', $this->userDetail->id);
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

        $title = 'Lead Report';
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
                    $data[$active] = $row->project;
                    $data['sales_price'] = $row->sales_price;
                }
                else{
                    $data[$active] = $row->{$active};
                }
            }
            $exportArray[] = $data;
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

}
