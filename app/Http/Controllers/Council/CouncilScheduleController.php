<?php

namespace App\Http\Controllers\Faculty\Council;

use App\Http\Controllers\Controller;
use App\Http\Requests\Faculty\ScheduleDefenseRequest;
use App\Http\Resources\Faculty\Council\CouncilScheduledResource;
use App\Models\Council;
use App\Services\NotificationService;
use App\Services\ScheduleConflictService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CouncilScheduleController extends Controller
{
    protected ScheduleConflictService $scheduleConflictService;
    protected NotificationService $notificationService;

    public function __construct(
        ScheduleConflictService $scheduleConflictService,
        NotificationService $notificationService
    ) {
        $this->scheduleConflictService = $scheduleConflictService;
        $this->notificationService = $notificationService;
    }

    // UC55: Xếp lịch bảo vệ
    public function scheduleDefense(int $councilId, ScheduleDefenseRequest $request): JsonResponse
    {
        // Kiểm tra hội đồng tồn tại
        $council = Council::with(['semester', 'members'])->find($councilId);
        if (!$council) {
            return response()->json([
                'success' => false,
                'message' => 'Hội đồng không tồn tại',
            ], 404);
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $buildings = $request->input('buildings');
        $rooms = $request->input('rooms');

        // Convert to Carbon for conflict checking
        $startDateCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $startDate);
        $endDateCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $endDate);

        // Check for scheduling conflicts
        $conflictResult = $this->scheduleConflictService->hasConflict(
            $startDateCarbon,
            $endDateCarbon,
            $buildings,
            $rooms,
            $councilId
        );

        if ($conflictResult['has_conflict']) {
            return response()->json([
                'success' => false,
                'message' => 'Lịch bảo vệ đồ án bị trùng',
                'data' => [
                    'conflicting_councils' => $conflictResult['conflicting_councils'],
                ]
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Update council schedule
            $council->update([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'buildings' => $buildings,
                'rooms' => $rooms,
            ]);

            DB::commit();

            // Reload relationships
            $council->load('members', 'semester');

            // Send notifications to lecturers and students
            $this->notifyLecturersAndStudentsAboutSchedule($council);

            return response()->json([
                'success' => true,
                'message' => 'Xếp lịch bảo vệ thành công',
                'data' => new CouncilScheduledResource($council),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error scheduling defense: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xếp lịch bảo vệ',
            ], 500);
        }
    }

    /**
     * Gửi thông báo cho giảng viên và sinh viên khi xếp lịch bảo vệ
     * 
     * @param Council $council
     * @return void
     */
    private function notifyLecturersAndStudentsAboutSchedule(Council $council): void
    {
        try {
            $recipients = [];

            // Add lecturers (role_id = 3)
            $lecturerRecipients = $council->members->map(function ($member) {
                return [
                    'user_id' => $member->lecturer_id,
                    'role_id' => 3, // Role Lecturer
                ];
            })->toArray();

            // Add students (role_id = 4)
            $students = $council->capstones()->with('student')->get();
            $studentRecipients = $students->map(function ($capstone) {
                return [
                    'user_id' => $capstone->student_id,
                    'role_id' => 4, // Role Student
                ];
            })->toArray();

            $recipients = array_merge($lecturerRecipients, $studentRecipients);

            if (empty($recipients)) {
                return;
            }

            $scheduleInfo = 'Thời gian: ' . $council->start_date . ' - ' . $council->end_date .
                ', Địa điểm: ' . $council->buildings . ' - ' . $council->rooms;

            $this->notificationService->send(
                'Lịch bảo vệ đề tài',
                'Lịch bảo vệ đề tài hội đồng "' . $council->name . '" đã được xếp. ' . $scheduleInfo,
                $recipients
            );
        } catch (\Exception $e) {
            Log::error('Error sending schedule notification: ' . $e->getMessage());
        }
    }
}
