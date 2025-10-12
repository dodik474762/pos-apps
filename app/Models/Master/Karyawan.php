<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    //
    protected $table = 'karyawan';

    public function CompanyKaryawan()
    {
        return $this->belongsTo(CompanyModel::class, 'company', 'id');
    }
}
