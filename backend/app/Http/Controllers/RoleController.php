<?php

    namespace App\Http\Controllers;
    use Illuminate\Http\Request;
    use App\Http\Requests;
    use App\Http\Controllers\Controller;

    class RoleController extends Controller
    {
        public function index()
        {
            // Fetch all roles with their permissions
            $roles = \Spatie\Permission\Models\Role::with('permissions')->get();
            return response()->json($roles);
        }
    }

?>