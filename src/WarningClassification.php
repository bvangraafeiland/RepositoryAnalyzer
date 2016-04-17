<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 17-04-2016
 * Time: 17:01
 */
class WarningClassification extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'category'];

    public function warnings()
    {
        return $this->hasMany(Warning::class, 'classification_id');
    }
}
