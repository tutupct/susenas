<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['petugas_id', 'wa_message_id', 'wa_number', 'direction', 'message_type', 'body', 'message_at', 'raw_payload'])]
class WhatsappMessage extends Model
{
    public const DIRECTION_INCOMING = 'incoming';
    public const DIRECTION_OUTGOING = 'outgoing';

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(Petugas::class);
    }

    public function detailReports(): BelongsToMany
    {
        return $this->belongsToMany(
            DetailReport::class,
            'detail_report_whatsapp_message'
        )->withTimestamps();
    }
}
