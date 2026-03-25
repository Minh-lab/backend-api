<?php

namespace App\Http\Controllers\Internship;

use App\Models\{Company, Internship, Milestone, Notification, UserNotification};
use App\Http\Requests\Internship\AssignCompanyRequest;
use App\Http\Resources\Internship\CompanySlotResource;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CompanyAssignmentController extends InternshipBaseController
{
    /**
     * UC 37 - Bước 4: Lấy danh sách doanh nghiệp kèm số lượng slot
     */
    public function getAvailableCompanies(\Illuminate\Http\Request $request)
    {
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = $request->get('perPage', 5);

        $query = Company::query();

        // Search by name or tax_code
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('usercode', 'LIKE', "%{$search}%");
            });
        }

        $companies = $query->paginate($perPage, ['*'], 'page', $page);
        return CompanySlotResource::collection($companies);
    }

    /**
     * UC 37 - Bước 7-11: Thực hiện phân công doanh nghiệp cho sinh viên
     */
    public function assignCompany(AssignCompanyRequest $request)
    {
        return DB::transaction(function () use ($request) {
            // BR-3: Kiểm tra thời hạn đăng ký của sinh viên đã kết thúc chưa
            // $milestone = Milestone::where('type', Milestone::TYPE_INTERNSHIP)->upcoming()->first();
            // if ($milestone) {
            //     return response()->json(['message' => 'Thời hạn tự đăng ký chưa kết thúc (BR-3).'], 400);
            // }

            $company = Company::findOrFail($request->company_id);
            $internshipIds = $request->internship_ids;
            $countSelected = count($internshipIds);

            // 7a: Kiểm tra slot còn lại của doanh nghiệp (BR-2)
            $currentInterns = $company->internships()->count();
            if (($currentInterns + $countSelected) > 20) {
                return response()->json(['message' => 'Doanh nghiệp không đủ slot (7a1).'], 400);
            }

            // Bước 8 & BR-1: Cập nhật trạng thái cho những SV "Chưa có doanh nghiệp"
            $affected = Internship::whereIn('internship_id', $internshipIds)
                ->where('status', 'INITIALIZED')
                ->update([
                'company_id' => $company->company_id,
                'status' => 'COMPANY_APPROVED',
                'updated_at' => Carbon::now()
            ]);

            if ($affected === 0) {
                return response()->json(['message' => 'Không có sinh viên hợp lệ để phân công.'], 400);
            }

            // Bước 11: Gửi thông báo cho sinh viên
            $notification = Notification::create([
                'title' => 'Thông báo phân công thực tập',
                'content' => "Bạn đã được phân công thực tập tại doanh nghiệp: {$company->name}."
            ]);

            $studentIds = Internship::whereIn('internship_id', $internshipIds)->pluck('student_id');
            foreach ($studentIds as $id) {
                UserNotification::create([
                    'notification_id' => $notification->notification_id,
                    'user_id' => $id,
                    'role_id' => 1,
                    'is_read' => false
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Phân công thành công (Bước 10)']);
        });
    }
}
