<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $table = 'notes';

    public static function allLeadNotes($id)
    {
        $notes = Note::where('lead_id', $id);
        return $notes->get();
    }

    public static function allClientNotes($id)
    {
        $notes = Note::where('client_id', $id);
        return $notes->get();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
