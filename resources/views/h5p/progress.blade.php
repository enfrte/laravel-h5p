<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H5P Student Progress</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">H5P Course Progress - All Students</h1>
        
        @if($interactions->isEmpty())
            <div class="alert alert-info">
                No interactions recorded yet.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Student ID</th>
                            <th>Content ID</th>
                            <th>Score</th>
                            <th>Status</th>
                            <th>First Interaction</th>
                            <th>Completed At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($interactions as $interaction)
                            <tr>
                                <td>{{ $interaction->student_id }}</td>
                                <td>{{ $interaction->content_id }}</td>
                                <td>
                                    @if($interaction->score !== null)
                                        {{ $interaction->score }} / {{ $interaction->max_score }}
                                        <span class="text-muted">
                                            ({{ $interaction->max_score > 0 ? round(($interaction->score / $interaction->max_score) * 100) : 0 }}%)
                                        </span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($interaction->completed)
                                        <span class="badge bg-success">Completed</span>
                                    @else
                                        <span class="badge bg-warning">In Progress</span>
                                    @endif
                                </td>
                                <td>{{ $interaction->first_interaction_at->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    @if($interaction->completed_at)
                                        {{ $interaction->completed_at->format('Y-m-d H:i:s') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('h5p.student.progress', $interaction->student_id) }}" 
                                       class="btn btn-sm btn-primary">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                <p class="text-muted">
                    Total Records: {{ $interactions->count() }}
                </p>
            </div>
        @endif
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>