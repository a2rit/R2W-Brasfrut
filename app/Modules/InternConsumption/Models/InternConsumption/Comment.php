<?php

namespace App\Modules\InternConsumption\Models\InternConsumption;

use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\InternConsumption\Models\InternConsumption\Comment
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property int $intern_consumption_id
 * @property string $comment
 * @property string $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\InternConsumption\Models\InternConsumption\Comment whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\InternConsumption\Models\InternConsumption\Comment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\InternConsumption\Models\InternConsumption\Comment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\InternConsumption\Models\InternConsumption\Comment whereInternConsumptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\InternConsumption\Models\InternConsumption\Comment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\InternConsumption\Models\InternConsumption\Comment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\InternConsumption\Models\InternConsumption\Comment whereUserId($value)
 * @property-read \App\User $user
 */
class Comment extends Model
{
    protected $table = 'intern_consumption_comments';

    protected $fillable = ['user_id', 'comment', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
