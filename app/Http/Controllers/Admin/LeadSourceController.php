<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Reply;
use App\Http\Requests\LeadSetting\StoreLeadSource;
use App\Http\Requests\LeadSetting\UpdateLeadSource;
use App\LeadSource;

class LeadSourceController extends AdminBaseController
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

    public function createSrc()
    {
        $this->sources = LeadSource::all();
        return view('admin.lead.create-source', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLeadSource $request)
    {
        $source = new LeadSource();
        $source->type = $request->type;
        $source->save();

        $allSources = LeadSource::all();

        $select = '';
        foreach($allSources as $allSource){
            $select.= '<option value="'.$allSource->id.'">'.ucwords($allSource->type).'</option>';
        }

        return Reply::successWithData(__('messages.leadSourceAddSuccess'), ['optionData' => $select]);
    }

    public function storeSrc(StoreLeadSource $request)
    {
        $source = new LeadSource();
        $source->name = $request->name;
        $source->description = $request->description;
        $source->created_by = $this->user->id;
        $source->save();
        $sourceData = LeadSource::all();
        return Reply::successWithData(__('messages.leadSourceAddSuccess'),['data' => $sourceData]);
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
        $this->source = LeadSource::findOrFail($id);

        return view('admin.lead-settings.source.edit', $this->data);
    }

    public function editSrc($id)
    {
        $this->source = LeadSource::findOrFail($id);
        return view('admin.lead.update-source', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLeadSource $request, $id)
    {
        $type = LeadSource::findOrFail($id);
        $type->type = $request->type;
        $type->save();

        return Reply::success(__('messages.leadSourceUpdateSuccess'));
    }

    public function updateSrc(UpdateLeadSource $request, $id)
    {
        $source = LeadSource::findOrFail($id);
        $source->name = $request->name;
        $source->description = $request->description;
        $source->updated_by = $this->user->id;
        $source->save();
        $sourceData = LeadSource::all();
        return Reply::successWithData(__('messages.leadSourceUpdateSuccess'),['data' => $sourceData]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        LeadSource::destroy($id);

        return Reply::success(__('messages.leadSourceDeleteSuccess'));
    }


}
