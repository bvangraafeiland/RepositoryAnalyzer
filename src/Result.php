<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 17-04-2016
 * Time: 14:12
 */
class Result extends Model
{
    public $timestamps = false;
    protected $fillable = ['hash', 'repository_id', 'committed_at'];
    protected $dates = ['committed_at'];

    public function repository()
    {
        return $this->belongsTo(Repository::class);
    }

    public function analysisTools()
    {
        return $this->belongsToMany(AnalysisTool::class)->withTimestamps();
    }

    public function warnings()
    {
        return $this->hasMany(Warning::class);
    }
}
