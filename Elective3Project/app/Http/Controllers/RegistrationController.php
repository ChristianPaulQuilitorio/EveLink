<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationController extends Controller
{
    public function index(Request $request): View|StreamedResponse
    {
        $selectedEventId = (int) $request->query('event_id');
        $search = $request->string('q')->toString();
        $eventSearch = $request->string('event_search')->toString();

        $eventsQuery = Event::query()
            ->withCount('registrations')
            ->orderBy('event_date');

        if ($eventSearch !== '') {
            $eventsQuery->where(function ($query) use ($eventSearch) {
                $query->where('event_name', 'like', '%' . $eventSearch . '%')
                    ->orWhere('venue', 'like', '%' . $eventSearch . '%');
            });
        }

        $events = $eventsQuery->get();

        $selectedEvent = $events->firstWhere('id', $selectedEventId) ?? $events->first();

        if (! $selectedEvent) {
            $registrations = Registration::query()->whereRaw('1 = 0')->paginate(15);

            return view('registrations.index', [
                'events' => $events,
                'selectedEventId' => 0,
                'selectedEvent' => null,
                'search' => $search,
                'eventSearch' => $eventSearch,
                'registrations' => $registrations,
                'capacityPercent' => 0,
            ]);
        }

        $selectedEventId = (int) $selectedEvent->id;

        $query = Registration::query()->with('event')->where('event_id', $selectedEventId)->latest();

        if ($search !== '') {
            $query->where(function ($inner) use ($search) {
                $inner->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('contact_number', 'like', '%' . $search . '%');
            });
        }

        if ($request->query('export') === 'xlsx') {
            $filename = 'registrations-event-' . $selectedEventId . '.xlsx';
            $event = Event::findOrFail($selectedEventId);

            if (! class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet', true)) {
                // Fallback: CSV if PhpSpreadsheet isn't installed
                $filenameCsv = 'registrations-event-' . $selectedEventId . '.csv';

                return response()->streamDownload(function () use ($selectedEventId, $search, $event) {
                    $handle = fopen('php://output', 'w');

                    fputcsv($handle, ['EveLink']);
                    fputcsv($handle, []);
                    fputcsv($handle, ['Event Details']);
                    fputcsv($handle, ['Event Name', $event->event_name]);
                    fputcsv($handle, ['Date', $event->event_date->format('F d, Y')]);
                    fputcsv($handle, ['Venue', $event->venue]);
                    fputcsv($handle, ['Status', $event->status]);
                    fputcsv($handle, ['Export Date', now()->format('F d, Y H:i:s')]);
                    fputcsv($handle, []);

                    fputcsv($handle, ['Participant Name', 'Email', 'Contact Number', 'Registration Status', 'Registered Date']);

                    Registration::query()
                        ->where('event_id', $selectedEventId)
                        ->when($search !== '', function ($exportQuery) use ($search) {
                            $exportQuery->where(function ($inner) use ($search) {
                                $inner->where('first_name', 'like', '%' . $search . '%')
                                    ->orWhere('last_name', 'like', '%' . $search . '%')
                                    ->orWhere('email', 'like', '%' . $search . '%')
                                    ->orWhere('contact_number', 'like', '%' . $search . '%');
                            });
                        })
                        ->orderByDesc('created_at')
                        ->get()
                        ->each(function ($registration) use ($handle) {
                            fputcsv($handle, [
                                $registration->full_name,
                                $registration->email,
                                $registration->contact_number,
                                $registration->attendance_status,
                                $registration->created_at->format('Y-m-d H:i:s'),
                            ]);
                        });

                    fclose($handle);
                }, $filenameCsv, [
                    'Content-Type' => 'text/csv',
                ]);
            }

            return response()->streamDownload(function () use ($selectedEventId, $search, $event) {
                try {
                    // Generate or convert a PNG icon if missing
                    $pngPath = public_path('favicon_export.png');
                    if (! file_exists($pngPath)) {
                        try {
                            $svgPath = public_path('favicon.svg');
                            if (class_exists('\\Imagick') && file_exists($svgPath)) {
                                try {
                                    $im = new \Imagick();
                                    $im->setBackgroundColor(new \ImagickPixel('transparent'));
                                    $im->readImage($svgPath);
                                    $im->setImageFormat('png32');
                                    $im->setImageResolution(300, 300);
                                    $im->resizeImage(512, 512, \Imagick::FILTER_LANCZOS, 1);
                                    $im->writeImage($pngPath);
                                    $im->clear();
                                    $im->destroy();
                                } catch (\Throwable $e) {
                                    // fall through to GD generation
                                }
                            }

                            if (! file_exists($pngPath) && function_exists('imagecreatetruecolor')) {
                                $w = 512; $h = 512;
                                $img = imagecreatetruecolor($w, $h);
                                if ($img) {
                                    $bg = sscanf('#2583f6', '#%02x%02x%02x');
                                    $bgColor = imagecolorallocate($img, $bg[0], $bg[1], $bg[2]);
                                    $white = imagecolorallocate($img, 255, 255, 255);
                                    imagefilledrectangle($img, 0, 0, $w, $h, $bgColor);
                                    $text = 'EL';
                                    $font = 5;
                                    $textW = imagefontwidth($font) * strlen($text);
                                    $textH = imagefontheight($font);
                                    imagestring($img, $font, (int)(($w - $textW) / 2), (int)(($h - $textH) / 2), $text, $white);
                                    imagepng($img, $pngPath);
                                    imagedestroy($img);
                                }
                            }
                        } catch (\Throwable $e) {
                            // Continue without PNG if generation fails
                        }
                    }

                    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();

                    // insert logo image
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
                    $sheet->setCellValue('B' . $row, $event->event_name);
                    $row++;
                    $sheet->setCellValue('A' . $row, 'Date');
                    try {
                        $sheet->setCellValue('B' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($event->event_date->toDateTime()));
                        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_LONG);
                    } catch (\Throwable $e) {
                        $sheet->setCellValue('B' . $row, $event->event_date->format('F d, Y'));
                    }
                    $row++;
                    $sheet->setCellValue('A' . $row, 'Venue');
                    $sheet->setCellValue('B' . $row, $event->venue);
                    $row++;
                    $sheet->setCellValue('A' . $row, 'Status');
                    $sheet->setCellValue('B' . $row, $event->status);
                    $row++;
                    $sheet->setCellValue('A' . $row, 'Export Date');
                    try {
                        $sheet->setCellValue('B' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(now()->toDateTime()));
                        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME);
                    } catch (\Throwable $e) {
                        $sheet->setCellValue('B' . $row, now()->format('F d, Y H:i:s'));
                    }

                    $row += 2;
                    $startHeader = $row;
                    $sheet->setCellValue('A' . $row, 'Participant Name');
                    $sheet->setCellValue('B' . $row, 'Email');
                    $sheet->setCellValue('C' . $row, 'Contact Number');
                    $sheet->setCellValue('D' . $row, 'Registration Status');
                    $sheet->setCellValue('E' . $row, 'Registered Date');

                    $row++;

                    Registration::query()
                        ->where('event_id', $selectedEventId)
                        ->when($search !== '', function ($exportQuery) use ($search) {
                            $exportQuery->where(function ($inner) use ($search) {
                                $inner->where('first_name', 'like', '%' . $search . '%')
                                    ->orWhere('last_name', 'like', '%' . $search . '%')
                                    ->orWhere('email', 'like', '%' . $search . '%')
                                    ->orWhere('contact_number', 'like', '%' . $search . '%');
                            });
                        })
                        ->orderByDesc('created_at')
                        ->chunk(100, function ($registrations) use (&$sheet, &$row) {
                            foreach ($registrations as $registration) {
                                $sheet->setCellValue('A' . $row, $registration->full_name);
                                $sheet->setCellValue('B' . $row, $registration->email);
                                $sheet->setCellValueExplicit('C' . $row, $registration->contact_number, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                                $sheet->setCellValue('D' . $row, $registration->attendance_status);
                                try {
                                    $sheet->setCellValue('E' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($registration->created_at->toDateTime()));
                                    $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME);
                                } catch (\Throwable $e) {
                                    $sheet->setCellValue('E' . $row, $registration->created_at->format('Y-m-d H:i:s'));
                                }
                                $row++;
                            }
                        });

                    // make header bold and auto-size columns
                    try {
                        $sheet->getStyle('A' . $startHeader . ':E' . $startHeader)->getFont()->setBold(true);
                        foreach (['A','B','C','D','E'] as $col) {
                            $sheet->getColumnDimension($col)->setAutoSize(true);
                        }
                    } catch (\Throwable $e) {
                    }

                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                    $writer->save('php://output');
                } catch (\Throwable $e) {
                    // If XLSX generation fails, fallback to CSV
                    $handle = fopen('php://output', 'w');
                    fputcsv($handle, ['EveLink']);
                    fputcsv($handle, []);
                    fputcsv($handle, ['Event Details']);
                    fputcsv($handle, ['Event Name', $event->event_name]);
                    fputcsv($handle, ['Date', $event->event_date->format('F d, Y')]);
                    fputcsv($handle, ['Venue', $event->venue]);
                    fputcsv($handle, ['Status', $event->status]);
                    fputcsv($handle, ['Export Date', now()->format('F d, Y H:i:s')]);
                    fputcsv($handle, []);
                    fputcsv($handle, ['Participant Name', 'Email', 'Contact Number', 'Registration Status', 'Registered Date']);
                    
                    Registration::query()
                        ->where('event_id', $selectedEventId)
                        ->when($search !== '', function ($exportQuery) use ($search) {
                            $exportQuery->where(function ($inner) use ($search) {
                                $inner->where('first_name', 'like', '%' . $search . '%')
                                    ->orWhere('last_name', 'like', '%' . $search . '%')
                                    ->orWhere('email', 'like', '%' . $search . '%')
                                    ->orWhere('contact_number', 'like', '%' . $search . '%');
                            });
                        })
                        ->orderByDesc('created_at')
                        ->chunk(100, function ($registrations) use ($handle) {
                            foreach ($registrations as $registration) {
                                fputcsv($handle, [
                                    $registration->full_name,
                                    $registration->email,
                                    $registration->contact_number,
                                    $registration->attendance_status,
                                    $registration->created_at->format('Y-m-d H:i:s'),
                                ]);
                            }
                        });
                    fclose($handle);
                }
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        $registrations = $query->paginate(8)->withQueryString();

        $capacityPercent = $selectedEvent->max_slots > 0
            ? (int) min(100, round(($selectedEvent->registered_count / $selectedEvent->max_slots) * 100))
            : 0;

        return view('registrations.index', [
            'events' => $events,
            'selectedEventId' => $selectedEventId,
            'selectedEvent' => $selectedEvent,
            'search' => $search,
            'eventSearch' => $eventSearch,
            'registrations' => $registrations,
            'capacityPercent' => $capacityPercent,
        ]);
    }

    public function create(): View
    {
        $events = Event::query()->withCount('registrations')->orderBy('event_date')->get();

        return view('registrations.create', compact('events'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:100'],
            'contact_number' => ['required', 'regex:/^[0-9]{11}$/'],
        ]);

        $event = Event::query()->withCount('registrations')->findOrFail($validated['event_id']);

        if (! $event->canAcceptRegistration()) {
            return back()->withInput()->withErrors([
                'event_id' => 'Registration closed. This event is full or already concluded.',
            ]);
        }

        $duplicate = Registration::query()
            ->where('event_id', $event->id)
            ->where('email', $validated['email'])
            ->exists();

        if ($duplicate) {
            return back()->withInput()->withErrors([
                'email' => 'This attendee email is already registered for the selected event.',
            ]);
        }

        Registration::create($validated + ['attendance_status' => 'Pending']);

        if ($request->input('dashboard_register') === '1') {
            return redirect()->route('dashboard')
                ->with('success', 'Attendee registered successfully.');
        }

        return redirect()->route('registrations.index', ['event_id' => $event->id])
            ->with('success', 'Attendee registered successfully.');
    }

    public function edit(Registration $registration): View
    {
        $events = Event::query()->withCount('registrations')->orderBy('event_date')->get();

        return view('registrations.edit', [
            'registration' => $registration,
            'events' => $events,
        ]);
    }

    public function update(Request $request, Registration $registration): RedirectResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('registrations')->where(fn ($query) => $query
                    ->where('event_id', (int) $request->input('event_id'))
                )->ignore($registration->id),
            ],
            'contact_number' => ['required', 'regex:/^[0-9]{11}$/'],
        ]);

        $event = Event::query()->withCount('registrations')->findOrFail($validated['event_id']);

        if (! $event->canAcceptRegistration() && $registration->event_id !== $event->id) {
            return back()->withInput()->withErrors([
                'event_id' => 'Cannot move attendee. Target event is full or already concluded.',
            ]);
        }

        $registration->update($validated);

        return redirect()->route('registrations.index', ['event_id' => $registration->event_id])
            ->with('success', 'Registration updated successfully.');
    }

    public function destroy(Registration $registration): RedirectResponse
    {
        $eventId = $registration->event_id;
        $registration->delete();

        return redirect()->route('registrations.index', ['event_id' => $eventId])
            ->with('success', 'Registrant removed successfully.');
    }
}