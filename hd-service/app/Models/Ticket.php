<?php

namespace App\Models;

use App\Traits\Taggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;

class Ticket extends Model
{
    use HasFactory, LogsActivity, Taggable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tickets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'subject',
        'content',
        'due_at',
        'priority',
    ];

    /**
     * The attributes that need to be logged.
     *
     * @var array<string>
     */
    protected static $logAttributes = [
        'subject',
        'content',
        'owner_id',
        'priority',
    ];

    /**
     * The attributes that are mutated to dates.
     *
     * @var array<string>
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * Get the statuses.
     */
    public function statuses(): HasMany
    {
        return $this->hasMany(Status::class);
    }

    /**
     * Get the owners.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the current assigned agents.
     */
    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ticket_agent', 'ticket_id', 'agent_id');
    }

    /**
     * Get the flags.
     */
    public function flags(): HasMany
    {
        return $this->hasMany(Flag::class);
    }

    /**
     * Get the ticket's files.
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    /**
     * Scope a query to filter tickets with current status.
     *
     * @param string|array<string> $status
     */
    public function scopeWhereStatus(Builder $query, $status): Builder
    {
        return $query
            ->leftJoinSub(
                Status::select(DB::raw('MAX(id) as id, ticket_id, name'))->groupBy('ticket_id'),
                'current_status',
                function ($join) {
                    $join->on('tickets.id', '=', 'current_status.ticket_id');
                }
            )
            ->select('tickets.*', 'current_status.name')
            ->whereIn('current_status.name', Arr::wrap($status));
    }

    /**
     * Scope a query to filter tickets with owner.
     *
     * @param int|\App\Models\User $user
     */
    public function scopeWhereOwner(Builder $query, $user): Builder
    {
        return $query->where('owner_id', $user instanceof User ? $user->id : $user);
    }

    /**
     * Scope a query to filter tickets by agent.
     *
     * @param int|\App\Models\User $user
     */
    public function scopeWhereHasAgent(Builder $query, $user): Builder
    {
        return $query->whereHas('agents', function (Builder $query) use ($user) {
            $query->where('id', $user instanceof User ? $user->id : $user);
        });
    }

    /**
     * Get the tickets's status.
     */
    public function getStatusAttribute(): Status
    {
        return $this->statuses()->latest()->first();
    }

    /**
     * Returns true if the agent if active on the ticket.
     *
     * @param int|\App\Models\User $user
     */
    public function hasAgent($user): bool
    {
        return !!$this->agents()->find(is_int($user) ? $user : $user->id);
    }
}
