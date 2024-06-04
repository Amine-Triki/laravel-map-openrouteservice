<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Distance;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class DistanceController extends Controller
{
    // calc distance
    public function calculateDistance(Request $request)
    {
        /// check for valid data
        $validator = Validator::make($request->all(), [
            'current_x' => 'required|numeric',
            'current_y' => 'required|numeric',
            'target_x' => 'required|numeric',
            'target_y' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $currentX = $request->input('current_x');
        $currentY = $request->input('current_y');
        $targetX = $request->input('target_x');
        $targetY = $request->input('target_y');

        $url = "https://api.openrouteservice.org/v2/directions/driving-car/geojson";
        $apiKey = '5b3ce3597851110001cf6248c6a3767f2ac3407ca1407676443fe24d';
        $body = [
            'coordinates' => [
                [$currentY, $currentX],
                [$targetY, $targetX]
            ],
            'alternative_routes' => [
                'target_count' => 3
            ]];



        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json, application/geo+json, application/gpx+xml, img/png; charset=utf-8',
                'Authorization' => $apiKey,
                'Content-Type' => 'application/json; charset=utf-8'
            ])->post($url, $body);


            if ($response->failed()) {
                throw new \Exception('Failed to connect to OpenRouteService');
            }

            $data = $response->json();

            if (!isset($data['features']) || count($data['features']) === 0) {
                return response()->json(['error' => 'The path cannot be calculated for these points.'], 400);
            }

            $allDistances = [];
            $allRoutes = [];

            foreach ($data['features'] as $feature) {
                $distance = $feature['properties']['segments'][0]['distance'] / 1000; //Distance in kilometers
                $geometry = $feature['geometry']; // path (geometric shape)

                $allDistances[] = $distance; //Store the distance in a matrix
                $allRoutes[] = $geometry; // Storing the geometric shape in a matrix
            }

            $distancesJson = json_encode($allDistances);
            $routesJson = json_encode($allRoutes);

            // Record the distance in the database
            $distanceRecord = Distance::create([
                'current_x' => $currentX,
                'current_y' => $currentY,
                'target_x' => $targetX,
                'target_y' => $targetY,
                'distance' => $distancesJson,
                'geometry' => $routesJson,
            ]);

            return response()->json([ 'status' => 'successfully', $distanceRecord ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while calculating the distance: ' . $e->getMessage()], 500);
        }
    }





    // Get Distances
    function GetDistances()
    {
        $userDistance = Distance::All();
        return response()->json($userDistance, 200);
    }

    // get Distance
    function getDistance($id)
    {
        $userDistance = Distance::find($id);
        if (!$userDistance) {
            return response()->json(['errors' => 'check your id , Distance not found'], 404);
        }
        return response()->json($userDistance, 200);
    }







// update Distance
public function updateDistance(Request $request, $id)
{
    $userDistance = Distance::find($id);
    if (!$userDistance) {
        return response()->json(['errors' => 'Distance not found.'], 404);
    }

    $validator = Validator::make($request->all(), [
        'current_x' => 'required|numeric',
        'current_y' => 'required|numeric',
        'target_x' => 'required|numeric',
        'target_y' => 'required|numeric',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $currentX = $request->input('current_x');
    $currentY = $request->input('current_y');
    $targetX = $request->input('target_x');
    $targetY = $request->input('target_y');



    $url = "https://api.openrouteservice.org/v2/directions/driving-car/geojson";
    $apiKey = '5b3ce3597851110001cf6248c6a3767f2ac3407ca1407676443fe24d';
    $body = [
        'coordinates' => [
            [$currentY, $currentX],
            [$targetY, $targetX]
        ],
        'alternative_routes' => [
            'target_count' => 3
        ]];



    try {
        $response = Http::withHeaders([
            'Accept' => 'application/json, application/geo+json, application/gpx+xml, img/png; charset=utf-8',
            'Authorization' => $apiKey,
            'Content-Type' => 'application/json; charset=utf-8'
        ])->post($url, $body);


        if ($response->failed()) {
            throw new \Exception('Failed to connect to OpenRouteService');
        }

        $data = $response->json();

        if (!isset($data['features']) || count($data['features']) === 0) {
            return response()->json(['error' => 'The path cannot be calculated for these points.'], 400);
        }

        $allDistances = [];
        $allRoutes = [];

        foreach ($data['features'] as $feature) {
            $distance = $feature['properties']['segments'][0]['distance'] / 1000; //Distance in kilometers
            $geometry = $feature['geometry']; //  path (geometric shape)

            $allDistances[] = $distance; //Store the distance in a matrix
            $allRoutes[] = $geometry; // Storing the geometric shape in a matrix
        }

        $distancesJson = json_encode($allDistances);
        $routesJson = json_encode($allRoutes);


    // Update data in the database
    $userDistance->update([
        'current_x' => $currentX,
        'current_y' => $currentY,
        'target_x' => $targetX,
        'target_y' => $targetY,
        'distance' => $distancesJson,
        'geometry' => $routesJson,
    ]);

    return response()->json([
        'status' => 'Data updated successfully.',
        'distance' => $userDistance
    ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while calculating the distance: ' . $e->getMessage()], 500);
        }
    }










    // delete Distance

    function deleteDistance($id)
    {
        if ($userDistance = Distance::find($id)) {
            $userDistance->delete();
            return response()->json(['status' => 'Distance deleted successfully.'], 200);
        } else {
            return response()->json(['errors' => 'Distance not found.'], 404);
        }
    }





    // View the distance on the page
    public function showDistance($id)
    {
        $distance = Distance::find($id);

        if (!$distance) {
            return "Please enter a valid ID";
        }

        return view('show_distance', compact('distance'));
    }
}
