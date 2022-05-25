<?php

namespace App\Http\Controllers\Admin;

use App\Commission;
use App\Helper\Reply;
use App\Notifications\NewReportEmail;
use App\Payment;
use App\Project;
use App\ReportSetting;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class CommissionReportController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.commissionReport');
        $this->pageIcon = 'ti-pie-chart';
    }

    public function index() {
        $this->fromDate = Carbon::today()->firstOfMonth();
        $this->toDate = Carbon::today();
        $this->designers = User::allDesigners();
        $this->projects = Project::select('id', 'project_name')->pluck('project_name', 'id')->toArray();
        $this->fieldsData = ReportSetting::where('type', 'commission');
        $this->statuses = Commission::getEnumColumnValues('commissions', 'status');
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $this->fieldsData = $this->fieldsData->where('role', 'admin');

        } elseif ($user->hasRole('designer')) {
            $this->fieldsData = $this->fieldsData->where('role', 'designer');

        } elseif ($user->hasRole('employee')) {
            $this->fieldsData = $this->fieldsData->where('role', 'employee');
        }
        $this->fieldsData = $this->fieldsData->select('field_name', 'status')->pluck('status', 'field_name')->toArray();
        return view('admin.reports.commissions.index', $this->data);
    }

    public function individualReport() {
        $this->fromDate = Carbon::today()->subDays(13);
        $this->toDate = Carbon::today();
        $this->designers = User::allDesigners();
        $this->projects = Project::select('id', 'project_name')->pluck('project_name', 'id')->toArray();

        return view('admin.reports.commissions.individual', $this->data);
    }

    public function store(Request $request){

    }

    public function data(Request $request) { //$startDate = null, $endDate = null, $client = null, $project = null
        $startDate = null; $endDate = null; $client = null; $project = null;
        $designer_id = null; $status = null;
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

        if($request->has('status')){
            $status = $request->get('status');
        }

        if($request->has('source_id')){
            $source_id = $request->get('source_id');
        }

        if($request->has('city')){
            $city = $request->get('city');
        }

        $commissions = Commission::leftJoin('projects', 'projects.id', 'commissions.project_id')
            ->leftJoin('users', 'users.id', 'projects.user_id')
            ->leftJoin('clients', 'clients.id', 'projects.client_id')
            ->leftJoin('payments', 'payments.id', 'commissions.payment_id')
            ->select('commissions.id', 'commissions.created_at', 'commissions.amount','commissions.pay_start_date', 'commissions.pay_end_date', 'commissions.status',
                'users.name as designer', 'clients.first_name as client_first_name', 'clients.last_name as client_last_name',
                'projects.project_name as project', 'projects.sales_price as sales_price', 'payments.payment_type as payment_type');

        if (!is_null($startDate)) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
            $commissions->where(DB::raw('DATE(commissions.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
            $commissions->where(DB::raw('DATE(commissions.`created_at`)'), '<=', $endDate);
        }

        if(!is_null($designer_id)){
            $commissions->where('users.id', $designer_id);
        }

        if(!is_null($project) && $project){
            $commissions->where('commissions.project_id', $project);
        }

        if(!is_null($status)){
            $commissions->where('commissions.status', $status);
        }

        $commissions->orderBy('commissions.created_at', 'DESC');
        $commissions = $commissions->get();

        $removeData = ['updated_at', 'client_first_name', 'client_last_name', 'client_id', 'user_id'];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'commission');

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

        return DataTables::of($commissions)
            ->addColumn('action', function($row) {
                $string = '<a href="javascript:void(0);" data-id="'.$row->id.'" class="btn btn-info btn-circle btn_send_email"
                      data-toggle="tooltip" data-original-title="Send Email"><i class="fa fa-envelope-o" aria-hidden="true"></i></a>';
                return $string;
            })
            ->addColumn('created_at', function ($row) {
                return $row->created_at->format($this->global->date_format);
            })
            ->addColumn('client', function ($row) {
                return ucfirst($row->client_first_name) . ' ' . ucfirst($row->client_last_name);
            })
            ->editColumn('payment_type', function ($row) {
                return ucfirst($row->payment_type);
            })
            ->editColumn('status', function ($row) {
                if($row->status == 'bank'){
                    return '<label class="label label-warning">'.strtoupper($row->status).'</label>';
                }
                elseif($row->status == 'in progress'){
                    return '<label class="label label-info">'.strtoupper($row->status).'</label>';
                }
                else{
                    return '<label class="label label-success">'.strtoupper($row->status).'</label>';
                }
            })
            ->editColumn('amount', function ($row) {
                return '$'.number_format($row->amount, 2, '.', ',');
            })
            ->editColumn('pay_start_date', function ($row) {
                return $row->pay_start_date->format($this->global->date_format);
            })
            ->editColumn('pay_end_date', function ($row) {
                return $row->pay_end_date->format($this->global->date_format);
            })
            ->editColumn('project', function ($row) {
                return ucfirst($row->project);
            })
            ->rawColumns(['created_at', 'status', 'action'])
            ->removeColumn($removeData)
            ->make(true);
    }

    public function export(Request $request) {
        $startDate = null; $endDate = null; $client = null; $project = null;
        $designer_id = null; $status = null;
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

        if($request->has('status')){
            $status = $request->get('status');
        }

        if($request->has('source_id')){
            $source_id = $request->get('source_id');
        }

        if($request->has('city')){
            $city = $request->get('city');
        }

        $commissions = Commission::leftJoin('projects', 'projects.id', 'commissions.project_id')
            ->leftJoin('users', 'users.id', 'projects.user_id')
            ->leftJoin('clients', 'clients.id', 'projects.client_id')
            ->leftJoin('payments', 'payments.id', 'commissions.payment_id')
            ->select('commissions.id', 'commissions.created_at', 'commissions.amount','commissions.pay_start_date', 'commissions.pay_end_date', 'commissions.status',
                'users.name as designer', 'clients.first_name as client_first_name', 'clients.last_name as client_last_name',
                'projects.project_name as project', 'projects.sales_price as sales_price', 'payments.payment_type as payment_type');

        if (!is_null($startDate)) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
            $commissions->where(DB::raw('DATE(commissions.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
            $commissions->where(DB::raw('DATE(commissions.`created_at`)'), '<=', $endDate);
        }

        if(!is_null($designer_id)){
            $commissions->where('users.id', $designer_id);
        }

        if(!is_null($project) && $project){
            $commissions->where('commissions.project_id', $project);
        }

        if(!is_null($status)){
            $commissions->where('commissions.status', $status);
        }

        $commissions->orderBy('commissions.created_at', 'DESC');
        $commissions = $commissions->get();

        $activeData = ['id'];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'commission');

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

        $title = 'Commission Report';

        foreach ($commissions as $row) {
            $data = [];
            $data['id'] = $row->id;
            foreach ($activeData as $active) {
                if($active == 'client'){
                    $data['client'] = ucfirst($row->client_first_name). ' ' . ucfirst($row->client_last_name);
                }
                else if($active == 'sales_price'){
                    $data['sales_price'] = '$'.number_format($row->sales_price, 2, '.', ',');
                }
                else if($active == 'pay_start_date'){
                    $data['pay_start_date'] = $row->pay_start_date->format($this->global->date_format);
                }
                else if($active == 'pay_end_date'){
                    $data['pay_end_date'] = $row->pay_end_date->format($this->global->date_format);
                }
                else if($active == 'amount'){
                    $data['amount'] = '$'.number_format($row->amount, 2, '.', ',');
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
            $excel->setDescription('Commission Report file');

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

    public function commonPDF(Request $request) {
        $startDate = null; $endDate = null; $client = null; $project = null;
        $designer_id = null; $status = null;
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

        if($request->has('status')){
            $status = $request->get('status');
        }

        if($request->has('source_id')){
            $source_id = $request->get('source_id');
        }

        if($request->has('city')){
            $city = $request->get('city');
        }

        $commissions = Commission::leftJoin('projects', 'projects.id', 'commissions.project_id')
            ->leftJoin('users', 'users.id', 'projects.user_id')
            ->leftJoin('clients', 'clients.id', 'projects.client_id')
            ->leftJoin('payments', 'payments.id', 'commissions.payment_id')
            ->select('commissions.id', 'commissions.created_at', 'commissions.amount','commissions.pay_start_date', 'commissions.pay_end_date', 'commissions.status',
                'users.name as designer', 'clients.first_name as client_first_name', 'clients.last_name as client_last_name',
                'projects.project_name as project', 'projects.sales_price as sales_price', 'payments.payment_type as payment_type');

        if (!is_null($startDate)) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
            $commissions->where(DB::raw('DATE(commissions.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
            $commissions->where(DB::raw('DATE(commissions.`created_at`)'), '<=', $endDate);
        }

        if(!is_null($designer_id)){
            $commissions->where('users.id', $designer_id);
        }

        if(!is_null($project) && $project){
            $commissions->where('commissions.project_id', $project);
        }

        if(!is_null($status)){
            $commissions->where('commissions.status', $status);
        }

        $commissions->orderBy('commissions.created_at', 'DESC');
        $commissions = $commissions->get();

        $activeData = ['id'];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'commission');

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

        $title = 'Commission Report';

        foreach ($commissions as $row) {
            $data = [];
            $data['id'] = $row->id;
            foreach ($activeData as $active) {
                if($active == 'client'){
                    $data['client'] = ucfirst($row->client_first_name). ' ' . ucfirst($row->client_last_name);
                }
                else if($active == 'sales_price'){
                    $data['sales_price'] = '$'.number_format($row->sales_price, 2, '.', ',');
                }
                else if($active == 'pay_start_date'){
                    $data['pay_start_date'] = $row->pay_start_date->format($this->global->date_format);
                }
                else if($active == 'pay_end_date'){
                    $data['pay_end_date'] = $row->pay_end_date->format($this->global->date_format);
                }
                else if($active == 'amount'){
                    $data['amount'] = '$'.number_format($row->amount, 2, '.', ',');
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
        $pdf->loadView('admin.reports.commissions.common-pdf', $this->data);
        return $pdf->download($title.'.pdf');
    }


    public function individualData(Request $request) { //$startDate = null, $endDate = null, $client = null, $project = null
        $startDate = null; $endDate = null; $client = null; $project = null;
        $designer_id = null; $status = null;
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

        $commissions = Commission::leftJoin('projects', 'projects.id', 'commissions.project_id')
            ->leftJoin('users', 'users.id', 'projects.user_id')
            ->leftJoin('clients', 'clients.id', 'projects.client_id')
            ->select('commissions.id', 'commissions.project_id', 'commissions.pay_start_date', 'commissions.pay_end_date', 'commissions.amount',
                'users.name as designer', 'clients.first_name as client_first_name', 'clients.last_name as client_last_name',
                'projects.project_name as project', 'projects.sales_price as sales_price', 'projects.commission as commission_rate', 'projects.commission_type as commission_type');

        if (!is_null($startDate)) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
            $commissions->where(DB::raw('DATE(commissions.`pay_start_date`)'), '=', $startDate);
        }

        if (!is_null($endDate)) {
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
            $commissions->where(DB::raw('DATE(commissions.`pay_end_date`)'), '=', $endDate);
        }

        if(!is_null($designer_id)){
            $commissions->where('users.id', $designer_id);
        }

        if(!is_null($project) && $project){
            $commissions->where('commissions.project_id', $project);
        }

        $commissions->orderBy('commissions.created_at', 'DESC');
        $commissions = $commissions->get();

        return DataTables::of($commissions)
            ->addColumn('action', function ($row) {
                $string = '<a href="' . route('admin.commission-report.export-pdf', $row->project_id) . '" class="btn btn-info btn-circle"
                      data-toggle="tooltip" data-original-title="Download"><i class="fa fa-file-pdf-o" aria-hidden="true"></i></a>';
                return $string;
            })
            ->addColumn('client', function ($row) {
                return ucfirst($row->client_first_name) . ' ' . ucfirst($row->client_last_name);
            })
            ->addColumn('commission_paid', function ($row) {
                $commissions = DB::table('commissions')
                    ->select(
                        DB::raw('(select sum(commissions.amount) from `commissions` where `project_id`='.$row->project_id.' and `status`="paid") as paidCommissions ')

                    )
                    ->first();
                return '$'.number_format($commissions->paidCommissions, 2, '.', ',');
            })

            ->addColumn('balance_due', function ($row) {
                $commissions = DB::table('commissions')
                    ->select(
                        DB::raw('(select sum(commissions.amount) from `commissions` where `project_id`='.$row->project_id.' and `status`="paid") as paidCommissions')
                    )
                    ->first();
                $payments = Payment::where('project_id', '=',$row->project_id)->where('status', 'complete')->get();
                $paid_amount = 0;
                foreach ($payments as $payment){
                    $paid_amount += $payment->amount;
                }

                if($row->commission_type == 'dolloar'){
                    $due_amount = $row->commission_rate - $commissions->paidCommissions;
                }
                else{
                    $due_amount = $paid_amount * $row->commission_rate / 100 - $commissions->paidCommissions;
                }

                if($due_amount < 0){
                    return '$0.00';
                }
                else{
                    return '$'.number_format($due_amount, 2, '.', ',');
                }
            })
            ->addColumn('project_cost', function ($row) {
                $payments = Payment::where('project_id', '=',$row->project_id)->where('status', 'complete')->get();
                $paid_amount = 0;
                foreach ($payments as $payment){
                    $paid_amount += $payment->amount;
                }

                return '$'.number_format($paid_amount, 2, '.', ',');
            })
            ->editColumn('sales_price', function ($row) {
                return '$'.number_format($row->sales_price, 2, '.', ',');
            })
            ->editColumn('project', function ($row) {
                return ucfirst($row->project);
            })
            ->editColumn('amount', function ($row) {
                return '$'.number_format($row->amount, 2, '.', ',');
            })
            ->rawColumns(['date', 'status', 'action'])
            ->removeColumn('commission_type')
            ->removeColumn('commission_rate')
            ->removeColumn('client_first_name')
            ->removeColumn('client_last_name')
            ->make(true);
    }

    public function individualExport(Request $request) {
        $startDate = null; $endDate = null; $client = null; $project = null;
        $designer_id = null; $status = null;
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

        $commissions = Commission::leftJoin('projects', 'projects.id', 'commissions.project_id')
            ->leftJoin('users', 'users.id', 'projects.user_id')
            ->leftJoin('clients', 'clients.id', 'projects.client_id')
            ->select('commissions.id', 'commissions.project_id', 'commissions.pay_start_date', 'commissions.pay_end_date', 'commissions.amount',
                'users.name as designer', 'clients.first_name as client_first_name', 'clients.last_name as client_last_name',
                'projects.project_name as project', 'projects.sales_price as sales_price', 'projects.commission as commission_rate', 'projects.commission_type as commission_type');

        if (!is_null($startDate)) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
            $commissions->where(DB::raw('DATE(commissions.`pay_start_date`)'), '=', $startDate);
        }

        if (!is_null($endDate)) {
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
            $commissions->where(DB::raw('DATE(commissions.`pay_end_date`)'), '=', $endDate);
        }

        if(!is_null($designer_id)){
            $commissions->where('users.id', $designer_id);
        }

        if(!is_null($project) && $project){
            $commissions->where('commissions.project_id', $project);
        }

        $commissions->orderBy('commissions.created_at', 'DESC');
        $commissions = $commissions->get();

        $exportArray[0] = ['Designer', 'Client', 'Project', 'Sales Price', 'Payments Received', 'Commission Paid', 'Balance Due', 'Amount'];

        $title = 'Commission Report';
        foreach ($commissions as $row) {
            $data = [];
            $payments = Payment::where('project_id', '=',$row->project_id)->where('status', 'complete')->get();
            $paid_amount = 0;
            foreach ($payments as $payment){
                $paid_amount += $payment->amount;
            }

            $commissions = DB::table('commissions')
                ->select(
                    DB::raw('(select sum(commissions.amount) from `commissions` where `project_id`='.$row->project_id.' and `status`="paid") as paidCommissions ')

                )
                ->first();
            if($row->commission_type == 'dolloar'){
                $due_amount = $row->commission_rate - $commissions->paidCommissions;
            }
            else{
                $due_amount = $paid_amount * $row->commission_rate / 100 - $commissions->paidCommissions;
            }

            if($due_amount < 0){
                $due_amount = '$0.00';
            }
            else{
                $due_amount = '$'.number_format($due_amount, 2, '.', ',');
            }

            $data['Designer'] = ucfirst($row->designer);
            $data['client'] = ucfirst($row->client_first_name) . ' ' . ucfirst($row->client_last_name);
            $data['Project'] = ucfirst($row->project);
            $data['sales_price'] = '$'.number_format($row->sales_price, 2, '.', ',');
            $data['payments_received'] = '$'.number_format($paid_amount, 2, '.', ',');
            $data['commission_paid'] = '$'.number_format($commissions->paidCommissions, 2, '.', ',');
            $data['balance_due'] = $due_amount;
            $data['amount'] = '$'.number_format($row->amount, 2, '.', ',');

            $exportArray[] = $data;
        }

        // Generate and return the spreadsheet
        Excel::create($title, function($excel) use ($exportArray, $title) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle($title);
            $excel->setCreator('Classy CRM')->setCompany('Classy Closet');
            $excel->setDescription('Commission Report file');

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

    public function individualPDF(Request $request) {
        $startDate = null; $endDate = null; $client = null; $project = null;
        $designer_id = null; $status = null;
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
        if(empty($designer_id)){
            return Redirect::back()->withErrors(['No designer selected.']);
        }

        $commissions = Commission::leftJoin('projects', 'projects.id', 'commissions.project_id')
            ->leftJoin('users', 'users.id', 'projects.user_id')
            ->leftJoin('clients', 'clients.id', 'projects.client_id')
            ->select('commissions.id', 'commissions.project_id', 'commissions.pay_start_date', 'commissions.pay_end_date',
                'users.name as designer', 'commissions.amount', 'clients.first_name as client_first_name', 'clients.last_name as client_last_name',
                'projects.project_name as project', 'projects.sales_price as sales_price', 'projects.commission as commission_rate', 'projects.commission_type as commission_type');

        if (!is_null($startDate)) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
            $commissions->where(DB::raw('DATE(commissions.`pay_start_date`)'), '=', $startDate);
        }

        if (!is_null($endDate)) {
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
            $commissions->where(DB::raw('DATE(commissions.`pay_end_date`)'), '=', $endDate);
        }

        if(!is_null($designer_id)){
            $commissions->where('users.id', $designer_id);
        }

        if(!is_null($project) && $project){
            $commissions->where('commissions.project_id', $project);
        }

        $commissions->orderBy('commissions.created_at', 'DESC');
        $commissions = $commissions->get();
        $res = [];
        $total_amount = 0; $total_sales_price = 0; $total_payments_received = 0; $total_commission_paid = 0; $total_balance_due = 0;
        foreach ($commissions as $row) {
            $data = [];
            $payments = Payment::where('project_id', '=',$row->project_id)->where('status', 'complete')->get();
            $paid_amount = 0;
            foreach ($payments as $payment){
                $paid_amount += $payment->amount;
            }

            $commissions = DB::table('commissions')
                ->select(
                    DB::raw('(select sum(commissions.amount) from `commissions` where `project_id`='.$row->project_id.' and `status`="paid") as paidCommissions ')
                )
                ->first();

            if($row->commission_type == 'dolloar'){
                $due_amount = $row->commission_rate - $commissions->paidCommissions;
            }
            else{
                $due_amount = $paid_amount * $row->commission_rate / 100 - $commissions->paidCommissions;
            }
            if($due_amount < 0){
                $due_amount = '$0.00';
            }
            else{
                $due_amount = '$'.number_format($due_amount, 2, '.', ',');
            }

            $this->designer = ucfirst($row->designer ?? '');
            $data['client'] = ucfirst($row->client_first_name ?? '') . ' ' . ucfirst($row->client_last_name ?? '');
            $data['project'] = ucfirst($row->project ?? '');
            $data['sales_price'] = '$'.number_format($row->sales_price ?? 0, 2, '.', ',');
            $data['payments_received'] = '$'.number_format($paid_amount ?? 0, 2, '.', ',');
            $data['commission_paid'] = '$'.number_format($commissions->paidCommissions ?? 0, 2, '.', ',');
            $data['commission_due'] = $due_amount;
            $data['commission_amount'] = '$'.number_format($row->amount ?? 0, 2, '.', ',');
            $total_sales_price += $row->sales_price;
            $total_payments_received += $paid_amount;
            $total_commission_paid += $commissions->paidCommissions;
            $total_balance_due += (float)str_replace('$', '', $due_amount);
            $total_amount += $row->amount;
            $res[] =$data;
        }

        if(!empty($res)){
            $this->exportData = $res;
            $this->pay_start_date = $request->get('startDate');
            $this->pay_end_date = $request->get('endDate');
            $this->total_sales_price = $total_sales_price;
            $this->total_payments_received = $total_payments_received;
            $this->total_commission_paid = $total_commission_paid;
            $this->total_balance_due = $total_balance_due;
            $this->total_amount = $total_amount;

//        return view('admin.reports.commissions.report-individual', $this->data);
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('admin.reports.commissions.report-individual', $this->data);
            return $pdf->download('Commission Report - '. $this->designer . '.pdf');
        }
        else{
            return Redirect::back()->withErrors(['No commission found.']);
        }

    }

    public function exportPDF($projectID = null){
        $this->project = Project::findorFail($projectID);
        $this->payments = Payment::where('project_id', '=',$projectID)->get();
        $this->commissions = Commission::where('project_id', '=',$projectID)->get();

        $this->completes = DB::table('commissions')
            ->select(
                DB::raw('(select sum(payments.amount) from `payments` where `project_id`='.$projectID.' and `status`="complete") as paidPayments '),
                DB::raw('(select sum(commissions.amount) from `commissions` where `project_id`='.$projectID.' and `status`="paid") as paidCommissions ')
            )
            ->first();

//        return view('admin.reports.commissions.report-pdf', $this->data);
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('admin.reports.commissions.report-pdf', $this->data);
        return $pdf->download('Commission Report - '. $this->project->designer->name . '.pdf');
    }

    public function sendEmail($commission_id = null) {

        $commission = Commission::leftJoin('projects', 'projects.id', 'commissions.project_id')
            ->leftJoin('users', 'users.id', 'projects.user_id')
            ->leftJoin('clients', 'clients.id', 'projects.client_id')
            ->leftJoin('payments', 'payments.id', 'commissions.payment_id')
            ->select('commissions.id', 'commissions.created_at', 'commissions.amount','commissions.pay_start_date', 'commissions.pay_end_date', 'commissions.status',
                'users.name as designer', 'users.id as user_id', 'clients.first_name as client_first_name', 'clients.last_name as client_last_name',
                'projects.project_name as project', 'projects.sales_price as sales_price', 'payments.payment_type as payment_type');

        $commission->where('commissions.id', $commission_id);
        $commission = $commission->first();

        $activeData = [];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'commission');

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
        $data['ID'] = $commission->id;
        foreach ($activeData as $active) {
            $key = Lang::has('modules.commissions.'.$active) ? __('modules.commissions.'.$active) : __('modules.commissions.'.Str::camel($active));
            if($active == 'client'){
                $data[$key] = ucfirst($commission->client_first_name). ' ' . ucfirst($commission->client_last_name);
            }
            else if($active == 'sales_price'){
                $data[$key] = '$'.number_format($commission->sales_price, 2, '.', ',');
            }
            else if($active == 'pay_start_date'){
                $data[$key] = $commission->pay_start_date->format($this->global->date_format);
            }
            else if($active == 'pay_end_date'){
                $data[$key] = $commission->pay_end_date->format($this->global->date_format);
            }
            else if($active == 'amount'){
                $data[$key] = '$'.number_format($commission->amount, 2, '.', ',');
            }
            else{
                $data[$key] = $commission->{$active};
            }
        }
        $data['Created On'] = $commission->created_at->format($this->global->date_format);
        if(!empty($commission->user_id)){
            $notifyUser = User::withoutGlobalScope('active')->findOrFail($commission->user_id);
            $notifyUser->notify(new NewReportEmail($data, $notifyUser, 'commission'));
            return Reply::success(__('messages.emailSendSuccess'));
        }
        else
            return Reply::error(__('messages.emailSendError'));
    }
}
