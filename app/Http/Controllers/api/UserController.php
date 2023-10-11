<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Jobs\SendResetPasswordEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\DataTables;

use  App\Jobs\SendEmailVerificationJob;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\VerifyEmailRequest;

class UserController extends Controller
{

    public function register(UserRequest $request)
    {

          $verificationCode = random_int(1000, 9999);

    $user = User::create([
        'first_name' => $request->input('first_name'),
        'last_name' => $request->input('last_name'),
        'email' => $request->input('email'),
        'phone' => $request->input('phone'),
        'password' => Hash::make($request->input('password')),
        'verification_code' => $verificationCode,
    ]);

    dispatch(new SendEmailVerificationJob([
        'email' => $user->email,
        'verification_code' => $user->verification_code,
    ]));

    $token = $user->createToken('Api-Token')->plainTextToken;



    return DataTables::of([$user])
        ->addRowData('message', function ($user) {
            return 'Registration successful. Please check your email for the verification code.';
        })
        ->addRowData('verification_code', function ($user) use ($verificationCode) {
            return $verificationCode;
        })
        ->addRowData('token', function ($user) use ($token) {
            return $token;
        })
        ->removeColumn('password')
        ->make(true);


    }
    public function verifyEmail(VerifyEmailRequest $request)
    {


        $user = User::where('email', $request->input('email'))->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        if ($user->is_verified) {
            return response()->json([
                'message' => 'Email already verified.',
            ], 400);
        }

        if ($user->verification_code !== $request->input('verification_code')) {
            return response()->json([
                'message' => 'Invalid verification code.',
            ], 400);
        }

        $user->is_verified = true;
        $user->verification_code = null;
        $user->save();

return DataTables::of([$user])
        ->addColumn('message', 'Email verification successful.')
        ->make(true);


    }

    public function login(Request $request)
{
    $credentials = $request->only('email', 'phone' , 'password');

    if (isset($credentials['email'])) {
        $user = User::where('email', $credentials['email'])->first();
    } elseif (isset($credentials['phone'])) {
        $user = User::where('phone', $credentials['phone'])->first();
    } else {
        return response()->json(['error' => 'Email or phone is required.'], 400);
    }


    if (!$user->is_verified ) {
        return response()->json(['error' => 'Verify Your Email First'], 401);
    }
    if (!$user ) {
        return response()->json(['error' => 'Email, phone, or password is incorrect.'], 401);
    }

    if (!Hash::check($credentials['password'], $user->password)) {
        return response()->json(['error' => 'Email, phone, or password is incorrect.'], 401);
    }


    $token = $user->createToken('Api-Token')->plainTextToken;

    return response()->json([
        'user' => $user,
        'token' => $token,
    ]);

}


    public function update($id,UpdateUserRequest $request){
        $user =User::findOrFail($id);
        if(!$user){
            return response()->json([
                'error'=>'User Not Found'
            ]);
        }

        $user->update($request->all());





        return DataTables::of([$user])
        ->addColumn('message', 'User Updated Successfully')
        ->make(true);

    }

    public function updatePassword(Request $request){

        $user = User::where('email',$request->email)->first();

        if(!$user){
            return response()->json([
                'message' => 'User Not Found',

            ]);

        }
        $user->update([
            'password'=>Hash::make($request->password)
        ]);

        // return response()->json([
        //     'message' => 'Password Updated Successfully',

        // ], 201);


           return DataTables::of([$user])
        ->addColumn('message', 'Password Updated Successfully')
        ->make(true);


    }


    public function userInformation(){
        $user = Auth::user();

        return DataTables::of([$user])
        ->make(true);

    }



    public function forgotPassword(Request $request){

        $user = User::where('email',$request->email)->first();


        if(!$user) {
            return response()->json([
                'error' => 'Email not found'
            ]);
        }
        $token = $user->createToken('Api-Token')->plainTextToken;

        DB::table('password_reset_tokens')->insert([
            'email'=>$request->email,
            'token'=>$token,
        ]);

            dispatch(new SendResetPasswordEmail($user , $token));

            $data =[];
  return DataTables::of([$data])
            ->addColumn('message', 'Password reset email sent')
            ->make(true);


}

public function resetPassword(User $user, Request $request)
{
    $verificationCode = random_int(1000, 9999);


    $tokenData =     DB::table('password_reset_tokens')
        ->where('email',$request->email)
        ->where('token',$request->token);

    if(!$tokenData) {
        return response()->json([
            'error' => 'email or token is incorrect'
        ]);
    }
    $newPassword = Hash::make($request->password);
    User::where('email', $request->email)->update(['password' => $newPassword]);
    $user = User::where('email', $request->email)->first();
    $user->verification_code = $verificationCode;
    $user->save();

    // Dispatch email verification job
    dispatch(new SendEmailVerificationJob([
        'email' => $user->email,
        'verification_code' => $verificationCode,
    ]));


    DB::table('password_reset_tokens')
    ->where('email',$request->email)
    ->where('token',$request->token)->delete();



    return DataTables::of([$user])
    ->addColumn('message', 'Password Reset successful. Please check your email for the verification code ')
    ->make(true);






}

public function userProducts(Request $request, $id)
{
    $perPage = $request->input('per_page', 5);
    $page = $request->input('page');

    $user = User::findOrFail($id);
    $userProducts = $user->products()->paginate($perPage, ['*'], 'page', $page);

    return response()->json([
        'products' => $userProducts->items(),
        'current_page' => $userProducts->currentPage(),
        'total_pages' => $userProducts->lastPage(),
        'total_items' => $userProducts->total(),
        'length'=>$perPage
    ], 200);
}
public function logout(){


    $user= Auth::user();
    $user->tokens()->delete();
 
 
        $data = [];
       return DataTables::of([$data])
       ->addColumn('message', 'logout successfully ')
       ->make(true);
   
   
 
     }
 
}
