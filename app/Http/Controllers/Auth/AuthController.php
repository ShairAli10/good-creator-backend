<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ResponseDataHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Http\Requests\{CreatorSocialEdit, LoginRequest, RegisterRequest, SocialRequest, UpdateDeviceID, UserID};
use App\Models\{CreatorMediaFiles, Package, Service, User,};
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
        ];
        $user = User::create($data);

        $user_data = User::with('creator_media')->where('id', $user->id)->first();
        $user_data['service_detail'] = $user->service_id
            ? Service::find($user->service_id)
            : (object)[];
        $user_data['specilization_detail'] = $user->specilization_id
            ? Service::find($user->specilization_id)
            : (object)[];
        $user_data['package_detail'] = $user->package_id
            ? Package::find($user->package_id)
            : (object)[];
        if ($request->user_type == 'user') {
            $user_data['first_login'] = $user_data->service_id == 0;
        } else {
            $user_data['first_login'] = $user_data->bio == "";
        }

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
            $user = Auth::user()->load('creator_media');
            $user['service_detail'] = $user->service_id
                ? Service::find($user->service_id)
                : (object)[];
            $user['specilization_detail'] = $user->specilization_id
                ? Service::find($user->specilization_id)
                : (object)[];
            $user['package_detail'] = $user->package_id
                ? Package::find($user->package_id)
                : (object)[];
            if ($user->user_type == 'user') {
                $user['first_login'] = $user->service_id == 0;
            } else {
                $user['first_login'] = $user->bio == "";
            }
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
                if ($check_socail_token->user_type == 'user') {
                    $check_socail_token['first_login'] = $check_socail_token->service_id == 0;
                } else {
                    $check_socail_token['first_login'] = $check_socail_token->bio == "";
                }
                $check_socail_token['service_detail'] = $check_socail_token->service_id
                    ? Service::find($check_socail_token->service_id)
                    : (object)[];
                $check_socail_token['specilization_detail'] = $check_socail_token->specilization_id
                    ? Service::find($check_socail_token->specilization_id)
                    : (object)[];
                $check_socail_token['package_detail'] = $check_socail_token->package_id
                    ? Package::find($check_socail_token->package_id)
                    : (object)[];
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
                if ($data->user_type == 'user') {
                    $data['first_login'] = $data->service_id == 0;
                } else {
                    $data['first_login'] = $data->bio == "";
                }
                $data['service_detail'] = $data->service_id
                    ? Service::find($data->service_id)
                    : (object)[];
                $data['specilization_detail'] = $data->specilization_id
                    ? Service::find($data->specilization_id)
                    : (object)[];
                $data['package_detail'] = $data->package_id
                    ? Package::find($data->package_id)
                    : (object)[];

                return ResponseDataHelper::jsonDataResponse(true, 'User Signup Successfully', $data, 200);
            }
        }
        if ($social_key == 'apple') {
            $check_socail_token = User::with('creator_media')->where('a_code', '=', $social_token)->first();
            if ($check_socail_token) {
                if ($check_socail_token->user_type == 'user') {
                    $check_socail_token['first_login'] = $check_socail_token->service_id == 0;
                } else {
                    $check_socail_token['first_login'] = $check_socail_token->bio == "";
                }
                $check_socail_token['service_detail'] = $check_socail_token->service_id
                    ? Service::find($check_socail_token->service_id)
                    : (object)[];
                $check_socail_token['specilization_detail'] = $check_socail_token->specilization_id
                    ? Service::find($check_socail_token->specilization_id)
                    : (object)[];
                $check_socail_token['package_detail'] = $check_socail_token->package_id
                    ? Package::find($check_socail_token->package_id)
                    : (object)[];
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
                if ($data->user_type == 'user') {
                    $data['first_login'] = $data->service_id == 0;
                } else {
                    $data['first_login'] = $data->bio == "";
                }
                $data['service_detail'] = $data->service_id
                    ? Service::find($data->service_id)
                    : (object)[];
                $data['specilization_detail'] = $data->specilization_id
                    ? Service::find($data->specilization_id)
                    : (object)[];
                $data['package_detail'] = $data->package_id
                    ? Package::find($data->package_id)
                    : (object)[];

                return ResponseDataHelper::jsonDataResponse(true, 'User Signup Successfully', $data, 200);
            }
        }
        if ($social_key == 'facebook') {
            $check_socail_token = User::with('creator_media')->where('f_code', '=', $social_token)->first();
            if ($check_socail_token) {
                if ($check_socail_token->user_type == 'user') {
                    $check_socail_token['first_login'] = $check_socail_token->service_id == 0;
                } else {
                    $check_socail_token['first_login'] = $check_socail_token->bio == "";
                }
                $check_socail_token['service_detail'] = $check_socail_token->service_id
                    ? Service::find($check_socail_token->service_id)
                    : (object)[];
                $check_socail_token['specilization_detail'] = $check_socail_token->specilization_id
                    ? Service::find($check_socail_token->specilization_id)
                    : (object)[];
                $check_socail_token['package_detail'] = $check_socail_token->package_id
                    ? Package::find($check_socail_token->package_id)
                    : (object)[];
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
                if ($data->user_type == 'user') {
                    $data['first_login'] = $data->service_id == 0;
                } else {
                    $data['first_login'] = $data->bio == "";
                }
                $data['service_detail'] = $data->service_id
                    ? Service::find($data->service_id)
                    : (object)[];
                $data['specilization_detail'] = $data->specilization_id
                    ? Service::find($data->specilization_id)
                    : (object)[];
                $data['package_detail'] = $data->package_id
                    ? Package::find($data->package_id)
                    : (object)[];
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

    public function update_location(UserID $request)
    {
        $user = User::find($request->user_id);
        if (!$user) {
            return ResponseHelper::jsonResponse(false, 'User Not Found');
        }

        $user->lat = $request->lat;
        $user->longi = $request->longi;
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

    public function get_all_services(Request $request)
    {
        $data = Service::all();
        $message = $data->isEmpty() ? 'No Services found' : 'All Services/ Specializations';

        return ResponseDataHelper::jsonDataResponse(!$data->isEmpty(), $message, $data, 200);
    }

    public function get_all_packages(Request $request)
    {
        $data = Package::all();
        $message = $data->isEmpty() ? 'No Packages found' : 'All Packages';

        return ResponseDataHelper::jsonDataResponse(!$data->isEmpty(), $message, $data, 200);
    }

    // update user onboarding information
    public function user_social_edit(UserID $request)
    {
        $user = User::with('creator_media')->find($request->user_id);
        if (!$user) {
            return ResponseDataHelper::jsonDataResponse(false, 'User Not Found', (object)[], 200);
        }
        User::where('id', $request->user_id)
            ->update([
                'service_id' => $request->service_id,
                'package_id' => $request->package_id
            ]);
        $user['service_detail'] = $user->service_id
            ? Service::find($user->service_id)
            : (object)[];
        $user['specilization_detail'] = $user->specilization_id
            ? Service::find($user->specilization_id)
            : (object)[];
        $user['package_detail'] = $user->package_id
            ? Package::find($user->package_id)
            : (object)[];
        if ($user->user_type === 'user') {
            $user['first_login'] = $user->service_id == 0;
        } else {
            $user['first_login'] = $user->bio == "";
        }
        return ResponseDataHelper::jsonDataResponse(true, 'Updated successfully', $user, 200);
    }

    // update creator onboarding information
    public function creator_social_edit(CreatorSocialEdit $request)
    {
        $user = User::with('creator_media')->find($request->user_id);
        if (!$user) {
            return ResponseDataHelper::jsonDataResponse(false, 'User Not Found', (object)[], 200);
        }
        User::where('id', $request->user_id)
            ->update([
                'specilization_id' => $request->specilization_id,
                'bio' => $request->bio
            ]);
        foreach ($request->media as $file) {
            if ($file && $file->isValid()) {
                // Get the original filename and extension
                $filenameWithExt = $file->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                // Create a unique filename to store
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                // Store the file
                $path = $file->storeAs('creator_media', $fileNameToStore, 'public');
            } else {
                $fileNameToStore = '';
            }
            // Save the file path and user ID in the database
            $media = new CreatorMediaFiles;
            $media->user_id = $request->input('user_id');
            $media->media = 'creator_media/' . $fileNameToStore;
            $media->save();
        }
        $user = User::with('creator_media')->find($request->user_id);
        $user['service_detail'] = $user->service_id
            ? Service::find($user->service_id)
            : (object)[];
        $user['specilization_detail'] = $user->specilization_id
            ? Service::find($user->specilization_id)
            : (object)[];
        $user['package_detail'] = $user->package_id
            ? Package::find($user->package_id)
            : (object)[];
        if ($user->user_type === 'user') {
            $user['first_login'] = $user->service_id == 0;
        } else {
            $user['first_login'] = $user->bio == "";
        }
        return ResponseDataHelper::jsonDataResponse(true, 'Updated successfully', $user, 200);
    }
}
