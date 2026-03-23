<?php

namespace App\Http\Controllers\Internship;

use App\Models\{Internship};
use App\Http\Requests\Internship\SearchInternshipRequest;
use App\Http\Resources\Internship\InternshipSearchResource;
use Illuminate\Database\Eloquent\Builder;

class InternshipSearchController extends InternshipBaseController
{
    /**
     * UC 36: Tìm kiếm và lọc danh sách thực tập
     */
    public function search(SearchInternshipRequest $request)
    {
        $user = auth()->user();
        $role = $request->get('current_role');

        // 5. Thực hiện truy vấn với Eager Loading
        $query = Internship::with(['student.studentClass', 'company', 'lecturer']);

        // 4. Xác định phạm vi dữ liệu theo vai trò (BR-1)
        if ($role === 'lecturer') {
            $query->where('lecturer_id', $user->lecturer_id);
        }

        // Lọc theo từ khóa (Tên hoặc MSSV)
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->whereHas('student', function (Builder $q) use ($keyword) {
                $q->where('full_name', 'like', "%{$keyword}%")
                    ->orWhere('usercode', 'like', "%{$keyword}%");
            });
        }

        // Lọc theo học kỳ
        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo doanh nghiệp
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // BR-2: Phân trang kết quả (10 bản ghi/trang)
        $results = $query->paginate(10);

        return InternshipSearchResource::collection($results);
    }
}
