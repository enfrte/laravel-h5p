
const responceObject = {}; // Holds the final data to be sent
const questionSets = {}; // Holds question-level data
const answer = {};
let foundSavedUserState = null; // Holds the found state object

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
	
	const url = window.H5PIntegration['url'];
	const savedUserState = window.H5PIntegration.contents[`cid-${url}`].contentUserData;

	responceObject.H5PIntegration = window.H5PIntegration;
	
	// // Or if you don't want to send all that data
	// {
	// 	activityId: window.H5PIntegration.url, 
	// 	userId: window.H5PIntegration.user.userId
	// }; 
	
	findState(savedUserState);

	const st = event.data.statement;
	const verb = st.verb?.id || '';
	responceObject.statement = st;

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
		const score = st.result?.score?.scaled ?? null;

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
			score,
			timestamp: st.timestamp || new Date().toISOString()
		});

		responceObject['questions'] = questionSets;
		//console.log('questionSets', questionSets);
	}
	//console.log(JSON.stringify(sessionData, null, 2));

	// If foundState is !null, attach or update questionSets
	if (foundSavedUserState) {
		responceObject['state'] = foundSavedUserState;
	}

	if ( verb.includes('answered') ) {
		//console.log(H5PIntegration);
		
		console.log('responceObject', responceObject);
		// Send to the backend
		fetch('/api/h5p/questionAnswerCollection', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify(responceObject)
		});
	}
	
	// Optionally reset for next run
	//Object.keys(questionSets).forEach(k => delete questionSets[k]);
});
