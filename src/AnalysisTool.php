<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 19-02-2016
 * Time: 14:54
 */
class AnalysisTool extends Model
{
    protected $fillable = ['name'];
    public $timestamps = false;

    public function warnings()
    {
        return $this->hasMany(Warning::class);
    }

    public function results()
    {
        return $this->belongsToMany(Result::class)->withTimestamps();
    }
}
