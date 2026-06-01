@extends('layouts.admin')
@section('title', 'Garsonlar')
@section('page_heading', 'Garson Hesapları')
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl font-semibold text-gray-800">Garsonlar</h2>
        <p class="mt-1 text-sm text-gray-500">Her garson kendi e-posta ve şifresiyle <strong>/admin/giris</strong> üzerinden garson paneline (PWA) girer.</p>
    </div>
    <a href="{{ route('admin.waiters.create') }}" class="btn btn-primary">+ Yeni Garson</a>
</div>

@if($waiters->isEmpty())
<div class="admin-card py-12 text-center text-gray-500">
    Henüz garson hesabı yok.
    <a href="{{ route('admin.waiters.create') }}" class="font-medium text-[#E67E22] hover:underline">İlk garsonu ekleyin</a>.
</div>
@else
<div class="admin-card overflow-hidden p-0">
    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-gray-100 bg-gray-50/80 text-xs uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-5 py-3 font-semibold">Ad</th>
                    <th class="px-5 py-3 font-semibold">E-posta</th>
                    <th class="px-5 py-3 font-semibold">Durum</th>
                    <th class="px-5 py-3 font-semibold text-right">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($waiters as $waiter)
                <tr
                    class="transition {{ $waiter->is_active ? '' : 'bg-gray-50/60' }}"
                    data-waiter-item
                    data-waiter-id="{{ $waiter->id }}"
                >
                    <td class="px-5 py-4">
                        <p class="font-medium text-gray-800">{{ $waiter->name }}</p>
                        <p class="text-xs text-gray-400">Garson · ID #{{ $waiter->id }}</p>
                    </td>
                    <td class="px-5 py-4 text-gray-600">{{ $waiter->email }}</td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <label class="relative inline-flex shrink-0 cursor-pointer items-center" title="Hesabı aç / kapat">
                                <input
                                    type="checkbox"
                                    class="peer sr-only"
                                    data-waiter-toggle
                                    data-toggle-url="{{ route('admin.waiters.toggle-active', $waiter) }}"
                                    {{ $waiter->is_active ? 'checked' : '' }}
                                    aria-label="{{ $waiter->name }} aktif"
                                >
                                <span class="relative h-6 w-11 shrink-0 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-emerald-500 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-500/30"></span>
                            </label>
                            <span
                                class="text-xs font-medium {{ $waiter->is_active ? 'text-emerald-600' : 'text-gray-400' }}"
                                data-waiter-status-label
                            >{{ $waiter->is_active ? 'Aktif' : 'Pasif' }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.waiters.edit', $waiter) }}" class="btn btn-sm btn-secondary">Düzenle</a>
                            <form action="{{ route('admin.waiters.destroy', $waiter) }}" method="POST" onsubmit="return confirm('Bu garson hesabı silinsin mi?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-secondary text-red-600 hover:border-red-200 hover:bg-red-50">Sil</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection

@push('scripts')
@vite('resources/js/pages/admin-waiters.js')
@endpush
