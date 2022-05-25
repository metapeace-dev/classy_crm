<?php

namespace App\Http\Controllers\Installer;

use App\Client;
use App\Currency;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\Project\StoreProject;
use App\Lead;
use App\Payment;
use App\Project;
use App\ProjectTimeLog;
use App\Task;
use App\Traits\ProjectProgress;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Yajra\DataTables\Facades\DataTables;


/**
 * class DesignerProjectsController
 * @package App\Http\Controllers\Designer
 */
class InstallerProjectsController extends InstallerBaseController
{
    use ProjectProgress;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.projects');
        $this->pageIcon = 'icon-layers';
        $this->middleware(function ($request, $next) {
            if (!in_array('projects', $this->user->modules)) {
                abort(403);
            }
            return $next($request);
        });

    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $this->clients = Client::all();
        return view('installer.projects.index', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->project = Project::findOrFail($id);
        if(!is_null($this->project->sold_date))
            $this->project->sold_date = Carbon::createFromFormat('Y-m-d', $this->project->sold_date)->format($this->global->date_format);
        if(!is_null($this->project->install_start_date))
            $this->project->install_start_date = Carbon::createFromFormat('Y-m-d', $this->project->install_start_date)->format($this->global->date_format);
        if(!is_null($this->project->install_end_date))
            $this->project->install_end_date = Carbon::createFromFormat('Y-m-d', $this->project->install_end_date)->format($this->global->date_format);
        $this->currencies = Currency::all();
        $this->states = Config::get('constants.states');
        $this->designers = User::allDesigners();
        $this->clients = Client::all();

        $payments = Payment::where('project_id', '=',$id)->where('status', 'complete')->get();
        $this->paid_amount = 0;
        foreach ($payments as $payment) {
            $this->paid_amount += $payment->amount;
        }
        return view('installer.projects.edit', $this->data);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        $this->userDetail = auth()->user();

        $this->project = Project::findOrFail($id);

        $isMember = $this->project->checkProjectUser($id);
        // Check authorised user

        if ($isMember) {
            return view('installer.projects.show', $this->data);
        } else {
            // If not authorised user
            abort(403);
        }


    }

    public function data(Request $request)
    {
        $this->userDetail = auth()->user();
        $projects = Project::leftJoin('project_installers', 'project_installers.project_id', '=', 'projects.id')
            ->select('projects.id', 'projects.project_name', 'projects.client_id','projects.created_at', 'projects.updated_at', 'projects.status');

        $projects = $projects->where('project_installers.user_id', '=', $this->userDetail->id);
        if (!is_null($request->client_id) && $request->client_id != 'all') {
            $projects->where('client_id', $request->client_id);
        }

        $projects = $projects->orderBy('projects.created_at', 'DESC')->get();

        return DataTables::of($projects)
            ->addColumn('action', function ($row) {
                $action = '<a href="' . route('installer.projects.show', [$row->id]) . '" class="btn btn-success btn-circle"
                      data-toggle="tooltip" data-original-title="View Project Details"><i class="fa fa-search" aria-hidden="true"></i></a>';
                return $action;
            })
            ->addColumn('installers', function ($row) {
                $installers = '';

                if (count($row->installers) > 0) {
                    foreach ($row->installers as $installer) {
                        $installers .= ($installer->user->image) ? '<img data-toggle="tooltip" data-original-title="' . ucwords($installer->user->name) . '" src="' . asset_url('avatar/' . $installer->user->image) . '"
                        alt="user" class="img-circle" width="30"> ' : '<img data-toggle="tooltip" data-original-title="' . ucwords($installer->user->name) . '" src="' . asset('img/default-profile-2.png') . '"
                        alt="user" class="img-circle" width="30"> ';
                    }
                } else {
                    $installers .= __('messages.noMemberAddedToProject');
                }

                if ($this->user->can('add_projects')) {
                    $installers .= '<br><br><a class="font-12" href="' . route('installer.project-members.show', $row->id) . '"><i class="fa fa-plus"></i> ' . __('modules.projects.addMemberTitle') . '</a>';
                }
                return $installers;
            })

            ->editColumn('project_name', function ($row) {
                return '<a href="' . route('installer.projects.show', $row->id) . '">' . ucfirst($row->project_name) . '</a>';
            })
            ->editColumn('status', function ($row) {

                if ($row->status == 'in progress') {
                    $status = '<label class="label label-info">' . __('app.inProgress') . '</label>';
                } else if ($row->status == 'on hold') {
                    $status = '<label class="label label-warning">' . __('app.onHold') . '</label>';
                } else if ($row->status == 'not started') {
                    $status = '<label class="label label-warning">' . __('app.notStarted') . '</label>';
                } else if ($row->status == 'canceled') {
                    $status = '<label class="label label-danger">' . __('app.canceled') . '</label>';
                } else if ($row->status == 'completed') {
                    $status = '<label class="label label-success">' . __('app.completed') . '</label>';
                }
                return $status;
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format($this->global->date_format);
            })
            ->editColumn('client_id', function ($row) {
                return '<a href="' . route('installer.clients.show', $row->client_id) . '">' . $row->client->full_name . '</a>';
            })
            ->rawColumns(['project_name', 'action', 'client_id', 'status', 'installers'])
            ->make(true);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreProject $request, $id)
    {
        $project = Project::findOrFail($id);
        $project->address1 = $request->address1;
        $project->address2 = $request->address2;
        $project->city = $request->city;
        $project->state = $request->state;
        $project->zip = $request->zip;
        $project->contact = $request->contact;
        $project->cell2 = $request->cell2;
        $project->email = $request->email;
        $project->second_email = $request->second_email;
        $project->save();

        $this->logProjectActivity($project->id, ucwords($project->project_name) . __('modules.projects.projectUpdated'));
        return Reply::redirect(route('installer.projects.index'), __('messages.projectUpdated'));
    }

    public function create()
    {
        abort(403);
        $this->currencies = Currency::all();
        $this->states = Config::get('constants.states');
        $this->designers = User::allDesigners();
        $this->clients = Client::all();

        return view('installer.projects.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProject $request) {
        abort(403);
        $project = new Project();
        $project->project_name = $request->project_name;
        $project->address1 = $request->address1;
        $project->address2 = $request->address2;
        $project->city = $request->city;
        $project->state = $request->state;
        $project->zip = $request->zip;
        $project->contact = $request->contact;
        $project->phone = $request->phone;
        $project->ext = $request->ext;
        $project->cell = $request->cell;
        $project->cell2 = $request->cell2;
        $project->fax = $request->fax;
        $project->email = $request->email;
        $project->second_email = $request->second_email;
        if(!empty($request->install_start_date))
            $project->install_start_date = Carbon::createFromFormat($this->global->date_format, $request->install_start_date)->format('Y-m-d');
        if(!empty($request->install_end_date))
            $project->install_end_date = Carbon::createFromFormat($this->global->date_format, $request->install_end_date)->format('Y-m-d');
        $project->status = $request->status;
        $project->sales_price = $request->sales_price;
        if(!empty($request->sold_date))
            $project->sold_date = Carbon::createFromFormat($this->global->date_format, $request->sold_date)->format('Y-m-d');
        $project->discount = $request->discount;
        $project->discount_type = $request->discount_type;
        $project->user_id = auth()->user()->id;
        $project->client_id = $request->client_id;
        $project->save();

        $this->logSearchEntry($project->id, 'Project: ' . $project->project_name, 'admin.projects.show');

        $this->logProjectActivity($project->id, ucwords($project->project_name) . ' ' . __("messages.addedAsNewProject"));


        if ($request->has('leadDetail')) {
            $lead = Lead::findOrFail($request->leadDetail);
            $lead->project_id = $project->id;
            $lead->save();

            return Reply::redirect(route('installer.leads.index'), __('messages.leadProjectChangeSuccess'));
        }

        return Reply::redirect(route('installer.projects.index'), __('modules.projects.projectUpdated'));
    }

    public function destroy($id)
    {
        abort(403);
        $project = Project::withTrashed()->findOrFail($id);

        //delete project files
        Files::deleteDirectory('project-files/' . $id);

        $project->forceDelete();

        return Reply::success(__('messages.projectDeleted'));
    }

}
