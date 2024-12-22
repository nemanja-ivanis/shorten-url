<?php

namespace App\Http\Controllers;

use App\Models\Url;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application home and return the token from session.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home')->with('apiToken', session('apiToken'));
    }

    /**
     * Redirect to the original URL and increment visits counter.
     *
     * @param $shortenedUrl
     * @return \Illuminate\Foundation\Application|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function redirect($shortenedUrl)
    {
        try {
            // Find the URL associated with the shortened URL
            $url = Url::where('short_url', $shortenedUrl)->first();

            // Check if the URL exists
            if (!$url) {

                return response()->json([
                    'success' => false,
                    'message' => 'The shortened URL does not exist.',
                ], 404); // Not Found
            }

            // Increment the visit counter
            $url->increment('visits_counter');

            // Redirect to the original URL
            return redirect($url->real_url);
        } catch (\Exception $e) {
            // Handle unexpected errors
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the redirection.',
                'error' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Show the form to add a new URL.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function addUrl()
    {
        return view('add-url')->with('apiToken', session('apiToken'));
    }

    /**
     * Show the form to update a URL.
     *
     * @param $id
     * @return \Illuminate\Contracts\Support\Renderable|\Illuminate\Http\RedirectResponse
     */
    public function updateUrl($id)
    {
        $url = Url::find($id);

        // redirect to home if the url does not exist
        if(!$url) {
            return redirect()->route('home');
        }

        return view('update-url')->with('id', $id)->with('apiToken', session('apiToken'));
    }
}
