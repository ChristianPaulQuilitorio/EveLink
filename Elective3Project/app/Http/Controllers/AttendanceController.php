<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function index(Request $request): View|StreamedResponse
    {
        $selectedEventId = (int) $request->query('event_id');
        $search = $request->string('q')->toString();
        $status = $request->string('status')->toString();
        $eventSearch = $request->string('event_search')->toString();
        $eventStatus = $request->string('event_status')->toString();
        $today = now()->toDateString();

        $eventsQuery = Event::query()
            ->withCount('registrations')
            ->orderBy('event_date');

        if ($eventSearch !== '') {
            $eventsQuery->where(function ($query) use ($eventSearch) {
                $query->where('event_name', 'like', '%' . $eventSearch . '%')
                    ->orWhere('venue', 'like', '%' . $eventSearch . '%');
            });
        }

        if ($eventStatus !== '') {
            if (strcasecmp($eventStatus, 'Open') === 0) {
                $eventsQuery
                    ->whereDate('event_date', '>=', $today)
                    ->whereRaw('(select count(*) from registrations where registrations.event_id = events.id) < events.max_slots');
            } elseif (strcasecmp($eventStatus, 'Full') === 0) {
                $eventsQuery
                    ->whereDate('event_date', '>=', $today)
                    ->whereRaw('(select count(*) from registrations where registrations.event_id = events.id) >= events.max_slots');
            } elseif (strcasecmp($eventStatus, 'Concluded') === 0) {
                $eventsQuery->whereDate('event_date', '<', $today);
            } elseif (strcasecmp($eventStatus, 'Upcoming') === 0) {
                $eventsQuery->whereDate('event_date', '>=', $today);
            }
        }

        $events = $eventsQuery->get();

        $selectedEvent = $events->firstWhere('id', $selectedEventId) ?? $events->first();

        if (! $selectedEvent) {
            $registrations = Registration::query()->whereRaw('1 = 0')->paginate(15);

            return view('attendance.index', [
                'events' => $events,
                'selectedEventId' => 0,
                'selectedEvent' => null,
                'search' => $search,
                'eventSearch' => $eventSearch,
                'eventStatus' => $eventStatus,
                'status' => $status,
                'registrations' => $registrations,
                'summary' => ['total' => 0, 'Present' => 0, 'Absent' => 0, 'Pending' => 0],
            ]);
        }

        $selectedEventId = (int) $selectedEvent->id;

        if ($request->query('export') === 'xlsx') {
            $filename = 'attendance-event-' . $selectedEventId . '.xlsx';

            if (! class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet', true)) {
                $filenameCsv = 'attendance-event-' . $selectedEventId . '.csv';

                return response()->streamDownload(function () use ($selectedEventId, $selectedEvent) {
                    $handle = fopen('php://output', 'w');

                    $this->writeAttendanceCsv($handle, $selectedEventId, $selectedEvent);

                    fclose($handle);
                }, $filenameCsv, [
                    'Content-Type' => 'text/csv',
                ]);
            }

            return response()->streamDownload(function () use ($selectedEventId, $selectedEvent) {
                try {
                    $pngPath = public_path('favicon_export.png');

                    if (! file_exists($pngPath)) {
                        $this->createExportBadge($pngPath);
                    }

                    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();

                    if (file_exists($pngPath)) {
                        try {
                            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                            $drawing->setName('EL');
                            $drawing->setDescription('EL');
                            $drawing->setPath($pngPath);
                            $drawing->setHeight(72);
                            $drawing->setCoordinates('A1');
                            $drawing->setWorksheet($sheet);
                        } catch (\Throwable $e) {
                        }
                    }

                    $row = 4;
                    $sheet->setCellValue('A' . $row, 'Event Name');
                    $sheet->setCellValue('B' . $row, $selectedEvent->event_name);
                    $row++;
                    $sheet->setCellValue('A' . $row, 'Date');
                    $sheet->setCellValue('B' . $row, $this->formatDateValue($selectedEvent->event_date));
                    $row++;
                    $sheet->setCellValue('A' . $row, 'Venue');
                    $sheet->setCellValue('B' . $row, $selectedEvent->venue);
                    $row++;
                    $sheet->setCellValue('A' . $row, 'Status');
                    $sheet->setCellValue('B' . $row, $selectedEvent->status);
                    $row++;
                    $sheet->setCellValue('A' . $row, 'Export Date');
                    $sheet->setCellValue('B' . $row, now()->format('F d, Y H:i:s'));

                    $row += 2;
                    $headerRow = $row;
                    $sheet->setCellValue('A' . $row, 'Participant Name');
                    $sheet->setCellValue('B' . $row, 'Contact Number');
                    $sheet->setCellValue('C' . $row, 'Email');
                    $sheet->setCellValue('D' . $row, 'Attendance Status');
                    $row++;

                    Registration::query()
                        ->where('event_id', $selectedEventId)
                        ->orderBy('last_name')
                        ->orderBy('first_name')
                        ->chunk(100, function ($registrations) use (&$sheet, &$row) {
                            foreach ($registrations as $registration) {
                                $sheet->setCellValue('A' . $row, $registration->full_name);
                                $sheet->setCellValueExplicit('B' . $row, $registration->contact_number, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                                $sheet->setCellValue('C' . $row, $registration->email);
                                $sheet->setCellValue('D' . $row, $registration->attendance_status);
                                $row++;
                            }
                        });

                    try {
                        $sheet->getStyle('A' . $headerRow . ':D' . $headerRow)->getFont()->setBold(true);
                        foreach (['A','B','C','D'] as $col) {
                            $sheet->getColumnDimension($col)->setAutoSize(true);
                        }
                    } catch (\Throwable $e) {
                    }

                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                    $writer->save('php://output');
                } catch (\Throwable $e) {
                    $handle = fopen('php://output', 'w');
                    $this->writeAttendanceCsv($handle, $selectedEventId, $selectedEvent);
                    fclose($handle);
                }
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        $registrationsQuery = Registration::query()
            ->where('event_id', $selectedEventId)
            ->orderBy('last_name')
            ->orderBy('first_name');

        if ($search !== '') {
            $normalizedSearch = mb_strtolower(trim($search));
            $likeSearch = '%' . $normalizedSearch . '%';

            $registrationsQuery->where(function ($query) use ($likeSearch) {
                $query->whereRaw('LOWER(first_name) LIKE ?', [$likeSearch])
                    ->orWhereRaw('LOWER(last_name) LIKE ?', [$likeSearch])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$likeSearch])
                    ->orWhereRaw('LOWER(contact_number) LIKE ?', [$likeSearch])
                    ->orWhereRaw("LOWER(concat(coalesce(first_name, ''), ' ', coalesce(last_name, ''))) LIKE ?", [$likeSearch])
                    ->orWhereRaw("LOWER(concat(coalesce(last_name, ''), ' ', coalesce(first_name, ''))) LIKE ?", [$likeSearch]);
            });
        }

        if (in_array($status, ['Present', 'Absent', 'Pending'], true)) {
            $registrationsQuery->where('attendance_status', $status);
        }

        $registrations = $registrationsQuery->paginate(15)->withQueryString();

        $summary = ['total' => 0, 'Present' => 0, 'Absent' => 0, 'Pending' => 0];

        if ($selectedEvent) {
            $statusCounts = Registration::query()
                ->select('attendance_status', DB::raw('COUNT(*) as aggregate'))
                ->where('event_id', $selectedEvent->id)
                ->groupBy('attendance_status')
                ->pluck('aggregate', 'attendance_status')
                ->all();

            $summary = [
                'total' => array_sum($statusCounts),
                'Present' => (int) ($statusCounts['Present'] ?? 0),
                'Absent' => (int) ($statusCounts['Absent'] ?? 0),
                'Pending' => (int) ($statusCounts['Pending'] ?? 0),
            ];
        }

        return view('attendance.index', [
            'events' => $events,
            'selectedEventId' => $selectedEventId,
            'selectedEvent' => $selectedEvent,
            'registrations' => $registrations,
            'summary' => $summary,
            'search' => $search,
            'status' => $status,
            'eventSearch' => $eventSearch,
            'eventStatus' => $eventStatus,
        ]);
    }

    public function update(Request $request, Registration $registration): RedirectResponse
    {
        $validated = $request->validate([
            'attendance_status' => ['required', 'in:Pending,Present,Absent'],
        ]);

        $registration->update([
            'attendance_status' => $validated['attendance_status'],
            'present_at' => $validated['attendance_status'] === 'Present' ? now() : null,
        ]);

        return back()->with('success', 'Attendance updated.');
    }

    protected function writeAttendanceCsv($handle, int $selectedEventId, Event $selectedEvent): void
    {
        fputcsv($handle, ['EveLink']);
        fputcsv($handle, []);
        fputcsv($handle, ['Event Details']);
        fputcsv($handle, ['Event Name', $selectedEvent->event_name]);
        fputcsv($handle, ['Date', $this->formatDateLabel($selectedEvent->event_date)]);
        fputcsv($handle, ['Venue', $selectedEvent->venue]);
        fputcsv($handle, ['Status', $selectedEvent->status]);
        fputcsv($handle, ['Export Date', now()->format('F d, Y H:i:s')]);
        fputcsv($handle, []);

        fputcsv($handle, ['Participant Name', 'Contact Number', 'Email', 'Attendance Status']);

        Registration::query()
            ->where('event_id', $selectedEventId)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->chunk(100, function ($registrations) use ($handle) {
                foreach ($registrations as $registration) {
                    fputcsv($handle, [
                        $registration->full_name,
                        $registration->contact_number,
                        $registration->email,
                        $registration->attendance_status,
                    ]);
                }
            });
    }

    protected function createExportBadge(string $pngPath): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            return;
        }

        $width = 512;
        $height = 512;
        $image = imagecreatetruecolor($width, $height);

        if (! $image) {
            return;
        }

        $background = sscanf('#2583f6', '#%02x%02x%02x');
        $backgroundColor = imagecolorallocate($image, $background[0], $background[1], $background[2]);
        $white = imagecolorallocate($image, 255, 255, 255);

        imagefilledrectangle($image, 0, 0, $width, $height, $backgroundColor);

        $text = 'EL';
        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);

        imagestring($image, $font, (int) (($width - $textWidth) / 2), (int) (($height - $textHeight) / 2), $text, $white);
        imagepng($image, $pngPath);
        imagedestroy($image);
    }

    protected function formatDateLabel(DateTimeInterface|string|null $date): string
    {
        if ($date instanceof DateTimeInterface) {
            return $date->format('F d, Y');
        }

        if (blank($date)) {
            return '';
        }

        return DateTimeImmutable::createFromFormat('Y-m-d', (string) $date)?->format('F d, Y') ?? (string) $date;
    }

    protected function formatDateValue(DateTimeInterface|string|null $date): string
    {
        return $this->formatDateLabel($date);
    }
}