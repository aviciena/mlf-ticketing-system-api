<?php

namespace App\Http\Controllers;

use App\Helpers\HashidHelper;
use App\Http\Resources\UserResource;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends BaseController
{
    //Get All Users
    public function index(Request $request)
    {
        // Ambil parameter dari query
        $search = $request->query('search');
        $roleFilter = $request->query('role'); // filter role description
        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');

        // Query builder
        $query = User::with(['role', 'events']);

        if ($search) {
            $query->where('name', 'like', "%$search%");
        }

        if ($roleFilter) {
            $query->whereHas('role', function ($q) use ($roleFilter) {
                $q->where('description', $roleFilter);
            });
        }

        // Sorting
        $query->orderBy($sortBy, $sortOrder);

        // Offset Pagination
        $pagination = $this->getPagination($request, $query);
        $limit = $pagination['limit'];
        $start = $pagination['start'];
        $meta = $pagination['meta'];

        // Pagination Query
        $users = $query->skip($start)->take($limit)->get();

        return $this->sendResponse(
            UserResource::collection($users),
            'User list retrieved successfully',
            $meta
        );
    }

    //Find User
    public function find($id)
    {
        $user = User::findByHashid($id);

        if (!$user) {
            return $this->sendError('User not found', [], 422);
        }

        return $this->sendResponse(
            new UserResource($user),
            'User retrieved successfully'
        );
    }

    // Create User
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:users,name',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6',
            'event_id' => 'nullable|string|exists:events,id',
            'role' => 'nullable|string|max:255|exists:roles,code'
        ]);

        $role = $request->role ?  $request->role : 'staff';
        $roleId = Roles::where('code', $role)->first()->id;

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'event_id' => $request->event_id,
            'role_id' => $roleId
        ]);

        if (!$user) {
            $this->validateException([
                'Failed to create user, please try again later!',
            ]);
        }

        return $this->sendResponse(
            new UserResource($user),
            'User created successfully',
            null,
            201
        );
    }

    //Update User
    public function updateUser(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'nullable|string|exists:events,id',
            'role' => 'nullable|string|max:255|exists:roles,code'
        ]);

        $user = User::find($request->user()->id);
        if (!$user) {
            return $this->sendError('User not found', [], 422);
        }

        $role = empty($request->role) ? $user->role->code : $request->role;
        $roleId = Roles::where('code', $role)->first()->id;

        $user->update(array_merge($validated, ['role_id' => $roleId]));

        return $this->sendResponse(
            new UserResource($user),
            'User updated successfully',
            null,
            200
        );
    }

    //Update User
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => [
                'nullable',
                'string',
                Rule::unique('users', 'name')->ignore(HashidHelper::decode($id)),
            ],
            'username' => [
                'nullable',
                'string',
                Rule::unique('users', 'username')->ignore(HashidHelper::decode($id)),
            ],
            'event_id' => 'nullable|string|exists:events,id',
            'role' => 'nullable|string|max:255|exists:roles,code'
        ]);

        $user = User::findByHashid($id);
        if (!$user) {
            return $this->sendError('User not found', [], 422);
        }

        $role = empty($request->role) ? $user->role->code : $request->role;
        $roleId = Roles::where('code', $role)->first()->id;

        $user->update(array_merge($validated, ['role_id' => $roleId, 'is_admin' => $role == 'admin' ? 1 : 0]));

        return $this->sendResponse(
            new UserResource($user),
            'User updated successfully',
            null,
            200
        );
    }

    public function delete(Request $request)
    {
        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return $this->sendError('User not found', [], 422);
        }

        $user->delete();

        return $this->sendResponse([], 'User deleted successfully');
    }
}
