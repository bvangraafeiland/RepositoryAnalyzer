<?php
namespace App;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Psr\Http\Message\StreamInterface;

class Repository extends Model
{
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = ['id', 'full_name', 'stargazers_count', 'created_at', 'pushed_at', 'language', 'default_branch', 'has_issues', 'open_issues_count'];

    protected $dates = ['created_at', 'pushed_at'];

    public static function addIfNew(array $item)
    {
        if (! static::query()->find($item['id']))
            return static::create($item);

        return null;
    }

    /**
     * @return BelongsToMany
     */
    public function asats()
    {
        return $this->belongsToMany(AnalysisTool::class)->withPivot(['config_file_present', 'in_dev_dependencies', 'in_build_tool'])->withTimestamps();
    }

    public function buildTools()
    {
        return $this->belongsToMany(BuildTool::class)->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function pullRequests()
    {
        return $this->hasMany(PullRequest::class);
    }

    /**
     * @return HasMany
     */
    public function mergedPullRequests()
    {
        return $this->pullRequests()->whereNotNull('merged_at');
    }

    /**
     * @return HasMany
     */
    public function rejectedPullRequests()
    {
        return $this->pullRequests()->whereNull('merged_at');
    }

    /**
     * @return HasMany
     */
    public function results()
    {
        return $this->hasMany(Result::class);
    }

    /**
     * Get the contents of the given file in the repository
     *
     * @param $path
     *
     * @return StreamInterface|null
     * @throws ClientException
     */
    public function getFile($path)
    {
        $githubRaw = new Client([
            'base_uri' => 'http://raw.githubusercontent.com',
        ]);

        try {
            return $githubRaw->get('/' . $this->full_name . '/' . $this->default_branch . '/' . $path)->getBody();
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                return null;
            } else {
                throw $e;
            }
        }
    }

    /**
     * Check if a project file contains the given substring, case insensitive
     *
     * @param $filePath
     * @param $string
     *
     * @return bool
     */
    public function fileContains($filePath, $string)
    {
        return str_contains(strtolower($this->getFile($filePath)), strtolower($string));
    }

    public function usesBuildTool($tool)
    {
        return $this->buildTools->contains('name', $tool);
    }

    protected function setPushedAtAttribute($value)
    {
        $this->attributes['pushed_at'] = Carbon::parse($value)->format($this->getDateFormat());
    }

    protected function setCreatedAtAttribute($value)
    {
        $this->attributes['created_at'] = Carbon::parse($value)->format($this->getDateFormat());
    }

    protected function getNameAttribute()
    {
        $parts = explode('/', $this->full_name);
        return $parts[1];
    }

    public function getAgeAttribute()
    {
        return $this->created_at->diffInHours($this->pushed_at);
    }

    public function getLifetimeDensityAttribute()
    {
        if ($this->age === 0)
            return 0;

        return $this->pull_request_count / $this->age;
    }
}
