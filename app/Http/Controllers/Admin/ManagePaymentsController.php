<?php

namespace App\Http\Controllers\Admin;

use App\Client;
use App\Helper\Reply;
use App\Http\Requests\Payments\ImportPayment;
use App\Http\Requests\Payments\StoreCommissions;
use App\Http\Requests\Payments\StorePayment;
use App\Http\Requests\Payments\UpdateCommissions;
use App\Http\Requests\Payments\UpdatePayments;
use App\Invoice;
use App\Payment;
use App\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ManagePaymentsController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.payments');
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
        $this->clients = Client::all();
        return view('admin.payments.index', $this->data);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function data(Request $request)
    {
        $payments = Payment::leftJoin('projects', 'projects.id', 'payments.project_id')
            ->leftJoin('users', 'users.id', 'projects.user_id')
            ->select('payments.id', 'payments.project_id', 'payments.amount', 'payments.status', 'payments.paid_on', 'payments.remarks', 'payments.payment_type', 'users.name as designer');

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = Carbon::createFromFormat($this->global->date_format, $request->startDate)->toDateString();
            $payments = $payments->where(DB::raw('DATE(payments.`paid_on`)'), '>=', $startDate);
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = Carbon::createFromFormat($this->global->date_format, $request->endDate)->toDateString();
            $payments = $payments->where(DB::raw('DATE(payments.`paid_on`)'), '<=', $endDate);
        }

        if ($request->status != 'all' && !is_null($request->status)) {
            $payments = $payments->where('payments.status', '=', $request->status);
        }

        if ($request->project != 'all' && !is_null($request->project)) {
            $payments = $payments->where('payments.project_id', '=', $request->project);
        }

        if ($request->client != 'all' && !is_null($request->client)) {
            $payments = $payments->where('projects.client_id', '=', $request->client);
        }

        $payments = $payments->orderBy('payments.id', 'desc')->get();

        return DataTables::of($payments)
            ->addColumn('action', function ($row) {
                return '<a href="' . route("admin.payments.edit", $row->id) . '" data-toggle="tooltip" data-original-title="Edit" class="btn btn-info btn-circle"><i class="fa fa-pencil"></i></a>
                        &nbsp;&nbsp;<a href="javascript:;" data-toggle="tooltip" data-original-title="Delete" data-payment-id="' . $row->id . '" class="btn btn-danger btn-circle sa-params"><i class="fa fa-times"></i></a>';
            })
            ->editColumn('remarks', function($row) {
                return ucfirst($row->remarks);
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
                if($row->status == 'pending'){
                    return '<label class="label label-warning">'.strtoupper($row->status).'</label>';
                }else{
                    return '<label class="label label-success">'.strtoupper($row->status).'</label>';
                }
            })
            ->editColumn('payment_type', function ($row) {
                return strtoupper($row->payment_type);
            })
            ->editColumn('amount', function ($row) {
                return '$' . number_format((float)$row->amount, 2, '.', '');
            })
            ->editColumn(
                'paid_on',
                function ($row) {
                    if(!is_null($row->paid_on)){
                        return $row->paid_on->format($this->global->date_format);
                    }
                }
            )
            ->editColumn('project_id', function ($row) {
                if ($row->project_id != null) {
                    return '<a href="'.route("admin.projects.edit", $row->project_id).'">#'.$row->project_id.'</a>';
                }
                else{
                    return '';
                }
            })
            ->rawColumns(['invoice', 'action', 'status', 'designer', 'project_id'])
            ->removeColumn('currency_symbol')
            ->removeColumn('currency_code')
            ->make(true);
    }

    public function create(){
        $this->projects = Project::all();
        return view('admin.payments.create', $this->data);
    }

    public function store(StorePayment $request)
    {
        $payment = new Payment();
        $payment->project_id = $request->project_id;

        $payment->amount = round($request->amount, 2);
        $payment->paid_on =  Carbon::createFromFormat($this->global->date_format, $request->paid_on)->format('Y-m-d H:i:s');

        $payment->remarks = $request->remarks;
        $payment->payment_type = $request->payment_type;
        $payment->save();

        if($request->has('create_commission') && $request->create_commission){
            return Reply::redirect(route('admin.commissions.create').'/'.$payment->id, __('messages.paymentSuccess'));
        }

        return Reply::redirect(route('admin.payments.index'), __('messages.paymentSuccess'));
    }

    public function destroy($id) {
        Payment::destroy($id);
        return Reply::success(__('messages.paymentDeleted'));
    }

    public function edit($id){
        $this->projects = Project::all();
        $this->payment = Payment::findOrFail($id);
        $payments = Payment::where('project_id', '=',$this->payment->project_id)->where('status', 'complete')->get();
        $this->paid_amount = 0;
        foreach ($payments as $payment){
            $this->paid_amount += $payment->amount;
        }

        $this->commissions = DB::table('commissions')
            ->select(
                DB::raw('(select sum(commissions.amount) from `commissions` where `project_id`='.$this->payment->project_id.') as totalCommissions'),
                DB::raw('(select sum(commissions.amount) from `commissions` where `project_id`='.$this->payment->project_id.' and `status`="paid") as paidCommissions '),
                DB::raw('(select sum(commissions.amount) from `commissions` where `project_id`='.$this->payment->project_id.' and `status`!="paid") as pendingCommissions')
            )
            ->first();
        return view('admin.payments.edit', $this->data);
    }

    public function update(UpdatePayments $request, $id){

        $payment = Payment::findOrFail($id);
        if($request->project_id != ''){
            $payment->project_id = $request->project_id;
        }
        $payment->amount = round(str_replace(',', '',$request->amount), 2);
        $payment->paid_on = Carbon::createFromFormat($this->global->date_format, $request->paid_on)->format('Y-m-d H:i:s');
        $payment->status = $request->status;
        $payment->remarks = $request->remarks;
        $payment->payment_type = $request->payment_type;

        if($request->has('create_commission') && $request->create_commission){
            return Reply::redirect(route('admin.commissions.create').'/'.$payment->id, __('messages.paymentSuccess'));
        }

        if($request->has('update_commission') && $request->update_commission){
            if(!empty($payment->commission)){
                $amount = null;
                if($payment->project->commission_type == 'dollar'){
                    $amount = $payment->project->commission;
                }
                else{
                    $amount = $payment->amount * $payment->project->commission / 100;
                }
                $payment->commission->amount = $amount;
                $payment->commission->save();
            }
        }


        $payment->save();

        return Reply::redirect(route('admin.payments.index'), __('messages.paymentSuccess'));
    }

    public function payInvoice($invoiceId){
        $this->invoice = Invoice::findOrFail($invoiceId);
        $this->paidAmount = $this->invoice->getPaidAmount();


        if($this->invoice->status == 'paid'){
            return "Invoice already paid";
        }

        return view('admin.payments.pay-invoice', $this->data);
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

        return Reply::redirect(route('admin.payments.index'), __('messages.importSuccess'));
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

        $payments = Payment::leftJoin('projects', 'projects.id', '=', 'payments.project_id')
            ->join('currencies', 'currencies.id', '=', 'payments.currency_id')
            ->select('payments.id','projects.project_name', 'payments.amount', 'currencies.currency_symbol', 'currencies.currency_code', 'payments.status', 'payments.paid_on', 'payments.remarks');

        if($startDate !== null && $startDate != 'null' && $startDate != ''){
            $payments = $payments->where(DB::raw('DATE(payments.`paid_on`)'), '>=', $startDate);
        }

        if($endDate !== null && $endDate != 'null' && $endDate != ''){
            $payments = $payments->where(DB::raw('DATE(payments.`paid_on`)'), '<=', $endDate);
        }

        if($status != 'all' && !is_null($status)){
            $payments = $payments->where('payments.status', '=', $status);
        }

        if($project != 'all' && !is_null($project)){
            $payments = $payments->where('payments.project_id', '=', $project);
        }

        $attributes =  ['amount', 'currency_symbol', 'paid_on'];

        $payments = $payments->orderBy('payments.id', 'desc')->get()->makeHidden($attributes);

        // Initialize the array which will be passed into the Excel
        // generator.
        $exportArray = [];

        // Define the Excel spreadsheet headers
        $exportArray[] = ['ID','Project','Currency Code','Status','Remark','Amount', 'Paid On'];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($payments as $row) {
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
