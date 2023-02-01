<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Image;
use Illuminate\Support\Facades\Storage;

use App\Models\Tweet;

class TweetController extends Controller
{
    function index(Request $request)
    {
        try {
            $count = $request->limit ? $request->limit : 20;
            $paged = $request->paged ? $request->paged : 1;
            $skip = $count * ($paged - 1);

            $response = Tweet::latest()
                ->take($count)
                ->skip($skip)
                ->allTweetWithComment();

            return response()->json($response);
        } catch (\Exception $e) {
            Logger($e);
            abort(404);
        }
    }

    function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate(
                [
                    'comment_to' => 'nullable|numeric',
                    'body' => 'nullable|string',
                    'images' => 'nullable|array',
                ]
            );

            $data = $request->except('images');
            $user = $request->user();

            $tweet = $user->tweets()->create($data);

            if ($request->has('images')) {
                $path = 'tweets/images';

                foreach ($request->images as $request_image) {
                    $image = Image::make($request_image)->orientate()->encode('png');
                    $hash = md5($image->__toString());
                    $file_name = "{$hash}.png";
                    Storage::put($path . '/' . $file_name, $image->encode(), 'public');

                    $image->destroy();
                    $tweet->images()->create(['image' => $file_name]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Logger($e);
            abort(404);
        }
    }

    function show(Tweet $tweet)
    {
        $response = $tweet->getCommentStatus($tweet);
        $response->load('user', 'images');

        return response()->json($response);
    }

    function likeTweet(Tweet $tweet)
    {
        try {
            $user_id = auth()->id();

            $is_like = $tweet->likeUsers()->where('user_id', $user_id)->exists();

            $is_like
                ? $tweet->likeUsers()->detach($user_id)
                : $tweet->likeUsers()->attach($user_id);

            $response = Tweet::allTweetWithComment();

            return response()->json($response);
        } catch (\Exception $e) {
            Logger($e);
            abort(404);
        }
    }

    function retweet(Tweet $tweet)
    {
        try {
            $user_id = auth()->id();

            $is_like = $tweet->reTweetUsers()->where('user_id', $user_id)->exists();

            $is_like
                ? $tweet->reTweetUsers()->detach($user_id)
                : $tweet->reTweetUsers()->attach($user_id);

            $response = Tweet::allTweetWithComment();

            return response()->json($response);
        } catch (\Exception $e) {
            Logger($e);
            abort(404);
        }
    }
}
