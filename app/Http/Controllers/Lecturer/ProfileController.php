<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lecturer\UpdateExpertiseRequest;
use App\Http\Resources\UserResource;
use App\Models\Expertise;
use App\Models\LecturerExpertise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{

    // UC6 - Lấy danh sách chuyên môn

    public function getExpertises(Request $request): JsonResponse
    {
        $lecturer   = $request->user();
        $lecturerId = $lecturer->lecturer_id;

        $allExpertises       = Expertise::orderBy('name')->get();
        $currentExpertiseIds = LecturerExpertise::where('lecturer_id', $lecturerId)
            ->pluck('expertise_id')
            ->toArray();

        $expertises = $allExpertises->map(fn($e) => [
            'expertise_id' => $e->expertise_id,
            'name'         => $e->name,
            'description'  => $e->description,
            'is_selected'  => in_array($e->expertise_id, $currentExpertiseIds),
        ]);

        return response()->json([
            'success' => true,
            'data'    => $expertises,
        ], 200);
    }


    // UC6 - Cập nhật chuyên môn

    public function updateExpertises(UpdateExpertiseRequest $request): JsonResponse
    {
        $lecturer     = $request->user();
        $lecturerId   = $lecturer->lecturer_id;
        $expertiseIds = $request->input('expertise_ids');

        // Xoá tất cả cũ → thêm lại mới
        LecturerExpertise::where('lecturer_id', $lecturerId)->delete();

        $now  = now();
        $data = array_map(fn($id) => [
            'lecturer_id'  => $lecturerId,
            'expertise_id' => $id,
            'created_at'   => $now,
        ], $expertiseIds);

        LecturerExpertise::insert($data);

        $lecturer->load('expertises');

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật chuyên môn thành công.',
            'data'    => new UserResource($lecturer, 'lecturer'),
        ], 200);
    }
}