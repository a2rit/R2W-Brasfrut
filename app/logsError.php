<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\logsError
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $idUser
 * @property string $value
 * @property string $operation
 * @property string $message
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\logsError whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\logsError whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\logsError whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\logsError whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\logsError whereOperation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\logsError whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\logsError whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\logsError newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\logsError newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\logsError query()
 */
class logsError extends Model
{

    //
    public function saveInDB($value, $operation, $message){
      $this->idUser = isset(auth()->user()->id) ? auth()->user()->id : '-1';
      $this->value = $value;
      $this->operation = $operation;
      $this->message = $message;
      $this->save();
      return $this->id;
    }
}
