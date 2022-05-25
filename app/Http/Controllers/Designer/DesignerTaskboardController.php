<?php

namespace App\Http\Controllers\Designer;

use App\Helper\Reply;
use App\Http\Requests\TaskBoard\StoreTaskBoard;
use App\Project;
use App\Task;
use App\TaskboardColumn;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DesignerTaskboardController extends DesignerBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('modules.tasks.taskBoard');
        $this->pageIcon = 'ti-layout-column3';
        $this->middleware(function ($request, $next) {
            if(!in_array('tasks',$this->modules)){
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
    public function index(Request $request)
    {
        $this->startDate = Carbon::today()->subDays(15)->format($this->global->date_format);
        $this->endDate = Carbon::today()->addDays(15)->format($this->global->date_format);
        $this->projects = ($this->user->can('view_projects')) ? Project::all() : Project::where('projects.user_id', $this->user->id)
            ->get();
        $this->employees = ($this->user->can('view_employees')) ? User::allEmployees() : User::where('id', $this->user->id)->get();

        $this->clients = User::allClients();

        if ($request->ajax()) {
            $startDate = Carbon::createFromFormat($this->global->date_format, $request->startDate)->toDateString();
            $endDate = Carbon::createFromFormat($this->global->date_format, $request->endDate)->toDateString();

            $boardColumns = TaskboardColumn::with(['tasks' => function ($q) use ($startDate, $endDate, $request) {
                $q->with(['subtasks', 'completedSubtasks', 'comments', 'user'])
                    ->leftJoin('projects', 'projects.id', '=', 'tasks.project_id')
                    ->leftJoin('users as client', 'client.id', '=', 'projects.client_id')
                    ->join('users', 'users.id', '=', 'tasks.user_id')
                    ->join('taskboard_columns', 'taskboard_columns.id', '=', 'tasks.board_column_id')
                    ->leftJoin('users as creator_user', 'creator_user.id', '=', 'tasks.created_by')
                    ->select('tasks.*');

                $q->whereNull('projects.deleted_at');

                $q->where(function ($task) use ($startDate, $endDate) {
                    $task->whereBetween(DB::raw('DATE(tasks.`due_date`)'), [$startDate, $endDate]);
    
                    $task->orWhereBetween(DB::raw('DATE(tasks.`start_date`)'), [$startDate, $endDate]);
                });
                
                if ($request->projectID != 0 && $request->projectID !=  null && $request->projectID !=  'all') {
                    $q = $q->where('tasks.project_id', '=', $request->projectID);
                }

                if ($request->clientID != '' && $request->clientID !=  null && $request->clientID !=  'all') {
                    $q = $q->where('projects.client_id', '=', $request->clientID);
                }

                if ($request->assignedTo != '' && $request->assignedTo !=  null && $request->assignedTo !=  'all') {
                    $q = $q->where('tasks.user_id', '=', $request->assignedTo);
                }

                if ($request->assignedBY != '' && $request->assignedBY !=  null && $request->assignedBY !=  'all') {
                    $q = $q->where('creator_user.id', '=', $request->assignedBY);
                }

                if (!$this->user->can('view_tasks')) {
                    $q = $q->where('tasks.user_id', '=', $this->user->id);
                }
            }])->orderBy('priority', 'asc')->get();

            $this->boardColumns = $boardColumns;
      
            $view = view('designer.taskboard.board_data', $this->data)->render();
            return Reply::dataOnly(['view' => $view]);
        }
        return view('designer.taskboard.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('designer.taskboard.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTaskBoard $request)
    {
        $maxPriority = TaskboardColumn::max('priority');

        $board = new TaskboardColumn();
        $board->column_name = $request->column_name;
        $board->label_color = $request->label_color;
        $board->priority = ($maxPriority+1);
        $board->save();

        return Reply::redirect(route('designer.taskboard.index'), __('messages.boardColumnSaved'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->boardColumn = TaskboardColumn::findOrFail($id);
        $this->maxPriority = TaskboardColumn::max('priority');
        $view =  view('designer.taskboard.edit', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreTaskBoard $request, $id)
    {
        $board = TaskboardColumn::findOrFail($id);
        $oldPosition = $board->priority;
        $newPosition = $request->priority;

        if($oldPosition < $newPosition){

            $otherColumns = TaskboardColumn::where('priority', '>', $oldPosition)
                ->where('priority', '<=', $newPosition)
                ->orderBy('priority', 'asc')
                ->get();

            foreach($otherColumns as $column){
                $pos = TaskboardColumn::where('priority', $column->priority)->first();
                $pos->priority = ($pos->priority-1);
                $pos->save();
            }
        }
        else if($oldPosition > $newPosition){

        $otherColumns = TaskboardColumn::where('priority', '<', $oldPosition)
            ->where('priority', '>=', $newPosition)
            ->orderBy('priority', 'asc')
            ->get();

        foreach($otherColumns as $column){
            $pos = TaskboardColumn::where('priority', $column->priority)->first();
            $pos->priority = ($pos->priority+1);
            $pos->save();
        }
    }

        $board->column_name = $request->column_name;
        $board->label_color = $request->label_color;
        $board->priority = $request->priority;
        $board->save();

        return Reply::redirect(route('designer.taskboard.index'), __('messages.boardColumnSaved'));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Task::where('board_column_id', $id)->update(['board_column_id' => 1]);

        $board = TaskboardColumn::findOrFail($id);

        $otherColumns = TaskboardColumn::where('priority', '>', $board->priority)
            ->orderBy('priority', 'asc')
            ->get();

        foreach($otherColumns as $column){
            $pos = TaskboardColumn::where('priority', $column->priority)->first();
            $pos->priority = ($pos->priority-1);
            $pos->save();
        }

        TaskboardColumn::destroy($id);

        return Reply::dataOnly(['status' => 'success']);
    }

    public function updateIndex(Request $request){
        $taskIds = $request->taskIds;
        $boardColumnIds = $request->boardColumnIds;
        $priorities = $request->prioritys;

        if(!empty($taskIds)){
            foreach($taskIds as $key=>$taskId){
                if(!is_null($taskId)){
                    $task = Task::findOrFail($taskId);
                    $task->board_column_id = $boardColumnIds[$key];
                    $task->column_priority = $priorities[$key];
                    $task->save();
                }
            }
        }
        return Reply::dataOnly(['status' => 'success']);
    }
}
