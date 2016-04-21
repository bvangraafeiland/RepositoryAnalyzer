<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 17-04-2016
 * Time: 18:28
 */
class Warning extends Model
{
    public $timestamps = false;
    protected $fillable = ['file', 'line', 'column', 'rule', 'message'];

    public function classification()
    {
        return $this->belongsTo(WarningClassification::class);
    }

    public function result()
    {
        return $this->belongsTo(Result::class);
    }

    public function analysisTool()
    {
        return $this->belongsTo(AnalysisTool::class);
    }
}
