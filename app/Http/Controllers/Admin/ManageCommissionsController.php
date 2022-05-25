<?php

namespace App\Http\Controllers\Admin;

use App\Client;
use App\Commission;
use App\Currency;
use App\Helper\Reply;
use App\Http\Requests\Payments\ImportPayment;
use App\Http\Requests\Commissions\StoreCommissions;
use App\Http\Requests\Commissions\UpdateCommissions;
use App\Invoice;
use App\Payment;
use App\Project;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ManageCommissionsController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = 'Commissions';
        $this->pageIcon = 'fa fa-money';
        $this->middleware(function ($request, $next) {
            if(!in_array('payments',$this->user->modules)){
                abort(403);
            }
            return $next($request);
        });

    }

    public function index()
    {
        $this->projects = Project::all();
//        $this->clients = Client::all();
        return view('admin.commissions.index', $this->data);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function data(Request $request)
    {
        $commissions = Commission::leftJoin('projects', 'projects.id', 'commissions.project_id')
            ->leftJoin('users', 'users.id', 'projects.user_id')
            ->leftJoin('payments', 'payments.id', 'commissions.payment_id')
            ->select('commissions.id', 'commissions.project_id', 'commissions.amount', 'commissions.status', 'commissions.pay_start_date', 'commissions.pay_end_date', 'users.name as designer', 'payments.payment_type as payment_type');


        $commissions = $commissions->orderBy('commissions.id', 'desc')->get();
        return DataTables::of($commissions)
            ->addColumn('action', function ($row) {
                return '<a href="' . route("admin.commissions.edit", $row->id) . '" data-toggle="tooltip" data-original-title="Edit" class="btn btn-info btn-circle"><i class="fa fa-pencil"></i></a>
                        &nbsp;&nbsp;<a href="javascript:;" data-toggle="tooltip" data-original-title="Delete" data-payment-id="' . $row->id . '" class="btn btn-danger btn-circle sa-params"><i class="fa fa-times"></i></a>';
            })

            ->addColumn('project_name', function($row) {
                if ($row->project_id != null) {
                    return ucfirst($row->project->project_name);
                }
                else{
                    return '--';
                }
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
                return '$' . number_format((float)$row->amount, 2, '.', '');
            })
            ->editColumn(
                'pay_start_date',
                function ($row) {
                    if(!is_null($row->pay_start_date)){
                        return $row->pay_start_date->format($this->global->date_format);
                    }
                }
            )
            ->editColumn(
                'pay_end_date',
                function ($row) {
                    if(!is_null($row->pay_end_date)){
                        return $row->pay_end_date->format($this->global->date_format);
                    }
                }
            )
            ->editColumn('payment_type', function ($row) {
                return strtoupper($row->payment_type);
            })
            ->editColumn('project_id', function ($row) {
                return '<a href="'.route("admin.projects.edit", $row->project_id).'">#'.$row->project_id.'</a>';
            })
            ->editColumn('project_id', function ($row) {
                if ($row->project_id != null) {
                    return '<a href="'.route("admin.projects.edit", $row->project_id).'">#'.$row->project_id.'</a>';
                }
                else{
                    return '';
                }
            })
            ->rawColumns(['action', 'status', 'project_id'])
            ->make(true);
    }

    public function create($paymentID = null){
        $this->projects = Project::all();
        if($paymentID){
            $this->payment = Payment::findorFail($paymentID);
        }
        return view('admin.commissions.create', $this->data);
    }

    public function store(StoreCommissions $request)
    {
        $commission = new Commission();
        $commission->project_id = $request->project_id;
        if($request->has('payment_id')){
            $commission->payment_id = $request->payment_id;
        }
        $commission->amount = round($request->amount, 2);
        $commission->pay_start_date =  Carbon::createFromFormat($this->global->date_format, $request->pay_start_date)->format('Y-m-d');
        $commission->pay_end_date =  Carbon::createFromFormat($this->global->date_format, $request->pay_end_date)->format('Y-m-d');

        $commission->status = $request->status;
        $commission->save();

        return Reply::redirect(route('admin.commissions.index'), __('messages.paymentSuccess'));
    }

    public function destroy($id) {
        Payment::destroy($id);
        return Reply::success(__('messages.paymentDeleted'));
    }

    public function edit($id){
        $this->projects = Project::all();
        $this->commission = Commission::findOrFail($id);
        $payments = Payment::where('project_id', '=',$this->commission->project_id)->where('status', 'complete')->get();
        $this->paid_amount = 0;
        foreach ($payments as $payment){
            $this->paid_amount += $payment->amount;
        }

        $this->commissions = DB::table('commissions')
            ->select(
                DB::raw('(select sum(commissions.amount) from `commissions` where `project_id`='.$this->commission->project_id.') as totalCommissions'),
                DB::raw('(select sum(commissions.amount) from `commissions` where `project_id`='.$this->commission->project_id.' and `status`="paid") as paidCommissions '),
                DB::raw('(select sum(commissions.amount) from `commissions` where `project_id`='.$this->commission->project_id.' and `status`!="paid") as pendingCommissions')
            )
            ->first();
        return view('admin.commissions.edit', $this->data);
    }

    public function update(UpdateCommissions $request, $id){

        $commission = Commission::findOrFail($id);
        if($request->project_id != ''){
            $commission->project_id = $request->project_id;
        }
        $commission->amount = round(str_replace(',', '',$request->amount), 2);
        $commission->pay_start_date =  Carbon::createFromFormat($this->global->date_format, $request->pay_start_date)->format('Y-m-d');
        $commission->pay_end_date =  Carbon::createFromFormat($this->global->date_format, $request->pay_end_date)->format('Y-m-d');
        $commission->status = $request->status;
        $commission->save();

        return Reply::redirect(route('admin.commissions.index'), __('messages.paymentSuccess'));
    }

    public function payInvoice($invoiceId){
        $this->invoice = Invoice::findOrFail($invoiceId);
        $this->paidAmount = $this->invoice->getPaidAmount();


        if($this->invoice->status == 'paid'){
            return "Invoice already paid";
        }

        return view('admin.commissions.pay-invoice', $this->data);
    }

    public function importExcel(ImportPayment $request){
        if($request->hasFile('import_file')){
            $path = $request->file('import_file')->getRealPath();
            $data = Excel::load($path)->get();

            if($data->count()){

                foreach ($data as $key => $value) {

                    if($request->currency_character){
                        $amount = substr($value->amount, 1);
                    }
                    else{
                        $amount = substr($value->amount, 0);
                    }

                    $amount = str_replace( ',', '', $amount );
                    $amount = str_replace( ' ', '', $amount );

                    $arr[] = [
                        'paid_on' => Carbon::createFromFormat($this->global->date_format, $value->date)->format('Y-m-d'),
                        'amount' => $amount,
                        'currency_id' => $this->global->currency_id,
                        'status' => 'complete'
                    ];
                }

                if(!empty($arr)){
                    DB::table('payments')->insert($arr);
                }
            }
        }

        return Reply::redirect(route('admin.commissions.index'), __('messages.importSuccess'));
    }

    public function downloadSample(){
        return response()->download(public_path().'/payment-sample.csv');
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param $status
     */
    public function export($startDate, $endDate, $status, $project) {

        $commissions = Payment::leftJoin('projects', 'projects.id', '=', 'payments.project_id')
            ->join('currencies', 'currencies.id', '=', 'payments.currency_id')
            ->select('payments.id','projects.project_name', 'payments.amount', 'currencies.currency_symbol', 'currencies.currency_code', 'payments.status', 'payments.paid_on', 'payments.remarks');

        if($startDate !== null && $startDate != 'null' && $startDate != ''){
            $commissions = $commissions->where(DB::raw('DATE(payments.`paid_on`)'), '>=', $startDate);
        }

        if($endDate !== null && $endDate != 'null' && $endDate != ''){
            $commissions = $commissions->where(DB::raw('DATE(payments.`paid_on`)'), '<=', $endDate);
        }

        if($status != 'all' && !is_null($status)){
            $commissions = $commissions->where('payments.status', '=', $status);
        }

        if($project != 'all' && !is_null($project)){
            $commissions = $commissions->where('payments.project_id', '=', $project);
        }

        $attributes =  ['amount', 'currency_symbol', 'paid_on'];

        $commissions = $commissions->orderBy('payments.id', 'desc')->get()->makeHidden($attributes);

        // Initialize the array which will be passed into the Excel
        // generator.
        $exportArray = [];

        // Define the Excel spreadsheet headers
        $exportArray[] = ['ID','Project','Currency Code','Status','Remark','Amount', 'Paid On'];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($commissions as $row) {
            $exportArray[] = $row->toArray();
        }

        // Generate and return the spreadsheet
        Excel::create('payment', function($excel) use ($exportArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Payment');
            $excel->setCreator('Worksuite')->setCompany($this->companyName);
            $excel->setDescription('payment file');

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
