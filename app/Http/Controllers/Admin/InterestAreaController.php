<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Reply;
use App\Http\Requests\InterestArea\StoreInterestArea;
use App\Http\Requests\InterestArea\UpdateInterestArea;
use App\InterestArea;

class InterestAreaController extends AdminBaseController
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

    public function createArea()
    {
        $this->areas = InterestArea::all();
        return view('admin.interest-area.create-area', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInterestArea $request)
    {
    }

    public function storeArea(StoreInterestArea $request)
    {
        $area = new InterestArea();
        $area->type = $request->type;
        $area->created_by = $this->user->id;
        $area->save();
        $areaData = InterestArea::all();
        return Reply::successWithData(__('messages.leadSourceAddSuccess'),['data' => $areaData]);
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
    }

    public function editArea($id)
    {
        $this->area = InterestArea::findOrFail($id);
        return view('admin.interest-area.update-area', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInterestArea $request, $id)
    {
    }

    public function updateArea(UpdateInterestArea $request, $id)
    {
        $area = InterestArea::findOrFail($id);
        $area->type = $request->type;
        $area->updated_by = $this->user->id;
        $area->save();
        $areaData = InterestArea::all();
        return Reply::successWithData(__('messages.leadSourceUpdateSuccess'),['data' => $areaData]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        InterestArea::destroy($id);
        return Reply::success(__('messages.leadSourceDeleteSuccess'));
    }


}
