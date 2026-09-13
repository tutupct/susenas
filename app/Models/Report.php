<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['petugas_id', 'urutan', 'nama_krt'])]
class Report extends Model
{
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(Petugas::class);
    }

    public function detailReports(): HasMany
    {
        return $this->hasMany(DetailReport::class);
    }
}
