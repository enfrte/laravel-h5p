
const questionSets = {};

let foundState = null;

function findState(obj) {
	if (!obj || typeof obj !== 'object') return false;

	for (const key of Object.keys(obj)) {
		if (key === 'state' && obj[key] != null) {
			const raw = obj[key];
			if (typeof raw === 'string') {
				try {
					foundState = JSON.parse(raw);
				} catch (e) {
					foundState = raw;
				}
			} else {
				foundState = raw;
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
	const saveData = window.H5PIntegration.contents[`cid-${url}`].contentUserData;
	//console.log(saveData);

	// 
	findState(saveData);
	//console.log('foundState', foundState);
	

	//console.log("xAPI event: ", event.data);
	const st = event.data.statement;
	const verb = st.verb?.id || '';

	// Handle question-level events
	if (verb.includes('answered')) {
		const qId = st.object?.id || 'unknown-id';
		const question = st.object?.definition?.description || '(no question text)';
		const type = st.object?.definition?.type || '(no type)';
		const interactionType = st.object?.definition?.interactionType || '(no interaction type)';
		const answer = st.result?.response || '(no response)';
		const correct = st.result?.success ?? null;
		const correctAnswers = st.object?.definition?.correctResponsesPattern || [];
		const score = st.result?.score?.scaled ?? null;

		if (!questionSets[qId]) {
			questionSets[qId] = {
				id: qId,
				question,
				type,
				interactionType,
				attempts: []
			};
		}

		questionSets[qId].attempts.push({
			answer,
			correct,
			correctAnswers,
			score,
			timestamp: st.timestamp || new Date().toISOString()
		});

		return;
	}

	// Handle when the *entire activity* is completed
	if (verb.includes('completed')) {
		const totalScore = st.result?.score || {};
		const scaled = totalScore.scaled ?? null;
		const raw = totalScore.raw ?? null;
		const max = totalScore.max ?? null;
		const duration = st.result?.duration ?? null;

		const sessionData = {
			finishedAt: new Date().toISOString(),
			activityId: st.object?.id || '(unknown activity)',
			total: { scaled, raw, max, duration },
			questions: questionSets
		};

		//console.log('🏁 Activity complete!');
		//console.log(JSON.stringify(sessionData, null, 2));

		// Send to your backend
		fetch('/api/h5p/interaction', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			//body: JSON.stringify(sessionData)
			body: JSON.stringify(foundState)
		});
		
		// Optionally reset for next run
		//Object.keys(questionSets).forEach(k => delete questionSets[k]);
	}

});
