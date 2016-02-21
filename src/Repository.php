<?php
namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Repository extends Model
{
    public $timestamps = false;

    protected $fillable = ['id', 'full_name', 'stargazers_count', 'created_at', 'pushed_at', 'language', 'default_branch', 'has_issues', 'open_issues_count'];

    protected $dates = ['created_at', 'pushed_at'];

    protected function setPushedAtAttribute($value)
    {
        $this->attributes['pushed_at'] = Carbon::parse($value)->format($this->getDateFormat());
    }

    protected function setCreatedAtAttribute($value)
    {
        $this->attributes['created_at'] = Carbon::parse($value)->format($this->getDateFormat());
    }

    public function asats()
    {
        return $this->belongsToMany(AnalysisTool::class);
    }
}
