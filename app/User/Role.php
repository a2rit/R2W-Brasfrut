<?php

namespace App\User;

use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\User\Role
 *
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User\Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User\Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User\Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User\Role whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $code
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User\Role whereCode($value)
 * @property-read \App\User\Group $group
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User\Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User\Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User\Role query()
 */
class Role extends Model
{
    protected $table = 'user_roles';

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, "user_group_user_role",
            "user_role_id", "user_group_id", "id",
            "group_id");
    }
}
