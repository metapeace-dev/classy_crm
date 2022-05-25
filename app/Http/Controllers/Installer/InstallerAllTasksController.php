<?php

namespace App\Http\Controllers\Installer;

use App\Client;
use App\Helper\Reply;
use App\Http\Requests\Tasks\StoreTask;
use App\Http\Requests\Tasks\UpdateTask;
use App\Notifications\TaskReminder;
use App\Project;
use App\ProjectInstaller;
use App\Task;
use App\TaskAttendee;
use App\TaskboardColumn;
use App\TaskCategory;
use App\Traits\ProjectProgress;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class InstallerAllTasksController extends InstallerBaseController
{
    use ProjectProgress;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.tasks');
        $this->pageIcon = 'ti-layout-list-thumb';
        $this->middleware(function ($request, $next) {
            if (!in_array('tasks', $this->user->modules)) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index()
    {
//        $this->projects = Project::all();
        $this->employees = User::allEmployees();

        $this->clients = Client::all();
        $this->taskBoardStatus = TaskboardColumn::all();

        return view('installer.all-tasks.index', $this->data);
    }

    public function data(Request $request, $startDate = null, $endDate = null, $hideCompleted = null, $projectId = null)
    {
        $tasks = Task::leftJoin('task_attendees', 'task_attendees.task_id', '=', 'tasks.id')
            ->leftJoin('users as creator_user', 'creator_user.id', '=', 'tasks.created_by')
            ->leftJoin('taskboard_columns', 'taskboard_columns.id', '=', 'tasks.board_column_id')
            ->select('task_attendees.*','tasks.id', 'tasks.heading', 'creator_user.name as created_by', 'creator_user.image as created_image', 'tasks.due_date', 'taskboard_columns.column_name', 'taskboard_columns.label_color', 'tasks.priority')
            ->groupBy('tasks.id');

        $tasks->where('tasks.created_by', $this->user->id);
        $tasks->orWhere('task_attendees.user_id', '=', $this->user->id);

        $tasks = $tasks->orderBy('tasks.created_at', 'DESC')->get();

        return DataTables::of($tasks)
            ->addColumn('action', function ($row) {
                $action = '';

                $action .= '<a href="' . route('installer.all-tasks.edit', $row->id) . '" class="btn btn-info btn-circle"
                  data-toggle="tooltip" data-original-title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>';

                $recurringTaskCount = Task::where('recurring_task_id', $row->id)->count();
                $recurringTask = $recurringTaskCount > 0 ? 'yes' : 'no';

                $action .= '&nbsp;&nbsp;<a href="javascript:;" class="btn btn-danger btn-circle sa-params"
                      data-toggle="tooltip" data-task-id="' . $row->id . '" data-recurring="' . $recurringTask . '" data-original-title="Delete"><i class="fa fa-times" aria-hidden="true"></i></a>';
                return $action;
            })
            ->editColumn('due_date', function ($row) {
                if ($row->due_date->isPast()) {
                    return '<span class="text-danger">' . $row->due_date->format($this->global->date_format) . '</span>';
                }
                return '<span class="text-success">' . $row->due_date->format($this->global->date_format) . '</span>';
            })
            ->editColumn('priority', function ($row) {
                if ($row->priority == 'high') {
                    return '<label class="label-danger label">' . $row->priority . '</label>';
                }
                elseif($row->priority == 'medium'){
                    return '<label class="label-warning label">' . $row->priority . '</label>';
                }
                return '<label class="label-success label">' . $row->priority . '</label>';
            })
            ->editColumn('created_by', function ($row) {
                if (!is_null($row->created_by)) {
                    return ($row->created_image) ? '<img src="' . asset_url('avatar/' . $row->created_image) . '"
                                                            alt="user" class="img-circle" width="30"> ' . ucwords($row->created_by) : '<img src="' . asset('img/default-profile-2.png') . '"
                                                            alt="user" class="img-circle" width="30"> ' . ucwords($row->created_by);
                }
                return '-';
            })
            ->addColumn('name', function ($row) {
                $members = '';

                if (count($row->attendees) > 0) {
                    foreach ($row->attendees as $attendee) {
                        $members .= ($attendee->user->image) ? '<img data-toggle="tooltip" data-original-title="' . ucwords($attendee->user->name) . '" src="' . asset_url('avatar/' . $attendee->user->image) . '"
                        alt="user" class="img-circle" width="30"> ' : '<img data-toggle="tooltip" data-original-title="' . ucwords($attendee->user->name) . '" src="' . asset('img/default-profile-2.png') . '"
                        alt="user" class="img-circle" width="30"> ';

                    }
                } else {
                    $members .= __('messages.noMemberAddedToTask');
                }
                return $members;
            })
            ->editColumn('heading', function ($row) {
                return '<a href="javascript:;" data-task-id="' . $row->id . '" class="show-task-detail">' . ucfirst($row->heading) . '</a>';
            })
            ->editColumn('column_name', function ($row) {
                return '<label class="label" style="background-color: ' . $row->label_color . '">' . $row->column_name . '</label>';
            })
            ->rawColumns(['column_name', 'action', 'clientName', 'created_by', 'due_date', 'priority', 'name', 'heading'])
            ->removeColumn('project_id')
            ->removeColumn('image')
            ->removeColumn('label_color')
            ->removeColumn('taskUserID')
            ->make(true);
    }

    public function edit($id)
    {
        $this->taskBoardColumns = TaskboardColumn::all();
        $this->task = Task::findOrFail($id);

        if (!$this->user->can('add_tasks') && $this->global->task_self == 'yes') {
            $this->projects = Project::join('project_installers', 'project_installers.project_id', '=', 'projects.id')
                ->join('users', 'users.id', '=', 'project_installers.user_id')
                ->where('project_installers.user_id', $this->user->id)
                ->select('projects.id', 'projects.project_name')
                ->get();
        } else {
            $this->projects = Project::all();
        }

        $this->employees = User::allEmployees();
        $this->categories = TaskCategory::all();
        $this->attendees = [];
        if(!empty($this->task->attendees))
            $this->attendees = $this->task->attendees->pluck('user_id')->toArray();

        if($this->task->created_by === $this->user->id) {
            $this->isCreator = true;
        }
        else{
            $this->isCreator = false;
        }

        return view('installer.all-tasks.edit', $this->data);
    }

    public function update(UpdateTask $request, $id)
    {
        $task = Task::findOrFail($id);

        if($task->created_by == $this->user->id) {
            if($request->user_id){
                TaskAttendee::where('task_id', $task->id)->delete();
                foreach($request->user_id as $userId){
                    TaskAttendee::create(['user_id' => $userId, 'task_id' => $task->id]);
                }
            }
            else{
                $messages = [
                    'user_id.0.required' => 'Choose an assignee'
                ];
                $rules = [
                    'user_id.0' => 'required'
                ];
                $this->validate($request, $rules, $messages);
            }
        }

        $task->heading = $request->heading;
        if ($request->description != '') {
            $task->description = $request->description;
        }
        $task->start_date = Carbon::createFromFormat($this->global->date_format, $request->start_date)->format('Y-m-d');
        $task->due_date = Carbon::createFromFormat($this->global->date_format, $request->due_date)->format('Y-m-d');
        $task->priority = $request->priority;
        $task->board_column_id = $request->status;

        $taskBoardColumn = TaskboardColumn::findOrFail($request->status);
        if ($taskBoardColumn->slug == 'completed') {
            $task->completed_on = Carbon::today()->format('Y-m-d');
        } else {
            $task->completed_on = null;
        }

        $task->save();


        return Reply::redirect(route('installer.all-tasks.index'), __('messages.taskUpdatedSuccessfully'));
    }

    public function destroy(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        // If it is recurring and allowed by user to delete all its recurring tasks
        if ($request->has('recurring') && $request->recurring == 'yes') {
            Task::where('recurring_task_id', $id)->delete();
        }

        Task::destroy($id);

        //calculate project progress if enabled
//        $this->calculateProjectProgress($task->project_id);

        return Reply::success(__('messages.taskDeletedSuccessfully'));
    }


    public function create()
    {
        $this->employees = User::allEmployees();
        $this->categories = TaskCategory::all();
        return view('installer.all-tasks.create', $this->data);
    }

    public function membersList($projectId)
    {
        $this->members = ProjectInstaller::byProject($projectId);
        $list = view('installer.all-tasks.members-list', $this->data)->render();
        return Reply::dataOnly(['html' => $list]);
    }

    public function store(StoreTask $request)
    {
        $taskBoardColumn = TaskboardColumn::where('slug', 'incomplete')->first();

        $task = new Task();
        $task->heading = $request->heading;
        if ($request->description != '') {
            $task->description = $request->description;
        }
        $task->start_date = Carbon::createFromFormat($this->global->date_format, $request->start_date)->format('Y-m-d');
        $task->due_date = Carbon::createFromFormat($this->global->date_format, $request->due_date)->format('Y-m-d');
        $task->priority = $request->priority;
        $task->board_column_id = $taskBoardColumn->id;

        if ($request->board_column_id) {
            $task->board_column_id = $request->board_column_id;
        }
        $task->save();

        if($request->user_id){
            foreach($request->user_id as $userId){
                TaskAttendee::firstOrCreate(['user_id' => $userId, 'task_id' => $task->id]);
            }
        }

        // Add repeated task
        if ($request->has('repeat') && $request->repeat == 'yes') {
            $repeatCount = $request->repeat_count;
            $repeatType = $request->repeat_type;
            $repeatCycles = $request->repeat_cycles;
            $startDate = Carbon::createFromFormat($this->global->date_format, $request->start_date)->format('Y-m-d');
            $dueDate = Carbon::createFromFormat($this->global->date_format, $request->due_date)->format('Y-m-d');


            for ($i = 1; $i < $repeatCycles; $i++) {
                $repeatStartDate = Carbon::createFromFormat('Y-m-d', $startDate);
                $repeatDueDate = Carbon::createFromFormat('Y-m-d', $dueDate);

                if ($repeatType == 'day') {
                    $repeatStartDate = $repeatStartDate->addDays($repeatCount);
                    $repeatDueDate = $repeatDueDate->addDays($repeatCount);
                } else if ($repeatType == 'week') {
                    $repeatStartDate = $repeatStartDate->addWeeks($repeatCount);
                    $repeatDueDate = $repeatDueDate->addWeeks($repeatCount);
                } else if ($repeatType == 'month') {
                    $repeatStartDate = $repeatStartDate->addMonths($repeatCount);
                    $repeatDueDate = $repeatDueDate->addMonths($repeatCount);
                } else if ($repeatType == 'year') {
                    $repeatStartDate = $repeatStartDate->addYears($repeatCount);
                    $repeatDueDate = $repeatDueDate->addYears($repeatCount);
                }


                $newTask = new Task();
                $newTask->heading = $request->heading;
                if ($request->description != '') {
                    $newTask->description = $request->description;
                }
                $newTask->start_date = $repeatStartDate->format('Y-m-d');
                $newTask->due_date = $repeatDueDate->format('Y-m-d');
                $newTask->priority = $request->priority;
                $newTask->board_column_id = $taskBoardColumn->id;
                $newTask->recurring_task_id = $task->id;

                if ($request->board_column_id) {
                    $newTask->board_column_id = $request->board_column_id;
                }

                $newTask->save();

                if($request->user_id){
                    foreach($request->user_id as $userId){
                        TaskAttendee::firstOrCreate(['user_id' => $userId, 'task_id' => $newTask->id]);
                    }
                }

                $startDate = $newTask->start_date->format('Y-m-d');
                $dueDate = $newTask->due_date->format('Y-m-d');
            }
        }

        //log search
        $this->logSearchEntry($task->id, 'Task ' . $task->heading, 'admin.all-tasks.edit');

        if ($request->board_column_id) {
            return Reply::redirect(route('installer.taskboard.index'), __('messages.taskCreatedSuccessfully'));
        }
        return Reply::redirect(route('installer.all-tasks.index'), __('messages.taskCreatedSuccessfully'));
    }

    public function ajaxCreate($columnId)
    {
        $this->projects = Project::all();
        $this->columnId = $columnId;
        $this->employees = User::allEmployees();
        return view('installer.all-tasks.ajax_create', $this->data);
    }

    public function remindForTask($taskID)
    {
        $task = Task::with('user')->findOrFail($taskID);

        // Send  reminder notification to user
        $notifyUser = $task->user;
        $notifyUser->notify(new TaskReminder($task));

        return Reply::success('messages.reminderMailSuccess');
    }

    public function show($id)
    {
        $this->task = Task::findOrFail($id);
        $view = view('installer.all-tasks.show', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }
}
