@extends('layouts.public')

@section('title', 'Gesetzbuch')

@section('content')
<!-- Hero Section -->
<div class="hero-header">
    <div class="container text-center">
        <h1 class="hero-title display-4"><i class="fas fa-balance-scale mr-3"></i>Gesetzbuch</h1>
        <p class="hero-subtitle mt-2">Die geltenden Rechtsvorschriften der Hansestadt Hamburg</p>
    </div>
</div>

<!-- Main content -->
<div class="content">
    <div class="container">
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Navigation Tabs styled as Pills, Akzentfarbe: Info (helles Blau) für guten Kontrast -->
                <div class="card card-outline card-info mb-4">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills nav-fill" id="law-tabs" role="tablist">
                            @foreach($laws as $book => $entries)
                                <li class="nav-item">
                                    <a class="nav-link {{ $loop->first ? 'active' : '' }}" 
                                        id="tab-{{ Str::slug($book) }}" 
                                        data-toggle="pill" 
                                        href="#content-{{ Str::slug($book) }}" 
                                        role="tab">
                                         <i class="fas fa-book-open mr-2 text-info"></i> {{ $book }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="tab-content" id="law-tabs-content">
                    @foreach($laws as $book => $entries)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                             id="content-{{ Str::slug($book) }}" 
                             role="tabpanel">
                            
                            <!-- Timeline Style for Laws -->
                            <div class="timeline">
                                <div class="time-label">
                                    <span class="bg-info">{{$entries->first()->book_label}} ({{ $book }})</span>
                                </div>

                                @foreach($entries as $law)
                                    <div>
                                        <!-- bg-secondary für Sichtbarkeit des Icons im Dark Mode -->
                                        <i class="fas fa-paragraph bg-secondary"></i>
                                        <div class="timeline-item shadow-sm">
                                            <h3 class="timeline-header border-bottom-0" style="font-size: 1.1rem; font-weight: 600;">
                                                <span class="text-info mr-2">{{ $law->paragraph }}</span> {{ $law->title }}
                                            </h3>
                                            <div class="timeline-body text-justify" style="font-size: 1.05rem; line-height: 1.6;">
                                                {!! nl2br(e($law->content)) !!}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                
                                <div>
                                    <i class="fas fa-gavel bg-info"></i>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
                
                <div class="text-center mt-5 mb-5 text-muted">
                    <small>Stand der Gesetzgebung: {{ date('d.m.Y') }} | Änderungen vorbehalten.</small>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection