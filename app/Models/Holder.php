<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Holder
 * @package App\Models
 * @property  int $id
 * @property  int $project_id
 * @property  int $count
 * @property  string $date
 * @property  Carbon $created_at
 * @property  Carbon $updated_at
 */
class Holder extends Model
{
    use HasFactory;

    protected $table = 'holders';
}
