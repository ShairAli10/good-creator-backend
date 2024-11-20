<?php

namespace App\Http\Controllers\User;

use App\Helpers\ResponseDataHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\{BookingRequest, ReviewRequest, UserID};
use Illuminate\Support\Carbon;
use App\Models\{Booking, CreatorMediaFiles, Earning, Package, Review, Service, User,};

class BookingController extends Controller
{
    // get nearby users on for map pin points on the basis of location under 10 KM radius
    public function map(UserID $request)
    {
        $user = User::with('creator_media')->find($request->user_id);
        if (!$user) {
            return ResponseDataHelper::jsonDataResponse(true, 'User not found.', (object)[], 200);
        }
        $lat = $user->lat;
        $longi = $user->longi;
        $service = $user->service_id;
        $radius = 10;

        // Check for missing or invalid location data
        if (empty($lat) || empty($longi)) {
            return ResponseDataHelper::jsonDataResponse(
                true,
                'Please update your location',
                (object)[],
                200
            );
        }

        // Query to find creators within the radius
        $creators = User::with('creator_media')
            ->where('user_type', 'creator')
            ->where('specilization_id', $service)
            ->select('*')
            ->selectRaw(
                'CAST(ROUND((6371 * acos(cos(radians(?)) *
                            cos(radians(lat)) *
                            cos(radians(`longi`) - radians(?)) +
                            sin(radians(?)) *
                            sin(radians(lat)))), 4) AS CHAR) AS distance',
                [$lat, $longi, $lat]
            )
            ->havingRaw('distance < ?', [$radius]) // Use `havingRaw` for dynamic filtering
            ->orderBy('distance')
            ->get();
        foreach ($creators as $creator) {

            $average_rating = round(Review::where('creator_id', $creator->id)->avg('rating'), 2);
            $creator['average_rating'] = $average_rating;
            $reviews = Review::with('review_by')->where('creator_id', $creator->id)->get()
                ->map(function ($review) {
                    $review->since = Carbon::parse($review->created_at)->diffForHumans();
                    return $review;
                });

            $creator['reviews'] = $reviews;
            $creator['total_reviews'] = count($reviews);
            $creator['service_detail'] = $creator->service_id
                ? Service::find($creator->service_id)
                : (object)[];
            $creator['specilization_detail'] = $creator->specilization_id
                ? Service::find($creator->specilization_id)
                : (object)[];
            $creator['package_detail'] = $creator->package_id
                ? Package::find($creator->package_id)
                : (object)[];
        }

        $data = [
            "auto_match" => $user->auto_match,
            "nearby_creators" => $creators
        ];


        return ResponseDataHelper::jsonDataResponse(true, 'User Fetched Successfully', $data, 200);
    }


    public function new_booking(BookingRequest $request)
    {
        $user = User::find($request->user_id);
        if (!$user) {
            return ResponseDataHelper::jsonDataResponse(true, 'User not found.', (object)[], 200);
        }
        $creator = User::find($request->creator_id);
        if (!$creator) {
            return ResponseDataHelper::jsonDataResponse(true, 'Creator not found.', (object)[], 200);
        }

        $package = Package::find($request->package_id);
        $service = Service::find($request->service_id);
        // Check if the user has already booked at the same time on the given date
        $bookingExists = Booking::where('user_id', $request->user_id)
            ->where('creator_id', $request->creator_id)
            ->whereDate('date', Carbon::parse($request->date))
            ->whereTime('time', Carbon::parse($request->time))
            ->exists();

        if ($bookingExists) {
            return ResponseDataHelper::jsonDataResponse(false, 'You already have a booking at this time.', (object)[], 200);
        }

        $data = [
            'user_id' => $request->user_id,
            'creator_id' => $request->creator_id,
            'date' => $request->date,
            'time' => $request->time,
            'service_id' => $request->service_id,
            'package_id' => $request->package_id,
            'email' => $request->email,
            'lat' => $request->lat,
            'longi' => $request->longi,
            'location' => $request->location,
            'status' => 0,
            'payment_method' => $request->payment_method,
            'payment_id' => $request->payment_id,
        ];

        $booking = Booking::create($data);

        // make an entry in the earnings table

        $earnings_data = [
            'booking_id' => $booking->id,
            'user_id' => $request->creator_id,
            'amount' => $package->rate,
            'hours' => $package->hours,
            'date' => $request->date,
            'service_name' => $service->name
        ];

        $earnings = Earning::create($earnings_data);

        // booking details

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
        
        return ResponseDataHelper::jsonDataResponse(true, 'Booking created successfully', $booking_detail, 200);
    }

    public function booking_detail(Request $request)
    {
        $booking = Booking::find($request->booking_id);
        if (!$booking) {
            return ResponseDataHelper::jsonDataResponse(true, 'Booking not found.', (object)[], 200);
        }

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

        return ResponseDataHelper::jsonDataResponse(true, 'Booking created successfully', $booking_detail, 200);
    }

    public function traking(UserID $request)
    {
        $user = User::with('creator_media')->find($request->user_id);
        if (!$user) {
            return ResponseDataHelper::jsonDataResponse(true, 'User not found.', (object)[], 200);
        }
        
        $active_bookings = Booking::with('user_detail.creator_media', 'creator_detail.creator_media')
        ->where('user_id', $request->user_id)->where('status', '!=', 3)->get();

        foreach($active_bookings as $booking_detail) {
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
        ->where('user_id', $request->user_id)->where('status', '=', 3)->get();

        foreach($previous_bookings as $booking_detail) {
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
            'active' => $active_bookings,
            'previous' => $previous_bookings,
        ];

        return ResponseDataHelper::jsonDataResponse(true, 'data fetched successfully', $data, 200);
    }


    //  review posted by user to creator service.
    public function review(ReviewRequest $request)
    {
        $user = User::with('creator_media')->find($request->user_id);
        if (!$user) {
            return ResponseDataHelper::jsonDataResponse(true, 'User not found.', (object)[], 200);
        }
        $data = [
            'user_id' => $request->user_id,
            'creator_id' => $request->creator_id,
            'rating' => $request->rating,
            'comments' => $request->comments,
        ];
        $review = Review::create($data);

        return ResponseDataHelper::jsonDataResponse(true, 'Review submitted', (object)[], 200);
    }
}
