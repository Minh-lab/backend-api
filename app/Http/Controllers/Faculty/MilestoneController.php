<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Http\Requests\Faculty\StoreMileStonesRequest;
use App\Http\Requests\Faculty\UpdateMilestoneRequest;
use App\Models\Milestone;
use Illuminate\Http\JsonResponse;

class MilestoneController extends Controller
{
    /**
     * UC: Thêm mốc thời gian
     * Chỉ Văn phòng Khoa (faculty_staff) mới được phép thực hiện.
     */
    public function store(StoreMileStonesRequest $request): JsonResponse
    {
        $milestone = Milestone::create([
            'semester_id' => $request->input('semester_id'),
            'phase_name'  => $request->input('phase_name'),
            'description' => $request->input('description'),
            'type'        => $request->input('type'),
            'start_date'  => $request->input('start_date'),
            'end_date'    => $request->input('end_date'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thêm mốc thời gian thành công.',
            'data'    => $milestone,
        ], 201);
    }

    public function storeForSemester(StoreMileStonesRequest $request, int $id): JsonResponse
    {
        $milestone = Milestone::create([
            'semester_id' => $id,
            'phase_name'  => $request->input('phase_name'),
            'description' => $request->input('description'),
            'type'        => $request->input('type'),
            'start_date'  => $request->input('start_date'),
            'end_date'    => $request->input('end_date'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thêm mốc thời gian thành công.',
            'data'    => $milestone,
        ], 201);
    }

    /**
     * UC: Sửa mốc thời gian
     * Chỉ Văn phòng Khoa (faculty_staff) mới được phép thực hiện.
     */
    public function update(UpdateMilestoneRequest $request, int $milestone): JsonResponse
    {
        $record = Milestone::find($milestone);

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Mốc thời gian không tồn tại.',
            ], 404);
        }

        $record->update([
            'phase_name'  => $request->input('phase_name'),
            'description' => $request->input('description'),
            'type'        => $request->input('type'),
            'start_date'  => $request->input('start_date'),
            'end_date'    => $request->input('end_date'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật mốc thời gian thành công.',
            'data'    => $record->fresh(),
        ], 200);
    }
}
