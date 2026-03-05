<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TopicSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['expertise_id' => 1, 'lecturer_id' => 1, 'faculty_staff_id' => null,
             'title' => 'Xây dựng hệ thống quản lý bán hàng trực tuyến',
             'description' => 'Phát triển ứng dụng thương mại điện tử với quản lý sản phẩm, đơn hàng, thanh toán',
             'technologies' => 'Laravel, React, MySQL, Redis, Docker', 'is_available' => 1, 'is_bank_topic' => 1],

            ['expertise_id' => 1, 'lecturer_id' => 1, 'faculty_staff_id' => null,
             'title' => 'Hệ thống quản lý nhân sự và chấm công online',
             'description' => 'Quản lý nhân viên, chấm công, tính lương tích hợp QR Code',
             'technologies' => 'Laravel, Vue.js, MySQL, WebSocket', 'is_available' => 1, 'is_bank_topic' => 1],

            ['expertise_id' => 1, 'lecturer_id' => 3, 'faculty_staff_id' => null,
             'title' => 'Ứng dụng đặt lịch hẹn khám bệnh trực tuyến',
             'description' => 'Cho phép bệnh nhân đặt lịch và bác sĩ quản lý lịch hẹn',
             'technologies' => 'React, Node.js, PostgreSQL, Socket.io', 'is_available' => 1, 'is_bank_topic' => 1],

            ['expertise_id' => 2, 'lecturer_id' => 3, 'faculty_staff_id' => null,
             'title' => 'Ứng dụng di động quản lý chi tiêu cá nhân',
             'description' => 'App theo dõi thu chi với biểu đồ thống kê và nhắc nhở thông minh',
             'technologies' => 'Flutter, Dart, Firebase, SQLite', 'is_available' => 1, 'is_bank_topic' => 1],

            ['expertise_id' => 2, 'lecturer_id' => 3, 'faculty_staff_id' => null,
             'title' => 'Ứng dụng học từ vựng tiếng Anh với Flashcard',
             'description' => 'App học từ vựng bằng Spaced Repetition và gamification',
             'technologies' => 'React Native, Firebase, SQLite', 'is_available' => 1, 'is_bank_topic' => 1],

            ['expertise_id' => 3, 'lecturer_id' => 2, 'faculty_staff_id' => null,
             'title' => 'Hệ thống nhận diện khuôn mặt điểm danh tự động',
             'description' => 'Điểm danh sinh viên bằng nhận diện khuôn mặt thời gian thực',
             'technologies' => 'Python, OpenCV, TensorFlow, Flask, MySQL', 'is_available' => 1, 'is_bank_topic' => 1],

            ['expertise_id' => 3, 'lecturer_id' => 2, 'faculty_staff_id' => null,
             'title' => 'Chatbot hỗ trợ tư vấn tuyển sinh đại học',
             'description' => 'Chatbot NLP tự động tư vấn và trả lời câu hỏi về tuyển sinh',
             'technologies' => 'Python, Rasa, TensorFlow, FastAPI, MongoDB', 'is_available' => 1, 'is_bank_topic' => 1],

            ['expertise_id' => 4, 'lecturer_id' => 2, 'faculty_staff_id' => null,
             'title' => 'Phân tích và dự báo giá bất động sản',
             'description' => 'Mô hình ML dự báo giá nhà dựa trên vị trí, diện tích, tiện ích',
             'technologies' => 'Python, Scikit-learn, Pandas, Matplotlib, Jupyter', 'is_available' => 1, 'is_bank_topic' => 1],

            ['expertise_id' => 5, 'lecturer_id' => 4, 'faculty_staff_id' => null,
             'title' => 'Xây dựng hệ thống phát hiện xâm nhập mạng',
             'description' => 'IDS/IPS sử dụng ML phát hiện và cảnh báo tấn công mạng',
             'technologies' => 'Python, Snort, ELK Stack, Scikit-learn', 'is_available' => 1, 'is_bank_topic' => 1],

            ['expertise_id' => 6, 'lecturer_id' => 4, 'faculty_staff_id' => null,
             'title' => 'Triển khai microservices với Kubernetes và Docker',
             'description' => 'Xây dựng ứng dụng microservices trên Kubernetes với CI/CD tự động',
             'technologies' => 'Docker, Kubernetes, Jenkins, AWS, Terraform', 'is_available' => 1, 'is_bank_topic' => 1],

            ['expertise_id' => 7, 'lecturer_id' => 5, 'faculty_staff_id' => null,
             'title' => 'Hệ thống nhà thông minh điều khiển qua smartphone',
             'description' => 'Smart Home tích hợp cảm biến, điều khiển thiết bị điện từ xa',
             'technologies' => 'Arduino, Raspberry Pi, MQTT, React Native, Firebase', 'is_available' => 1, 'is_bank_topic' => 1],

            ['expertise_id' => 8, 'lecturer_id' => 5, 'faculty_staff_id' => null,
             'title' => 'Ứng dụng quản lý dự án theo Agile/Scrum',
             'description' => 'Hệ thống quản lý dự án hỗ trợ Scrum board, Sprint planning, burndown chart',
             'technologies' => 'React, Laravel, MySQL, WebSocket, Redis', 'is_available' => 1, 'is_bank_topic' => 1],
        ];

        foreach ($rows as $row) {
            DB::table('topics')->insertOrIgnore(array_merge($row, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
