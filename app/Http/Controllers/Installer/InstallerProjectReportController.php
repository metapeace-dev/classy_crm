<?php

namespace App\Http\Controllers\Installer;

use App\Helper\Reply;
use App\Project;
use App\Lead;
use App\ReportSetting;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class InstallerProjectReportController extends InstallerBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.projectReport');
        $this->pageIcon = 'ti-pie-chart';
    }

    public function index() {
        $this->fromDate = Carbon::today()->firstOfMonth();
        $this->toDate = Carbon::today();
        $this->fieldsData = ReportSetting::where('type', 'project');
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $this->fieldsData = $this->fieldsData->where('role', 'admin');

        } elseif ($user->hasRole('designer') || $user->hasRole('installer')) {
            $this->fieldsData = $this->fieldsData->where('role', 'designer');

        } elseif ($user->hasRole('employee')) {
            $this->fieldsData = $this->fieldsData->where('role', 'employee');
        }
        $this->fieldsData = $this->fieldsData->select('field_name', 'status')->pluck('status', 'field_name')->toArray();
        return view('installer.reports.projects.index', $this->data);
    }

    public function store(Request $request){
        $taskBoardColumn = TaskboardColumn::all();
        $startDate = Carbon::createFromFormat($this->global->date_format, $request->startDate)->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $request->endDate)->toDateString();

        $incompletedTaskColumn = $taskBoardColumn->filter(function ($value, $key) {
            return $value->slug == 'incomplete';
        })->first();

        $completedTaskColumn = $taskBoardColumn->filter(function ($value, $key) {
            return $value->slug == 'completed';
        })->first();

        $totalTasks = Task::where(DB::raw('DATE(`due_date`)'), '>=', $startDate)
            ->where(DB::raw('DATE(`due_date`)'), '<=', $endDate);

        if (!is_null($request->projectId)) {
            $totalTasks->where('project_id', $request->projectId);
        }

        if (!is_null($request->employeeId)) {
            $totalTasks->where('user_id', $request->employeeId);
        }

        $totalTasks = $totalTasks->count();

        $completedTasks = Task::where(DB::raw('DATE(`due_date`)'), '>=', $startDate)
            ->where(DB::raw('DATE(`due_date`)'), '<=', $endDate);

        if (!is_null($request->projectId)) {
            $completedTasks->where('project_id', $request->projectId);
        }

        if (!is_null($request->employeeId)) {
            $completedTasks->where('user_id', $request->employeeId);
        }
        $completedTasks = $completedTasks->where('tasks.board_column_id', $completedTaskColumn->id)->count();

        $pendingTasks = Task::where(DB::raw('DATE(`due_date`)'), '>=', $startDate)
            ->where(DB::raw('DATE(`due_date`)'), '<=', $endDate);

        if (!is_null($request->projectId)) {
            $pendingTasks->where('project_id', $request->projectId);
        }

        if (!is_null($request->employeeId)) {
            $pendingTasks->where('user_id', $request->employeeId);
        }

        $pendingTasks = $pendingTasks->where('tasks.board_column_id', '<>', $completedTaskColumn->id)->count();

        return Reply::successWithData(__('messages.reportGenerated'),
            ['pendingTasks' => $pendingTasks, 'completedTasks' => $completedTasks, 'totalTasks' => $totalTasks]
        );
    }

    public function data($startDate = null, $endDate = null) {
        $this->userDetail = auth()->user();
        $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();

        $projects = Project::leftJoin('clients', 'clients.id', 'projects.client_id')
                ->leftJoin('project_installers', 'project_installers.project_id', '=','projects.id')
                ->select('projects.*','clients.first_name as client_first_name', 'clients.last_name as client_last_name');

        if (!is_null($startDate)) {
            $projects->where(DB::raw('DATE(projects.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $projects->where(DB::raw('DATE(projects.`created_at`)'), '<=', $endDate);
        }

        $projects->where('project_installers.user_id', '=', $this->userDetail->id);
        $projects->orderBy('projects.created_at', 'DESC');
        $projects = $projects->get();

        $removeData = ['updated_at', 'isProjectAdmin', 'client_first_name', 'client_last_name', 'client_id', 'user_id'];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'project');

        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $fieldsData = $fieldsData->where('role', 'admin');

        } elseif ($user->hasRole('designer') || $user->hasRole('installer')) {
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

        return DataTables::of($projects)
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format($this->global->date_format);
            })
            ->editColumn('status', function ($row) {
                if ($row->status == 'canceled') {
                    $status = '<label class="label label-danger">' . $row->status . '</label>';
                } else if ($row->status == 'completed') {
                    $status = '<label class="label label-success">' . $row->status . '</label>';
                } else if ($row->status == 'in progress') {
                    $status = '<label class="label label-info">' . $row->status . '</label>';
                } else if ($row->status == 'not started') {
                    $status = '<label class="label label-inverse">' . $row->status . '</label>';
                } else if ($row->status == 'on hold') {
                    $status = '<label class="label label-warning">' . $row->status . '</label>';
                } else {
                    $status = '<label class="label label-info">' . $row->status . '</label>';
                }
                return $status;
            })
            ->addColumn('client', function($row){
                return ucfirst($row->client_first_name). ' ' . ucfirst($row->client_last_name);
            })
            ->rawColumns(['created_at', 'client', 'status', 'project_name'])
            ->removeColumn($removeData)
            ->make(true);

    }

    public function export($startDate = null, $endDate = null) {
        $this->userDetail = auth()->user();
        $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();

        $projects = Project::leftJoin('clients', 'clients.id', 'projects.client_id')
            ->leftJoin('project_installers', 'project_installers.project_id', '=','projects.id')
            ->select('projects.*', 'clients.first_name as client_first_name', 'clients.last_name as client_last_name');

        if (!is_null($startDate)) {
            $projects->where(DB::raw('DATE(projects.`created_at`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $projects->where(DB::raw('DATE(projects.`created_at`)'), '<=', $endDate);
        }

        $projects->where('project_installers.user_id', '=', $this->userDetail->id);
        $projects->orderBy('projects.created_at', 'DESC');

        $title = 'Project Report';
        $attributes = ['client_first_name', 'client_last_name'];

        $projects = $projects->get()->makeHidden($attributes);

        $activeData = ['id'];
        $fieldsData = ReportSetting::select('field_name', 'status')->where('type', 'project');

        $user = auth()->user();
        if ($user->hasRole('admin')) {
            $fieldsData = $fieldsData->where('role', 'admin');

        } elseif ($user->hasRole('designer') || $user->hasRole('installer')) {
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
        foreach ($projects as $row) {
            $data = [];
            $data['id'] = $row->id;
            foreach ($activeData as $active) {
                if($active == 'client'){
                    $data['client'] = ucfirst($row->client_first_name). ' ' . ucfirst($row->client_last_name);
                }
                else if($active == 'sales_price'){
                    $data['sales_price'] = number_format($row->sales_price, 2, '.', ',');
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
            $excel->setDescription('Appointment Report file');

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
