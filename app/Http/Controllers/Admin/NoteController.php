<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Reply;
use App\Http\Requests\Note\StoreNote;
use App\Http\Requests\Note\UpdateNote;
use App\Note;

class NoteController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    public function createLeadNote($id)
    {
        $this->notes = Note::allLeadNotes($id);
        return view('admin.note.create-note', $this->data);
    }

    public function createClientNote($id)
    {
        $this->notes = Note::allClientNotes($id);
        return view('admin.note.create-note', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreNote $request)
    {
        $note = new Note();
        $note->type = $request->type;
        $note->save();

        $allNotes = Note::all();

        $select = '';
        foreach($allNotes as $allNote){
            $select.= '<option value="'.$allNote->id.'">'.ucwords($allNote->type).'</option>';
        }

        return Reply::successWithData(__('messages.leadSourceAddSuccess'), ['optionData' => $select]);
    }

    public function storeNote(StoreNote $request)
    {
        $note = new Note();
        $note->note = $request->note;
        $note->user_id = $this->user->id;
        if($request->has('client_id') && !empty($request->client_id)){
            $note->client_id = $request->client_id;
        }
        if($request->has('lead_id') && !empty($request->lead_id)){
            $note->lead_id = $request->lead_id;
        }
        $note->save();
        $noteData = Note::all();
        return Reply::successWithData(__('messages.noteAddSuccess'),['data' => $noteData]);
    }

//    public function storeClientNote(StoreNote $request, $id)
//    {
//        $note = new Note();
//        $note->note = $request->note;
//        $note->user_id = $this->user->id;
//        if($request->has('client_id') && !empty($request->client_id)){
//            $note->client_id = $request->client_id;
//        }
//        if($request->has('lead_id') && !empty($request->lead_id)){
//            $note->lead_id = $request->lead_id;
//        }
//        $note->save();
//        $noteData = Note::all();
//        return Reply::successWithData(__('messages.noteAddSuccess'),['data' => $noteData]);
//    }

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
        $this->note = Note::findOrFail($id);

        return view('admin.lead-settings.source.edit', $this->data);
    }

    public function editNote($id)
    {
        $this->note = Note::findOrFail($id);
        return view('admin.lead.update-source', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateNote $request, $id)
    {
        $type = Note::findOrFail($id);
        $type->type = $request->type;
        $type->save();

        return Reply::success(__('messages.noteUpdateSuccess'));
    }

    public function updateNote(UpdateNote $request, $id)
    {
        $note = Note::findOrFail($id);
        $note->note = $request->note;
        $note->user_id = $this->user->id;
        $note->save();
        $noteData = Note::all();
        return Reply::successWithData(__('messages.noteUpdateSuccess'),['data' => $noteData]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Note::destroy($id);

        return Reply::success(__('messages.noteDeleteSuccess'));
    }


}
