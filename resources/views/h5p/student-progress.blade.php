<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Progress - {{ $student_id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Progress for Student: {{ $student_id }}</h1>
            <a href="{{ route('h5p.progress') }}" class="btn btn-secondary">Back to All</a>
        </div>
        
        @if($interactions->isEmpty())
            <div class="alert alert-info">
                No interactions recorded for this student.
            </div>
        @else
            @foreach($interactions as $interaction)
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Content: {{ $interaction->content_id }}</h5>
                        @if($interaction->completed)
                            <span class="badge bg-success">Completed</span>
                        @else
                            <span class="badge bg-warning">In Progress</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Score:</strong> 
                                    @if($interaction->score !== null)
                                        {{ $interaction->score }} / {{ $interaction->max_score }}
                                        ({{ $interaction->max_score > 0 ? round(($interaction->score / $interaction->max_score) * 100) : 0 }}%)
                                    @else
                                        N/A
                                    @endif
                                </p>
                                <p><strong>First Interaction:</strong> {{ $interaction->first_interaction_at->format('Y-m-d H:i:s') }}</p>
                                @if($interaction->completed_at)
                                    <p><strong>Completed At:</strong> {{ $interaction->completed_at->format('Y-m-d H:i:s') }}</p>
                                    <p><strong>Time Spent:</strong> 
                                        {{ $interaction->first_interaction_at->diffForHumans($interaction->completed_at, true) }}
                                    </p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <p><strong>Last Updated:</strong> {{ $interaction->updated_at->format('Y-m-d H:i:s') }}</p>
                            </div>
                        </div>
                        
                        @if($interaction->interaction_data)
                            <hr>
                            <h6>Interaction Data:</h6>
                            <pre class="bg-light p-3 rounded"><code>{{ json_encode($interaction->interaction_data, JSON_PRETTY_PRINT) }}</code></pre>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>