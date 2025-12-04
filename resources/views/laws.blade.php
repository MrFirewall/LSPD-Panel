@extends('layouts.app')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Gesetzbuch der Hansestadt Hamburg</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Gesetze</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline card-outline-tabs">
                    <div class="card-header p-0 border-bottom-0">
                        <ul class="nav nav-tabs" id="law-tabs" role="tablist">
                            @foreach($laws as $book => $entries)
                                <li class="nav-item">
                                    <a class="nav-link {{ $loop->first ? 'active' : '' }}" 
                                       id="tab-{{ Str::slug($book) }}" 
                                       data-toggle="pill" 
                                       href="#content-{{ Str::slug($book) }}" 
                                       role="tab">
                                        {{ $book }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="law-tabs-content">
                            @foreach($laws as $book => $entries)
                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                                     id="content-{{ Str::slug($book) }}" 
                                     role="tabpanel">
                                    
                                    <div class="timeline">
                                        @foreach($entries as $law)
                                            <div>
                                                <i class="fas fa-book bg-blue"></i>
                                                <div class="timeline-item">
                                                    <h3 class="timeline-header">
                                                        <a href="#">{{ $law->paragraph }}</a> {{ $law->title }}
                                                    </h3>
                                                    <div class="timeline-body">
                                                        {{ $law->content }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        <div>
                                            <i class="fas fa-clock bg-gray"></i>
                                        </div>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </div>
    </div>
</section>
@endsection