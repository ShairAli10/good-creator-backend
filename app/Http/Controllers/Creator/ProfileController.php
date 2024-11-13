<?php

namespace App\Http\Controllers\Creator;

use App\Helpers\ResponseDataHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\{LoginRequest, RegisterRequest, SocialRequest, UpdateDeviceID, UserID};
use App\Models\{CreatorMediaFiles, Package, Service, User,};

class ProfileController extends Controller
{
    // get user profile
    public function my_profile(UserID $request)
    {
        $user = User::with('creator_media')->find($request->user_id);
        if (!$user) {
            return ResponseDataHelper::jsonDataResponse(true, 'User not found.', (object)[], 200);
        }
        $user['service_detail'] = $user->service_id
            ? Service::find($user->service_id)
            : (object)[];
        $user['specilization_detail'] = $user->specilization_id
            ? Service::find($user->specilization_id)
            : (object)[];
        $user['package_detail'] = $user->package_id
            ? Package::find($user->package_id)
            : (object)[];

        return ResponseDataHelper::jsonDataResponse(true, 'User Fetched Successfully', $user, 200);
    }

    // update/add user profile image
    public function edit_profile_image(UserID $request)
    {

        $user = User::find($request->user_id);
        if (!$user) {
            return ResponseDataHelper::jsonDataResponse(true, 'User not found.', (object)[], 200);
        }

        // decode the base64 image
        $base64File = request('profile_pic');

        // store orignal image
        $fileData = base64_decode($base64File);

        $name = 'users_profile/' . Str::random(15) . '.png';

        Storage::disk('public')->put($name, $fileData);

        // update the user's profile_pic
        $user->profile_pic = $name;
        $user->save();
        return response()->json(
            [
                'message' => 'Profile picture updated',
                'status' => true,
                'data' => [
                    'profile_pic' => $name,
                ],
            ],
            200
        );
    }

    // update user name
    public function edit_name(UserID $request)
    {
        $user = User::find($request->user_id);
        if (!$user) {
            return ResponseDataHelper::jsonDataResponse(true, 'User not found.', (object)[], 200);
        }

        $user->name = $request->name;
        $user->save();
        return ResponseDataHelper::jsonDataResponse(true, 'updated successfully', (object)[], 200);
    }

    // update user name
    public function edit_bio(UserID $request)
    {
        $user = User::find($request->user_id);
        if (!$user) {
            return ResponseDataHelper::jsonDataResponse(true, 'User not found.', (object)[], 200);
        }

        $user->bio = $request->bio;
        $user->save();
        return ResponseDataHelper::jsonDataResponse(true, 'updated successfully', (object)[], 200);
    }

    public function edit_media(UserID $request)
    {
        $user = User::with('creator_media')->find($request->user_id);
        if (!$user) {
            return ResponseDataHelper::jsonDataResponse(false, 'User Not Found', (object)[], status_code: 200);
        }
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
        return ResponseDataHelper::jsonDataResponse(true, 'Updated successfully', (object)[], 200);
    }

    public function delete_media(Request $request)
    {
        $media = CreatorMediaFiles::find($request->media_id);
        if (!$media) {
            return ResponseDataHelper::jsonDataResponse(true, 'Media not found.', (object)[], 200);
        }
        $media->delete();
        return ResponseDataHelper::jsonDataResponse(true, 'deleted successfully', (object)[], 200);
    }
}
