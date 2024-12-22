<?php

namespace App\Http\Controllers;

use App\Models\Url;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ApiController extends Controller
{
    /**
     * Retrieve all URLs associated with the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {

            // Pagination: Get the current page and items per page (limit)
            $limit = intval($request->input('limit', 10));  // Default to 10 items per page
            $page = intval($request->input('page', 1));     // Default to page 1

            // Calculate the offset based on the page
            $offset = ($page - 1) * $limit;

            // Retrieve the URLs for the authenticated user with pagination logic
            $urls = $request->user()->urls()->skip($offset)->take($limit)->get();

            // Get the total count of URLs for pagination metadata
            $total = $request->user()->urls()->count();

            // Calculate the total number of pages
            $lastPage = ceil($total / $limit);

            // Check if the user has any URLs
            if ($urls->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No URLs found for the user.',
                    'data' => [],
                ], 200); // OK
            }

            // Return the URLs with a success message
            return response()->json([
                'success' => true,
                'message' => 'URLs retrieved successfully.',
                'data' => $urls,
                'pagination' => [
                    'total' => $total,
                    'current_page' => $page,
                    'limit' => $limit,
                    'last_page' => $lastPage,
                ]
            ], 200); // OK
        } catch (\Exception $e) {
            // Handle unexpected errors
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving URLs.',
                'error' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Create a new shortened URL record.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request) {
        try {
            // Validate the input with proper validation rules
            $validated = $request->validate([
                'real_url' => ['required', 'url'],
            ]);

            // Generate a unique short URL and handle potential uniqueness conflicts
            do {
                $shortenedUrl = Str::random(8);
            } while (Url::where('short_url', $shortenedUrl)->exists());

            // Create the URL record associated with the authenticated user
            $url = $request->user()->urls()->create([
                'real_url' => $validated['real_url'],
                'short_url' => $shortenedUrl,
            ]);

            // Hide the user_id field from the response
            $url->makeHidden(['user_id']);

            // Return success response with 201 Created status
            return response()->json([
                'success' => true,
                'message' => 'URL successfully shortened.',
                'data' => $url,
            ], 201);

        } catch (ValidationException $e) {
            // Return validation error response
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            // Handle any other exceptions and return a generic error response
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a URL record.
     *
     * @param Request $request
     * @param Url $url
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Url $url)
    {
        // Authorization: Ensure the user owns the URL
        if ($url->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not authorized to update this URL.',
            ], 403); // Forbidden
        }

        try {
            // Validation: Validate the incoming data
            $validated = $request->validate([
                'real_url' => 'required|url',
            ]);

            // Update the URL
            $url->update(['real_url' => $validated['real_url']]);

            // Hide the user_id field from the response
            $url->makeHidden(['user_id']);

            // Return updated resource with success message
            return response()->json([
                'success' => true,
                'message' => 'URL updated successfully.',
                'data' => $url,
            ], 200); // OK
        } catch (ValidationException $e) {
            // Handle validation exceptions explicitly
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422); // Unprocessable Entity
        } catch (\Exception $e) {
            // Catch unexpected errors and return a server error response
            return response()->json([
                'message' => 'An error occurred while updating the URL.',
                'error' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Delete a URL record.
     *
     * @param Request $request
     * @param Url $url
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Url $url) {

        try {
            // Authorization: Ensure the user owns the URL
            if ($url->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to delete this URL.',
                ], 403); // Forbidden
            }

            // Attempt to delete the URL
            $url->delete();

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'URL deleted successfully.',
            ], 200); // OK
        } catch (\Exception $e) {
            // Handle unexpected errors
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the URL.',
                'error' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Retrieve a single URL record.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            // Attempt to find the URL by id
            $url = Url::select('real_url', 'short_url')->where('id', $id)->first();

            // If the URL does not exist, return a 404 Not Found response
            if (!$url) {
                return response()->json([
                    'success' => false,
                    'message' => 'URL not found.',
                ], 404);
            }

            // Return the found URL with a 200 OK response
            return response()->json([
                'success' => true,
                'message' => 'URL retrieved successfully.',
                'data' => $url,
            ], 200);

        } catch (\Exception $e) {
            // Catch any other exceptions and return a 500 Internal Server Error
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
