<?php

namespace LaravelEnso\PermissionManager\app\Http\Controllers;

use App\Http\Controllers\Controller;
use LaravelEnso\PermissionManager\app\Http\Requests\ValidatePermissionGroupRequest;
use LaravelEnso\PermissionManager\app\Models\PermissionGroup;

class PermissionGroupController extends Controller
{
    public function index()
    {
        return view('laravel-enso/permissionmanager::permissionGroups.index');
    }

    public function create()
    {
        return view('laravel-enso/permissionmanager::permissionGroups.create');
    }

    public function store(ValidatePermissionGroupRequest $request, PermissionGroup $permissionGroup)
    {
        $group = $permissionGroup->create($request->all());
        flash()->success(__('Permission created'));

        return redirect('system/permissionGroups/'.$group->id.'/edit');
    }

    public function edit(PermissionGroup $permissionGroup)
    {
        return view('laravel-enso/permissionmanager::permissionGroups.edit', compact('permissionGroup'));
    }

    public function update(ValidatePermissionGroupRequest $request, PermissionGroup $permissionGroup)
    {
        $permissionGroup->update($request->all());
        flash()->success(__(config('labels.savedChanges')));

        return back();
    }

    public function destroy(PermissionGroup $permissionGroup)
    {
        if ($permissionGroup->permissions->count()) {
            throw new \EnsoException(__('The permission group cannot be deleted because it has child permissions'), 'warning');
        }

        $permissionGroup->delete();

        return ['message' => __(config('labels.successfulOperation'))];
    }
}
