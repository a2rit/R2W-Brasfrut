<?php

namespace App\Modules\Administration\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Administration\Models\Approver
 *
 * @property int $id
 * @property string $approverUser
 * @property string $idLoftedApproveds
 * @property string $idUser
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $nameApproverUser
 * @property string|null $nameLoftedApproveds
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Administration\Models\Approver newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Administration\Models\Approver newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Administration\Models\Approver query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Administration\Models\Approver whereApproverUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Administration\Models\Approver whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Administration\Models\Approver whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Administration\Models\Approver whereIdLoftedApproveds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Administration\Models\Approver whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Administration\Models\Approver whereNameApproverUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Administration\Models\Approver whereNameLoftedApproveds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Administration\Models\Approver whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\Administration\Models\Approver whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Approver extends Model
{
    protected $table = 'approver_documents';
    protected $fillable = ['approverUser','nivel','nameApproverUser','idLoftedApproveds','nameLoftedApproveds','idUser','status'];
}
