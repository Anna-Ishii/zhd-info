<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;

class ImsSyncLog extends Model
{
    use HasFactory;

    protected $table = 'ims_sync_logs';

    protected $fillable = 
    [
        'import_at',
        'import_department_at',
        'import_department_message',
        'import_department_error',
        'import_crew_at',
        'import_crew_message',
        'import_crew_error'
    ];

    protected $casts = [
        'import_at' => 'datetime',
        'import_department_at' => 'datetime',
        'import_department_error' => 'boolean',
        'import_crew_at' => 'datetime',
        'import_crew_error' => 'boolean',
    ];

    public function is_error(): bool {
        if ($this->import_department_error || $this->import_crew_error ) {
            return true;
        }
        return false;
    }
}
