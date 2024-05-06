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
            'data' => $data
        ];

        return response()->json($response, 200);
    }

    // ! Method to upload profile picture
    protected function uploadProfilePicture(Request $request)
    {
        if ($request->hasFile('profile_picture')) {
            $profile_picture = $request->file('profile_picture');
            $fileName = time() . '_' . $profile_picture->getClientOriginalName();
            $profile_picture->move(public_path('images/user'), $fileName);
        } else {
            $fileName = 'default.png';
        }

        return $fileName;
    }

    // ! Method to normalize phone number
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

    // ! Method to Generate Profile Picture by Name
    protected function generateProfilePicture($name)
    {
        // Replace spaces with '+' to format the name correctly for the API
        $name = str_replace(' ', '+', $name);

        // Generate Random Background Color
        $random_color = substr(md5(rand()), 0, 6);

        // Generate URL with parameters for random background color and bold text
        $url = 'https://ui-avatars.com/api/?name=' . $name . '&background=' . urlencode($random_color) . '&bold=true&size=512';

        // Fetch the profile picture from the API
        $profile_picture = file_get_contents($url);

        // Generate a unique file name
        $fileName = time() . '_' . $name . '.png';

        // Save the profile picture to the public/images/user directory
        file_put_contents(public_path('images/user/' . $fileName), $profile_picture);

        // Return the file name
        return $fileName;
    }
}
