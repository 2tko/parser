<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Project
 * @package App\Models
 * @property integer $id
 * @property integer $coinmarketcap_project_id
 * @property string $name
 * @property string $slug
 * @property integer $cmc_rank
 * @property integer $rating
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';

    public function projectGrowth()
    {
        return $this->hasMany(ProjectGrowth::class);
    }

    public function holders()
    {
        return $this->hasMany(Holder::class);
    }
}
