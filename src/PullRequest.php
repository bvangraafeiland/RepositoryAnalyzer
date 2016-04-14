<?php
namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: Bastiaan
 * Date: 14-04-2016
 * Time: 13:40
 */
class PullRequest extends Model
{
    public $timestamps = false;
    public $incrementing = false;

    protected $dates = ['created_at', 'updated_at', 'closed_at', 'merged_at'];

    protected $fillable = ['id', 'number', 'state', 'title', 'user_id', 'created_at', 'updated_at', 'closed_at', 'merged_at'];

    public function repository()
    {
        return $this->belongsTo(Repository::class);
    }
}
