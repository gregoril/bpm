<?php

namespace ProcessMaker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

/**
 * Represents a group definition.
 *
 * @property string $id
 * @property string $name
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $created_at
 *
 *   @OA\Schema(
 *   schema="groupsEditable",
 *   @OA\Property(property="id", type="string", format="id"),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="description", type="string"),
 *   @OA\Property(property="status", type="string", enum={"ACTIVE", "INACTIVE"}),
 * ),
 * @OA\Schema(
 *   schema="groups",
 *   allOf={@OA\Schema(ref="#/components/schemas/groupsEditable")},
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time"),
 * )
 *
 */
class Group extends Model
{

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public static function rules($existing = null)
    {
        $rules = [
            'name' => 'required|string|unique:groups,name',
            'status' => 'in:ACTIVE,INACTIVE'
        ];

        if ($existing) {
            $rules['name'] = [
                'required',
                'string',
                Rule::unique('groups')->ignore($existing->id, 'id')
            ];
        }

        return $rules;
    }

    public function permissionAssignments()
    {
        return $this->morphMany(PermissionAssignment::class, 'assignable', null, 'assignable_id');
    }

    public function groupMembersFromMemberable()
    {
        return $this->morphMany(GroupMember::class, 'member', null, 'member_id');
    }

    public function groupMembers()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function permissions()
    {
        $permissions = [];
        foreach ($this->groupMembersFromMemberable as $gm) {
            $group = $gm->group;
            $permissions =
                array_merge($permissions, $group->permissions());
        }
        foreach ($this->permissionAssignments as $pa) {
            $permissions[] = $pa->permission;
        }
        return $permissions;
    }
}
