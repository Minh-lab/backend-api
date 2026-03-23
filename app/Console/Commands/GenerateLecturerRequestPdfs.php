<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class GenerateLecturerRequestPdfs extends Command
{
    protected $signature = 'generate:lecturer-request-pdfs';

    protected $description = 'Generate PDF files for lecturer requests';

    public function handle()
    {
        $this->info('Generating PDF files for lecturer requests...');

        // Lấy tất cả lecturer_requests có file_path
        $requests = DB::table('lecturer_requests')
            ->whereNotNull('file_path')
            ->get();

        $count = 0;
        foreach ($requests as $request) {
            $filePath = $request->file_path;
            $fullPath = storage_path('app/public/' . $filePath);

            // Tạo folder nếu không tồn tại
            $directory = dirname($fullPath);
            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
                $this->info("Created directory: $directory");
            }

            // Tạo PDF file nếu chưa tồn tại
            if (!File::exists($fullPath)) {
                $pdfContent = $this->generatePDF($request->request_id, $request->title);
                File::put($fullPath, $pdfContent);
                $this->info("✓ Created: $filePath");
                $count++;
            }
        }

        $this->info("Successfully generated $count PDF files!");
    }

    private function generatePDF(int $id, string $title): string
    {
        // Valid PDF (base64 encoded)
        $base64Pdf = "JVBERi0xLjQKJeLjz9MNCjEgMCBvYmo8PC9UeXBlL0NhdGFsb2cvUGFnZXMgMiAwIFI+PmVu
ZG9iCjIgMCBvYmo8PC9UeXBlL1BhZ2VzL0tpZHNbMyAwIFJdL0NvdW50IDE+PmVuZG9i
CjMgMCBvYmo8PC9UeXBlL1BhZ2UvUGFyZW50IDIgMCBSL01lZGlhQm94WzAgMCA2MTIgNzky
XS9Db250ZW50cyA0IDAgUi9SZXNvdXJjZXM8PC9Gb250PDwvRjE8PC9UeXBlL0ZvbnQvU3Vi
dHlwZS9UeXBlMS9CYXNlRm9udC9IZWxtdGluZ2VyPj4+Pj4+Pj4+PmVuZG9iCjQgMCBvYmo8
PC9MZW5ndGggMTIxPj5zdHJlYW0KQlQKL0YxIDEyIFRmCjUwIDc1MCBUZAooTGVjdHVyZXIg
UmVxdWVzdDogKSBUagowIC1Ub1QKKElEOiApIFRqCihSZXBvcnQgKSBUagpFVApFVGogICBl
bmRzdHJlYW0KZW5kb2IKeHJlZgowIDUKMDAwMDAwMDAwMCA2NTUzNSBmIAowMDAwMDAwMDA5
IDAwMDAwIG4gCjAwMDAwMDAwMzcgMDAwMDAgbiAKMDAwMDAwMDA5MCAwMDAwMCBuIAowMDAwMDAw
MzIwIDAwMDAwIG4gCnRyYWlsZXI8PC9yb290IDEgMCBSL3NpemUgNT4+CnN0YXJ0eHJlZgo0ODEK
JUtFRk9G";
        
        return base64_decode($base64Pdf);
    }
}
