<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function toggleFollow(Request $request, $userId)
    {
        $userToFollow = User::findOrFail($userId);
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        // Không cho phép tự follow chính mình
        if ($currentUser->id === $userToFollow->id) {
            return response()->json(['message' => 'Bạn không thể tự theo dõi chính mình'], 400);
        }

        // Nếu đã follow rồi thì hủy (Unfollow), nếu chưa thì Follow
        if ($currentUser->isFollowing($userId)) {
            $currentUser->followings()->detach($userId);
            $isFollowing = false;
            $message = 'Đã bỏ theo dõi';
        } else {
            $currentUser->followings()->attach($userId);
            $isFollowing = true;
            $message = 'Đã theo dõi thành công';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_following' => $isFollowing,
            'followers_count' => $userToFollow->followers()->count() // Trả về số lượng để update UI
        ]);
    }

    public function getFollowers($userId)
    {
        $user = User::findOrFail($userId);
        $followers = $user->followers()->select('users.id', 'name', 'email', 'avatar')->get();
        return response()->json(['success' => true, 'data' => $followers]);
    }

    public function getFollowings($userId)
    {
        $user = User::findOrFail($userId);
        $followings = $user->followings()->select('users.id', 'name', 'email', 'avatar')->get();
        return response()->json(['success' => true, 'data' => $followings]);
    }
}
