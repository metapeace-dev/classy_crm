<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportSetting extends Model
{

    protected $guarded = ['id'];

    public static function checkModule($fieldName, $type)
    {

        $user = auth()->user();

        $module = ReportSetting::where('field_name', $fieldName)->where('type', $type);

        if ($user->hasRole('admin')) {
            $module = $module->where('role', 'admin');

        } elseif ($user->hasRole('designer')) {
            $module = $module->where('role', 'designer');

        } elseif ($user->hasRole('employee')) {
            $module = $module->where('role', 'employee');
        }

        $module = $module->where('status', 'active');

        $module = $module->first();

        return $module ? true : false;
    }
}
