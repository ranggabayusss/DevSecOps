<?php

namespace App\Support;

use App\Models\Event;
use Illuminate\Support\Str;

class AttendanceExcelExporter
{
    public function make(Event $event): array
    {
        $rows = [[
            'No. Urut',
            'Nomor Anggota',
            'Nama Lengkap',
            'Jabatan/Role',
            'Nama Motor',
            'Plat Nomor',
            'Status Kehadiran',
            'Waktu Konfirmasi',
        ]];

        $number = 1;

        $rsvps = $event->rsvps()
            ->with('user')
            ->orderBy('confirmed_at')
            ->orderBy('id')
            ->get();

        foreach ($rsvps as $rsvp) {
            $user = $rsvp->user;

            $rows[] = [
                $number++,
                SpreadsheetValueSanitizer::sanitize($user?->member_number ?? '-'),
                SpreadsheetValueSanitizer::sanitize($user?->name ?? 'Anggota tidak tersedia'),
                SpreadsheetValueSanitizer::sanitize($user ? ucfirst($user->role) : '-'),
                SpreadsheetValueSanitizer::sanitize($user?->motorcycle_type ?? '-'),
                SpreadsheetValueSanitizer::sanitize($user?->license_plate ?? '-'),
                SpreadsheetValueSanitizer::sanitize($rsvp->statusLabel()),
                SpreadsheetValueSanitizer::sanitize($rsvp->confirmed_at?->format('Y-m-d H:i:s') ?? '-'),
            ];
        }

        return [
            'filename' => $this->filename($event),
            'content' => (new XlsxWriter($rows, 'Kehadiran'))->toString(),
        ];
    }

    public function filename(Event $event): string
    {
        $slug = Str::ascii((string) $event->title);
        $slug = preg_replace('/[^A-Za-z0-9]+/', '_', $slug) ?? '';
        $slug = trim(substr($slug, 0, 80), '_');

        if ($slug === '') {
            $slug = 'Acara';
        }

        return sprintf(
            'Kehadiran_%s_%s.xlsx',
            $slug,
            $event->starts_at?->format('Y-m-d') ?? now()->format('Y-m-d')
        );
    }
}
