<?php

namespace LaravelEnso\PermissionManager\app\Http\Services;

use Illuminate\Http\Request;
use LaravelEnso\FormBuilder\app\Classes\FormBuilder;
use LaravelEnso\PermissionManager\app\Enums\ResourcePermissions;
use LaravelEnso\PermissionManager\app\Models\Permission;
use LaravelEnso\PermissionManager\app\Models\PermissionGroup;

class ResourceService
{
    const AdminRoleId = 1;

    const FormPath = __DIR__.'/../../Forms/resource.json';

    public function create()
    {
        $form = (new FormBuilder(self::FormPath))
            ->setSelectOptions('permission_group_id', PermissionGroup::pluck('name', 'id'))
            ->getData();

        return compact('form');
    }

    public function store(Request $request)
    {
        \DB::transaction(function () use ($request) {
            $this->getPermissionCollection($request)->each(function ($permission) {
                $permission = Permission::create($permission);
                $permission->roles()->attach(self::AdminRoleId);
            });
        });

        return [
            'message'  => __('The permissions were created!'),
            'redirect' => 'system.permissions.index',
        ];
    }

    private function getPermissionCollection(Request $request)
    {
        $permissions = collect();

        foreach ($this->getPermissionList() as $permission) {
            if (!$request->filled($permission['name'])) {
                continue;
            }

            $permission['name'] = $request->get('prefix').'.'.$permission['name'];
            $permission['description'] = $permission['description'].ucfirst($request->get('prefix'));
            $permission['permission_group_id'] = $request->get('permission_group_id');
            $permissions->push($permission);
        }

        return $permissions;
    }

    private function getPermissionList()
    {
        $resource = (new ResourcePermissions());
        $permissions = $resource->get('resource');
        $permissions = array_merge($permissions, $resource->get('dataTables'));
        $permissions = array_merge($permissions, $resource->get('vueSelect'));

        return $permissions;
    }
}
