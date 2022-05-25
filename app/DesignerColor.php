<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DesignerColor extends Model
{
    protected $table = 'designer_colors';
    protected $guarded = ['id'];

    public function schedule(){
        return $this->hasOne(InstallSchedule::class, 'designer_color_id');
    }
}
