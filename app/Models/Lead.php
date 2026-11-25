<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    public const STATUSES = ['new', 'contacted', 'in_progress', 'won', 'closed', 'spam'];

    protected $fillable = [
        'name', 'email', 'company', 'phone', 'service_id', 'service_label', 'message', 'locale',
        'status', 'notes', 'recaptcha_score', 'ip_hash', 'user_agent',
        'ack_status', 'ack_sent_at', 'ack_error', 'notify_status', 'notify_sent_at', 'notify_error',
    ];

    protected $casts = [
        'ack_sent_at' => 'datetime',
        'notify_sent_at' => 'datetime',
        'recaptcha_score' => 'float',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function hasFailedEmails(): bool
    {
        return $this->ack_status === 'failed' || $this->notify_status === 'failed';
    }

    /** @return array<string, string|null> */
    public function templateVariables(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'company' => $this->company,
            'phone' => $this->phone,
            'service' => $this->service_label,
            'message' => $this->message,
            'date' => $this->created_at?->timezone('America/Guatemala')->format('Y-m-d H:i'),
        ];
    }
}
