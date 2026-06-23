<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FocusSession extends Model
{

    protected $fillable=[
        'user_id',
        'mode',
        'duration_seconds',
        'completed_at',
    ];
    //todo разобраться
    // почитать что такое тесты для чего нужны, написать с gpt
    // почитать про   use Illuminate\Database\Eloquent\Relations\BelongsTo;для чего нужен, как внедрить
    protected $casts = [
        'completed_at' => 'datetime',
    ];
    /**
     * @return BelongsTo<User, $this>
     */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
