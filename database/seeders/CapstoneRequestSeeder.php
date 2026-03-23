<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CapstoneRequestSeeder extends Seeder
{
    /**
     * Seed the capstone_requests table với dữ liệu test cho tất cả loại và trạng thái
     * 
     * Type: LECTURER_REG, TOPIC_PROP, TOPIC_BANK, CANCEL_REQ
     * Status: PENDING_TEACHER, PENDING_FACULTY, APPROVED, REJECTED
     */
    public function run(): void
    {
        $rows = [
            // === LECTURER_REG: Đăng ký giảng viên hướng dẫn ===
            [
                'proposed_topic_id' => null,
                'capstone_id' => 1,
                'lecturer_id' => 1,
                'topic_id' => 1,
                'type' => 'LECTURER_REG',
                'status' => 'APPROVED',
                'student_message' => 'Em xin đăng ký thầy An hướng dẫn đề tài này ạ',
                'lecturer_feedback' => 'Đồng ý hướng dẫn',
                'file_path' => null,
            ],

            // === TOPIC_BANK: Đăng ký đề tài trong ngân hàng ===
            [
                'proposed_topic_id' => null,
                'capstone_id' => 1,
                'lecturer_id' => null,
                'topic_id' => 1,
                'type' => 'TOPIC_BANK',
                'status' => 'APPROVED',
                'student_message' => 'Em xin đăng ký đề tài quản lý bán hàng',
                'lecturer_feedback' => 'Chấp nhận',
                'file_path' => null,
            ],

            // === TOPIC_PROP: Đề xuất đề tài mới ===
            [
                'proposed_topic_id' => 1,
                'capstone_id' => 4,
                'lecturer_id' => 3,
                'topic_id' => null,
                'type' => 'TOPIC_PROP',
                'status' => 'APPROVED',
                'student_message' => 'Em muốn đề xuất đề tài website quản lý câu lạc bộ sinh viên',
                'lecturer_feedback' => 'Đồng ý, đề tài thú vị',
                'file_path' => null,
            ],

            // Sinh viên 5: LECTURER_REG đang chờ VPK phê duyệt
            [
                'proposed_topic_id' => null,
                'capstone_id' => 5,
                'lecturer_id' => 5,
                'topic_id' => 11,
                'type' => 'LECTURER_REG',
                'status' => 'PENDING_FACULTY',
                'student_message' => 'Em xin đăng ký thầy Em hướng dẫn',
                'lecturer_feedback' => 'Đồng ý',
                'file_path' => null,
            ],

            // === THÊM: Nhiều yêu cầu PENDING_FACULTY cho VPK duyệt ===
            
            // Sinh viên 4: TOPIC_BANK đang chờ VPK phê duyệt
            [
                'proposed_topic_id' => null,
                'capstone_id' => 4,
                'lecturer_id' => null,
                'topic_id' => 9,
                'type' => 'TOPIC_BANK',
                'status' => 'PENDING_FACULTY',
                'student_message' => 'Em xin đăng ký đề tài hệ thống quản lý bán hàng online',
                'lecturer_feedback' => null,
                'file_path' => null,
            ],

            // Sinh viên 4: LECTURER_REG đang chờ VPK phê duyệt
            [
                'proposed_topic_id' => null,
                'capstone_id' => 4,
                'lecturer_id' => 1,
                'topic_id' => 9,
                'type' => 'LECTURER_REG',
                'status' => 'PENDING_FACULTY',
                'student_message' => 'Em xin đăng ký thầy Nguyễn Văn A hướng dẫn',
                'lecturer_feedback' => 'Chấp nhận hướng dẫn',
                'file_path' => null,
            ],

            // Sinh viên 5: TOPIC_PROP đang chờ VPK phê duyệt (giảng viên đã phê duyệt)
            [
                'proposed_topic_id' => 2,
                'capstone_id' => 5,
                'lecturer_id' => 3,
                'topic_id' => null,
                'type' => 'TOPIC_PROP',
                'status' => 'PENDING_FACULTY',
                'student_message' => 'Em xin đề xuất đề tài ứng dụng machine learning cho dự đoán',
                'lecturer_feedback' => 'Đề tài rất hay, em hãy tiếp tục phát triển',
                'file_path' => null,
            ],

            // Sinh viên 6: CANCEL_REQ đang chờ VPK phê duyệt
            [
                'proposed_topic_id' => null,
                'capstone_id' => 6,
                'lecturer_id' => 2,
                'topic_id' => 4,
                'type' => 'CANCEL_REQ',
                'status' => 'PENDING_FACULTY',
                'student_message' => 'Em xin hủy đồ án do tình huống bất khả kháng',
                'lecturer_feedback' => 'Đồng ý hỗ trợ',
                'file_path' => null,
            ],

            // Sinh viên 6: TOPIC_BANK đang chờ giảng viên phê duyệt
            [
                'proposed_topic_id' => null,
                'capstone_id' => 6,
                'lecturer_id' => null,
                'topic_id' => 2,
                'type' => 'TOPIC_BANK',
                'status' => 'PENDING_TEACHER',
                'student_message' => 'Em xin đăng ký đề tài phần mềm quản lý kho hàng',
                'lecturer_feedback' => null,
                'file_path' => null,
            ],

            // Sinh viên 7: TOPIC_PROP bị từ chối
            [
                'proposed_topic_id' => 2,
                'capstone_id' => 7,
                'lecturer_id' => 2,
                'topic_id' => null,
                'type' => 'TOPIC_PROP',
                'status' => 'REJECTED',
                'student_message' => 'Em xin đề xuất đề tài ứng dụng AI',
                'lecturer_feedback' => 'Đề tài quá rộng, cần hẹp phạm vi hơn',
                'file_path' => null,
            ],

            // === CANCEL_REQ: Yêu cầu hủy đồ án ===
            // Sinh viên 3: Xin hủy, chờ giảng viên phê duyệt
            [
                'proposed_topic_id' => null,
                'capstone_id' => 3,
                'lecturer_id' => 2,  // GVHD của sinh viên 3
                'topic_id' => 6,
                'type' => 'CANCEL_REQ',
                'status' => 'PENDING_TEACHER',
                'student_message' => 'Em xin phép hủy đồ án vì lý do sức khỏe',
                'lecturer_feedback' => null,
                'file_path' => null,
            ],

            // Sinh viên 8: Xin hủy, giảng viên đã duyệt, chờ VPK
            [
                'proposed_topic_id' => null,
                'capstone_id' => 8,
                'lecturer_id' => 2,  // GVHD của sinh viên 8
                'topic_id' => 3,
                'type' => 'CANCEL_REQ',
                'status' => 'PENDING_FACULTY',
                'student_message' => 'Em xin hủy đồ án để tập trung học tập',
                'lecturer_feedback' => 'Đồng ý, em có lý do chính đáng',
                'file_path' => null,
            ],

            // Sinh viên 10: Xin hủy, đã được VPK duyệt
            [
                'proposed_topic_id' => null,
                'capstone_id' => 10,
                'lecturer_id' => 4,  // GVHD của sinh viên 10
                'topic_id' => 8,
                'type' => 'CANCEL_REQ',
                'status' => 'APPROVED',
                'student_message' => 'Em xin hủy đồ án',
                'lecturer_feedback' => 'Được phê duyệt',
                'file_path' => null,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('capstone_requests')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
