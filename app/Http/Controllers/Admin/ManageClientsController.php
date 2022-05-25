<?php

namespace App\Http\Controllers\Admin;

use App\Client;
use App\Event;
use App\EventType;
use App\Helper\Reply;
use App\Http\Requests\Admin\Client\StoreClientRequest;
use App\Http\Requests\Admin\Client\UpdateClientRequest;
use App\Http\Requests\Gdpr\SaveConsentUserDataRequest;
use App\Invoice;
use App\Lead;
use App\LeadStatus;
use App\PurposeConsent;
use App\PurposeConsentUser;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ManageClientsController extends AdminBaseController
{

    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.clients');
        $this->pageIcon = 'icon-people';
        $this->middleware(function ($request, $next) {
            if (!in_array('clients', $this->user->modules)) {
                abort(403);
            }
            return $next($request);
        });

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->clients = Client::all();
        $this->totalClients = count($this->clients);

        return view('admin.clients.index', $this->data);
    }

    public function getDetail($id){
        $client = Client::findOrFail($id);

        if($client){
            $data = [];
            $data['client_name'] = $client->full_name;
            $data['phone'] = $client->phone_number;
            $data['email'] = $client->email;
            return Reply::dataOnly(['status'  => 'success', 'data' => $data ]);
        }
        return [];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($leadID = null, $status = null)
    {
        if ($leadID) {
            $this->leadDetail = Lead::findOrFail($leadID);
            if($status && $status === 'convert'){
                $this->convert_sale = true;
            }
        }

        $this->states = Config::get('constants.states');
        return view('admin.clients.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreClientRequest $request)
    {
        $data = $request->except(['_token', 'lead', 'convert_sale']);
        $client = Client::create($data);

        //log search
        $this->logSearchEntry($client->id, $client->first_name. ' ' . $client->last_name, 'admin.clients.edit');

        if ($request->has('lead')) {
            $lead = Lead::findOrFail($request->lead);
            $lead->client_id = $client->id;
            if ($request->has('convert_sale')) {
                $lead->status_id = 3;
            }
            $lead->save();

            return Reply::redirect(route('admin.leads.index'), __('messages.leadClientChangeSuccess'));
        }
        else{
            return Reply::redirect(route('admin.clients.index'), __('messages.clientAdded'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->client = Client::findOrFail($id);
        $this->states = Config::get('constants.states');
        $this->designers = User::allDesignersList();
        $this->status = LeadStatus::all();
        $this->eventTypes = EventType::all()->pluck('type', 'id');
        $lead_ids = []; $project_ids = [];
        foreach ($this->client->leads as $lead) {
            $lead_ids[]  = $lead->id;
        }
        foreach ($this->client->projects as $projects) {
            $project_ids[]  = $projects->id;
        }
        $this->appointments = Event::whereIn('lead_id', $lead_ids)->orWhereIn('project_id', $project_ids)->orWhere('client_id', $id)->get();
        return view('admin.clients.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->client = Client::findOrFail($id);
        $this->states = Config::get('constants.states');
        return view('admin.clients.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateClientRequest $request, $id)
    {
        $data = $request->except(['_token']);
        $client = Client::findOrFail($id);
        $client->update($data);
        return Reply::redirect(route('admin.clients.index'), __('messages.clientUpdated'));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Client::destroy($id);
        $leads = Lead::where('client_id', $id)->get();
        foreach ($leads as $lead){
            $lead->client_id = Null;
            $lead->status_id = 1;
            $lead->save();
        }
        return Reply::success(__('messages.clientDeleted'));
    }

    public function data(Request $request)
    {
        $clients = Client::select('id', 'first_name', 'last_name', 'email', 'cell', 'zip', 'created_at');
        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = Carbon::createFromFormat($this->global->date_format, $request->startDate)->toDateString();
            $clients = $clients->where(DB::raw('DATE(`created_at`)'), '>=', $startDate);
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = Carbon::createFromFormat($this->global->date_format, $request->endDate)->toDateString();
            $clients = $clients->where(DB::raw('DATE(`created_at`)'), '<=', $endDate);
        }

        if ($request->client != 'all' && $request->client != '') {
            $clients = $clients->where('id', $request->client);
        }

        $clients = $clients->orderBy('first_name', 'ASC')->get();
        
        return DataTables::of($clients)
            ->addColumn('action', function($row){
                $createlead = '<li><a href="'.route('admin.leads.create').'/'.$row->id.'"><i class="ti-receipt"></i> '.__('modules.client.createLead').'</a></li>';
                $createProject = '<li><a href="'.route('admin.projects.create-client', [$row->id]).'"><i class="icon-layers"></i> '.__('modules.client.createProject').'</a></li>';

                $action = '<div class="btn-group m-r-10">
                <button aria-expanded="false" data-toggle="dropdown" class="btn btn-info btn-outline  dropdown-toggle waves-effect waves-light" type="button">'.__('modules.lead.action').'  <span class="caret"></span></button>
                <ul role="menu" class="dropdown-menu">
                    <li><a href="'.route('admin.clients.show', $row->id).'"><i class="fa fa-search"></i> '.__('modules.lead.view').'</a></li>
                    <li><a href="'.route('admin.clients.edit', $row->id).'"><i class="fa fa-edit"></i> '.__('modules.lead.edit').'</a></li>
                    <li><a href="javascript:;" class="sa-params" data-user-id="'.$row->id.'"><i class="fa fa-trash "></i> '.__('app.delete').'</a></li>
                     '.$createlead.$createProject.'
                </ul>
              </div>';
               return $action;
            })
            ->editColumn(
                'email',
                function ($row) {
                    if(!empty($row->email)){
                        return '<a href="mailto:'.$row->email.'">'.$row->email.'</a>';
                    }
                    else{
                        return '';
                    }
                }
            )
            ->editColumn(
                'created_at',
                function ($row) {
                    return Carbon::parse($row->created_at)->format($this->global->date_format);
                }
            )
            ->rawColumns(['name', 'action', 'email'])
            ->make(true);
    }

    public function showProjects($id) {
        $this->client = Client::findOrFail($id);
        $this->states = Config::get('constants.states');
        $this->designers = User::allDesignersList();
        $this->status = LeadStatus::all();
        $this->eventTypes = EventType::all()->pluck('type', 'id');
        $lead_ids = []; $project_ids = [];
        foreach ($this->client->leads as $lead) {
            $lead_ids[]  = $lead->id;
        }
        foreach ($this->client->projects as $projects) {
            $project_ids[]  = $projects->id;
        }
        $this->appointments = Event::whereIn('lead_id', $lead_ids)->orWhereIn('project_id', $project_ids)->orWhere('client_id', $id)->get();
        return view('admin.clients.projects', $this->data);
    }

    public function showInvoices($id) {
        $this->client = Client::findOrFail($id);

        $this->invoices = Invoice::join('projects', 'projects.id', '=', 'invoices.project_id')
            ->join('currencies', 'currencies.id', '=', 'invoices.currency_id')
            ->join('clients', 'clients.id', '=', 'projects.client_id')
            ->select('invoices.invoice_number', 'invoices.total', 'currencies.currency_symbol', 'invoices.issue_date', 'invoices.id')
            ->where('projects.client_id', $id)
            ->get();
        return view('admin.clients.invoices', $this->data);
    }

    public function export($status, $client) {
        $rows = Client::all();

            if($status != 'all' && $status != ''){
                $rows = $rows->where('users.status', $status);
            }

            if($client != 'all' && $client != ''){
                $rows = $rows->where('users.id', $client);
            }

            $rows = $rows->get();

        // Initialize the array which will be passed into the Excel
        // generator.
        $exportArray = [];

        // Define the Excel spreadsheet headers
        $exportArray[] = ['ID', 'Name','Email','Mobile','Company Name', 'Address', 'Website', 'Created at'];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($rows as $row) {
            $exportArray[] = $row->toArray();
        }

        // Generate and return the spreadsheet
        Excel::create('clients', function($excel) use ($exportArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Clients');
            $excel->setCreator('Worksuite')->setCompany($this->companyName);
            $excel->setDescription('clients file');

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

    public function gdpr($id)
    {
        $this->client = User::withoutGlobalScope('active')->findOrFail($id);
        $this->clientDetail = ClientDetails::where('user_id', '=', $this->client->id)->first();
        $this->allConsents = PurposeConsent::with(['user' => function($query) use ($id) {
            $query->where('client_id', $id)
                ->orderBy('created_at', 'desc');
        }])->get();

        return view('admin.clients.gdpr', $this->data);
    }

    public function consentPurposeData($id)
    {
        $purpose = PurposeConsentUser::select('purpose_consent.name', 'purpose_consent_users.created_at', 'purpose_consent_users.status', 'purpose_consent_users.ip', 'users.name as username', 'purpose_consent_users.additional_description')
            ->join('purpose_consent', 'purpose_consent.id', '=', 'purpose_consent_users.purpose_consent_id')
            ->leftJoin('users', 'purpose_consent_users.updated_by_id', '=', 'users.id')
            ->where('purpose_consent_users.client_id', $id);

        return DataTables::of($purpose)
            ->editColumn('status', function ($row) {
                if($row->status == 'agree')
                {
                    $status = __('modules.gdpr.optIn');
                } else if($row->status == 'disagree')
                {
                    $status = __('modules.gdpr.optOut');
                } else {
                    $status = '';
                }

                return $status;
            })
            ->make(true);
    }

    public function saveConsentLeadData(SaveConsentUserDataRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $consent = PurposeConsent::findOrFail($request->consent_id);

        if($request->consent_description && $request->consent_description != '')
        {
            $consent->description = $request->consent_description;
            $consent->save();
        }

        // Saving Consent Data
        $newConsentLead = new PurposeConsentUser();
        $newConsentLead->client_id = $user->id;
        $newConsentLead->purpose_consent_id = $consent->id;
        $newConsentLead->status = trim($request->status);
        $newConsentLead->ip = $request->ip();
        $newConsentLead->updated_by_id = $this->user->id;
        $newConsentLead->additional_description = $request->additional_description;
        $newConsentLead->save();

        $url = route('admin.clients.gdpr', $user->id);

        return Reply::redirect($url);
    }
}
