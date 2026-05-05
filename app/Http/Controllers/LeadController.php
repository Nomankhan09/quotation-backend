<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Lead;
use Illuminate\Http\Request;
use App\Helpers\ImageUploadHelper;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        $query = Lead::where('user_id', $userId);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('job_title', 'like', "%{$search}%")
                    ->orWhere('leads', 'like', "%{$search}%");
            });
        }

        $limit = $request->get('limit', 5);
        $leads = $query->latest()->paginate($limit);

        return response()->json($leads);
    }

    public function store(Request $request)
    {
        try {
            $profile_image = null;

            if ($request->profile_image) {
                $profile_image = ImageUploadHelper::uploadBase64Image(
                    $request->profile_image,
                    'lead-profile-image'
                );
            }

            $lead = Lead::create([
                'user_id'      => auth()->id(),
                'full_name'    => $request->full_name,
                'company_name' => $request->company_name,
                'email'        => $request->email,
                'phone'        => $request->phone,
                'notes'        => $request->notes,
                'stage'        => $request->stage,
                'job_title'    => $request->job_title,
                'location'     => $request->location,
                'source'       => $request->source,
                'profile_image' => $profile_image,
            ]);

            return response()->json($lead->fresh(), 201);
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function destroy($id)
    {
        $lead = Lead::where('user_id', auth()->id())->findOrFail($id);
        $lead->delete();
        return response()->json(['message' => 'Lead deleted']);
    }

    public function update(Request $request, $id)
    {
        try {
            $lead = Lead::where('user_id', auth()->id())->findOrFail($id);

            if (!$lead) {
                return response()->json(['message' => 'Lead not found'], 404);
            }

            $request->validate([
                'full_name'    => 'required|string|max:255',
                'company_name' => 'nullable|string|max:255',
                'email'        => 'nullable|max:255',
                'phone'        => 'nullable|string|max:20',
                'notes'        => 'nullable|string',
                'stage'        => 'nullable|string|max:50',
                'job_title'    => 'nullable|string|max:255',
                'location'     => 'nullable|string|max:255',
                'source'       => 'nullable|string|max:255',
            ]);

            $profileImage = $lead->profile_image;

            // upload new image
            if ($request->has('profile_image')) {
                // remove image
                if (
                    empty($request->profile_image)
                ) {

                    if (
                        $lead->profile_image &&
                        file_exists(
                            public_path($lead->profile_image)
                        )
                    ) {

                        unlink(
                            public_path($lead->profile_image)
                        );
                    }

                    $profileImage = null;
                } else {

                    $profileImage =
                        ImageUploadHelper::uploadBase64Image(
                            $request->profile_image,
                            'lead-profile-image',
                            $lead->profile_image
                        );
                }
            }

            $lead->update([
                'full_name'    => $request->full_name,
                'company_name' => $request->company_name,
                'email'        => $request->email,
                'phone'        => $request->phone,
                'notes'        => $request->notes,
                'stage'        => $request->stage,
                'job_title'    => $request->job_title,
                'location'     => $request->location,
                'source'       => $request->source,
                'profile_image' => $profileImage,
            ]);
            return response()->json($lead, 200);
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function getLogs(Request $request)
    {
        $activityLogs = Activity::where("user_id", auth()->id())
            ->where("lead_id", $request->lead_id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => 200,
            'message' => 'All activity data',
            'activity' => $activityLogs
        ]);
    }
}
