<?php

namespace App\Http\Controllers\Designer;

use App\Task;

class DesignerCalendarController extends DesignerBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.taskCalendar');
        $this->pageIcon = 'icon-calender';
        $this->middleware(function ($request, $next) {
            if(!in_array('tasks',$this->user->modules)){
                abort(403);
            }
            return $next($request);
        });

    }

    public function index() {
        $this->tasks = Task::where('status', 'incomplete');
        if (!$this->user->can('view_tasks')) {
            $this->tasks = $this->tasks->where('user_id', $this->user->id);
        }
        $this->tasks =  $this->tasks->get();
        return view('designer.task-calendar.index', $this->data);
    }

    public function show($id) {
        $this->task = Task::findOrFail($id);
        return view('designer.task-calendar.show', $this->data);
    }
}
