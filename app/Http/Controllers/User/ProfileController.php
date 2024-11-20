<?php

namespace App\Http\Controllers\User;

use App\Helpers\ResponseDataHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use App\Http\Requests\{LoginRequest, RegisterRequest, SocialRequest, UpdateDeviceID, UserID};
use App\Models\{CreatorMediaFiles, Package, Service, User,};

class ProfileController extends Controller
{
    // get user profile
    public function my_profile(UserID $request)
    {
        $user = User::with('creator_media', 'reviews.review_by')->find($request->user_id);
        if (!$user) {
            return ResponseDataHelper::jsonDataResponse(true, 'User not found.', (object)[], 200);
        }
        $user->reviews->each(function ($review) {
            $review->since = Carbon::parse($review->created_at)->diffForHumans();
        });
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
}
