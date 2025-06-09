<?php
namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Otp;
use URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Exception;
use Carbon\Carbon;
use Helper;


use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="API Documentation",
 *     version="1.0.0",
 *     description="This is the API documentation for our application"
 * )
 *
 * @OA\PathItem(path="/api")
 */
class AuthController extends Controller
{


    
    public function register(Request $request)
    {
        try {
            // Validate the request data
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed', // Use 'confirmed' for matching
            ]);
    
            // Create the user if validation passes
            $user = User::create(attributes: [
                'name' => $request->name,
                'username' => $request->email,
                'email' => $request->email,
                'confirm_password' => $request->password,
                'password' => Hash::make($request->password),
                'status' => 1,
                'role_id' => 3
            ]);
    
            // Create the token
            $token = $user->createToken('auth_token ')->plainTextToken;
    
            // Return the token as a response
            return response()->json(['access_token' => $token, 'token_type' => 'Bearer'], 201);
    
        } catch (ValidationException $e) {
            // Return validation error messages
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors(),
            ], 422);
            
        } catch (Exception $e) {
            // Handle other exceptions
            return response()->json(['error' => 'Registration failed', 'message' => $e->getMessage()], 500);
        }
    }


    public function login(Request $request)
    {

        try {
            // Attempt login
            if (!Auth::attempt($request->only('username', 'password'))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid login credentials',
                    'data' => null
                ], 401); // Unauthorized
            }
    
                $getArray = [
                    'users.id', 'users.status', 'users.name', 'users.mobile', 'users.username',
                    'users.dob', 'users.gender', 'users.address', 'users.image', 'users.pincode','users.auth_provider','users.role_id',
                    'users.branch_id','users.admin_id',
                ];
        
                $user = User:: where('users.id', Auth::id())
                    ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
                    ->select(array_merge($getArray, ['roles.name as role_name'])) // Rename role column for clarity
                    ->first();



            // Check if the user's account is inactive
            if ($user->status !== '1') {
                Auth::logout();
                return response()->json([
                    'status' => false,
                    'message' => 'Account is inactive or suspended',
                    'data' => null
                ], 403); // Forbidden
            }
    
            // Restrict login for specific role IDs (Example: 1 and 4)
            $restrictedRoles = []; 
            if (in_array($user->role_id, $restrictedRoles)) {
                Auth::logout();
                return response()->json([
                    'status' => false,
                    'message' => 'Access denied for this role',
                    'data' => null
                ], 403); // Forbidden
            }
    
            // Create token
            $token = $user->createToken('auth_token')->plainTextToken;
    
            // Return success response
            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                    'access_token' => $token,
                    'user' => $user, // Return the user data
            ], 200);
    
        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
    
        } catch (\Throwable $e) {
            // Handle unexpected errors
            return response()->json([
                'status' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    


    public function OAUTH2(Request $request)
{
    try {
        // Validate incoming request
        $request->validate([
            'credentials' => 'required', 
        ]);

        $data  = json_decode($request->credentials);

        $getArray = [
            'users.id', 'users.status', 'users.name', 'users.mobile', 'users.username',
            'users.dob', 'users.gender', 'users.address', 'users.image', 'users.pincode','users.auth_provider'
        ];

        $user = User::where('users.username', $data->email)
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->select(array_merge($getArray, ['roles.name as role_name'])) // Rename role column for clarity
            ->first();

        // If the user doesn't exist, create a new user
        if (!$user) {
            $user = User::create([
                'email' => $data->email ?? '',
                'username' => $data->email ?? '',
                'name' => ($data->name ?? ''),
                'image' => $data->picture ?? '',
                'auth_provider' => 'GOOGLE_OAUTH2',
                'role_id' => $request->role_id,
                'status' => '1',  // Assuming '1' means active
            ]);
        } else {
            if (!empty($data->picture)) {
                $imageUrl = $data->picture;
                $imageContents = file_get_contents($imageUrl);

                // Generate a unique filename
                $photo = time() . uniqid() . '.jpg';
                $destinationPath = env('IMAGE_UPLOAD_PATH_API') . 'user/';

                // Ensure the directory exists
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                // Delete old image if exists
                if (!empty($user->image)) {
                    $oldImagePath = $destinationPath . $user->image;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath); // Delete old image
                    }
                }

                // Save the new image
                file_put_contents($destinationPath . $photo, $imageContents);

                // Save the new image path to the database
                $user->update([
                    'image' => $photo,
                    'auth_provider' => 'GOOGLE_OAUTH2',
                ]);
            }
        }

        if (isset($user->status) && $user->status !== '1') {
            return response()->json([
                'status' => false,
                'message' => 'Account is inactive or suspended',
                'data' => null
            ], 403); // Forbidden
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful.',
                'access_token' => $token,
                'user' => $user, // Return the user DATA
        ], 200);

    } catch (ValidationException $e) {
        return response()->json([
            'status' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422); // Unprocessable Entity

    } catch (Exception $e) {
        return response()->json([
            'status' => false, 
            'message' => 'Login failed', 
            'error' => $e->getMessage()
        ], 500); // Internal Server Error
    }
}


    /**
 * @OA\Post(
 *     path="/api/loginWithOtp",
 *     tags={"Authentication"},
 *     summary="Login with OTP",
 *     description="Authenticates the user by generating and sending an OTP to their registered whatsapp mobile number.",
 *     operationId="loginWithOtp",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"username"},
 *             @OA\Property(property="username", type="string", example="9166697302", description="The mobile number of the user.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="OTP sent successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Otp sent to registered mobile no."),
 *             @OA\Property(property="mobile", type="string", example="9166697302", description="The mobile number of the user.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation failed",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Validation failed"),
 *             @OA\Property(property="errors", type="object",
 *                 additionalProperties=@OA\Property(type="array", @OA\Items(type="string"))
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Account inactive or suspended",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Account is inactive or suspended"),
 *             @OA\Property(property="data", type="null")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Login failed"),
 *             @OA\Property(property="error", type="string", example="An unexpected error occurred.")
 *         )
 *     )
 * )
 */

    public function loginWithOtp(Request $request)
    {
        try {
            // Validate incoming request
            $request->validate([
                'username' => 'required|min:10|max:10', 
            ]);
    
            // Retrieve the user based on the username (mobile number)
            $user = User::where('mobile', $request->username)->first();
    
            // If the user doesn't exist, create a new user
            if (!$user) {
                $user = User::create([
                    'username' => $request->username,
                    'mobile' => $request->username,
                    'role_id' => $request->role_id,
                    // You can set the default status if you want
                    'status' => '1',  // Assuming '1' means active
                ]);
            }
    
            // Check if the user's account is active (if you have a status column)
            // If 'status' is not null or not '1', return an error
            if (isset($user->status) && $user->status !== '1') {
                return response()->json([
                    'status' => false,
                    'message' => 'Account is inactive or suspended',
                    'data' => null
                ], 403); // Forbidden
            }
    
            // Generate a random OTP
            $otp = rand(1000, 9999);
    
            // Save the OTP to the database (assuming you have an Otp model)
            $saveOtp = new Otp;
            $saveOtp->username = $request->username;
            $saveOtp->otp = $otp;
            $saveOtp->save();
    
            // Send OTP via WhatsApp or any other medium (helper function call)
            $helper = new Helper();
            $respo = $helper->send('whatsapp', $request->username, $otp, $filepath = null);
    
            // Return response with the status
            return response()->json([
                'status' => true,
                'message' => 'Otp sent to registered mobile no.',
                'mobile' => $user->mobile,
            ], 200);
    
        } catch (ValidationException $e) {
            // Return validation error messages
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422); // Unprocessable Entity
    
        } catch (Exception $e) {
            // Handle other exceptions
            return response()->json([
                'status' => false, 
                'message' => 'Login failed', 
                'error' => $e->getMessage()
            ], 500); // Internal Server Error
        }
    }
    

   /**
 * @OA\Post(
 *     path="/api/matchOtp",
 *     summary="Match OTP and authenticate user",
 *     tags={"Authentication"},
 *     description="Validates the OTP and authenticates the user by returning an access token.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"otp", "username"},
 *             @OA\Property(property="otp", type="string", description="The OTP sent to the user", example="1234"),
 *             @OA\Property(property="username", type="string", description="The username or mobile number", example="9660045163")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Login successful.",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", description="Status of the response", example=true),
 *             @OA\Property(property="message", type="string", description="Response message", example="Login successful."),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="access_token", type="string", description="Access token for the user", example="269|yoqf1jKhyi4ypyehD7Vgtb3kBsQv23Lvxj7dXztO9ceb92fb"),
 *                 @OA\Property(property="token_type", type="string", description="Type of the token", example="Bearer"),
 *                 @OA\Property(
 *                     property="data",
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", description="User ID", example=9),
 *                     @OA\Property(property="name", type="string", description="User name", example="Himanshu Saini"),
 *                     @OA\Property(property="mobile", type="string", description="User mobile number", example="9660045163"),
 *                     @OA\Property(property="email", type="string", description="User email address", example="janedoe@example.com"),
 *                     @OA\Property(property="dob", type="string", nullable=true, description="Date of birth", example=null),
 *                     @OA\Property(property="gender", type="string", nullable=true, description="Gender", example=null),
 *                     @OA\Property(property="address", type="string", description="User address", example="sikar"),
 *                     @OA\Property(property="image", type="string", nullable=true, description="User profile image URL", example=null),
 *                     @OA\Property(property="role_name", type="string", description="Role name", example="User")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Invalid OTP or OTP expired",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", description="Status of the response", example=false),
 *             @OA\Property(property="message", type="string", description="Error message", example="Invalid OTP or OTP expired"),
 *             @OA\Property(property="data", type="null", description="No data returned", example=null)
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation failed",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", description="Status of the response", example=false),
 *             @OA\Property(property="message", type="string", description="Error message", example="Validation failed"),
 *             @OA\Property(property="errors", type="object", description="Validation errors")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", description="Status of the response", example=false),
 *             @OA\Property(property="message", type="string", description="Error message", example="Login failed"),
 *             @OA\Property(property="error", type="string", description="Detailed error message")
 *         )
 *     )
 * )
 */


    public function matchOtp(Request $request)
    {
        try {
            // Validate incoming request
            $request->validate([
                'otp' => 'required|min:4|max:4',
                'username' => 'required',
            ]);
    
            // Retrieve the user based on the username
            $otpRecord = Otp::where('otp', $request->otp)->where('username', $request->username)->first();
           
            if ($otpRecord && (Carbon::now()->diffInMinutes($otpRecord->created_at) <= 2)) {
                // Get the difference in minutes from created_at to now
               
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid OTP or OTP expired',
                    'data' => null
                ], 403); // Forbidden
            }
            
            // $user = User::where('mobile', $otpRecord->username)->first();

            $getArray = ['users.id','users.status','users.name','users.mobile','users.email','users.dob','users.gender','users.address','users.image'];

            $user = User::where('users.mobile', $otpRecord->username)
                ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
                ->select(array_merge($getArray, ['roles.name as role_name']))
                ->first();
        
            // Create token
            $token = $user->createToken('auth_token')->plainTextToken;
    
            // Retrieve roles based on role_id
            $roles = Role::find($user->role_id); // Assuming you have a roles relationship
            $roleName = $roles ? $roles->name : null; // Ensure role exists
            
            // Return response
            return response()->json([
                'status' => true,
                'message' => 'Login successful.',
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'data' => $user, // Return the user DATA
                ]
            ], 200);
    
        } catch (ValidationException $e) {
            // Return validation error messages
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422); // Unprocessable Entity
    
        } catch (Exception $e) {
            // Handle other exceptions
            return response()->json(['status' => false, 'message' => 'Login failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }
    
    

 /**
 * @OA\Get(
 *     path="/api/userLoggedIn",
 *     summary="Get logged-in user data",
 *     tags={"Authentication"},
 *     description="Fetches the details of the currently authenticated user. If the user's status is not '1', access is denied.",
 *     security={{ "bearerAuth":{} }},
 *     @OA\Response(
 *         response=200,
 *         description="Logged-in user data retrieved successfully.",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true, description="Indicates if the request was successful"),
 *             @OA\Property(property="message", type="string", example="Logged User Data", description="Response message"),
 *             @OA\Property(property="user", type="object", description="User details",
 *                 @OA\Property(property="id", type="integer", example=9, description="User ID"),
 *                 @OA\Property(property="status", type="string", example="1", description="User status"),
 *                 @OA\Property(property="name", type="string", example="Himanshu Saini", description="User name"),
 *                 @OA\Property(property="mobile", type="string", example="9660045163", description="User mobile number"),
 *                 @OA\Property(property="email", type="string", example="janedoe@example.com", description="User email address"),
 *                 @OA\Property(property="dob", type="string", nullable=true, example="1990-01-01", description="Date of birth"),
 *                 @OA\Property(property="gender", type="string", nullable=true, example="Male", description="Gender"),
 *                 @OA\Property(property="address", type="string", example="Sikar", description="User address"),
 *                 @OA\Property(property="image", type="string", nullable=true, example="http://example.com/images/profile.jpg", description="User profile image URL"),
 *                 @OA\Property(property="pincode", type="string", example="332001", description="User pincode"),
 *                 @OA\Property(property="role_name", type="string", example="User", description="Role name")
 *             ),
 *             @OA\Property(property="show_path", type="string", example="http://example.com/images/", description="Path for showing images")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Access denied. User account is inactive or suspended.",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false, description="Indicates if the request failed"),
 *             @OA\Property(property="message", type="string", example="Account is inactive or suspended", description="Error message"),
 *             @OA\Property(property="data", type="null", example=null, description="No data returned")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized. User is not authenticated.",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false, description="Indicates if the request failed"),
 *             @OA\Property(property="message", type="string", example="Unauthenticated", description="Error message")
 *         )
 *     )
 * )
 */
public function userLoggedIn(Request $request)
{
    $loggedUser = auth()->user();

    // Check if user is inactive
    if ($loggedUser->status !== '1') {
        return response()->json([
            'status' => false,
            'message' => 'Account is inactive or suspended',
            'data' => null
        ], 403); // Forbidden
    }

    $getArray = [
        'users.id', 'users.status', 'users.name', 'users.mobile', 'users.email',
        'users.dob', 'users.gender', 'users.address', 'users.image', 'users.pincode'
    ];

    $user = User::where('users.id', $loggedUser->id)
        ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
        ->select(array_merge($getArray, ['roles.name as role_name'])) // Rename role column for clarity
        ->first();

    return response()->json([
        'status' => true,
        'message' => 'Logged User Data',
        'user' => $user,
        'show_path' => env('IMAGE_SHOW_PATH')
    ], 200);
}
    
    	public function appDataApi(Request $request)
	{
	   $setting = Setting::first();
	    try{
	     $data = array(
                'logo' => env('IMAGE_SHOW_PATH').'branch/'.$setting->left_logo,
                'name' => $setting->name
            );
	    
	 
	      return response()->json(['data' => $data]);
        } catch (Exception $e) {
            return $this->sendError('Validation Error.', 'Error');
        }
	}

}
