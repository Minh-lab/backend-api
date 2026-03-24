<?php

namespace App\Http\Controllers\Capstone;

use App\Http\Controllers\Controller;
use App\Models\{CapstoneReport, Capstone, Milestone};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CapstoneReportsController extends Controller
{
    // Lấy đợt (milestones) đồ án
    public function getMilestones()
    {
        $studentId = auth()->id();
        $capstone = Capstone::where('student_id', $studentId)->latest()->first();

        if (!$capstone) {
            return response()->json(['success' => true, 'data' => []], 200);
        }

        $milestones = Milestone::where('semester_id', $capstone->semester_id)
            ->where('type', Milestone::TYPE_CAPSTONE)
            ->orderBy('start_date', 'asc')
            ->get();

        return response()->json(['success' => true, 'data' => $milestones]);
    }

    // Lấy lịch sử nộp báo cáo
    public function getReportHistory(Request $request)
    {
        $studentId = auth()->id();
        $capstone = Capstone::where('student_id', $studentId)->latest()->first();

        if (!$capstone) {
            return response()->json(['success' => true, 'data' => []], 200);
        }

        $reports = CapstoneReport::where('capstone_id', $capstone->capstone_id)
            ->with('milestone')
            ->orderBy('submission_date', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $reports]);
    }

    // UC 21: Nộp báo cáo đồ án
    public function submitReport(Request $request)
    {
        $studentId = auth()->id();
        
        $request->validate([
            'capstone_id' => 'required|exists:capstones,capstone_id',
            'milestone_id'=> 'required|exists:milestones,milestone_id',
            'report_file' => 'required|file|mimes:pdf,doc,docx|max:20480',
            'link'        => 'required|url|max:255'
        ]);

        $capstone = Capstone::where('capstone_id', $request->capstone_id)
            ->where('student_id', $studentId)
            ->first();

        if (!$capstone) {
            return response()->json(['success' => false, 'message' => 'Thông tin không hợp lệ.'], 403);
        }

        $milestone = Milestone::find($request->milestone_id);
        if (!$milestone || now() < $milestone->start_date || now() > $milestone->end_date) {
            $msg = !$milestone ? 'Mốc thời gian không hợp lệ.' : (now() < $milestone->start_date ? 'Đợt nộp báo cáo chưa bắt đầu.' : 'Đã quá hạn nộp báo cáo.');
            return response()->json(['success' => false, 'message' => $msg], 400);
        }

        $filePath = $request->file('report_file')->store('reports/capstones', 'public');

        $report = CapstoneReport::create([
            'capstone_id'  => $request->capstone_id,
            'milestone_id' => $request->milestone_id,
            'status'       => CapstoneReport::STATUS_PENDING,
            'file_path'    => $filePath,
            'link'         => $request->link,
            'submission_date' => now(),
        ]);

        return response()->json([
            'success'      => true,
            'message'      => 'Báo cáo đã được nộp thành công.',
            'data'         => $report
        ], 201);
    }
}
