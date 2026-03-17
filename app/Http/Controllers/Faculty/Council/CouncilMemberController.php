<?php

namespace App\Http\Controllers\Faculty\Council;

use App\Http\Controllers\Controller;
use App\Http\Requests\Faculty\CreateCouncilRequest;
use App\Http\Requests\Faculty\UpdateCouncilRequest;
use App\Http\Resources\Faculty\Council\CouncilDetailResource;
use App\Models\Council;
use App\Models\CouncilMember;
use App\Services\LecturerLeaveService;
use App\Services\NotificationService;
use App\Services\SemesterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CouncilMemberController extends Controller
{
    protected SemesterService $semesterService;
    protected LecturerLeaveService $lecturerLeaveService;
    protected NotificationService $notificationService;

    public function __construct(
        SemesterService $semesterService,
        LecturerLeaveService $lecturerLeaveService,
        NotificationService $notificationService
    ) {
        $this->semesterService = $semesterService;
        $this->lecturerLeaveService = $lecturerLeaveService;
        $this->notificationService = $notificationService;
    }

    // UC53: Lập hội đồng chấm thi
    public function create(CreateCouncilRequest $request): JsonResponse
    {
        $lecturerIds = $request->input('lecturer_ids');

        // Check nếu GV nào đang nghỉ phép
        $onLeaveIds = $this->lecturerLeaveService->filterOnLeaveIds($lecturerIds);
        if (!empty($onLeaveIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể chọn gv đang nghỉ phép, vui lòng chọn gv khác',
                'data' => [
                    'on_leave_ids' => $onLeaveIds,
                ]
            ], 422);
        }

        // Get current semester
        $semester = $this->semesterService->getCurrentSemester();
        if (!$semester) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy học kỳ hiện tại',
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Generate auto council name: "Hội đồng x" (x = count + 1)
            $councilCount = Council::where('semester_id', $semester->semester_id)->count();
            $councilName = 'Hội đồng ' . ($councilCount + 1);

            // Create Council record
            $council = Council::create([
                'name' => $councilName,
                'semester_id' => $semester->semester_id,
                'buildings' => null,
                'rooms' => null,
                'start_date' => null,
                'end_date' => null,
            ]);

            // Create 5 CouncilMember records
            foreach ($lecturerIds as $lecturerId) {
                CouncilMember::create([
                    'council_id' => $council->council_id,
                    'lecturer_id' => $lecturerId,
                    'position' => 'member',
                ]);
            }

            DB::commit();

            // Load relationships
            $council->load('members', 'semester');

            // Send notifications to lecturers
            $this->notifyLecturersAboutCouncil($council);

            return response()->json([
                'success' => true,
                'message' => 'Tạo hội đồng thành công',
                'data' => new CouncilDetailResource($council),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating council: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo hội đồng',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error',
            ], 500);
        }
    }

    // UC54: Thay đổi hội đồng chấm thi
    public function update(int $councilId, UpdateCouncilRequest $request): JsonResponse
    {
        // Kiểm tra hội đồng tồn tại
        $council = Council::with(['semester', 'members'])->find($councilId);
        if (!$council) {
            return response()->json([
                'success' => false,
                'message' => 'Hội đồng không tồn tại',
            ], 404);
        }

        $lecturerIds = $request->input('lecturer_ids');

        // Check nếu GV nào đang nghỉ phép
        $onLeaveIds = $this->lecturerLeaveService->filterOnLeaveIds($lecturerIds);
        if (!empty($onLeaveIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể chọn gv đang nghỉ phép, vui lòng chọn gv khác',
                'data' => [
                    'on_leave_ids' => $onLeaveIds,
                ]
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Delete old council members
            CouncilMember::where('council_id', $councilId)->delete();

            // Create new council members
            foreach ($lecturerIds as $lecturerId) {
                CouncilMember::create([
                    'council_id' => $councilId,
                    'lecturer_id' => $lecturerId,
                    'position' => 'member',
                ]);
            }

            DB::commit();

            // Load updated relationships
            $council->load('members', 'semester');

            // Send notifications to lecturers
            $this->notifyLecturersAboutCouncil($council);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật hội đồng thành công',
                'data' => new CouncilDetailResource($council),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating council: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật hội đồng',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error',
            ], 500);
        }
    }

    /**
     * Gửi thông báo cho giảng viên khi tạo/cập nhật hội đồng
     * 
     * @param Council $council
     * @return void
     */
    private function notifyLecturersAboutCouncil(Council $council): void
    {
        try {
            $recipients = $council->members->map(function ($member) {
                return [
                    'user_id' => $member->lecturer_id,
                    'role_id' => 3, // Role Lecturer
                ];
            })->toArray();

            if (empty($recipients)) {
                return;
            }

            $this->notificationService->send(
                'Thông báo hội đồng chấm thi',
                'Bạn được chọn làm thành viên của hội đồng "' . $council->name . '" - ' . $council->semester->semester_name,
                $recipients
            );
        } catch (\Exception $e) {
            Log::error('Error sending council notification: ' . $e->getMessage());
        }
    }
}
