<?php

namespace App\Http\Controllers\api;


use App\Models\User;
use App\Models\Student;
use App\Models\Menu;
use App\Models\Role;
use App\Models\FeesGroup;
use App\Models\FeesType;
use App\Models\FeesMaster;
use App\Models\Branch;
use App\Models\Classes;
use App\Models\InputField;
// use App\Models\FormField;
// use App\Models\AllowedFormField;
use App\Models\RoleInputAssignment;
use App\Models\RolePermission;
use App\Models\UserPermission;
use App\Models\NewRegistrationIp;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use DB;
use Exception;
use Helper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ApiController extends Controller
{

    public function fillUsersToTwentyThousand()
    {
        $target = 20000;
        $currentCount = \App\Models\User::count();

        if ($currentCount >= $target) {
            return response()->json([
                'status' => true,
                'message' => "Already have $currentCount users."
            ]);
        }

        // Get users to duplicate (excluding role_id = 1)
        $users = \App\Models\User::where('role_id', '!=', 1)->get();

        if ($users->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => "No users to duplicate (excluding role_id = 1)."
            ]);
        }

        $toCreate = $target - $currentCount;
        $created = 0;

        while ($created < $toCreate) {
            foreach ($users as $user) {
                if ($created >= $toCreate) break;

                $newUser = $user->replicate();

                // Make username/email unique if they exist
                if (isset($newUser->username)) {
                    $newUser->username = $newUser->username . '_copy_' . uniqid();
                }
                if (isset($newUser->email)) {
                    $parts = explode('@', $newUser->email);
                    $newUser->email = $parts[0] . '+copy' . uniqid() . '@' . ($parts[1] ?? 'example.com');
                }

                // Optionally reset password
                // $newUser->password = bcrypt('defaultpassword');

                $newUser->save();
                $created++;
            }
        }

        return response()->json([
            'status' => true,
            'message' => "Filled users table to $target entries by duplicating users (excluding role_id = 1)."
        ]);
    }
    private function handlePasswordField(&$data)
    {
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
    }

    private function handleFileUpload($file, $folder = 'uploads')
    {
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('public/' . $folder, $filename);


        return str_replace('public/', 'storage/app/public/', $path);
    }
    private function normalizeDate($dob)
    {
        try {
            // Check if it's a numeric Excel-style serial number
            if (is_numeric($dob)) {
                return Carbon::createFromTimestamp(($dob - 25569) * 86400)->format('Y-m-d');
            }

            // Handle string formats like dd-mm-yyyy or dd/mm/yyyy
            $formatted = str_replace(['/', '.'], '-', $dob);
            return Carbon::parse($formatted)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
    //     public function baseImagePath()
    //     {
    //         // $baseImagePath = config('app.url') . '/dreamsakha-admin/webimage/';
    //         $baseImagePath = config('app.url') . '/public/webimage/';
    // return $baseImagePath;
    //     }

    public function baseImagePath()
    {
        // This should match the public URL path to the storage folder
        // return config('app.url') . '/';
        return config('app.url') . '/react-laravel-school/';
    }
    public function checkIp(Request $request)
    {
        $ip = $request->ip;
        $isAllowed = NewRegistrationIp::where('ip', $ip)->where('status', 1)->exists();

        if ($isAllowed) {
            return response()->json(['status' => true], 200);
        } else {
            return response()->json(['status' => false], 200);
        }
    }


    public function roleInputAssignment(Request $request)
    {
        try {
            $selectedIds = json_decode($request->selected_ids, true);

            if (!is_array($selectedIds)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid selected_ids format. Must be a JSON array.'
                ], 422);
            }

            $roleId = $request->role_id;
            $adminId = $request->admin_id;
            $branchId = $request->branch_id;

            // Step 1: Get existing assigned input_ids
            $existingAssignments = RoleInputAssignment::where('role_id', $roleId)
                ->where('admin_id', $adminId)
                ->where('branch_id', $branchId)
                ->get();

            $existingIds = $existingAssignments->pluck('input_id')->toArray();

            // Step 2: Determine which to delete
            $toDelete = array_diff($existingIds, $selectedIds);
            if (!empty($toDelete)) {
                RoleInputAssignment::where('role_id', $roleId)
                    ->where('admin_id', $adminId)
                    ->where('branch_id', $branchId)
                    ->whereIn('input_id', $toDelete)
                    ->delete();
            }

            // Step 3: Determine which to insert
            $toInsert = array_diff($selectedIds, $existingIds);
            foreach ($toInsert as $inputId) {
                RoleInputAssignment::create([
                    'role_id' => $roleId,
                    'admin_id' => $adminId,
                    'branch_id' => $branchId,
                    'input_id' => $inputId,
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Inputs synced successfully.',
                'added_count' => count($toInsert),
                'removed_count' => count($toDelete)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to sync inputs.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getRoleInputAssignments(Request $request)
    {
        try {
            $roleId = $request->role_id;
            $adminId = $request->admin_id;
            $branchId = $request->branch_id;

            if (!$roleId || !$adminId || !$branchId) {
                return response()->json([
                    'status' => false,
                    'message' => 'role_id, admin_id, and branch_id are required.'
                ], 422);
            }

            $assignedInputs = RoleInputAssignment::where('role_input_assignments.role_id', $roleId)
                ->where('role_input_assignments.admin_id', $adminId)
                // ->where('role_input_assignments.branch_id', $branchId)
                ->leftJoin('inputs', 'role_input_assignments.input_id', '=', 'inputs.id')
                ->select(
                    'inputs.label',
                    'inputs.type',
                    'inputs.name',
                    'inputs.required',
                    'inputs.col',
                    'inputs.placeholder',
                    'inputs.options',
                    'inputs.source_table',
                    'inputs.source_column',
                    'inputs.value',
                    'inputs.step',
                )
                ->get();
            return response()->json([
                'status' => true,
                'data' => $assignedInputs,
                'message' => 'Assigned inputs fetched successfully.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch assigned inputs.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function saveExcelData(Request $request)
    {
        $data = $request->all();

        if ($data) {
            $data  = $data['data'][0];
        }

        // Normalize DOB format
        if (!empty($data['dob'])) {
            $data['dob'] = $this->normalizeDate($data['dob']);
        }

        // Hash password before saving
        $data['confirm_password'] = $data['password'];
        $data['password'] = bcrypt($data['password']);
        $data['role_id'] = 1;


        // Create user
        $user = User::create($data);

        // Update admin_id after creating (assuming you want to set their own ID as admin_id)
        $user->admin_id = $user->id;
        $user->save();



        $getArray = [
            'users.id',
            'users.status',
            'users.name',
            'users.mobile',
            'users.username',
            'users.dob',
            'users.gender',
            'users.address',
            'users.image',
            'users.pincode',
            'users.auth_provider',
            'users.role_id',
            'users.branch_id',
            'users.admin_id',
        ];

        $user = User::where('users.id', $user->id)
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->select(array_merge($getArray, ['roles.name as role_name'])) // Rename role column for clarity
            ->first();
        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return success response
        return response()->json([
            'status' => true,
            'message' => 'User Created successfully',
            'access_token' => $token,
            'user' => $user, // Return the user data
        ], 200);
    }
    // public function createUser(Request $request, $roleId)
    // {
    //     $data = $request->all();
    //     // Handle image upload separately
    //     if ($request->hasFile('image')) {

    //         $imagePath = $request->file('image')->store('uploads/users', 'public'); 
    //         $data['image'] = $imagePath;
    //     }

    //     // // Remove confirm_password from data before saving
    //     // unset($data['confirm_password']);

    //     // Hash password before saving
    //     $data['password'] = bcrypt($data['password']);

    //     // Create user
    //     $user = User::create($data);

    //     return response()->json(['status' => true, 'message' => 'User created successfully'], 201);
    // }



    public function createBranch(Request $request)
    {


        $branch = Branch::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Branch created successfully',
            'data' => $branch
        ], 201);
    }
    public function createUser(Request $request)
    {
        $data = $request->all();

        // ✅ Extract and unset permissions
        $permissions = isset($data['permissions']) ? json_decode($data['permissions'], true) : [];

        unset($data['permissions']);

        // 🔐 Handle password encryption
        $this->handlePasswordField($data);

        // 📂 Handle uploaded files dynamically
        foreach ($request->allFiles() as $key => $file) {
            if ($file->isValid()) {
                $data[$key] = $this->handleFileUpload($file, 'users');
            }
        }

        // 👤 Create user
        $user = User::create($data);

        // 🔁 Save permissions if present
        if (!empty($permissions) && is_array($permissions)) {
            foreach ($permissions as $permission) {
                \App\Models\UserPermission::create([
                    'user_id' => $user->id,
                    'permission' => $permission,
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    // Create Student

    public function createStudent(Request $request)
    {
        $data = $request->all();

        // 🔐 Encrypt password if present (optional for students)
        $this->handlePasswordField($data); // You can remove this if students don't have passwords

        // 📂 Handle uploaded files dynamically (e.g., photo)
        foreach ($request->allFiles() as $key => $file) {
            if ($file->isValid()) {
                $data[$key] = $this->handleFileUpload($file, 'students');
            }
        }

        // 👤 Create student
        $student = Student::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Student created successfully',
            'data' => $student
        ], 201);
    }


    // Update an existing branch
    public function updateUser(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->update($request->all());
        return response()->json([
            'status' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ], 200);
    }
    
    public function updateBranch(Request $request, $id)
    {
        $branch = Branch::find($id);

        if (!$branch) {
            return response()->json([
                'status' => false,
                'message' => 'Branch not found'
            ], 404);
        }



        $branch->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Branch updated successfully',
            'data' => $branch
        ], 200);
    }
    public function createRole(Request $request)
    {
        $data = $request->all();

        // ✅ Extract and decode permissions (JSON string)
        $permissions = isset($data['permissions']) ? json_decode($data['permissions'], true) : [];

        // Remove permissions from role data before saving
        unset($data['permissions']);

        // ✅ Create role using only role-specific data
        $role = Role::create($data);

        // ✅ Insert role permissions separately
        if (!empty($permissions) && is_array($permissions)) {
            foreach ($permissions as $permission) {
                RolePermission::create([
                    'role_id'   => $role->id,
                    'permission' => $permission,
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Role created successfully',
            'data' => $role
        ], 201);
    }

    // Update an existing branch
    public function updateRole(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'status' => false,
                'message' => 'Role not found'
            ], 404);
        }



        $role->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Role updated successfully',
            'data' => $role
        ], 200);
    }


    public function statusChangeSingle($id, $modal)
    {
        $modelClass = "App\\Models\\" . $modal;

        if (!class_exists($modelClass)) {
            return response()->json(['status' => false, 'message' => 'Invalid model'], 400);
        }

        $record = $modelClass::find($id);

        if (!$record) {
            return response()->json(['status' => false, 'message' => 'Record not found'], 404);
        }

        // Toggle status: if 1 set to 0, if 0 set to 1
        $record->status = $record->status == 1 ? 0 : 1;
        $record->save();

        return response()->json([
            'status' => true,
            'message' => $modal . ' status changed successfully',
            'new_status' => $record->status
        ]);
    }
    public function deleteCommonSingle($id, $modal)
    {
        $modelClass = "App\\Models\\" . $modal;

        if (!class_exists($modelClass)) {
            return response()->json(['status' => false, 'message' => 'Invalid model'], 400);
        }

        $record = $modelClass::find($id);

        if (!$record) {
            return response()->json(['status' => false, 'message' => 'Record not found'], 404);
        }

        $record->delete();

        return response()->json(['status' => true, 'message' => $modal . ' Deleted successfully']);
    }



    public function deleteCommonBulk(Request $request, $modal)
    {
        $modelClass = "App\\Models\\" . $modal;

        if (!class_exists($modelClass)) {
            return response()->json(['status' => false, 'message' => 'Invalid model'], 400);
        }

        $ids = $request->ids; // expects: ['id1', 'id2', ...]
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['status' => false, 'message' => 'No IDs provided for deletion'], 400);
        }

        $deleted = $modelClass::whereIn('id', $ids)->delete();

        return response()->json([
            'status' => true,
            'deleted_count' => $deleted,
            'message' => "$deleted $modal records deleted successfully"
        ]);
    }

    private function applyCommonFilters($query, $filters, $table = 'users')
    {
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') continue;

            switch ($key) {
                case 'status':
                    $query->where("{$table}.status", $value);
                    break;
                case 'gender':
                    $query->where("{$table}.gender", $value);
                    break;

                case 'name':
                    $query->where("{$table}.name", 'like', "%$value%");
                    break;

                case 'date_from':
                    if (!empty($filters['date_to'])) {
                        $query->whereBetween("{$table}.created_at", [$filters['date_from'], $filters['date_to']]);
                    }
                    break;

                    // Add more filter cases as needed
            }
        }

        return $query;
    }
    public function getUsersData(Request $request)
    {

        $adminId = $request->admin_id;
        $roleId = $request->role_id;
        $branchId = $request->branch_id;
        $filters = $request->filters;



        // Fetch users based on role
        $users = DB::table('users')
            ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->leftJoin('users as admins', 'users.admin_id', '=', 'admins.id')
            ->select('users.*', 'branches.name as branch_name', 'roles.name as role_name', 'admins.name as admin_name')
            ->where('users.role_id', $roleId)
            ->whereNotNull('users.branch_id')
            ->where('users.admin_id', $adminId);

        if ($branchId != -1) {
            $users->where('users.branch_id', $branchId);
        }
        $users = $this->applyCommonFilters($users, $filters, 'users')->get();

        // // Transform response for consistency
        $data = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'password' => $user->confirm_password,
                'dob' => $user->dob,
                'mobile' => $user->mobile,
                'gender' => $user->gender,
                'role_id' => $user->role_name,
                'admin_id' => $user->admin_name,
                'branch_id' => $user->branch_name,
                // 'image' => asset('storage/' . $user->image),
                'image' => 'http://localhost/library-admin/storage/app/public/' . $user->image,

                // 'role' => $user->role,
                'branch_name' => $user->id == $user->branch_id ? 'Admin' : $user->branch_name,
                'status' => $user->status == 1 ? 'Active' : 'Inactive',
                // 'extra_field' => $user->role === 'student' ? $user->grade : ($user->role === 'teacher' ? $user->subject : null)
            ];
        });


        return response()->json(['status' => true, 'data' => $data, 'message' => 'Users Fetched Successfully'], 200);
    }

    // get student data
    public function getStudentsData(Request $request)
    {
        $adminId = $request->admin_id;
        $branchId = $request->branch_id;
        $filters = $request->filters;

        $students = DB::table('students')
            ->leftJoin('branches', 'students.branch_id', '=', 'branches.id')
            ->leftJoin('users as admins', 'students.admin_id', '=', 'admins.id')
            ->select(
                'students.*',
                'branches.name as branch_name',
                'admins.name as admin_name'
            )
            ->where('students.admin_id', $adminId)
            ->whereNotNull('students.branch_id');
        if ($branchId != -1) {
            $students->where('students.branch_id', $branchId);
        }
        $students = $this->applyCommonFilters($students, $filters, 'students')->get();

        $data = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'enrollment_no' => $student->enrollment_no,
                'dob' => $student->dob,
                'mobile' => $student->mobile,
                'email' => $student->email,
                'gender' => $student->gender,
                'admin_id' => $student->admin_name,
                'branch_id' => $student->branch_name,
                'image' => $student->image
                    ? 'http://localhost/library-admin/storage/app/public/' . $student->image
                    : null,
                'branch_name' => $student->id == $student->branch_id ? 'Admin' : $student->branch_name,
                'status' => $student->status == 1 ? 'Active' : 'Inactive',
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => 'Students Fetched Successfully'
        ], 200);
    }

    public function getStudents(Request $request)
    {
        $selectedBranchId = $request->branch_id;

        $students = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
            ->select('users.*', 'branches.name as branch_name', 'students.*')
            ->orderBy('users.id', 'DESC');

        if ($selectedBranchId != -1) {
            $students->where('users.branch_id', $selectedBranchId);
        }

        $students = $students->get()
            ->map(function ($student) {
                $student->image = $this->getImageUrlIfExists($student->image);
                return $student;
            });



        return response()->json([
            'status' => true,
            'data' => $students,
            'message' => 'Students Fetched Successfully'
        ]);
    }
    
    // update student data
    public function updateStudent(Request $request, $id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'status' => false,
                'message' => 'Student not found'
            ], 404);
        }



        $student->update($request->all());
        return response()->json([
            'status' => true,
            'message' => 'Student updated successfully',
            'data' => $student
        ], 200);
    }

    private function getImageUrlIfExists($imagePath)
    {
        if (empty($imagePath)) {
            return null;
        }

        // Remove storage/app/public/ to get relative path for Storage disk 'public'
        $relativePath = str_replace('storage/app/public/', '', $imagePath);

        if (Storage::disk('public')->exists($relativePath)) {
            return $this->baseImagePath() . $imagePath; // full URL
        }

        return null; // file does not exist
    }

    public function getUsers(Request $request)
    {

        $selectedBranchId = $request->branch_id;
        $users = User::leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
            ->select('users.*', 'roles.name as role_name', 'branches.name as branch_name')
            ->orderBy('id', 'DESC');

        if ($selectedBranchId != -1) {
            $users->where('users.branch_id', $selectedBranchId);
        }
        $users = $users->whereNotIn('users.id', [1])->get()
            ->map(function ($user) {
                $user->image = $this->getImageUrlIfExists($user->image);
                return $user;
            });

        return response()->json([
            'status' => true,
            'data' => $users,
            'message' => 'Users Fetched Successfully'
        ]);
    }

    public function checkUnique(Request $request)
    {
        $username = $request->query('username');
        $mobile = $request->query('mobile');
        $email = $request->query('email');

        $response = [];

        if ($username !== null) {
            $response['usernameExists'] = \App\Models\User::where('username', $username)->exists();
        }
        if ($mobile !== null) {
            $response['mobileExists'] = \App\Models\User::where('mobile', $mobile)->exists();
        }
        if ($email !== null) {
            $response['emailExists'] = \App\Models\User::where('email', $email)->exists();
        }

        return response()->json($response);
    }
    public function excelUpload(Request $request, $modal)
    {
        $modelClass = 'App\\Models\\' . ucfirst($modal);

        if (!class_exists($modelClass)) {
            return response()->json(['message' => 'Invalid modal provided'], 400);
        }

        // 'users' is expected JSON string containing array of users
        $usersData = $request->input('users');
        if (!$usersData) {
            return response()->json(['message' => 'No users data provided'], 422);
        }

        $dataList = json_decode($usersData, true);

        if (!is_array($dataList) || empty($dataList)) {
            return response()->json(['message' => 'Invalid or empty users data'], 422);
        }

        try {
            foreach ($dataList as $index =>  $data) {
                // If you want to handle files per user, get them from $request->file() here



                // Check for uploaded file in current record
                $fileKey = "file_{$index}_image";
                if ($request->hasFile($fileKey)) {
                    $file = $request->file($fileKey);
                    $data['image'] = $this->handleFileUpload($file, strtolower($modal));
                }


                $model = new $modelClass();
                $model->fill($data);
                $model->save();
            }

            return response()->json(['message' => 'Users uploaded successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Upload failed', 'error' => $e->getMessage()], 500);
        }
    }


    public function getBranches(Request $request)
    {
        $user = auth()->user();

        $selectedBranchId = $request->branch_id;
        // Fetch users based on role
        $branches = Branch::query();
        if ($user->role_id != 1) {
            $branches->where('id', $user->branch_id);
        }

        $branches = $branches->get();
        return response()->json(['status' => true, 'data' => $branches, 'message' => 'Branches Fetched Successfully'], 200);
    }
    public function getRoles(Request $request)
    {
        // Fetch users based on role
        $roles = Role::where('id', '!=', 1)->get();



        return response()->json(['status' => true, 'data' => $roles, 'message' => 'Roles Fetched Successfully'], 200);
    }
    public function getColumns(Request $request)
    {

        $roleId = $request->role_id;
        $adminId = $request->admin_id;
        $branchId = $request->branch_id;
        // Fetch users based on role
        $branches = RoleInputAssignment::where('admin_id', $adminId)
            ->where('branch_id', $branchId)
            ->where('role_id', $roleId)
            ->get();



        return response()->json(['status' => true, 'data' => $branches, 'message' => 'Branches Fetched Successfully'], 200);
    }
    public function getStates()
    {
        $states = [];

        return response()->json($states);
    }

    // public function getSidebarByUser($userId)
    // {
    //     try {
    //         // Check if user exists
    //         $user = User::find($userId);
    //         if (!$user) {
    //             return response()->json(['error' => 'User not found.'], 404);
    //         }

    //         // If user has role_id = 1, fetch all menus
    //         if ($user->role_id == 1) {
    //             $menus = Menu::whereNull('parent_id') // Fetch only main menus
    //                 ->with('submenus') // Load all submenus
    //                 ->get();
    //         } else {
    //             // Fetch menus assigned to the user
    //             $menus = Menu::whereHas('userPermissions', function ($query) use ($userId) {
    //                     $query->where('user_id', $userId);
    //                 })
    //                 ->whereNull('parent_id') // Fetch only main menus
    //                 ->with(['submenus' => function ($query) use ($userId) {
    //                     // Fetch only submenus where the user has permission
    //                     $query->whereHas('userPermissions', function ($subQuery) use ($userId) {
    //                         $subQuery->where('user_id', $userId);
    //                     });
    //                 }])
    //                 ->get();
    //         }

    //         if ($menus->isEmpty()) {
    //             return response()->json(['error' => 'No menus found for this user.'], 404);
    //         }

    //         // Format response with route URLs
    //         $menuList = $menus->map(function ($menu) {
    //             return [
    //                 'id' => $menu->id,
    //                 'name' => $menu->name,
    //                 'icon' => $menu->icon ?? 'bi-folder', // Default icon
    //                 'route' => $menu->route ?: '#', // Use 'route' field
    //                 'role_id' => $menu->role_id ?? '',
    //                 'submenus' => $menu->submenus->map(function ($submenu) {
    //                     return [
    //                         'id' => $submenu->id,
    //                         'name' => $submenu->name,
    //                         'icon' => $submenu->icon ?? 'bi-dot', // Default submenu icon
    //                         'route' => $submenu->route ?: '#', // Use 'route' field for submenu
    //                         'role_id' => $submenu->role_id ?? '',
    //                     ];
    //                 }),
    //             ];
    //         });

    //         return response()->json($menuList);
    //     } catch (Exception $e) {
    //         Log::error('Sidebar Fetch Error: ' . $e->getMessage());
    //         return response()->json(['error' => 'Failed to fetch sidebar menus.', 'message' => $e->getMessage()], 500);
    //     }
    // }


    public function menusForSetPermission(Request $request)
    {
        $roleId = $request->role_id ?? '';
        $userId = $request->user_id ?? '';

        $permissions = collect(); // default empty collection

        if (!empty($userId)) {
            // Try fetching user-specific permissions
            $permissions = UserPermission::where('user_id', $userId)->pluck('permission');

            // If user-specific permissions are empty, fallback to role-based
            if ($permissions->isEmpty() && !empty($roleId)) {
                $permissions = RolePermission::where('role_id', $roleId)->pluck('permission');
            }
        } elseif (!empty($roleId)) {
            // No user ID, fallback to role-based permissions
            $permissions = RolePermission::where('role_id', $roleId)->pluck('permission');
        }

        // Fetch all sidebar menus
        $getAllMenus = Helper::allSidebarMenus();

        if (!$getAllMenus) {
            return response()->json([
                'status' => false,
                'message' => 'Menu not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $getAllMenus,
            'permissions' => $permissions,
        ]);
    }

    public function getSidebarByUser($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }

        $sidebar = Helper::getSidebar($user);

        return response()->json([
            'status' => true,
            'data' => $sidebar
        ]);
    }

    // public function getFormFields()
    // {
    //     try {
    //         $adminId = 1;
    //         $roleId =5;

    //         // Fetch allowed form fields for given admin_id and role_id
    //         $allowedFields = AllowedFormField::with('formField')
    //             ->where('admin_id', $adminId)
    //             ->where('role_id', $roleId)
    //             ->get()
    //             ->pluck('formField') // extract form field details only
    //             ->filter() // remove any nulls (if foreign key was broken)
    //             ->values(); // reset keys

    //         return response()->json([
    //             'status' => true,
    //             'data' => $allowedFields,
    //             'message' => 'Form fields fetched successfully',
    //         ], 200);

    //     } catch (Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Error fetching form fields',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    // Helper function to parse JSON safely
    private function parseJson($value)
    {
        if (!$value) return null;

        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    public function createFeesGroup(Request $request)
    {
        $data = $request->all();

        // ✅ Create role using only role-specific data
        $fees_group = FeesGroup::create($data);



        return response()->json([
            'status' => true,
            'message' => 'Fees Group created successfully',
            'data' => $fees_group
        ], 201);
    }

    // Update an existing branch
    public function updateFeesGroup(Request $request, $id)
    {
        $fees_group = FeesGroup::find($id);

        if (!$fees_group) {
            return response()->json([
                'status' => false,
                'message' => 'Fees Group not found'
            ], 404);
        }



        $fees_group->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Fees Group updated successfully',
            'data' => $fees_group
        ], 200);
    }
    public function deleteFeesGroup($id)
    {
        $fees_group = FeesGroup::find($id);

        if (!$fees_group) {
            return response()->json([
                'status' => false,
                'message' => 'Fees Group not found'
            ], 404);
        }



        $fees_group->delete();

        return response()->json([
            'status' => true,
            'message' => 'Fees Group Deleted successfully',
            'data' => $fees_group
        ], 200);
    }

    public function getFeesGroup(Request $request)
    {
        // Fetch users based on role
        $fees_group = FeesGroup::all();



        return response()->json(['status' => true, 'data' => $fees_group, 'message' => 'Fees Group Successfully'], 200);
    }

   public function saveFcmToken(Request $request, $id)
    {
            try {


            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $user->update([
                'fcm_token' => $request->fcm_token
            ]);

            return response()->json([
                'status' => true,
                'message' => 'FCM Token saved successfully',
                'data' => $user
            ], 200);
        } catch (\Throwable $e) {
            Log::error('FCM Token Save Error', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createFeesType(Request $request){
        $data = $request->all();

        // ✅ Create role using only role-specific data
        $fees_Type = FeesType::create($data);



        return response()->json([
            'status' => true,
            'message' => 'Fees Type created successfully',
            'data' => $fees_Type
        ], 201);
    }

     public function updateFeesType(Request $request, $id)
    {
        $fees_Type = FeesType::find($id);

        if (!$fees_Type) {
            return response()->json([
                'status' => false,
                'message' => 'Fees Type not found'
            ], 404);
        }



        $fees_Type->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Fees Type updated successfully',
            'data' => $fees_Type
        ], 200);
    }
   

    public function getFeesType(Request $request)
    {
        // Fetch users based on role
       $fees_Type = DB::table('fees_types')
        ->select('fees_types.id', 'fees_types.name', 'fees_groups.name as fees_groups_name','fees_types.fees_group_id')
        ->leftJoin('fees_groups', 'fees_types.fees_group_id', '=', 'fees_groups.id')
        ->get();




        return response()->json(['status' => true, 'data' => $fees_Type, 'message' => 'Fees Type Successfully'], 200);
    }

    public function deleteFeesType($id){
        $fees_Type = FeesType::find($id);

        if (!$fees_Type) {
            return response()->json([
                'status' => false,
                'message' => 'Fees Type not found'
            ], 404);
        }

        $fees_Type->delete();

        return response()->json([
            'status' => true,
            'message' => 'Fees Type Deleted successfully',
            'data' => $fees_Type
        ], 200);
    }

public function getFcmToken()
{
    try {
        $fcmTokens = User::whereNotNull('fcm_token')->pluck('fcm_token');
        return response()->json([
            'status' => true,
            'tokens' => $fcmTokens
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

      public function createFeesMaster(Request $request){
        $data = $request->input('fees'); // only the "fees" array
        foreach ($data as $entry) {
            $classId = $entry['class_id'];

            foreach ($entry['fee_types'] as $type) {
                FeesMaster::create([
                    'class_type_id'      => $classId,
                    'fees_group_id' => $type['fees_group_id'],
                    'fees_type_id'  => $type['id'],
                    'amount'        => $type['amount'],
                    'installment_due_date'  => $type['due_date'],
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Fees Master created successfully',
            'data' => $data
        ], 201);
    }

    public function getFeesMaster(Request $request)
    {
        $rawData = DB::table('fees_masters')
            ->select(
                'fees_masters.id',
                'fees_masters.class_type_id',
                'fees_groups.id as fees_group_id',
                'fees_groups.name as fees_group_name',
                'fees_types.id as fees_type_id',
                'fees_types.name as fees_type_name',
                'fees_masters.amount',
                'fees_masters.installment_due_date',
                'fees_masters.created_at'
            )
            ->leftJoin('fees_groups', 'fees_masters.fees_group_id', '=', 'fees_groups.id')
            ->leftJoin('fees_types', 'fees_masters.fees_type_id', '=', 'fees_types.id')
            ->orderBy('fees_masters.class_type_id')
            ->get();

        // Group by class_id
        $grouped = [];

        foreach ($rawData as $row) {
            $classId = $row->class_type_id;

            if (!isset($grouped[$classId])) {
                $grouped[$classId] = [
                    'id' => $row->id,
                    'class_id' => $classId,
                    'groups' => [],
                    'fee_types' => [],
                    'total_amount' => 0,
                    'created_at' => $row->created_at,
                ];
            }

            // Add fee group if not already added
            if (!in_array($row->fees_group_name, array_column($grouped[$classId]['groups'], 'name'))) {
                $grouped[$classId]['groups'][] = [
                    'id' => $row->fees_group_id,
                    'name' => $row->fees_group_name,
                ];
            }

            // Add fee type
            $grouped[$classId]['fee_types'][] = [
                'group_name' => $row->fees_group_name,
                'type_name' => $row->fees_type_name,
                'amount' => $row->amount,
                'due_date' => $row->installment_due_date,
            ];

            $grouped[$classId]['total_amount'] += $row->amount;
        }

        $final = array_values($grouped);

        return response()->json([
            'status' => true,
            'data' => $final,
            'message' => 'Fees Master fetched successfully',
        ]);
    }

     public function deleteFeesMaster($id){
        $fees_Master = FeesMaster::find($id);

        if (!$fees_Master) {
            return response()->json([
                'status' => false,
                'message' => 'Fees Master not found'
            ], 404);
        }

        $fees_Master->delete();

        return response()->json([
            'status' => true,
            'message' => 'Fees Master Deleted successfully',
            'data' => $fees_Master
        ], 200);
    }

    public function createClass(Request $request)
    {
        $data = $request->all();

        // ✅ Create role using only role-specific data
        $class = Classes::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Class created successfully',
            'data' => $class
        ], 201);
    }

    // Update an existing branch
    public function updateClass(Request $request, $id)
    {
        $class = Classes::find($id);

        if (!$class) {
            return response()->json([
                'status' => false,
                'message' => 'Class not found'
            ], 404);
        }

        $class->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Class updated successfully',
            'data' => $class
        ], 200);
    }
    public function deleteClass($id)
    {
        $class = Classes::find($id);

        if (!$class) {
            return response()->json([
                'status' => false,
                'message' => 'Class not found'
            ], 404);
        }

        $class->delete();

        return response()->json([
            'status' => true,
            'message' => 'Class Deleted successfully',
            'data' => $class
        ], 200);
    }

    public function getClass(Request $request)
    {
        $class = Classes::all();
        return response()->json(['status' => true, 'data' => $class, 'message' => 'Class Successfully'], 200);
    }
}
