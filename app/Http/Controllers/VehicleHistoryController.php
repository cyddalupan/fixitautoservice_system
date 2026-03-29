<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VehicleHistory;

class VehicleHistoryController extends Controller
{
    /**
     * Search for vehicle descriptions.
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $results = VehicleHistory::search($query, 10);
        
        return response()->json($results);
    }

    /**
     * Record vehicle description usage.
     */
    public function record(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
        ]);
        
        $description = trim($request->input('description'));
        
        if (empty($description)) {
            return response()->json(['success' => false, 'message' => 'Description is required']);
        }
        
        $history = VehicleHistory::findOrCreate($description);
        $history->incrementUse();
        
        return response()->json(['success' => true, 'message' => 'Vehicle description recorded']);
    }

    /**
     * Get popular vehicle descriptions.
     */
    public function popular()
    {
        $popular = VehicleHistory::getPopular(10);
        
        return response()->json($popular);
    }
}
