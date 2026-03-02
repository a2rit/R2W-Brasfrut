<?php

namespace App\Modules\JournalEntry\Models\JournalEntry;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\JournalEntry\Models\JournalEntry\Item
 *
 * @property int $id
 * @property int $je_id
 * @property string $account
 * @property float|null $credit
 * @property float|null $debit
 * @property string|null $project
 * @property string|null $distribution_rule
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry\Item whereAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry\Item whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry\Item whereCredit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry\Item whereDebit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry\Item whereDistributionRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry\Item whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry\Item whereJeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry\Item whereProject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry\Item whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $cardCode
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry\Item whereCardCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry\Item newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry\Item newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Modules\JournalEntry\Models\JournalEntry\Item query()
 */
class Item extends Model
{
    protected $table = 'journal_entry_items';
    protected $fillable = ['je_id', 'account','cardCode', 'credit', 'debit', 'project', 'distribution_rule','costCenter','costCenter2'];

}
