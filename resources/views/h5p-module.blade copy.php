<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- Bootstrap 5 CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<!-- H5P styles -->
	<link rel="stylesheet" href="/h5p/h5p-standalone/dist/styles/h5p.css">
	<script src="/h5p/h5p-standalone/dist/main.bundle.js" charset="UTF-8"></script>
	<title>{{ config('app.name', 'Laravel') }}</title>
</head>
<body>

<div class="container pb-3">
	<div class="accordion" id="accordionExample">
		<div class="accordion-item">
			<h2 class="accordion-header" id="headingOne">
				<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
					Info menu
				</button>
			</h2>
			<div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
				<div class="accordion-body">
					<ul class="list-group list-group-flush">
						<li class="list-group-item"><strong>MVP:</strong></li>
						<li class="list-group-item">Upload to server - Verify H5P, validate max upload size.</li>
						<li class="list-group-item">Launch H5P module. Extract zip material. Destroy material with some kind of trigger same as SCORM.</li>
						<li class="list-group-item">Set id: 'activity-xxx' property from session variable that maps to launched h5p module id.</li>
						<li class="list-group-item">Report form to display activity and participants with completion details.</li>
						<li class="list-group-item"><strong>Next Epic:</strong></li>
						<li class="list-group-item">Set min pass mark.</li>
						<li class="list-group-item">Look into recording statistics module parts. Questions, answers, correct answer percentages...etc.</li>
					</ul>

					<p class="mt-3">Admin: <a href="/h5p/progress" class="btn btn-primary ms-2">Check the progress</a></p>
				</div>
			</div>
		</div>
	</div>
</div>


<div id="h5p-container" class="container"></div>

<!-- Bootstrap 5 JS (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
	
<script>
	document.addEventListener('DOMContentLoaded', function () {		
		const el = document.getElementById("h5p-container");

		const options = {
			id: 'activity', // set a known id for content (used for state lookups)
			h5pJsonPath: "/h5p/activity",
			frameJs: "/h5p/h5p-standalone/dist/frame.bundle.js",
			frameCss: "/h5p/h5p-standalone/dist/styles/h5p.css",
			saveFreq: 1, // Time in seconds for autosaving state - Needed for reporting - Does not send request every interval
			ajax: {
				setFinishedUrl: '/api/h5p/interaction/:contentId/finished',
				contentUserDataUrl: '/api/h5p/interaction',
				//contentUserDataUrl: '/h5p/:contentId/userdata/:dataType/:subContentId', // Makes a POST request to the contentUserDataUrl. The body of the POST request will contain the user state data (typically a JSON string representing the H5P state). Your server should store this data, associating it with the user and content ID.
				//postUserStatistics: true,
			},
			reportingIsEnabled: true,
			user: { // User object is required for automated state saving
				name: 'John Doe',
				mail: 'john@example.com' // Email is used by H5P core to uniquely identify the user
			},
			customJs: '{{ asset("js/h5p/custom-h5p.js") }}', // Can be a single string or an array of strings
		};

		new H5PStandalone.H5P(el, options);
		
	});
</script>

</body>
</html>