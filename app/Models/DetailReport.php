<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['report_id', 'keterangan_error', 'foto', 'respon_petugas', 'status'])]
class DetailReport extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_TERKIRIM = 'terkirim';
    public const STATUS_DIPERBAIKI = 'diperbaiki';
    public const STATUS_SELESAI = 'selesai';

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
