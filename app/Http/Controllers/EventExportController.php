<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Support\ActivityLogger;
use App\Support\AttendanceExcelExporter;
use Illuminate\Http\Request;

class EventExportController extends Controller
{
    public function download(
        Request $request,
        Event $event,
        AttendanceExcelExporter $exporter
    ) {
        $this->authorize('export', $event);

        $file = $exporter->make($event);

        ActivityLogger::record(
            'event.attendance.exported',
            'Daftar hadir acara ID '.$event->id.' "'.$event->title.'" diunduh sebagai Excel.',
            (int) $request->user()->id
        );

        return response($file['content'], 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$file['filename'].'"',
            'Cache-Control' => 'no-store, private',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
