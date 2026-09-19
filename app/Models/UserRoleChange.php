<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRoleChange extends Model
{
    protected $fillable = ['changed_by', 'from_role', 'to_role', 'note'];

    public function actor()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
