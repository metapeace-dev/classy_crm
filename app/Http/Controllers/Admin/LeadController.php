<?php

namespace App\Http\Controllers\Admin;

use App\Client;
use App\Event;
use App\EventStatus;
use App\EventType;
use App\Helper\Reply;
use App\Http\Requests\CommonRequest;
use App\Http\Requests\Gdpr\SaveConsentLeadDataRequest;
use App\Http\Requests\Lead\StoreRequest;
use App\Http\Requests\Lead\UpdateRequest;
use App\InterestArea;
use App\Lead;
use App\LeadFollowUp;
use App\LeadSource;
use App\LeadStatus;
use App\ProjectLocation;
use App\PurposeConsent;
use App\PurposeConsentLead;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class LeadController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageIcon = 'user-follow';
        $this->pageTitle = 'leads';

        $this->middleware(function ($request, $next) {
            if(!in_array('leads',$this->modules)){
                abort(403);
            }

            return $next($request);
        });

    }

    public function index() {
        $this->totalLeads = Lead::all();

        $this->totalClientConverted = $this->totalLeads->filter(function ($value, $key) {
            return $value->client_id != null;
        });
        $this->totalLeads = Lead::all()->count();
        $this->totalClientConverted = $this->totalClientConverted->count();

        return view('admin.lead.index', $this->data);
    }

    public function show($id) {
        $this->lead = Lead::findOrFail($id);
        $this->sources = LeadSource::all();
        $this->designers = User::allDesignersList();
        $this->areas = InterestArea::all()->toArray();
        $this->states = Config::get('constants.states');
        $this->appointments = Event::getEventsWithLead($id);
        $this->eventTypes = EventType::all()->pluck('type', 'id');
        $this->eventStatus = EventStatus::all()->pluck('name', 'id');
        return view('admin.lead.show', $this->data);
    }

    public function data(CommonRequest $request, $id = null) {
        $currentDate = Carbon::today()->format('Y-m-d');
        $lead = Lead::select('leads.id','leads.client_id', 'leads.project_id','first_name', 'last_name', 'cell', 'zip','lead_status.type as statusName','status_id', 'leads.created_at', 'lead_sources.name as source', 'users.name as designer')
           ->leftJoin('lead_status', 'lead_status.id', 'leads.status_id')
           ->leftJoin('lead_sources', 'lead_sources.id', 'leads.source_id')
           ->leftJoin('users', 'users.id', 'leads.user_id');
        if($request->client != 'all' && $request->client != ''){
            if($request->client == 'lead'){
                $lead = $lead->whereNull('client_id');
            }
            else{
                $lead = $lead->whereNotNull('client_id');
            }
        }

        $lead = $lead->GroupBy('leads.id')->orderBy('leads.created_at', 'DESC')->get();
        return DataTables::of($lead)
            ->addColumn('action', function($row){
                if($row->client_id == null || $row->client_id == ''){
                    $convertToClient = '<li><a href="javascript:void(0);" class="btn_convert" data-id="'.$row->id.'"><i class="fa fa-user"></i> '.__('modules.lead.changeToClient').'</a></li>';
                }
                else{
                    $convertToClient = '';
                }

                if($row->project_id == null || $row->project_id == ''){
                    if(!empty($row->client_id)){
                        $convertToProject = '<li><a href="'.route('admin.projects.create-lead', [$row->id]).'"><i class="icon-layers"></i> '.__('modules.lead.changeToProject').'</a></li>';
                    }
                    else
                        $convertToProject = '<li><a href="javascript:void(0);" class="btn_convert_project" data-id="'.$row->id.'"><i class="icon-layers"></i> '.__('modules.lead.changeToProject').'</a></li>';

                }
                else{
                    $convertToProject = '';
                }

                $action = '<div class="btn-group m-r-10">
                <button aria-expanded="false" data-toggle="dropdown" class="btn btn-info btn-outline  dropdown-toggle waves-effect waves-light" type="button">'.__('modules.lead.action').'  <span class="caret"></span></button>
                <ul role="menu" class="dropdown-menu">
                    <li><a href="'.route('admin.leads.show', $row->id).'"><i class="fa fa-search"></i> '.__('modules.lead.view').'</a></li>
                    <li><a href="'.route('admin.leads.edit', $row->id).'"><i class="fa fa-edit"></i> '.__('modules.lead.edit').'</a></li>
                    <li><a href="javascript:;" class="sa-params" data-user-id="'.$row->id.'"><i class="fa fa-trash"></i> '.__('app.delete').'</a></li>
                    <li><a href="'.route("admin.events.lead", $row->id).'"><i class="fa fa-calendar"></i> '.__('modules.lead.appointment_schedule').'</a></li>
                     '.$convertToClient.$convertToProject.'   
                </ul>
              </div>';
               return $action;
            })
            ->addColumn('status', function($row){
                $status = LeadStatus::all();
                $statusLi = '';
                foreach($status as $st) {
                    if($row->status_id == $st->id){
                        $selected = 'selected';
                    }else{
                        $selected = '';
                    }
                    $statusLi .= '<option '.$selected.' value="'.$st->id.'">'.$st->type.'</option>';
                }

                $action = '<select class="form-control" name="statusChange" onchange="changeStatus( '.$row->id.', this.value)">
                    '.$statusLi.'
                </select>';


                return $action;
            })
            ->editColumn('created_at', function($row){
                return $row->created_at->format($this->global->date_format);
            })
            ->removeColumn(['client_id', 'source', 'statusName'])
            ->rawColumns(['status','action','client_name'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($clientID = null)
    {
        if ($clientID) {
            $this->clientDetail = Client::findOrFail($clientID);
        }
        $this->sources = LeadSource::all();
        $this->status = LeadStatus::all();
        $this->designers = User::allDesigners();
        $this->areas = InterestArea::all();
        $this->states = Config::get('constants.states');
        return view('admin.lead.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        $interest_areas = '';
        if(!empty($request->interest_area)){
            $interest_areas = implode(',',$request->interest_area);
        }
        $project_location = new ProjectLocation();
        $project_location->address1 = $request->pl_address1;
        $project_location->address2 = $request->pl_address2;
        $project_location->city = $request->pl_city;
        $project_location->state = $request->pl_state;
        $project_location->zip = $request->pl_zip;
        $project_location->save();

        $lead = new Lead();
        $lead->company_name = $request->company_name;
        $lead->first_name = $request->first_name;
        $lead->last_name = $request->last_name;
        $lead->address1 = $request->address1;
        $lead->address2 = $request->address2;
        $lead->city = $request->city;
        $lead->state = $request->state;
        $lead->zip = $request->zip;
        $lead->phone = $request->phone;
        $lead->ext = $request->ext;
        $lead->cell = $request->cell;
        $lead->cell_contact = $request->cell_contact;
        $lead->cell2 = $request->cell2;
        $lead->cell2_contact = $request->cell2_contact;
        $lead->fax = $request->fax;
        $lead->email = $request->email;
        $lead->second_email = $request->second_email;
        $lead->ref = $request->ref;
        $lead->status_id = 1;
        $lead->note = $request->note;
        $lead->user_id = $request->user_id;
        $lead->source_id = $request->source;
        $lead->project_location_id = $project_location->id;
        $lead->interest_areas = $interest_areas;

        if($request->has('first_name') && !empty($request->first_name)){
            $client = Client::where('first_name', $request->first_name);
        }
        if($request->has('last_name') && !empty($request->last_name)){
            $client = $client->where('last_name', $request->last_name);
        }
        if($request->has('address1') && !empty($request->address1)){
            $client = $client->where('address1', $request->address1);
        }
        if($request->has('zip') && !empty($request->zip)){
            $client = $client->where('zip', $request->zip);
        }
        if($request->has('phone') && !empty($request->phone)){
            $client = $client->where('phone_number', $request->phone);
        }

        $client = $client->first();

        if(!empty($client)){
            $lead->client_id = $client->id;
        }

        $lead->save();

        //log search
        $this->logSearchEntry($lead->id, $lead->first_name . ' ' . $lead->last_name , 'admin.leads.show');

        return Reply::redirect(route('admin.leads.index'), __('messages.LeadAddedUpdated'));
    }


    public function getDetail($id){
        $lead = Lead::findOrFail($id);

        if($lead){
            $data = $lead->toArray();
            if($lead->client_id){
                $data['client_name'] = $lead->client->full_name;
            }
            else{
                $data['client_name'] ='';
            }
            return Reply::dataOnly(['status'  => 'success', 'data' => $data ]);
        }
        return [];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->lead = Lead::findOrFail($id);
        $this->sources = LeadSource::all();
        $this->status = LeadStatus::all();
        $this->designers = User::allDesigners();
        $this->areas = InterestArea::all();
        $this->states = Config::get('constants.states');
        return view('admin.lead.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        $interest_areas = '';
        if(!empty($request->interest_area)){
            $interest_areas = implode(',',$request->interest_area);
        }
        $lead = Lead::findOrFail($id);
        if($lead->project_location != null) {
            $lead->project_location->address1 = $request->pl_address1;
            $lead->project_location->address2 = $request->pl_address2;
            $lead->project_location->city = $request->pl_city;
            $lead->project_location->state = $request->pl_state;
            $lead->project_location->zip = $request->pl_zip;
            $lead->project_location->save();
        }
        else {
            $project_location = new ProjectLocation();
            $project_location->address1 = $request->pl_address1;
            $project_location->address2 = $request->pl_address2;
            $project_location->city = $request->pl_city;
            $project_location->state = $request->pl_state;
            $project_location->zip = $request->pl_zip;
            $project_location->save();
            $lead->project_location_id = $project_location->id;
        }
        $lead->company_name = $request->company_name;
        $lead->first_name = $request->first_name;
        $lead->last_name = $request->last_name;
        $lead->address1 = $request->address1;
        $lead->address2 = $request->address2;
        $lead->city = $request->city;
        $lead->state = $request->state;
        $lead->zip = $request->zip;
        $lead->phone = $request->phone;
        $lead->ext = $request->ext;
        $lead->cell = $request->cell;
        $lead->cell_contact = $request->cell_contact;
        $lead->cell2 = $request->cell2;
        $lead->cell2_contact = $request->cell2_contact;
        $lead->fax = $request->fax;
        $lead->email = $request->email;
        $lead->second_email = $request->second_email;
        $lead->ref = $request->ref;
        $lead->note = $request->note;
        $lead->status_id = $request->status;
        $lead->user_id = $request->user_id;
        $lead->source_id = $request->source;
        $lead->interest_areas = $interest_areas;
        $lead->save();

        if(!empty($request->user_id)){
            $event = Event::where('lead_id', $lead->id)->where('event_type', 1)->first();
            if(!empty($event) && !empty($event->attendee)){
                $event->attendee->user_id = $request->user_id;
                $event->attendee->save();
            }
        }


        return Reply::redirect(route('admin.leads.index'), __('messages.LeadUpdated'));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Lead::destroy($id);
        return Reply::success(__('messages.LeadDeleted'));
    }

    /**
     * @param CommonRequest $request
     * @return array
     */
    public function changeStatus(CommonRequest $request)
    {
        $lead = Lead::findOrFail($request->leadID);
        $lead->status_id = $request->statusID;
        $lead->save();

        return Reply::success(__('messages.leadStatusChangeSuccess'));
    }

    /**
     * @param $leadID
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function followUpCreate($leadID){
        $this->leadID = $leadID;
        return view('admin.lead.follow_up', $this->data);
    }

    public function gdpr($leadID)
    {
        $this->lead = Lead::findOrFail($leadID);
        $this->allConsents = PurposeConsent::with(['lead' => function($query) use ($leadID) {
            $query->where('lead_id', $leadID)
                ->orderBy('created_at', 'desc');
        }])->get();

        return view('admin.lead.gdpr.show', $this->data);
    }

    public function consentPurposeData($id)
    {
        $purpose = PurposeConsentLead::select('purpose_consent.name', 'purpose_consent_leads.created_at', 'purpose_consent_leads.status', 'purpose_consent_leads.ip', 'users.name as username', 'purpose_consent_leads.additional_description')
                                    ->join('purpose_consent', 'purpose_consent.id', '=', 'purpose_consent_leads.purpose_consent_id')
                                    ->leftJoin('users', 'purpose_consent_leads.updated_by_id', '=', 'users.id')
                                    ->where('purpose_consent_leads.lead_id', $id);

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

    public function saveConsentLeadData(SaveConsentLeadDataRequest $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $consent = PurposeConsent::findOrFail($request->consent_id);

        if($request->consent_description && $request->consent_description != '')
        {
            $consent->description = $request->consent_description;
            $consent->save();
        }

        // Saving Consent Data
        $newConsentLead = new PurposeConsentLead();
        $newConsentLead->lead_id = $lead->id;
        $newConsentLead->purpose_consent_id = $consent->id;
        $newConsentLead->status = trim($request->status);
        $newConsentLead->ip = $request->ip();
        $newConsentLead->updated_by_id = $this->user->id;
        $newConsentLead->additional_description = $request->additional_description;
        $newConsentLead->save();

        $url = route('admin.leads.gdpr', $lead->id);

        return Reply::redirect($url);
    }

    /**
     * @param CommonRequest $request
     * @return array
     */
    public function followUpStore(\App\Http\Requests\FollowUp\StoreRequest $request){

        $followUp = new LeadFollowUp();
        $followUp->lead_id = $request->lead_id;
        $followUp->next_follow_up_date = Carbon::createFromFormat($this->global->date_format, $request->next_follow_up_date)->format('Y-m-d');;
        $followUp->remark = $request->remark;
        $followUp->save();
        $this->lead = Lead::findOrFail($request->lead_id);

        $view = view('admin.lead.followup.task-list-ajax', $this->data)->render();

        return Reply::successWithData(__('messages.leadFollowUpAddedSuccess'), ['html' => $view]);
    }

    public function followUpShow($leadID){
        $this->leadID = $leadID;
        $this->lead = Lead::findOrFail($leadID);
        return view('admin.lead.followup.show', $this->data);
    }

    public function editFollow($id)
    {
        $this->follow = LeadFollowUp::findOrFail($id);
        $view = view('admin.lead.followup.edit', $this->data)->render();
        return Reply::dataOnly(['html' => $view]);
    }

    public function UpdateFollow(\App\Http\Requests\FollowUp\StoreRequest $request)
    {
        $followUp = LeadFollowUp::findOrFail($request->id);
        $followUp->lead_id = $request->lead_id;
        $followUp->next_follow_up_date = Carbon::createFromFormat($this->global->date_format, $request->next_follow_up_date)->format('Y-m-d');;
        $followUp->remark = $request->remark;
        $followUp->save();

        $this->lead = Lead::findOrFail($request->lead_id);

        $view = view('admin.lead.followup.task-list-ajax', $this->data)->render();

        return Reply::successWithData(__('messages.leadFollowUpUpdatedSuccess'), ['html' => $view]);
    }

    public function followUpSort(CommonRequest $request)
    {
        $leadId = $request->leadId;
        $this->sortBy = $request->sortBy;

        $this->lead = Lead::findOrFail($leadId);
        if($request->sortBy == 'next_follow_up_date'){
            $order = "asc";
        }
        else{
            $order = "desc";
        }

        $follow = LeadFollowUp::where('lead_id', $leadId)->orderBy($request->sortBy, $order);


        $this->lead->follow = $follow->get();

        $view = view('admin.lead.followup.task-list-ajax', $this->data)->render();

        return Reply::successWithData(__('messages.followUpFilter'), ['html' => $view]);
    }


    public function export($followUp, $client) {
        $currentDate = Carbon::today()->format('Y-m-d');
        $lead = Lead::select('leads.id','client_name','website','client_email','company_name','lead_status.type as statusName','leads.created_at', 'lead_sources.name as source', \DB::raw("(select next_follow_up_date from lead_follow_up where lead_id = leads.id and leads.next_follow_up  = 'yes' and DATE(next_follow_up_date) >= {$currentDate} ORDER BY next_follow_up_date asc limit 1) as next_follow_up_date"))
            ->leftJoin('lead_status', 'lead_status.id', 'leads.status_id')
            ->leftJoin('lead_sources', 'lead_sources.id', 'leads.source_id');
        if($followUp != 'all' && $followUp != ''){
            $lead = $lead->leftJoin('lead_follow_up', 'lead_follow_up.lead_id', 'leads.id')
                ->where('leads.next_follow_up', 'yes')
                ->where('lead_follow_up.next_follow_up_date', '<', $currentDate);
        }
        if($client != 'all' && $client != ''){
            if($client == 'lead'){
                $lead = $lead->whereNull('client_id');
            }
            else{
                $lead = $lead->whereNotNull('client_id');
            }
        }

        $lead = $lead->GroupBy('leads.id')->get();

        // Initialize the array which will be passed into the Excel
        // generator.
        $exportArray = [];

        // Define the Excel spreadsheet headers
        $exportArray[] = ['ID', 'Client Name', 'Website', 'Email','Company Name','Status','Created On', 'Source', 'Next Follow Up Date'];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($lead as $row) {
            $exportArray[] = $row->toArray();
        }

        // Generate and return the spreadsheet
        Excel::create('leads', function($excel) use ($exportArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Leads');
            $excel->setCreator('Worksuite')->setCompany($this->companyName);
            $excel->setDescription('leads file');

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
