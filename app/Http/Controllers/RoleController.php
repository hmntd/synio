<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::where('name', '!=', 'super-admin')->get();

        return response()->json([
            'success' => true,
            'roles' => $roles,
        ], Response::HTTP_OK);
    }
}
