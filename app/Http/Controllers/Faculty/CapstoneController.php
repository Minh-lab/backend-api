<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Capstone;
use App\Models\CapstoneRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CapstoneController extends Controller
{
    /**
     * GET /faculty/capstones - Danh sách đồ án
     * 
     * Lấy danh sách tất cả đồ án với filter, pagination, và thông tin yêu cầu hủy
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Lấy pagination params
            $page = $request->input('page', 1);
            $itemsPerPage = $request->input('itemsPerPage', 10);
            $search = $request->input('search', '');
            $status = $request->input('status', '');
            $lecturer = $request->input('lecturer', '');
            $council = $request->input('council', '');

            // Query với eager loading
            $query = Capstone::query()
                ->with([
                    'student.studentClass',
                    'topic.expertise',
                    'lecturer',
                    'reviewers.lecturer',
                    'council'
                ])
                ->when($search, function ($q) use ($search) {
                    $q->whereHas('student', function ($sq) use ($search) {
                        $sq->where('full_name', 'like', "%$search%")
                           ->orWhere('usercode', 'like', "%$search%");
                    });
                })
                ->when($status, fn($q) => $q->where('status', $status))
                ->when($lecturer, fn($q) => $q->where('lecturer_id', $lecturer))
                ->when($council, fn($q) => $q->where('council_id', $council));

            // Lấy tổng số records trước khi phân trang
            $total = $query->count();
            $totalPages = ceil($total / $itemsPerPage);

            // Phân trang
            $capstones = $query
                ->orderBy('created_at', 'desc')
                ->paginate($itemsPerPage, ['*'], 'page', $page);

            // Thêm thông tin cancel request cho mỗi capstone
            $data = $capstones->map(function ($capstone) {
                $cancelRequest = CapstoneRequest::where('capstone_id', $capstone->capstone_id)
                    ->where('type', CapstoneRequest::TYPE_CANCEL_REQ)
                    ->where('status', CapstoneRequest::STATUS_PENDING_FACULTY)
                    ->first();

                $capstone->has_pending_cancel_request = $cancelRequest !== null;
                $capstone->pending_cancel_request = $cancelRequest ? [
                    'capstone_request_id' => $cancelRequest->capstone_request_id,
                    'type' => $cancelRequest->type,
                    'status' => $cancelRequest->status,
                    'student_message' => $cancelRequest->student_message,
                    'created_at' => $cancelRequest->created_at
                ] : null;

                return $capstone;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'capstones' => $data,
                    'pagination' => [
                        'current_page' => (int)$page,
                        'total_pages' => (int)$totalPages,
                        'total_items' => $total,
                        'items_per_page' => (int)$itemsPerPage
                    ]
                ],
                'message' => 'Lấy danh sách đồ án thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách đồ án: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /faculty/capstones/{id} - Chi tiết đồ án
     * 
     * Lấy chi tiết 1 đồ án (dùng cho CapstoneDetailDialog)
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $capstone = Capstone::with([
                'student.studentClass',
                'topic.expertise',
                'lecturer',
                'reviewers.lecturer',
                'council'
            ])->findOrFail($id);

            // Thêm thông tin cancel request
            $cancelRequest = CapstoneRequest::where('capstone_id', $capstone->capstone_id)
                ->where('type', CapstoneRequest::TYPE_CANCEL_REQ)
                ->where('status', CapstoneRequest::STATUS_PENDING_FACULTY)
                ->first();

            $capstone->has_pending_cancel_request = $cancelRequest !== null;
            $capstone->pending_cancel_request = $cancelRequest ? [
                'capstone_request_id' => $cancelRequest->capstone_request_id,
                'type' => $cancelRequest->type,
                'status' => $cancelRequest->status,
                'student_message' => $cancelRequest->student_message,
                'created_at' => $cancelRequest->created_at
            ] : null;

            return response()->json([
                'success' => true,
                'data' => $capstone,
                'message' => 'Lấy thông tin đồ án thành công'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đồ án không tồn tại'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy thông tin đồ án: ' . $e->getMessage()
            ], 500);
        }
    }
}
