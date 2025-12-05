@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="home-page">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="hero-title">
                        Welcome To<br>
                        <span class="brand-text-italic">bingkis</span><span class="brand-kaca-bold">kaca.</span>
                    </h1>
                    <p class="hero-description">
                        You know that <span class="highlight">feeling</span> after a <span class="highlight">photobox</span> session?<br>
                        Cute pict, real laugh, and <span class="highlight">memories</span> you wish<br>
                        could stay a little longer
                    </p>
                </div>
                <div class="hero-image">
                    <img src="{{ asset('images/hero-character.png') }}" alt="Bingkis Kaca Character">
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-image">
                    <img src="{{ asset('images/group-photo.jpg') }}" alt="Group Photo">
                </div>
                <div class="about-text">
                    <h2 class="section-title">
                        Capture your moment<br>
                        with BingkisKaca 📸
                    </h2>
                    <p class="section-description">
                        <strong>Bingkis Kaca</strong> is a creative Photobox and 
                        photobooth service that captures special moments with aesthetic, 
                        custom designed setups, we deliver instant high quality prints 
                        and unique props to make every memory unforgettable.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <div class="cta-characters">
                    <img src="{{ asset('images/character-left.png') }}" alt="Character">
                    <img src="{{ asset('images/character-right.png') }}" alt="Character">
                </div>
                <div class="cta-text">
                    <h2 class="cta-title">Snap. Pose. Save.📸</h2>
                    <p class="cta-description">
                        Forget long lines and pricey booths.<br>
                        <strong>Bingkis Kaca</strong> lets you take cute, creative 
                        photos instantly online. Choose a frame,<br>
                        set the timer, and click away with<br>
                        zero pressure.
                    </p>
                    <a href="{{ route('photobooth') }}" class="btn-primary">Take a Picture !</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection