<?php

namespace App\Http\Controllers\Capstone;

use App\Models\{Capstone, Council, CapstoneReviewer, Lecturer};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CouncilController extends CapstoneBaseController
{
    /**
     * UC 29 - Bước 3 & 5: Lấy danh sách hội đồng và thành viên cho VPK
     */
    public function getCouncilsForAssignment()
    {
        $councils = Council::with('members')->get();
        // Giả định bạn có resource CouncilAssignmentResource
        // return CouncilAssignmentResource::collection($councils);
        return response()->json([
            'success' => true,
            'data' => $councils
        ]);
    }

    /**
     * UC 29 - Bước 7-12: Thực hiện phân công Hội đồng và 2 GVPB
     */
    public function assignCouncilAndReviewers(Request $request)
    {
        $validated = $request->validate([
            'council_id' => 'required|exists:councils,council_id',
            'reviewer_ids' => 'required|array|min:2',
            'reviewer_ids.*' => 'exists:lecturers,lecturer_id',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:capstones,capstone_id',
        ]);

        return DB::transaction(function () use ($validated) {
            $councilId = $validated['council_id'];
            $reviewerIds = $validated['reviewer_ids'];
            $capstoneIds = $validated['student_ids']; // Frontend gửi capstone_ids với key "student_ids"

            $council = Council::findOrFail($councilId);

            // 5a. Kiểm tra slot của hội đồng (Giả định mỗi hội đồng tối đa 70 SV)
            if ($council->capstones()->count() + count($capstoneIds) > 70) {
                return response()->json(['message' => "Hội đồng {$council->name} không đủ slot báo cáo (5a1)."], 400);
            }

            // Kiểm tra trạng thái nghỉ phép của GVPB (8c)
            foreach ($reviewerIds as $rId) {
                $rev = Lecturer::findOrFail($rId);
                if ($rev->leaves()->where('lecturer_leaves.status', 'LEAVE_ACTIVE')->exists()) {
                    return response()->json(['message' => "Giảng viên {$rev->full_name} đang nghỉ phép, không thể phân công (8c1)."], 400);
                }

                // 8b. Kiểm tra slot phản biện của GV (Giả định mỗi GV phản biện tối đa 15 SV/đợt)
                if ($rev->capstoneReviewers()->count() + count($capstoneIds) > 15) {
                    return response()->json(['message' => "Giảng viên {$rev->full_name} không đủ slot phản biện (8b1)."], 400);
                }
            }

            foreach ($capstoneIds as $capstoneId) {
                $capstone = Capstone::findOrFail($capstoneId);

                // 8a. Kiểm tra trùng GVHD (BR-1)
                if (in_array($capstone->lecturer_id, $reviewerIds)) {
                    return response()->json(['message' => "Giảng viên phản biện không được trùng với Giảng viên hướng dẫn của sinh viên: {$capstone->student->full_name} (8a1)."], 400);
                }

                // Bước 9: Cập nhật Hội đồng cho đồ án
                $capstone->update([
                    'council_id' => $councilId,
                    'status'     => Capstone::STATUS_REVIEW_ELIGIBLE
                ]);

                // Bước 9: Xóa phản biện cũ (nếu có) và tạo mới 2 GVPB (BR-2)
                CapstoneReviewer::where('capstone_id', $capstone->capstone_id)->delete();
                foreach ($reviewerIds as $rId) {
                    CapstoneReviewer::create([
                        'capstone_id' => $capstone->capstone_id,
                        'lecturer_id' => $rId
                    ]);

                    // Thông báo cho GVPB
                    $this->notifyStudent($rId, 2, "Bạn được phân công phản biện đồ án cho sinh viên {$capstone->student->full_name}.");
                }

                // Thông báo cho Sinh viên
                $this->notifyStudent($capstone->student_id, 1, "Bạn đã được phân công vào hội đồng {$council->name} và có giảng viên phản biện.");
            }

            return response()->json(['success' => true, 'message' => 'Phân công hội đồng và giảng viên phản biện thành công.']);
        });
    }
}
