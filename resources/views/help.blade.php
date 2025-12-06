@extends('layouts.app')

@section('title', 'Help')

@section('content')
<div class="help-page">
    <div class="container">
        <h1 class="page-title">Bagaimana Cara Mendapatkan Fitur Photo Booth?</h1>

        <div class="help-grid">
            <!-- Step 1 -->
            <div class="help-card">
                <div class="help-card-header">
                    <div class="help-icon">
                        <img src="{{ asset('images/icon-camera.png') }}" alt="Camera">
                    </div>
                </div>
                <div class="help-card-body">
                    <div class="help-screenshot">
                        <img src="{{ asset('images/help-step1.png') }}" alt="Ambil Foto">
                    </div>
                    <h3 class="help-card-title">Ambil Foto</h3>
                    <p class="help-card-text">
                        Pilih filter, tekan "Mulai," dan keseruan akan dimulai! 
                        Timer hanya memberi 3 detik untuk berpose per foto dan akan 
                        mengambil foto secara berurutan.
                    </p>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="help-card">
                <div class="help-card-header">
                    <div class="help-icon">
                        <img src="{{ asset('images/icon-frame.png') }}" alt="Frame">
                    </div>
                </div>
                <div class="help-card-body">
                    <div class="help-screenshot">
                        <img src="{{ asset('images/help-step2.png') }}" alt="Tambahkan Frame">
                    </div>
                    <h3 class="help-card-title">Tambahkan Frame</h3>
                    <p class="help-card-text">
                        Setelah mengambil foto, pilih warna frame yang 
                        lucu-lucu untuk membuat foto jadi lebih estetik dan 
                        meriah.
                    </p>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="help-card">
                <div class="help-card-header">
                    <div class="help-icon">
                        <img src="{{ asset('images/icon-download.png') }}" alt="Download">
                    </div>
                </div>
                <div class="help-card-body">
                    <div class="help-screenshot">
                        <img src="{{ asset('images/help-step3.png') }}" alt="Unduh Foto">
                    </div>
                    <h3 class="help-card-title">Unduh Foto Mu !</h3>
                    <p class="help-card-text">
                        Setelah selesai, klik "Unduh". Foto-foto tersebut siap 
                        untuk dibagikan di mana saja dengan siapa saja.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Karakter Kiri - di pojok kiri atas card pertama */
.help-card:first-child::before {
    background-image: url('{{ asset("images/character-left-head.png") }}') !important;
}

/* Karakter Kanan - di pojok kanan atas card terakhir */
.help-card:last-child::after {
    background-image: url('{{ asset("images/character-right-head.png") }}') !important;
}
</style>
@endsection