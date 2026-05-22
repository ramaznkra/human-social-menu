@extends('layouts.admin')

@section('title', 'Canlı Siparişler')
@section('page_heading', 'Birleşik Operasyon Ekranı')

@section('content')
    @include('admin.live-orders._app', ['fullscreen' => false])
@endsection

@push('scripts')
@vite('resources/js/pages/live-orders.js')
@endpush
