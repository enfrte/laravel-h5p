
const responceObject = {}; // Holds the final data to be sent
let foundSavedUserState = null; // Holds the found state object
const questionSets = {}; // Holds question-level data

// Recursive function to find 'state' property in nested objects
function findState(obj) {
	if (!obj || typeof obj !== 'object') return false;

	for (const key of Object.keys(obj)) {
		if (key === 'state' && obj[key] != null) {
			const raw = obj[key];
			if (typeof raw === 'string') {
				try {
					foundSavedUserState = JSON.parse(raw);
				} catch (e) {
					foundSavedUserState = raw;
				}
			} else {
				foundSavedUserState = raw;
			}
			return true;
		}

		const val = obj[key];

		if (val && typeof val === 'object') {
			if (findState(val)) return true;
		}
	}

	return false;
}

H5P.externalDispatcher.on('xAPI', (event) => {

	//console.log(event.data);
	const url = window.H5PIntegration['url'];
	const savedUserState = window.H5PIntegration.contents[`cid-${url}`].contentUserData;
	//console.log(saveData);

	findState(savedUserState);
	//console.log('foundState', foundState);

	//console.log("xAPI event: ", event.data);
	const st = event.data.statement;
	const verb = st.verb?.id || '';

	// Handle question-level events
	if (verb.includes('answered')) {
		const qId = st.object?.id || 'unknown-id';
		const qIdparsed = qId.split('subContentId=')[1] || qId; // get value after subContentId= 
		const question = st.object?.definition?.description || '(no question text)';
		const type = st.object?.definition?.type || '(no type)';
		const interactionType = st.object?.definition?.interactionType || '(no interaction type)';
		const answer = st.result?.response || '(no response)';
		const correct = st.result?.success ?? null;
		const correctAnswers = st.object?.definition?.correctResponsesPattern || [];
		//const score = st.result?.score?.scaled ?? null;

		if (!questionSets[qIdparsed]) {
			questionSets[qIdparsed] = {
				id: qId,
				question,
				type,
				interactionType,
				attempts: []
			};
		}

		questionSets[qIdparsed].attempts.push({
			answer,
			correct,
			correctAnswers,
			//score,
			timestamp: st.timestamp || new Date().toISOString()
		});

		responceObject['questions'] = questionSets;
		return;
	}

	//console.log('🏁 Activity complete!');
	//console.log(JSON.stringify(sessionData, null, 2));

	// If foundState is !null, attach or update questionSets
	if (foundSavedUserState) {
		responceObject['state'] = foundSavedUserState;
	}

	// Send to your backend
	fetch('/api/h5p/interaction', {
		method: 'POST',
		headers: { 'Content-Type': 'application/json' },
		body: JSON.stringify(responceObject)
	});
	
	// Optionally reset for next run
	//Object.keys(questionSets).forEach(k => delete questionSets[k]);

});
