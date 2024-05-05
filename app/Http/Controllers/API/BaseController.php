<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;

class BaseController extends Controller
{
    public function sendResponse($message)
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
    public function sendResponseWithToken($data, $message, $token)
    {
        $response = [
            'success' => true,
            'message' => $message,
            'token' => $token,
            'data'  => $data
        ];

        return response()->json($response, 200);
    }

       // Method to upload profile picture
       protected function uploadProfilePicture(Request $request)
       {
           if ($request->hasFile('profile_picture')) {
               $profile_picture = $request->file('profile_picture');
               $fileName = time() . '_' . $profile_picture->getClientOriginalName();
               $profile_picture->storeAs('public/user', $fileName);
               $profile_picture->move(public_path('images/user'), $fileName);
           } else {
               $fileName = 'default.png';
           }
   
           return $fileName;
       }
   
       // Method to normalize phone number
       protected function normalizePhoneNumber($phoneNumber)
       {
           $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
           if (substr($phoneNumber, 0, 1) === '0' || substr($phoneNumber, 0, 1) === '8') {
               $phoneNumber = '62' . substr($phoneNumber, 1);
           } else {
               $phoneNumber = null;
           }
           return $phoneNumber;
       }
}
