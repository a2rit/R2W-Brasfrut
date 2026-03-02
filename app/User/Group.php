<?php

namespace App\User;

use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\User\Group
 *
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User\Role[] $roles
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User\Group whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User\Group whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User\Group whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User\Group whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read mixed $roles_string
 * @property-read mixed $users_string
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User\Group newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User\Group newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User\Group query()
 */
class Group extends Model
{
    protected $table = 'user_groups';

    protected $fillable = ['name'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_group_user_role',
            'user_group_id', 'user_role_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'group_id', 'id');
    }

    public function getRolesStringAttribute()
    {
        $roles = $this->roles->toArray();
        return implode(', ', array_column($roles, 'name'));
    }

    public function getUsersStringAttribute()
    {
        $roles = $this->users->toArray();
        return implode(', ', array_column($roles, 'name'));
    }
}
