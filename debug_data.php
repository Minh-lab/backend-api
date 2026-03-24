<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Semester;
use App\Models\Milestone;

$now = now();
echo "Current Time: " . $now . "\n";

$currentSemester = Semester::whereDate('start_date', '<=', $now)
    ->whereDate('end_date', '>=', $now)
    ->first();

echo "Current Semester (by date): " . ($currentSemester ? $currentSemester->semester_id . " (" . $currentSemester->semester_name . ")" : "NONE") . "\n";

$latestSemester = Semester::orderByDesc('start_date')->first();
echo "Latest Semester: " . ($latestSemester ? $latestSemester->semester_id . " (" . $latestSemester->semester_name . ")" : "NONE") . "\n";

echo "\nInternship Milestones:\n";
$milestones = Milestone::where('type', 'INTERNSHIP')->get();
foreach ($milestones as $m) {
    $isOpen = ($now >= $m->start_date && $now <= $m->end_date) ? "OPEN" : "CLOSED";
    echo "ID: {$m->milestone_id}, Name: {$m->phase_name}, Sem: {$m->semester_id}, Start: {$m->start_date}, End: {$m->end_date}, Status: {$isOpen}\n";
}
