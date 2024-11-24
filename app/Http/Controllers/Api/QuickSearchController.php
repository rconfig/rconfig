<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Command;
use App\Models\Device;
use App\Models\Tag;
use App\Models\Task;
use App\Models\Template;
use App\Models\Vendor;
use Illuminate\Http\Request;

class QuickSearchController extends Controller
{
    public function search(Request $request)
    {
        // Get the search query from the request
        $query = $request->input('q');

        // Ensure the query is not empty
        if (!$query) {
            return response()->json(['error' => 'No search query provided'], 400);
        }

        // Search in Devices by name or IP
        $deviceResults = Device::where('device_name', 'like', "%$query%")
            ->orWhere('device_ip', 'like', "%$query%")
            ->get();
        foreach ($deviceResults as $device) {
            $device->makeHidden(['device_password', 'device_enable_password']);
        }
        // vendors by vendor_name
        $vendorResults = Vendor::where('vendorName', 'like', "%$query%")->get();

        // Categories by name
        $categoryResults = Category::where('categoryName', 'like', "%$query%")->get();

        // Search in Templates by name
        $templateResults = Template::where('templateName', 'like', "%$query%")->get();

        // tags by name
        $tagResults = Tag::where('tagname', 'like', "%$query%")->get();

        // tasks by name
        $taskResults = Task::where('task_name', 'like', "%$query%")->get();

        // commands by command
        $commandResults = Command::where('command', 'like', "%$query%")->get();

        // Combine results from different models
        $results = [
            'devices' => $deviceResults,
            'templates' => $templateResults,
            'vendors' => $vendorResults,
            'categories' => $categoryResults,
            'tags' => $tagResults,
            'tasks' => $taskResults,
            'commands' => $commandResults,
        ];

        // Return the combined results
        return response()->json($results);
    }
}
