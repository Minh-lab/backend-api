<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Thứ tự phải đúng theo phụ thuộc FK
     * Bảng nào không có FK thì chạy trước
     */
    public function run(): void
    {
        $this->call([
            // ============================================================
            // NHÓM 1: Bảng độc lập - không có FK
            // ============================================================
            RoleSeeder::class,              // 01. roles
            MajorSeeder::class,             // 02. majors
            CompanySeeder::class,           // 03. companies
            FacultyStaffSeeder::class,      // 04. faculty_staffs
            AdminSeeder::class,             // 05. admins
            ExpertiseSeeder::class,         // 06. expertises

            // ============================================================
            // NHÓM 2: Phụ thuộc nhóm 1
            // ============================================================
            LecturerSeeder::class,          // 07. lecturers
            LecturerExpertiseSeeder::class, // 08. lecturer_expertises (FK: lecturers, expertises)
            AcademicYearSeeder::class,      // 09. academic_years
            SemesterSeeder::class,          // 10. semesters (FK: academic_years)
            ClassesSeeder::class,           // 11. classes (FK: lecturers, majors)

            // ============================================================
            // NHÓM 3: Phụ thuộc nhóm 2
            // ============================================================
            StudentSeeder::class,           // 12. students (FK: classes)
            LoginSeeder::class,             // 13. logins (FK: roles)
            PasswordResetSeeder::class,     // 14. password_resets (FK: roles)
            MilestoneSeeder::class,         // 15. milestones (FK: semesters)
            NotificationSeeder::class,      // 16. notifications
            UserNotificationSeeder::class,  // 17. user_notifications (FK: notifications)

            // ============================================================
            // NHÓM 4: Topics & Requests
            // ============================================================
            UpdatedTopicSeeder::class,      // 18. updated_topics (FK: expertises)
            ProposedTopicSeeder::class,     // 19. proposed_topics (FK: expertises)
            LecturerRequestSeeder::class,   // 20. lecturer_requests (FK: lecturers)
            LecturerLeaveSeeder::class,     // 21. lecturer_leaves (FK: lecturer_requests)
            TopicSeeder::class,             // 22. topics (FK: expertises, lecturers, faculty_staffs)

            // ============================================================
            // NHÓM 5: Council
            // ============================================================
            CouncilSeeder::class,           // 23. councils (FK: semesters)
            CouncilMemberSeeder::class,     // 24. council_members (FK: councils, lecturers)

            // ============================================================
            // NHÓM 6: Capstone
            // ============================================================
            CapstoneSeeder::class,          // 25. capstones (FK: topics, students, lecturers, councils, semesters)
            CapstoneRequestSeeder::class,   // 26. capstone_requests (FK: capstones, proposed_topics, lecturers, topics)
            CapstoneReviewerSeeder::class,  // 27. capstone_reviewers (FK: capstones, lecturers)
            CapstoneReportSeeder::class,    // 28. capstone_reports (FK: capstones, milestones)

            // ============================================================
            // NHÓM 7: Internship
            // ============================================================
            InternshipSeeder::class,        // 29. internships (FK: students, lecturers, companies, semesters)
            ProposedCompanySeeder::class,   // 30. proposed_companies
            InternshipRequestSeeder::class, // 31. internship_requests (FK: internships, proposed_companies, companies)
            InternshipReportSeeder::class,  // 32. internship_reports (FK: internships, milestones)
        ]);
    }
}
