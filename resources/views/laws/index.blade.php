@extends('layouts.public')

@section('title', 'Gesetzbuch')

@section('content')
<!-- Hero Section -->
<div class="hero-header bg-dark pt-5 pb-4">
    <div class="container text-center text-white">
        <h1 class="hero-title display-4"><i class="fas fa-balance-scale mr-3 text-warning"></i>Gesetzbuch</h1>
        <p class="hero-subtitle mt-2 text-muted">Die geltenden Rechtsvorschriften der Hansestadt Hamburg</p>
    </div>
</div>

<!-- Main content -->
<div class="content" style="background-color: #1f2937;"> <!-- Dunklerer Hintergrund für den Content-Bereich -->
    <div class="container">
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Navigation Tabs styled as Pills -->
                <div class="card card-dark card-outline card-primary mb-4 mt-n4"> <!-- mt-n4 zieht die Karte nach oben in den Hero-Bereich -->
                    <div class="card-header p-2 bg-dark"> <!-- Hintergrund des Headers auf Dunkel setzen -->
                        <ul class="nav nav-pills nav-fill" id="law-tabs" role="tablist">
                            @foreach($laws as $book => $entries)
                                <li class="nav-item">
                                    <!-- nav-link colors werden im Dark Mode automatisch besser dargestellt, 
                                         aber wir nutzen den Akzent primary für Active -->
                                    <a class="nav-link {{ $loop->first ? 'active bg-primary' : '' }}" 
                                        id="tab-{{ Str::slug($book) }}" 
                                        data-toggle="pill" 
                                        href="#content-{{ Str::slug($book) }}" 
                                        role="tab">
                                         <i class="fas fa-book-open mr-2"></i> {{ $book }}
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
                                    <span class="bg-primary">{{ $book }}</span>
                                </div>

                                @foreach($entries as $law)
                                    <div>
                                        <i class="fas fa-paragraph bg-warning"></i> <!-- Icon Farbe Akzent -->
                                        <div class="timeline-item shadow-sm">
                                            <!-- FIX: Hintergrund auf Dark Mode Standard setzen (card-header style) -->
                                            <h3 class="timeline-header bg-dark border-bottom-0 text-white" style="font-size: 1.1rem; font-weight: 600;">
                                                <span class="text-primary mr-2">{{ $law->paragraph }}</span> {{ $law->title }}
                                            </h3>
                                            <!-- FIX: Hintergrund für Body auf Dark Mode Standard setzen -->
                                            <div class="timeline-body text-justify bg-gray-dark" style="font-size: 1.05rem; line-height: 1.6; padding: 15px; border-radius: 0 0 5px 5px;">
                                                {!! nl2br(e($law->content)) !!}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                
                                <div>
                                    <i class="fas fa-gavel bg-primary"></i>
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