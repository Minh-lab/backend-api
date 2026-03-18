<?php

namespace App\Http\Controllers\Capstone;

use App\Http\Controllers\Controller;
use App\Models\CapstoneRequest;
use App\Models\Capstone;
use App\Http\Resources\CapstoneResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CapstoneController extends Controller
{
    // Giới hạn số lượng hướng dẫn (BR-2)
    const MAX_SLOTS = 10;

    /**
     * BƯỚC 3: Hiển thị danh sách chờ duyệt
     */
    public function index()
    {
        $lecturerId = 1; // Giả định ID giảng viên đang test

        $requests = CapstoneRequest::with(['capstone.student', 'topic'])
            ->where('lecturer_id', $lecturerId)
            ->where('type', CapstoneRequest::TYPE_LECTURER_REG)
            ->where('status', CapstoneRequest::STATUS_PENDING_TEACHER)
            ->orderBy('created_at', 'asc') // BR-3: Cũ nhất lên đầu
            ->get();

        return CapstoneResource::collection($requests);
    }

    /**
     * BƯỚC 5: Xác nhận (APPROVED) hoặc Từ chối (REJECTED)
     */
    public function confirmRegistration(Request $request, $id)
    {
        $action = $request->status; // 'APPROVED' hoặc 'REJECTED'

        return DB::transaction(function () use ($id, $action) {
            $capReq = CapstoneRequest::findOrFail($id);
            $lecturer = $capReq->lecturer;

            // --- KIỂM TRA NGOẠI LỆ 2a (Nghỉ phép) ---
            if (!$lecturer->is_active) {
                return response()->json(['message' => 'Bạn không thể truy cập khi đang nghỉ phép (2a)'], 403);
            }

            if ($action === 'APPROVED') {
                // --- BƯỚC 5: Kiểm tra số lượng slot (BR-2) ---
                $currentSlots = Capstone::where('lecturer_id', $lecturer->lecturer_id)
                    ->whereNotIn('status', [Capstone::STATUS_CANCEL, Capstone::STATUS_FAILED])
                    ->count();

                if ($currentSlots >= self::MAX_SLOTS) {
                    return response()->json(['message' => 'Đã đạt giới hạn số lượng sinh viên (5a)'], 400);
                }

                // --- BƯỚC 6 & 7: Cập nhật thông tin đồ án và tăng slot ---
                $capReq->capstone->update([
                    'lecturer_id' => $lecturer->lecturer_id,
                    'status' => Capstone::STATUS_LECTURER_APPROVED
                ]);

                $capReq->update(['status' => CapstoneRequest::STATUS_PENDING_FACULTY]);
            } else {
                // --- LUỒNG THAY THẾ 4a: Từ chối ---
                $capReq->update(['status' => CapstoneRequest::STATUS_REJECTED]);
                $capReq->capstone->update(['status' => Capstone::STATUS_CANCEL]);
            }

            return response()->json(['success' => true, 'message' => 'Xử lý thành công (Bước 9)']);
        });
    }
}
