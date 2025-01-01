<?php
  
namespace App\Http\Controllers;
  
use App\Http\Controllers\Controller;
use App\Models\User;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Classes\ApiResponseClass;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
  
class AuthController extends Controller
{
 
    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register() {
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
  
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
  
        $user = new User;
        $user->name = request()->name;
        $user->email = request()->email;
        $user->password = bcrypt(request()->password);
        $user->position = 1;
        $user->save();
  
        return response()->json([
            'status'    =>  true,
            'message'   =>  "Register berhasil",
            'data'      =>  $user
        ], 201);
    }
  
  
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);
        
        if ($credentials["email"] == null || $credentials["password"] == null || $credentials["email"] == "" || $credentials["password"] == "") {
            // return ApiResponseClass::sendResponse('Tolong lengkapi form email dan password', 401);
            return response()->json([
                'status'    =>  false,
                'message'   =>  "Tolong lengkapi form email dan password",
                'data'      =>  $user
            ], 401);
        }
        
        $findEmail = DB::table('users')->where('email', $credentials["email"])->first();
        if ($findEmail == null) {
            // return ApiResponseClass::sendResponse('User email tidak ditemukan', 401);
            return response()->json([
                'status'    =>  false,
                'message'   =>  "User email tidak ditemukan",
            ], 401);
        } else {
            if (!Hash::check($credentials["password"], $findEmail->password)) {
                // return ApiResponseClass::sendResponse('User password anda salah', 401);
                return response()->json([
                    'status'    =>  false,
                    'message'   =>  "User password anda salah",
                ], 401);
            }
            // if ($findEmail->email_verified == 0 || $findEmail->email_verified == null) {
            //     return ApiResponseClass::sendResponse('USER_EMAIL_NOT_VERIFIED', 401);
            // }
        }

        if (!$token = auth()->attempt($credentials)) {
            return ApiResponseClass::sendResponse('Unauthorized', 404);
        }

        return $this->respondWithToken($token, auth()->user());
    }

    public function refreshLogin()
    {
        $credentials = request(['enc']);
        $decryptedObject = unserialize(Crypt::decrypt($credentials['enc']));
        $user = User::where('email', $decryptedObject["email"])->first();

        // Generate the JWT token for the user
        $token = JWTAuth::fromUser($user);

        return $this->respondWithToken($token, $user);
    }
  
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        // get userid from token
        // $userid = $request->user()->id;
        // $user = DB::table('users')->where('id', $userid)->first();
        // return response()->json($user);

        return response()->json(auth()->user());
    }
  
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
  
        return response()->json(['message' => 'Successfully logged out']);
    }
  
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }


    public function delete()
    {
        $findUser = DB::table('users')->wherel('id', request()->id)->first();
        if ($findUser != null) {
            DB::table('users')
                ->where('id', request()->id)
                ->update([
                    'delete'                => 1,
                    'updated_at'            => Carbon::now()->toDateTimeString(),
                ]);
            return response()->json([
                'status'    =>  true,
                'message'   =>  "User berhasil dinonaktifkan"
            ], 201);
        } else {
            return response()->json([
                'status'    =>  false,
                'error'     =>  'ERROR_NOT_FOUND',
                'message'   =>  'User tidak ditemukan',
            ], 422);
        }
    }
  
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $user)
    {
        $encryptedObject = Crypt::encrypt(serialize($user));

        return response()->json([
            'status'   => true,
            'data'   => [
                'enc'           =>  $encryptedObject,
                'user'          =>  $user,
                'access_token'  =>  $token,
                'token_type'    =>  'bearer',
                'expires_in'    =>  auth()->factory()->getTTL() * 0.5
            ]
        ]);
    }
}