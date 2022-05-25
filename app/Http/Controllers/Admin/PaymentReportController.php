<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Reply;
use App\InterestArea;
use App\LeadSource;
use App\LeadStatus;
use App\Notifications\NewReportEmail;
use App\Payment;
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

class PaymentReportController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.paymentReport');
        $this->pageIcon = 'ti-pie-chart';
    }

    public function index() {
        $this->fromDate = Carbon::today()->firstOfMonth();
        $this->toDate = Carbon::today();
        $this->designers = User::allDesigners();
        $this->projects = Project::select('id', 'project_name')->pluck('project_name', 'id')->toArray();
        $this->statuses = Payment::getEnumColumnValues('payments', 'status');
        $this->payment_types = Payment::getEnumColumnValues('payments', 'payment_type');
        $this->fieldsData = ReportSetting::where('type', 'payment');
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $this->fieldsData = $this->fieldsData->where('role', 'admin');

        } elseif ($user->hasRole('designer')) {
            $this->fieldsData = $this->fieldsData->where('role', 'designer');

        } elseif ($user->hasRole('employee')) {
            $this->fieldsData = $this->fieldsData->where('role', 'employee');
        }
        $this->fieldsData = $this->fieldsData->select('field_name', 'status')->pluck('status', 'field_name')->toArray();
        return view('admin.reports.payments.index', $this->data);
    }

    public function store(Request $request){

    }

    public function data(Request $request) { //$startDate = null, $endDate = null, $client = null, $project = null
        $startDate = null; $endDate = null; $client = null; $project = null;
        $designer_id = null; $status = null; $payment_type = null;
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

        if($request->has('status')){
            $payment_type = $request->get('payment_type');
        }

        $payments = Payment::leftJoin('projects', 'projects.id', 'payments.project_id')
            ->leftJoin('users', 'users.id', 'projects.user_id')
            ->leftJoin('clients', 'clients.id', 'projects.client_id')
            ->select('payments.id', 'payments.created_at', 'payments.amount','payments.paid_on', 'payments.remarks as remark', 'payments.payment_type', 'payments.status',
                'users.name as designer', 'clients.first_name as client_first_name', 'clients.last_name as client_last_name',
                'projects.project_name as project', 'projects.sales_price as sales_price');

        if (!is_null($startDate)) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
            $payments->where(DB::raw('DATE(payments.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
            $payments->where(DB::raw('DATE(payments.`created_at`)'), '<=', $endDate);
        }

        if(!is_null($designer_id)){
            $payments->where('users.id', $designer_id);
        }

        if(!is_null($project) && $project){
            $payments->where('payments.project_id', $project);
        }

        if(!is_null($status)){
            $payments->where('payments.status', $status);
        }

        if(!is_null($payment_type)){
            $payments->where('payments.payment_type', $payment_type);
        }

        $payments->orderBy('payments.created_at', 'DESC');
        $payments = $payments->get();

        $removeData = ['updated_at', 'client_first_name', 'client_last_name', 'client_id', 'user_id'];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'payment');

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

        return DataTables::of($payments)
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
            ->editColumn('status', function ($row) {
                if ($row->status == 'pending') {
                    $status = '<label class="label label-warning">' . strtoupper($row->status) . '</label>';
                }  else if ($row->status == 'complete') {
                    $status = '<label class="label label-success">' . strtoupper($row->status) . '</label>';
                }
                return $status;
            })
            ->editColumn('amount', function ($row) {
                return '$'.number_format($row->amount, 2, '.', ',');
            })
            ->editColumn('paid_on', function ($row) {
                return $row->paid_on->format($this->global->date_format);
            })
            ->editColumn('project', function ($row) {
                return ucfirst($row->project);
            })
            ->editColumn('payment_type', function ($row) {
                return ucfirst($row->payment_type);
            })
            ->rawColumns(['date', 'status', 'action'])
            ->removeColumn($removeData)
            ->make(true);
    }

    public function export(Request $request) {
        $startDate = null; $endDate = null; $client = null; $project = null;
        $designer_id = null; $status = null; $payment_type = null;
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

        if($request->has('payment_type')){
            $payment_type = $request->get('payment_type');
        }

        $payments = Payment::leftJoin('projects', 'projects.id', 'payments.project_id')
            ->leftJoin('users', 'users.id', 'projects.user_id')
            ->leftJoin('clients', 'clients.id', 'projects.client_id')
            ->select('payments.id', 'payments.created_at', 'payments.amount','payments.paid_on', 'payments.payment_type','payments.status',
                'users.name as designer', 'clients.first_name as client_first_name', 'clients.last_name as client_last_name',
                'projects.project_name as project', 'projects.sales_price as sales_price');

        if (!is_null($startDate)) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
            $payments->where(DB::raw('DATE(payments.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
            $payments->where(DB::raw('DATE(payments.`created_at`)'), '<=', $endDate);
        }

        if(!is_null($designer_id)){
            $payments->where('users.id', $designer_id);
        }

        if(!is_null($project) && $project){
            $payments->where('payments.project_id', $project);
        }

        if(!is_null($status)){
            $payments->where('payments.status', $status);
        }

        if(!is_null($payment_type)){
            $payments->where('payments.payment_type', $payment_type);
        }

        $payments->orderBy('payments.created_at', 'DESC');
        $payments = $payments->get();

        $activeData = ['id'];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'payment');

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

        $title = 'Payment Report';

        foreach ($payments as $row) {
            $data = [];
            $data['id'] = $row->id;
            foreach ($activeData as $active) {
                if($active == 'client'){
                    $data['client'] = ucfirst($row->client_first_name). ' ' . ucfirst($row->client_last_name);
                }
                else if($active == 'sales_price'){
                    $data['sales_price'] = number_format($row->sales_price, 2, '.', ',');
                }
                else if($active == 'paid_on'){
                    $data['paid_on'] = $row->paid_on->format($this->global->date_format);
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

    public function exportPDF(Request $request) {
        $startDate = null; $endDate = null; $client = null; $project = null;
        $designer_id = null; $status = null; $payment_type = null;
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

        if($request->has('payment_type')){
            $payment_type = $request->get('payment_type');
        }

        $payments = Payment::leftJoin('projects', 'projects.id', 'payments.project_id')
            ->leftJoin('users', 'users.id', 'projects.user_id')
            ->leftJoin('clients', 'clients.id', 'projects.client_id')
            ->select('payments.id', 'payments.created_at', 'payments.amount','payments.paid_on', 'payments.payment_type','payments.status',
                'users.name as designer', 'clients.first_name as client_first_name', 'clients.last_name as client_last_name',
                'projects.project_name as project', 'projects.sales_price as sales_price');

        if (!is_null($startDate)) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
            $payments->where(DB::raw('DATE(payments.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();
            $payments->where(DB::raw('DATE(payments.`created_at`)'), '<=', $endDate);
        }

        if(!is_null($designer_id)){
            $payments->where('users.id', $designer_id);
        }

        if(!is_null($project) && $project){
            $payments->where('payments.project_id', $project);
        }

        if(!is_null($status)){
            $payments->where('payments.status', $status);
        }

        if(!is_null($payment_type)){
            $payments->where('payments.payment_type', $payment_type);
        }

        $payments->orderBy('payments.created_at', 'DESC');
        $payments = $payments->get();

        $activeData = ['id'];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'payment');

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

        $title = 'Payment Report';

        foreach ($payments as $row) {
            $data = [];
            $data['id'] = $row->id;
            foreach ($activeData as $active) {
                if($active == 'client'){
                    $data['client'] = ucfirst($row->client_first_name). ' ' . ucfirst($row->client_last_name);
                }
                else if($active == 'sales_price'){
                    $data['sales_price'] = number_format($row->sales_price, 2, '.', ',');
                }
                else if($active == 'paid_on'){
                    $data['paid_on'] = $row->paid_on->format($this->global->date_format);
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
        $pdf->loadView('admin.reports.payments.report-pdf', $this->data);
        return $pdf->download($title.'.pdf');
    }

    public function sendEmail($payment_id = null) {

        $payment = Payment::leftJoin('projects', 'projects.id', 'payments.project_id')
            ->leftJoin('users', 'users.id', 'projects.user_id')
            ->leftJoin('clients', 'clients.id', 'projects.client_id')
            ->select('payments.id', 'payments.created_at', 'payments.amount','payments.paid_on', 'payments.payment_type','payments.status',
                'users.name as designer', 'users.id as user_id', 'clients.first_name as client_first_name', 'clients.last_name as client_last_name',
                'projects.project_name as project', 'projects.sales_price as sales_price');


        $payment->where('payments.id', $payment_id);
        $payment = $payment->first();

        $activeData = [];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'payment');

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
            }
        }

        $data = [];
        $data['ID'] = $payment->id;
        foreach ($activeData as $active) {
            $key = Lang::has('modules.payments.'.$active) ? __('modules.payments.'.$active) : __('modules.payments.'.Str::camel($active));
            if($active == 'client'){
                $data[$key] = ucfirst($payment->client_first_name). ' ' . ucfirst($payment->client_last_name);
            }
            else if($active == 'sales_price'){
                $data[$key] = number_format($payment->sales_price, 2, '.', ',');
            }
            else if($active == 'paid_on'){
                $data[$key] = $payment->paid_on->format($this->global->date_format);
            }
            else if($active == 'amount'){
                $data[$key] = '$'.number_format($payment->amount, 2, '.', ',');
            }
            else{
                $data[$key] = $payment->{$active};
            }
        }
        $data['Created On'] = $payment->created_at->format($this->global->date_format);

        if(!empty($payment->user_id)){
            $notifyUser = User::withoutGlobalScope('active')->findOrFail($payment->user_id);
            $notifyUser->notify(new NewReportEmail($data, $notifyUser, 'payment'));
            return Reply::success(__('messages.emailSendSuccess'));
        }
        else
            return Reply::error(__('messages.emailSendError'));

    }


}
