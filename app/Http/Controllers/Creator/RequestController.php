<?php

namespace App\Http\Controllers\Creator;

use App\Helpers\ResponseDataHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\{DaysOffRequest, UserID};
use App\Models\{Booking, CreatorMediaFiles, DayOff, Earning, Package, Review, Service, User,};


class RequestController extends Controller
{
    public function off_date_and_time(DaysOffRequest $request)
    {

        $user = User::find($request->user_id);
        if (!$user) {
            return ResponseDataHelper::jsonDataResponse(true, 'User not found.', (object)[], 200);
        }

        $userId = $request['user_id'];
        $startTime = $request['start_time'];
        $endTime = $request['end_time'];
        $dates = $request['dates'];

        // Prepare data for bulk insert
        $data = collect($dates)->map(function ($date) use ($userId, $startTime, $endTime) {
            return [
                'user_id' => $userId,
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        // Check for existing records to prevent duplicate entries
        $existingDates = DayOff::where('user_id', $userId)
            ->whereIn('date', $dates)
            ->pluck('date')
            ->toArray();

        if (!empty($existingDates)) {
            return ResponseDataHelper::jsonDataResponse(false, 'Some dates already exist.', (object)[], 200);
        }

        // Perform the bulk insert
        DayOff::insert($data);
        $days_of = DayOff::where('user_id', $request->user_id)->get();
        return ResponseDataHelper::jsonDataResponse(true, 'Days off added successfully.', $days_of, 200);
    }


    public function creator_off_date_and_time(UserID $request)
    {

        $user = User::find($request->user_id);
        if (!$user) {
            return ResponseDataHelper::jsonDataResponse(true, 'User not found.', (object)[], 200);
        }
        $month = $request->month;
        $days_of = DayOff::where('user_id', $request->user_id)->whereMonth('date', $month)  // Filter by month
            ->get();
        if (count($days_of) > 0) {
            return ResponseDataHelper::jsonDataResponse(true, 'Updated Calender', $days_of, 200);
        } else {
            return ResponseDataHelper::jsonDataResponse(false, 'No days off added', [], 200);
        }
    }

    public function home(UserID $request)
    {

        $user = User::find($request->user_id);
        if (!$user) {
            return ResponseDataHelper::jsonDataResponse(true, 'User not found.', (object)[], 200);
        }

        $new_request = Booking::with('service_detail')->where([
            'creator_id' => $request->user_id,
            'status' => 0,
        ])->get();

        $average_rating = round(Review::where('creator_id', $request->user_id)->avg('rating'), 2);
        $reviews = Review::with('review_by')->where('creator_id', $request->user_id)->get()
            ->map(function ($review) {
                $review->since = Carbon::parse($review->created_at)->diffForHumans();
                return $review;
            });
        $rating = [
            'average_rating' => $average_rating,
            'reviews' => $reviews,
        ];

        $earnings = Earning::where('user_id', $request->user_id)->whereMonth('date', $request->month)->get();

        $data = [
            'new_requets' => $new_request,
            'reviews' => $rating,
            'earnings' => $earnings
        ];

        return ResponseDataHelper::jsonDataResponse(true, 'Home Data.', $data, 200);
    }

    public function update_request(Request $request)
    {
        $booking = Booking::find($request->booking_id);
        if (!$booking) {
            return ResponseDataHelper::jsonDataResponse(true, 'Booking not found.', (object)[], 200);
        }
        $booking->status = $request->status;
        $booking->save();
        $booking_detail = Booking::with('user_detail.creator_media', 'creator_detail.creator_media')->find($booking->id);
        $booking_detail['user_detail']['service_detail'] = $booking_detail->user_detail->service_id
            ? Service::find($booking_detail->user_detail->service_id)
            : (object)[];
        $booking_detail['user_detail']['specilization_detail'] = $booking_detail->user_detail->specilization_id
            ? Service::find($booking_detail->user_detail->specilization_id)
            : (object)[];
        $booking_detail['user_detail']['package_detail'] =  $booking_detail->user_detail->package_id
            ? Package::find($booking_detail->user_detail->package_id)
            : (object)[];


        $booking_detail['creator_detail']['service_detail'] = $booking_detail->creator_detail->service_id
            ? Service::find($booking_detail->creator_detail->service_id)
            : (object)[];
        $booking_detail['creator_detail']['specilization_detail'] = $booking_detail->creator_detail->specilization_id
            ? Service::find($booking_detail->creator_detail->specilization_id)
            : (object)[];
        $booking_detail['creator_detail']['package_detail'] =  $booking_detail->creator_detail->package_id
            ? Package::find($booking_detail->creator_detail->package_id)
            : (object)[];

        return ResponseDataHelper::jsonDataResponse(true, 'status updated successfully', $booking_detail, 200);
    }

    public function requests(UserID $request)
    {

        $user = User::find($request->user_id);
        if (!$user) {
            return ResponseDataHelper::jsonDataResponse(true, 'User not found.', (object)[], 200);
        }
        $month = $request->month;
        $days_of = DayOff::where('user_id', $request->user_id)->whereMonth('date', $month)  // Filter by month
            ->get();

        $active_bookings = Booking::with('user_detail.creator_media', 'creator_detail.creator_media')
            ->where('creator_id', $request->user_id)->where('status', '!=', 3)->get();

        foreach ($active_bookings as $booking_detail) {
            $booking_detail['user_detail']['service_detail'] = $booking_detail->user_detail->service_id
                ? Service::find($booking_detail->user_detail->service_id)
                : (object)[];
            $booking_detail['user_detail']['specilization_detail'] = $booking_detail->user_detail->specilization_id
                ? Service::find($booking_detail->user_detail->specilization_id)
                : (object)[];
            $booking_detail['user_detail']['package_detail'] =  $booking_detail->user_detail->package_id
                ? Package::find($booking_detail->user_detail->package_id)
                : (object)[];


            $booking_detail['creator_detail']['service_detail'] = $booking_detail->creator_detail->service_id
                ? Service::find($booking_detail->creator_detail->service_id)
                : (object)[];
            $booking_detail['creator_detail']['specilization_detail'] = $booking_detail->creator_detail->specilization_id
                ? Service::find($booking_detail->creator_detail->specilization_id)
                : (object)[];
            $booking_detail['creator_detail']['package_detail'] =  $booking_detail->creator_detail->package_id
                ? Package::find($booking_detail->creator_detail->package_id)
                : (object)[];
        }


        $previous_bookings = Booking::with('user_detail.creator_media', 'creator_detail.creator_media')
            ->where('creator_id', $request->user_id)->where('status', '=', 3)->get();

        foreach ($previous_bookings as $booking_detail) {
            $booking_detail['user_detail']['service_detail'] = $booking_detail->user_detail->service_id
                ? Service::find($booking_detail->user_detail->service_id)
                : (object)[];
            $booking_detail['user_detail']['specilization_detail'] = $booking_detail->user_detail->specilization_id
                ? Service::find($booking_detail->user_detail->specilization_id)
                : (object)[];
            $booking_detail['user_detail']['package_detail'] =  $booking_detail->user_detail->package_id
                ? Package::find($booking_detail->user_detail->package_id)
                : (object)[];


            $booking_detail['creator_detail']['service_detail'] = $booking_detail->creator_detail->service_id
                ? Service::find($booking_detail->creator_detail->service_id)
                : (object)[];
            $booking_detail['creator_detail']['specilization_detail'] = $booking_detail->creator_detail->specilization_id
                ? Service::find($booking_detail->creator_detail->specilization_id)
                : (object)[];
            $booking_detail['creator_detail']['package_detail'] =  $booking_detail->creator_detail->package_id
                ? Package::find($booking_detail->creator_detail->package_id)
                : (object)[];
        }

        $data = [
            'days_of' => $days_of,
            'active' => $active_bookings,
            'previous' => $previous_bookings,
        ];

        return ResponseDataHelper::jsonDataResponse(true, 'Requests Data', $data, 200);
    }
}
