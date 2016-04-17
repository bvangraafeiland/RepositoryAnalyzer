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
    protected $fillable = ['hash'];

    public function repository()
    {
        return $this->belongsTo(Repository::class);
    }

    public function analysisTool()
    {
        return $this->belongsTo(AnalysisTool::class);
    }
}
