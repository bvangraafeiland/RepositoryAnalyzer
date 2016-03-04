<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 03-03-2016
 * Time: 15:53
 */
class BuildTool extends Model
{
    protected $fillable = ['name'];
    public $timestamps = false;
}
