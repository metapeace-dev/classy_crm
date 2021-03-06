<?php

namespace App\Http\Controllers\Admin;

use App\EmployeeDetails;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\User\UpdateProfile;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class EmployeeProfileController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.profileSettings');
        $this->pageIcon = 'icon-user';
    }

    public function index() {
        $this->userDetail = auth()->user();
        $this->employeeDetail = EmployeeDetails::where('user_id', '=', $this->userDetail->id)->first();
        return view('admin.profile.edit', $this->data);
    }

    public function update(UpdateProfile $request, $id) {
        config(['filesystems.default' => 'local']);

        $user = User::withoutGlobalScope('active')->findOrFail($id);
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->gender = $request->input('gender');
        if($request->password != ''){
            $user->password = Hash::make($request->input('password'));
        }
        $user->mobile = $request->input('mobile');

        if ($request->hasFile('image')) {
            
            Files::deleteFile($user->image,'avatar');
            $user->image = Files::upload($request->image, 'avatar',300);
        }

        $user->save();

        $validate = Validator::make(['address' => $request->address], [
            'address' => 'required'
        ]);

        if($validate->fails()){
            return Reply::formErrors($validate);
        }

        $employee = EmployeeDetails::where('user_id', '=', $user->id)->first();
        if(empty($employee)){
            $employee = new EmployeeDetails();
            $employee->user_id = $user->id;
        }
        $employee->address = $request->address;
        $employee->save();
        session()->forget('user');
        $this->logUserActivity($user->id, __('messages.updatedProfile'));
        return Reply::redirect(route('admin.profile.index'), __('messages.profileUpdated'));
    }

    public function updateOneSignalId(Request $request){
        $user = User::find($this->user->id);
        $user->onesignal_player_id = $request->userId;
        $user->save();
        session()->forget('user');
    }
}
