@extends('layouts.app')

@section('title', 'Buat Acara')

@section('content')
<x-page-header
    title="Buat Acara / Pengumuman"
    description="Publikasikan jadwal kegiatan untuk seluruh anggota komunitas."
/>

@include('events._form', [
    'action' => route('events.store'),
    'method' => 'POST',
    'submitLabel' => 'Publikasikan Acara',
    'event' => null,
])
@endsection
