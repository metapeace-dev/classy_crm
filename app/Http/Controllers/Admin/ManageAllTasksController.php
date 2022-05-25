<?php

namespace App\Http\Controllers\Admin;

use App\Client;
use App\Helper\Reply;
use App\Http\Requests\StoreFileLink;
use App\Http\Requests\Tasks\StoreTask;
use App\Http\Requests\Tasks\UpdateTask;
use App\Notifications\TaskDeatils;
use App\Notifications\TaskReminder;
use App\Project;
use App\ProjectInstaller;
use App\Task;
use App\TaskAttendee;
use App\TaskboardColumn;
use App\TaskCategory;
use App\TaskFile;
use App\Traits\ProjectProgress;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ManageAllTasksController extends AdminBaseController
{
    use ProjectProgress;

    private $mimeType = [
        'txt' => 'fa-file-text',
        'htm' => 'fa-file-code-o',
        'html' => 'fa-file-code-o',
        // 'php' => 'fa-file-code-o',
        'css' => 'fa-file-code-o',
        'js' => 'fa-file-code-o',
        'json' => 'fa-file-code-o',
        'xml' => 'fa-file-code-o',
        'swf' => 'fa-file-o',
        'flv' => 'fa-file-video-o',

        // images
        'png' => 'fa-file-image-o',
        'jpe' => 'fa-file-image-o',
        'jpeg' => 'fa-file-image-o',
        'jpg' => 'fa-file-image-o',
        'gif' => 'fa-file-image-o',
        'bmp' => 'fa-file-image-o',
        'ico' => 'fa-file-image-o',
        'tiff' => 'fa-file-image-o',
        'tif' => 'fa-file-image-o',
        'svg' => 'fa-file-image-o',
        'svgz' => 'fa-file-image-o',

        // archives
        'zip' => 'fa-file-o',
        'rar' => 'fa-file-o',
        'exe' => 'fa-file-o',
        'msi' => 'fa-file-o',
        'cab' => 'fa-file-o',

        // audio/video
        'mp3' => 'fa-file-audio-o',
        'qt' => 'fa-file-video-o',
        'mov' => 'fa-file-video-o',
        'mp4' => 'fa-file-video-o',
        'mkv' => 'fa-file-video-o',
        'avi' => 'fa-file-video-o',
        'wmv' => 'fa-file-video-o',
        'mpg' => 'fa-file-video-o',
        'mp2' => 'fa-file-video-o',
        'mpeg' => 'fa-file-video-o',
        'mpe' => 'fa-file-video-o',
        'mpv' => 'fa-file-video-o',
        '3gp' => 'fa-file-video-o',
        'm4v' => 'fa-file-video-o',

        // adobe
        'pdf' => 'fa-file-pdf-o',
        'psd' => 'fa-file-image-o',
        'ai' => 'fa-file-o',
        'eps' => 'fa-file-o',
        'ps' => 'fa-file-o',

        // ms office
        'doc' => 'fa-file-text',
        'rtf' => 'fa-file-text',
        'xls' => 'fa-file-excel-o',
        'ppt' => 'fa-file-powerpoint-o',
        'docx' => 'fa-file-text',
        'xlsx' => 'fa-file-excel-o',
        'pptx' => 'fa-file-powerpoint-o',


        // open office
        'odt' => 'fa-file-text',
        'ods' => 'fa-file-text',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.tasks');
        $this->pageIcon = 'ti-layout-list-thumb';
        if(config('filesystems.default') == 's3') {
            $this->url = "https://".config('filesystems.disks.s3.bucket').".s3.amazonaws.com/";
        }
        $this->middleware(function ($request, $next) {
            if (!in_array('tasks', $this->user->modules)) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index()
    {
        $this->projects = Project::all();
        $this->clients = Client::all();
        $this->employees = User::allEmployees();
        $this->taskBoardStatus = TaskboardColumn::all();
        $this->taskCategories = TaskCategory::all();

        return view('admin.tasks.index', $this->data);
    }

    public function data(Request $request, $startDate = null, $endDate = null, $hideCompleted = null, $projectId = null)
    {
        $startDate = ''; $endDate = '';
        if($request->startDate){
            $startDate = Carbon::createFromFormat($this->global->date_format, $request->startDate)->toDateString();
        }
        if($request->endDate){
            $endDate = Carbon::createFromFormat($this->global->date_format, $request->endDate)->toDateString();
        }

        $tasks = Task::leftJoin('task_attendees', 'task_attendees.task_id', '=', 'tasks.id')
            ->join('taskboard_columns', 'taskboard_columns.id', '=', 'tasks.board_column_id')
            ->leftJoin('users as creator_user', 'creator_user.id', '=', 'tasks.created_by')
            ->select('task_attendees.*','tasks.id', 'tasks.heading', 'creator_user.name as created_by', 'creator_user.image as created_image', 'tasks.due_date', 'taskboard_columns.column_name', 'taskboard_columns.label_color', 'tasks.project_id', 'tasks.priority');
        if($startDate && $endDate){
            $tasks->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween(DB::raw('DATE(tasks.`due_date`)'), [$startDate, $endDate]);

                $q->orWhereBetween(DB::raw('DATE(tasks.`start_date`)'), [$startDate, $endDate]);
            });
        }

        if(auth()->user()->id !== 1){
            $tasks->where('tasks.created_by', $this->user->id);
            $tasks->orWhere('task_attendees.user_id', '=', $this->user->id);
        }

        $tasks = $tasks->groupBy('tasks.id')->get();

        return DataTables::of($tasks)
            ->addColumn('action', function ($row) {
                $string = '<a href="' . route('admin.all-tasks.edit', $row->id) . '" class="btn btn-info btn-circle"
                      data-toggle="tooltip" data-original-title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>';

                $recurringTaskCount = Task::where('recurring_task_id', $row->id)->count();
                $recurringTask = $recurringTaskCount > 0 ? 'yes' : 'no';
                $string .= '&nbsp;&nbsp;<a href="javascript:void(0);" data-id="'.$row->id.'" class="btn btn-info btn-circle btn_send_email"
                      data-toggle="tooltip" data-original-title="Send Email"><i class="fa fa-envelope-o" aria-hidden="true"></i></a>';
                $string .= '&nbsp;&nbsp;<a href="javascript:;" class="btn btn-danger btn-circle sa-params"
                  data-toggle="tooltip" data-task-id="' . $row->id . '" data-recurring="' . $recurringTask . '" data-original-title="Delete"><i class="fa fa-times" aria-hidden="true"></i></a>';

                return $string;
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
            ->editColumn('clientName', function ($row) {
                return ($row->clientName) ? ucwords($row->clientName) : '-';
            })
            ->editColumn('created_by', function ($row) {
                if (!is_null($row->created_by)) {
                    return ($row->created_image) ? '<img src="' . asset_url('avatar/' . $row->created_image) . '"
                                                            alt="user" class="img-circle" width="30"> ' . ucwords($row->created_by) : '<img src="' . asset('img/default-profile-2.png') . '"
                                                            alt="user" class="img-circle" width="30"> ' . ucwords($row->created_by);
                }
                return '-';
            })
            ->editColumn('heading', function ($row) {
                return '<a href="javascript:;" data-task-id="' . $row->id . '" class="show-task-detail">' . ucfirst($row->heading) . '</a>';
            })
            ->editColumn('column_name', function ($row) {
                return '<label class="label" style="background-color: ' . $row->label_color . '">' . $row->column_name . '</label>';
            })
            ->rawColumns(['column_name', 'action', 'clientName', 'due_date', 'priority','name', 'created_by', 'heading'])
            ->removeColumn('created_image')
            ->removeColumn('label_color')
            ->make(true);
    }

    public function edit($id)
    {
        $this->task = Task::with('attendees')->findOrFail($id);
        $this->projects = Project::all();
        $this->employees = User::allEmployees();
        $this->categories = TaskCategory::all();
        $this->taskBoardColumns = TaskboardColumn::all();
        $this->attendees = [];
        if(!empty($this->task->attendees))
            $this->attendees = $this->task->attendees->pluck('user_id')->toArray();

        return view('admin.tasks.edit', $this->data);
    }

    public function update(UpdateTask $request, $id)
    {
        $task = Task::findOrFail($id);
        $oldStatus = TaskboardColumn::findOrFail($task->board_column_id);

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
        TaskAttendee::where('task_id', $task->id)->delete();
        if($request->user_id){
            foreach($request->user_id as $userId){
                TaskAttendee::create(['user_id' => $userId, 'task_id' => $task->id]);
            }
        }

        return Reply::redirect(route('admin.all-tasks.index'), __('messages.taskUpdatedSuccessfully'));
    }

    public function destroy(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        // If it is recurring and allowed by user to delete all its recurring tasks
        if ($request->has('recurring') && $request->recurring == 'yes') {
            Task::where('recurring_task_id', $id)->delete();
        }

        // Delete current task
        Task::destroy($id);



        //calculate project progress if enabled
        $this->calculateProjectProgress($task->project_id);

        return Reply::success(__('messages.taskDeletedSuccessfully'));
    }

    public function create()
    {
        $this->projects = Project::all();
        $this->employees = User::allEmployees();
        $this->categories = TaskCategory::all();
        return view('admin.tasks.create', $this->data);
    }

    public function membersList($projectId)
    {
        $this->members = ProjectInstaller::byProject($projectId);
        $list = view('admin.tasks.members-list', $this->data)->render();
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
            }
        }

        //log search
        $this->logSearchEntry($task->id, 'Task ' . $task->heading, 'admin.all-tasks.edit');

        if ($request->board_column_id) {
            return Reply::redirect(route('admin.taskboard.index'), __('messages.taskCreatedSuccessfully'));
        }
        return Reply::redirect(route('admin.all-tasks.index'), __('messages.taskCreatedSuccessfully'));
    }

    public function ajaxCreate($columnId)
    {
        $this->projects = Project::all();
        $this->columnId = $columnId;
        $this->employees = User::allEmployees();
        return view('admin.tasks.ajax_create', $this->data);
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
        $this->task = Task::with('board_column', 'subtasks', 'project')->findOrFail($id);
        $view = view('admin.tasks.show', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param $projectId
     * @param $hideCompleted
     */
    public function export($startDate, $endDate, $projectId, $hideCompleted)
    {

        $startDate = Carbon::createFromFormat($this->global->date_format, $startDate)->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $endDate)->toDateString();

        $tasks = Task::leftJoin('projects', 'projects.id', '=', 'tasks.project_id')
            ->join('users', 'users.id', '=', 'tasks.user_id')
            ->join('taskboard_columns', 'taskboard_columns.id', '=', 'tasks.board_column_id')
            ->select('tasks.id', 'projects.project_name', 'tasks.heading', 'users.name', 'users.image', 'taskboard_columns.column_name', 'tasks.due_date');

        if (!is_null($startDate)) {
            $tasks->where(DB::raw('DATE(tasks.`due_date`)'), '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $tasks->where(DB::raw('DATE(tasks.`due_date`)'), '<=', $endDate);
        }

        if ($projectId != 0) {
            $tasks->where('tasks.project_id', '=', $projectId);
        }

        if ($hideCompleted == '1') {
            $tasks->where('tasks.status', '=', 'incomplete');
        }

        $attributes =  ['image', 'due_date'];

        $tasks = $tasks->get()->makeHidden($attributes);

        // Initialize the array which will be passed into the Excel
        // generator.
        $exportArray = [];

        // Define the Excel spreadsheet headers
        $exportArray[] = ['ID', 'Project', 'Title', 'Assigned TO', 'Status', 'Due Date'];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($tasks as $row) {
            $exportArray[] = $row->toArray();
        }

        // Generate and return the spreadsheet
        Excel::create('task', function ($excel) use ($exportArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle('Task');
            $excel->setCreator('Worksuite')->setCompany($this->companyName);
            $excel->setDescription('task file');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function ($sheet) use ($exportArray) {
                $sheet->fromArray($exportArray, null, 'A1', false, false);

                $sheet->row(1, function ($row) {

                    // call row manipulation methods
                    $row->setFont(array(
                        'bold'       =>  true
                    ));
                });
            });
        })->download('xlsx');
    }

    public function sendEmail($task_id){
        $task = Task::find($task_id);
        $attendees = TaskAttendee::where('task_id', $task_id)->get();
        foreach ($attendees as $attendee){
            $notifyUser = User::withoutGlobalScope('active')->findOrFail($attendee->user_id);
            $notifyUser->notify(new TaskDeatils($task, $notifyUser));
        }
        return Reply::success(__('messages.emailSendSuccess'));
    }

    public function storeFile(Request $request)
    {

        if ($request->hasFile('file')) {
            $storage = config('filesystems.default');
            $file = new TaskFile();
            $file->user_id = $this->user->id;
            $file->task_id = $request->task_id;
            switch($storage) {
                case 'local':

                    $request->file->storeAs('task-files/'.$request->task_id, $request->file->hashName());
                    break;
                case 's3':
                    Storage::disk('s3')->putFileAs('task-files/'.$request->task_id, $request->file, $request->file->getClientOriginalName(), 'public');
                    break;
                case 'google':
                    $dir = '/';
                    $recursive = false;
                    $contents = collect(Storage::cloud()->listContents($dir, $recursive));
                    $dir = $contents->where('type', '=', 'dir')
                        ->where('filename', '=', 'task-files')
                        ->first();

                    if(!$dir) {
                        Storage::cloud()->makeDirectory('task-files');
                    }

                    $directory = $dir['path'];
                    $recursive = false;
                    $contents = collect(Storage::cloud()->listContents($directory, $recursive));
                    $directory = $contents->where('type', '=', 'dir')
                        ->where('filename', '=', $request->task_id)
                        ->first();

                    if ( ! $directory) {
                        Storage::cloud()->makeDirectory($dir['path'].'/'.$request->task_id);
                        $contents = collect(Storage::cloud()->listContents($directory, $recursive));
                        $directory = $contents->where('type', '=', 'dir')
                            ->where('filename', '=', $request->task_id)
                            ->first();
                    }

                    Storage::cloud()->putFileAs($directory['basename'], $request->file, $request->file->getClientOriginalName());

                    $file->google_url = Storage::cloud()->url($directory['path'].'/'.$request->file->getClientOriginalName());

                    break;
                case 'dropbox':
                    Storage::disk('dropbox')->putFileAs('task-files/'.$request->project_id.'/', $request->file, $request->file->getClientOriginalName());
                    $dropbox = new Client(['headers' => ['Authorization' => "Bearer ".config('filesystems.disks.dropbox.token'), "Content-Type" => "application/json"]]);
                    $res = $dropbox->request('POST', 'https://api.dropboxapi.com/2/sharing/create_shared_link_with_settings',
                        [\GuzzleHttp\RequestOptions::JSON => ["path" => '/task-files/'.$request->task_id.'/'.$request->file->getClientOriginalName()]]
                    );
                    $dropboxResult = $res->getBody();
                    $dropboxResult = json_decode($dropboxResult, true);
                    $file->dropbox_link = $dropboxResult['url'];
                    break;
            }

            $file->filename = $request->file->getClientOriginalName();
            $file->hashname = $request->file->hashName();
            $file->size = $request->file->getSize();
            $file->save();
//            $this->logProjectActivity($request->task_id, __('messages.newFileUploadedToTheProject'));
        }

        $this->task = Task::findOrFail($request->task_id);
        $this->icon($this->task);
        if ($request->view == 'list') {
            $view = view('admin.tasks.task-files.ajax-list', $this->data)->render();
        } else {
            $view = view('admin.tasks.task-files.thumbnail-list', $this->data)->render();
        }
        return Reply::successWithData(__('messages.fileUploaded'), ['html' => $view]);
    }

    public function deleteFile(Request $request)
    {
        $storage = config('filesystems.default');
        $id = $request->get('id');
        $file = TaskFile::findOrFail($id);
        switch($storage) {
            case 'local':
                File::delete('user-uploads/task-files/'.$file->task_id.'/'.$file->hashname);
                break;
            case 's3':
                Storage::disk('s3')->delete('task-files/'.$file->task_id.'/'.$file->filename);
                break;
            case 'google':
                Storage::disk('google')->delete('task-files/'.$file->task_id.'/'.$file->filename);
                break;
            case 'dropbox':
                Storage::disk('dropbox')->delete('task-files/'.$file->task_id.'/'.$file->filename);
                break;
        }
        TaskFile::destroy($id);
        $this->task = Task::findOrFail($file->task_id);
        $this->icon($this->task);
        if($request->view == 'list') {
            $view = view('admin.tasks.task-files.ajax-list', $this->data)->render();
        } else {
            $view = view('admin.tasks.task-files.thumbnail-list', $this->data)->render();
        }
        return Reply::successWithData(__('messages.fileDeleted'), ['html' => $view]);
    }

    public function download($id) {
        $storage = config('filesystems.default');
        $file = TaskFile::findOrFail($id);
        switch($storage) {
            case 'local':
                return response()->download('user-uploads/task-files/'.$file->task_id.'/'.$file->hashname, $file->filename);
                break;
            case 's3':
                $ext = pathinfo($file->filename, PATHINFO_EXTENSION);
                $fs = Storage::getDriver();
                $stream = $fs->readStream('task-files/'.$file->task_id.'/'.$file->filename);
                return Response::stream(function() use($stream) {
                    fpassthru($stream);
                }, 200, [
                    "Content-Type" => $ext,
                    "Content-Length" => $file->size,
                    "Content-disposition" => "attachment; filename=\"" .basename($file->filename) . "\"",
                ]);
                break;
            case 'google':
                $ext = pathinfo($file->filename, PATHINFO_EXTENSION);
                $dir = '/';
                $recursive = false; // Get subdirectories also?
                $contents = collect(Storage::cloud()->listContents($dir, $recursive));
                $directory = $contents->where('type', '=', 'dir')
                    ->where('filename', '=', 'task-files')
                    ->first();

                $direct = $directory['path'];
                $recursive = false;
                $contents = collect(Storage::cloud()->listContents($direct, $recursive));
                $directo = $contents->where('type', '=', 'dir')
                    ->where('filename', '=', $file->task_id)
                    ->first();

                $readStream = Storage::cloud()->getDriver()->readStream($directo['path']);
                return response()->stream(function () use ($readStream) {
                    fpassthru($readStream);
                }, 200, [
                    'Content-Type' => $ext,
                    'Content-disposition' => 'attachment; filename="'.$file->filename.'"',
                ]);
                break;
            case 'dropbox':
                $ext = pathinfo($file->filename, PATHINFO_EXTENSION);
                $fs = Storage::getDriver();
                $stream = $fs->readStream('project-files/'.$file->task_id.'/'.$file->filename);
                return Response::stream(function() use($stream) {
                    fpassthru($stream);
                }, 200, [
                    "Content-Type" => $ext,
                    "Content-Length" => $file->size,
                    "Content-disposition" => "attachment; filename=\"" .basename($file->filename) . "\"",
                ]);
                break;
        }
    }

    public function thumbnailShow(Request $request)
    {
        $this->task = Task::with('files')->findOrFail($request->id);
        $this->icon($this->task);
        $view = view('admin.tasks.task-files.thumbnail-list', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

    private function icon($tasks) {
        foreach ($tasks->files as $task) {
            if (is_null($task->external_link)) {
                $ext = pathinfo($task->filename, PATHINFO_EXTENSION);
                if ($ext == 'png' || $ext == 'jpe' || $ext == 'jpeg' || $ext == 'jpg' || $ext == 'gif' || $ext == 'bmp' ||
                    $ext == 'ico' || $ext == 'tiff' || $ext == 'tif' || $ext == 'svg' || $ext == 'svgz' || $ext == 'psd' || $ext == 'csv')
                {
                    $task->icon = 'images';
                } else {
                    $task->icon = $this->mimeType[$ext];
                }

            }
        }
    }

    public function storeLink(StoreFileLink $request)
    {
        $file = new TaskFile();
        $file->user_id = $this->user->id;
        $file->task_id = $request->task_id;
        $file->external_link = $request->external_link;
        $file->filename = $request->filename;
        $file->save();
//        $this->logProjectActivity($request->project_id, __('messages.newFileUploadedToTheProject'));

        return Reply::success(__('messages.fileUploaded'));
    }
}
