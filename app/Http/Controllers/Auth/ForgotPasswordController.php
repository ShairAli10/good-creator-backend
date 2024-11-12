<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ResponseHelper;
use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyOTPRequest;
use App\Mail\GenerateMailForOTP;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    public function generate_otp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
        ]);

        $validationError = ValidationHelper::handleValidationErrors($validator);
        if ($validationError !== null) {
            return $validationError;
        }
        $otp_code = mt_rand(1000, 9999);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return ResponseHelper::jsonResponse(false, 'User With this email address does not exist in our database');
        }

        $user->update(['email_code' => $otp_code]);

        $main_data = ['message' => $otp_code];
        Mail::to($request->email)->send(new GenerateMailForOTP($main_data));

        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully',
            'OTP Code' => $otp_code,
        ], 200);
    }
    public function verify_otp(VerifyOTPRequest $request)
    {
        $email = $request->email;
        $otp = $request->otp;
        $user = User::where([['email', $email], ['email_code', $otp]])->first();
        if ($user) {
            User::where('email', $email)->update(['email_code' => 0]);
            $user_data = $user->refresh()->load('creator_media');
            $data = [
                'status' => true,
                'message' => 'OTP Verified',
                'data' => $user_data,
            ];
            return response()->json($data, 200);
        }
        return ResponseHelper::jsonResponse(false, 'Invalid OTP. Please enter a valid OTP.');

    }
    public function reset_password(ResetPasswordRequest $request)
    {

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return ResponseHelper::jsonResponse(false, 'User Not Found');
        }
        $user->password = Hash::make($request->password);
        $user->save();
        return ResponseHelper::jsonResponse(true, 'Password update successfully');
    }
}
