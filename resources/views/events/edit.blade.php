@extends('layouts.app')

@section('title', 'Edit Acara')

@section('content')
<x-page-header
    title="Edit Acara / Pengumuman"
    :description="'Perbarui informasi acara '.$event->title"
/>

@include('events._form', [
    'action' => route('events.update', $event),
    'method' => 'PUT',
    'submitLabel' => 'Simpan Perubahan',
    'event' => $event,
])
@endsection
