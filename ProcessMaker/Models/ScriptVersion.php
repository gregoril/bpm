<?php

namespace ProcessMaker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
// use ProcessMaker\Models\EnvironmentVariable;
// use ProcessMaker\Exception\ScriptLanguageNotSupported;

/**
 * Represents an Eloquent model of a Script
 *
 * @package ProcessMaker\Model
 *
 * @property string id
 * @property string title
 * @property text description
 * @property string language
 * @property text code
 *
 * @OA\Schema(
 *   schema="scriptVersionsEditable",
 *   @OA\Property(property="id", type="string", format="id"),
 *   @OA\Property(property="title", type="string"),
 *   @OA\Property(property="description", type="string"),
 *   @OA\Property(property="language", type="string"),
 *   @OA\Property(property="code", type="string"),
 * ),
 * @OA\Schema(
 *   schema="scriptVersions",
 *   allOf={@OA\Schema(ref="#/components/schemas/scriptsEditable")},
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time"),
 * )
 *
 */
class ScriptVersion extends Model
{
    protected $guarded = [
        'id',
        'script_id',
        'created_at',
        'updated_at',
    ];

    /**
     * Validation rules
     *
     * @param $existing
     *
     * @return array
     */
    public static function rules($existing = null)
    {
        $rules = [
            'title' => 'required|unique:script_versions,title',
            'language' => 'required|in:php,lua'
        ];
        if ($existing) {
            // ignore the unique rule for this id
            $rules['title'] = [
                'required',
                'string',
                Rule::unique('script_versions')->ignore($existing->id, 'id')
            ];
        }
        return $rules;
    }
    
    public function script()
    {
        return $this->belongsTo(Script::class);
    }
}