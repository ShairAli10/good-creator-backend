<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ResponseDataHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\{LoginRequest, RegisterRequest, SocialRequest, UpdateDeviceID, UserID};
use App\Models\{User,};
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * The function registers a user by validating the input, creating a new user record,
     * and returning the user data.
     */
    public function sign_up(RegisterRequest $request)
    {
        $check_email =  User::where('email', $request->email)->first();
        if ($check_email) {
            return ResponseDataHelper::jsonDataResponse(false, 'This email is already connected to an account in our database', (object) [], 200);
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'user_type' => $request->user_type,
            'firebase_id' => $request->firebase_id ?: '',
            'device_id' => $request->device_id ?: '',
            'service_type' => $request->service_type ?: '',

        ];
        $user = User::create($data);

        $user_data = User::with('creator_media')->where('id', $user->id)->first();
        $user_data['first_login'] = $request->user_type !== 'user';
        return ResponseDataHelper::jsonDataResponse(true, 'Successfully registered!', $user_data, 200);
    }

    /**
     * The login function attempts to authenticate a user with the provided email and password,
     * returns a JSON response with the user data
     * or an unauthorized response if unsuccessful.
     */
    public function sign_in(LoginRequest $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user()->load(relations: 'creator_media');
            $user['first_login'] = $user->user_type !== 'user' && $user->bio === '';

            return ResponseDataHelper::jsonDataResponse(true, 'Successfully logged In!', $user, 200);
        } else {
            return ResponseDataHelper::jsonDataResponse(false, 'Invalid Credentials', (object)[], 401);
        }
    }
    /**  social login where you can signIn/SignUp using social app this api run on base of social token that
     * provide google or apple.
     */
    public function social_login_signUp(SocialRequest $request)
    {
        $social_key = $request->social_key;
        $social_token = $request->social_token;
        $email = $request->email;
        $name = $request->name;
        $user_type = $request->user_type;
        $device_id = $request->device_id;
        $firebase_id = $request->firebase_id;

        if ($social_key == 'google') {
            $check_socail_token = User::with('creator_media')->where('g_code', '=', $social_token)->first();
            if ($check_socail_token) {
                $check_socail_token['first_login'] = $user_type !== 'user' && $check_socail_token->bio === '';
                return ResponseDataHelper::jsonDataResponse(true, 'User Details', $check_socail_token, 200);
            } else {
                $signup = new User([
                    'name' => $name ?? " ",
                    'email' => $email ?? " ",
                    'device_id' => $device_id ?? " ",
                    'firebase_id' => $firebase_id ?? " ",
                    'g_code' => $social_token,
                    'user_type' => $user_type,
                ]);
                $signup->save(); 
                $data = User::with('creator_media')->find($signup->id);
                $data['first_login'] = $request->user_type !== 'user';

                return ResponseDataHelper::jsonDataResponse(true, 'User Signup Successfully', $data, 200);
            }
        }
        if ($social_key == 'apple') {
            $check_socail_token = User::with('creator_media')->where('a_code', '=', $social_token)->first();
            if ($check_socail_token) {
                $check_socail_token['first_login'] = $user_type !== 'user' && $check_socail_token->bio === '';
                return ResponseDataHelper::jsonDataResponse(true, 'User Details', $check_socail_token, 200);
            } else {
                $signup = new User([
                    'name' => $name ?? " ",
                    'email' => $email ?? " ",
                    'device_id' => $device_id ?? " ",
                    'firebase_id' => $firebase_id ?? " ",
                    'a_code' => $social_token,
                    'user_type' => $user_type,
                ]);
                $signup->save(); 
                $data = User::with('creator_media')->find($signup->id);
                $data['first_login'] = $request->user_type !== 'user';

                return ResponseDataHelper::jsonDataResponse(true, 'User Signup Successfully', $data, 200);
            }
        }
        if ($social_key == 'facebook') {
            $check_socail_token = User::with('creator_media')->where('f_code', '=', $social_token)->first();
            if ($check_socail_token) {
                $check_socail_token['first_login'] = $user_type !== 'user' && $check_socail_token->bio === '';
                return ResponseDataHelper::jsonDataResponse(true, 'User Details', $check_socail_token, 200);
            } else {
                $signup = new User([
                    'name' => $name ?? " ",
                    'email' => $email ?? " ",
                    'device_id' => $device_id ?? " ",
                    'firebase_id' => $firebase_id ?? " ",
                    'f_code' => $social_token,
                    'user_type' => $user_type,
                ]);
                $signup->save(); 
                $data = User::with('creator_media')->find($signup->id);
                $data['first_login'] = $request->user_type !== 'user';
                return ResponseDataHelper::jsonDataResponse(true, 'User Signup Successfully', $data, 200);
            }
        }
    }

    // for update device ID
    public function update_device_id(UpdateDeviceID $request)
    {
        $user = User::find($request->user_id);
        if (!$user) {
            return ResponseHelper::jsonResponse(false, 'User Not Found');
        }

        $user->device_id = $request->device_id;
        $user->save();
        return ResponseHelper::jsonResponse(true, 'Updated Successfully');
    }

    // for update firebase ID
    public function update_firebase_id(UserID $request)
    {
        $user = User::find($request->user_id);
        if (!$user) {
            return ResponseHelper::jsonResponse(false, 'User Not Found');
        }

        $user->firebase_id = $request->firebase_id;
        $user->save();
        return ResponseHelper::jsonResponse(true, 'Updated Successfully');
    }

    // for user logout
    public function logout_profile(UserID $request)
    {
        $user = User::find($request->user_id);
        if ($user) {
            $user->device_id = '';
            $user->save();
            return ResponseHelper::jsonResponse(true, 'logged out successfully');
        } else {
            return ResponseHelper::jsonResponse(false, 'User Not Found');
        }
    }
}
