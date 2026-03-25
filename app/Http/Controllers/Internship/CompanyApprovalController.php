<?php

namespace App\Http\Controllers\Internship;

use App\Models\{InternshipRequest, Company, Internship, ProposedCompany};
use App\Http\Requests\Internship\ApproveCompanyRequest;
use App\Http\Resources\Internship\CompanyPendingResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CompanyApprovalController extends InternshipBaseController
{
    /**
     * UC 42 - Bước 2: Hiển thị danh sách doanh nghiệp chờ duyệt
     */
    public function getPendingRequests()
    {
        // BR-1: Chức năng duyệt chỉ mở sau khi đóng cổng đăng ký
        $isClosed = \App\Models\Milestone::where('type', \App\Models\Milestone::TYPE_INTERNSHIP)
            ->where('end_date', '<', Carbon::now())
            ->exists();

        if (!$isClosed) {
            return response()->json(['message' => 'Cổng đăng ký của sinh viên chưa đóng (BR-1).'], 400);
        }

        $requests = InternshipRequest::with(['company', 'proposedCompany', 'internship.student'])
            ->where('status', InternshipRequest::STATUS_PENDING_FACULTY)
            ->get();

        return CompanyPendingResource::collection($requests);
    }

    /**
     * UC 42 - Bước 3: Xem chi tiết yêu cầu doanh nghiệp với danh sách sinh viên
     */
    public function getRequestDetail($id)
    {
        $internReq = InternshipRequest::with(['company', 'proposedCompany', 'internship.student'])
            ->findOrFail($id);

        // Phân biệt thông tin từ DN chính thức hoặc DN đề xuất
        $company = $internReq->company ?? $internReq->proposedCompany;

        // Lấy tất cả sinh viên đăng ký cho doanh nghiệp này
        $requests = InternshipRequest::where(function ($q) use ($internReq) {
                if ($internReq->proposed_company_id) {
                    $q->where('proposed_company_id', $internReq->proposed_company_id);
                }
                if ($internReq->company_id) {
                    $q->orWhere('company_id', $internReq->company_id);
                }
            })
            ->where('status', InternshipRequest::STATUS_PENDING_FACULTY)
            ->with('internship.student')
            ->get();

        $students = $requests->map(function($req) {
            $student = $req->internship?->student;
            if (!$student) return null;
            
            return [
                'id' => $student->student_id,
                'name' => $student->full_name ?? $student->name ?? 'N/A',
                'class' => $student->class ?? 'N/A',
                'gpa' => $student->gpa ?? 0,
            ];
        })->filter()->values();

        return response()->json([
            'success' => true,
            'data' => [
                'request_id' => $internReq->internship_request_id,
                'type' => $internReq->type,
                'proposed_company_id' => $internReq->proposed_company_id,
                'company' => [
                    'tax_code' => $company->usercode ?? $company->tax_code,
                    'name' => $company->name,
                    'address' => $company->address,
                    'email' => $company->email ?? $company->contact_email,
                    'website' => $company->website ?? '',
                ],
                'students' => $students,
            ]
        ]);
    }

    /**
     * UC 42 - Bước 6-10: Xử lý Duyệt hoặc Từ chối
     */
    public function approveRequest(ApproveCompanyRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $internReq = InternshipRequest::findOrFail($id);
            $approvedCount = 0;
            $rejectedCount = 0;

            if ($request->status === InternshipRequest::STATUS_APPROVED) {
                // BR-2: Xử lý cấp tài khoản doanh nghiệp nếu chưa có
                if (!$internReq->company_id && $internReq->proposed_company_id) {
                    $proposed = $internReq->proposedCompany;

                    $newCompany = Company::create([
                        'usercode' => $proposed->tax_code,
                        'username' => strtolower(str_replace(' ', '_', explode('@', $proposed->contact_email ?? $proposed->tax_code)[0])),
                        'name' => $request->company_name ?? $proposed->name,
                        'email' => $request->company_email ?? $proposed->contact_email,
                        'address' => $request->company_address ?? $proposed->address,
                        'password' => Hash::make($proposed->tax_code),
                        'is_active' => true,
                        'is_partnered' => true,
                    ]);

                    $internReq->company_id = $newCompany->company_id;
                }

                // BR-4: Xử lý danh sách sinh viên được duyệt
                $studentIds = $request->student_ids ?? [];
                
                // Duyệt các sinh viên được chọn
                if (!empty($studentIds)) {
                    InternshipRequest::where('company_id', $internReq->company_id)
                        ->orWhere('proposed_company_id', $internReq->proposed_company_id)
                        ->whereHas('internship', function ($q) use ($studentIds) {
                            $q->whereIn('student_id', $studentIds);
                        })
                        ->where('status', InternshipRequest::STATUS_PENDING_FACULTY)
                        ->update(['status' => InternshipRequest::STATUS_APPROVED]);

                    // Cập nhật thông tin doanh nghiệp vào bản ghi Internship
                    Internship::whereIn('student_id', $studentIds)
                        ->update([
                            'company_id' => $internReq->company_id,
                            'status' => 'COMPANY_APPROVED',
                        ]);

                    $approvedCount = count($studentIds);
                }

                // Từ chối các sinh viên không được chọn
                $allStudentIds = InternshipRequest::where('company_id', $internReq->company_id)
                    ->orWhere('proposed_company_id', $internReq->proposed_company_id)
                    ->where('status', InternshipRequest::STATUS_PENDING_FACULTY)
                    ->with('internship')
                    ->get()
                    ->pluck('internship.student_id')
                    ->all();

                $rejectedIds = array_diff($allStudentIds, $studentIds ?? []);
                if (!empty($rejectedIds)) {
                    InternshipRequest::whereHas('internship', function ($q) use ($rejectedIds) {
                        $q->whereIn('student_id', $rejectedIds);
                    })
                        ->where('status', InternshipRequest::STATUS_PENDING_FACULTY)
                        ->update(['status' => InternshipRequest::STATUS_REJECTED]);

                    $rejectedCount = count($rejectedIds);
                }
            }
            else {
                // 6a: Từ chối toàn bộ
                $internReq->update([
                    'status' => InternshipRequest::STATUS_REJECTED,
                    'feedback' => $request->feedback
                ]);
                $rejectedCount = 1;
            }

            return response()->json([
                'success' => true,
                'message' => "Duyệt doanh nghiệp thành công. Duyệt: $approvedCount, Từ chối: $rejectedCount sinh viên."
            ]);
        });
    }

    /**
     * UC 39.2: VPK Lấy danh sách tất cả internships với pending cancel requests (có phân trang)
     * (Hiển thị button duyệt hủy chỉ khi có PENDING_FACULTY cancel request)
     */
    public function getVPKInternshipsList(\Illuminate\Http\Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);

        // Lấy tất cả internships với phân trang
        $paginatedInternships = Internship::with([
            'student.studentClass',
            'lecturer',
            'company',
            'semester',
            // Chỉ load cancel request có status=PENDING_FACULTY
            'requests' => function ($q) {
                $q->where('type', InternshipRequest::TYPE_CANCEL_REQ)
                  ->where('status', InternshipRequest::STATUS_PENDING_FACULTY);
            }
        ])->paginate($perPage, ['*'], 'page', $page);

        // Transform response để hiển thị
        $data = $paginatedInternships->items();
        $transformedData = collect($data)->map(function ($internship) {
            $pendingCancelRequest = $internship->requests->first();
            
            return [
                'internship_id' => $internship->internship_id,
                'student_id' => $internship->student_id,
                'student_name' => $internship->student?->full_name ?? $internship->student?->name ?? 'N/A',
                'student_code' => $internship->student?->usercode ?? 'N/A',
                'class_name' => $internship->student?->studentClass?->class_name ?? '---',
                'lecturer_id' => $internship->lecturer_id,
                'lecturer_name' => $internship->lecturer?->full_name ?? $internship->lecturer?->name ?? '---',
                'company_id' => $internship->company_id,
                'company_name' => $internship->company?->name ?? '---',
                'company_grade' => $internship->company_grade ?? null,
                'semester_id' => $internship->semester_id,
                'status' => $internship->status,
                'position' => $internship->position,
                // Flag để hiển thị button
                'has_pending_cancel_request' => $pendingCancelRequest !== null,
                // Chi tiết pending cancel request (nếu có)
                'pending_cancel_request' => $pendingCancelRequest ? [
                    'internship_request_id' => $pendingCancelRequest->internship_request_id,
                    'type' => $pendingCancelRequest->type,
                    'status' => $pendingCancelRequest->status,
                    'student_message' => $pendingCancelRequest->student_message,
                    'feedback' => $pendingCancelRequest->feedback,
                    'created_at' => $pendingCancelRequest->created_at,
                    'updated_at' => $pendingCancelRequest->updated_at,
                ] : null,
                'created_at' => $internship->created_at,
                'updated_at' => $internship->updated_at,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $transformedData,
            'pagination' => [
                'current_page' => $paginatedInternships->currentPage(),
                'total' => $paginatedInternships->total(),
                'per_page' => $paginatedInternships->perPage(),
                'last_page' => $paginatedInternships->lastPage(),
                'from' => $paginatedInternships->firstItem(),
                'to' => $paginatedInternships->lastItem(),
            ]
        ]);
    }


    /**
     * UC 42 - Bước 5a: Cập nhật thông tin doanh nghiệp đề xuất
     */
    public function updateProposedCompany(\Illuminate\Http\Request $request, $proposedCompanyId)
    {
        try {
            $proposed = ProposedCompany::findOrFail($proposedCompanyId);

            $proposed->update($request->only([
                'name',
                'address', 
                'website',
                'tax_code',
                'contact_email',
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thông tin doanh nghiệp thành công',
                'data' => $proposed
            ]);
        } catch (\Exception $error) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi cập nhật doanh nghiệp: ' . $error->getMessage()
            ], 500);
        }
    }
}
