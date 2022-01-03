<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;

class Status extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'statuses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'ticket_id',
        'name',
        'content',
        'is_public',
    ];

    /**
     * The attributes that need to be logged.
     *
     * @var array<string>
     */
    protected static $logAttributes = [
        'content',
    ];

    /**
     * Get the tickets that owns the flag.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the user that set this flag.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
