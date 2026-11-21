@extends('layouts.app')

@section('title', 'Permintaan Bergabung')

@section('content')
<x-page-header
    title="Permintaan Bergabung"
    :description="'Komunitas '.$community->name.' · Ketua '.($community->leader->name ?? '-')"
/>

@if (session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif

@if (session('error'))
    <x-alert type="error">{{ session('error') }}</x-alert>
@endif

@if ($requests->isEmpty())
    <x-empty-state
        title="Belum ada permintaan"
        description="Permintaan bergabung dari calon anggota akan muncul di sini."
    />
@else
    <div class="overflow-x-auto rounded-xl border border-gray-800 bg-gray-900">
        <table class="w-full text-left">
            <thead class="border-b border-gray-800 bg-gray-900/60 text-xs uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-4 py-3 font-medium">No.</th>
                    <th class="px-4 py-3 font-medium">Nomor Anggota</th>
                    <th class="px-4 py-3 font-medium">Nama</th>
                    <th class="px-4 py-3 font-medium">Email</th>
                    <th class="px-4 py-3 font-medium">Nomor HP</th>
                    <th class="px-4 py-3 font-medium">Motor</th>
                    <th class="px-4 py-3 font-medium">Plat Nomor</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 text-right font-medium">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-800">
                @foreach ($requests as $index => $request)
                    <tr class="hover:bg-gray-800/40">
                        <td class="px-4 py-3.5 text-sm text-gray-500">
                            {{ $requests->firstItem() + $index }}
                        </td>

                        <td class="px-4 py-3.5 text-sm font-medium text-orange-300">
                            {{ $request->user->member_number ?? '-' }}
                        </td>

                        <td class="px-4 py-3.5 text-sm font-medium text-white">
                            {{ $request->user->name }}
                        </td>

                        <td class="px-4 py-3.5 text-sm text-gray-400">
                            {{ $request->user->email }}
                        </td>

                        <td class="px-4 py-3.5 text-sm text-gray-400">
                            {{ $request->user->phone ?? '-' }}
                        </td>

                        <td class="px-4 py-3.5 text-sm text-gray-400">
                            {{ $request->user->motorcycle_type ?? '-' }}
                        </td>

                        <td class="px-4 py-3.5 text-sm text-gray-400">
                            {{ $request->user->license_plate ?? '-' }}
                        </td>

                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center rounded-full border border-amber-500/30 bg-amber-500/10 px-2.5 py-0.5 text-xs font-medium text-amber-300">
                                Menunggu
                            </span>
                        </td>

                        <td class="px-4 py-3.5">
                            <div class="flex justify-end gap-2">
                                <form action="{{ route('ketua.communities.requests.approve', [$community->id, $request->id]) }}"
                                      method="POST"
                                      data-confirm="Setujui permintaan bergabung ini?">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-300 transition hover:bg-emerald-500/20">
                                        Setujui
                                    </button>
                                </form>

                                <form action="{{ route('ketua.communities.requests.reject', [$community->id, $request->id]) }}"
                                      method="POST"
                                      data-confirm="Tolak permintaan bergabung ini?">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="rounded-lg border border-red-500/30 bg-red-500/10 px-3 py-1.5 text-xs font-semibold text-red-300 transition hover:bg-red-500/20">
                                        Tolak
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $requests->links() }}</div>
@endif
@endsection
