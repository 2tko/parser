<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ProjectGrowth
 * @package App\Models
 * @property integer $id
 * @property integer $project_id
 * @property string $key
 * @property integer $value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ProjectGrowth extends Model
{
    use HasFactory;

    const WEEK = 'week';
    const TWO_WEEKS = '2_weeks';
    const MONTH = 'month';
    const THREE_MONTHS = '3_month';
    const SIX_MONTHS = '6_month';
    const YEAR = 'year';

    protected $table = 'projects_growth';

    protected $fillable = ['project_id', 'key', 'value'];
}
