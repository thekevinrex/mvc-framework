import { default as Axios } from "axios";
import { ref } from "vue";

const requestQueue = ref([]);
const handling = ref(false);

const request = (url, method, data, headers) => {
	const promise = new Promise((resolve, reject) => {
		requestQueue.value.push({
			data: { url, method, data, headers },
			resolve,
			reject,
			WebSocket: false,
		});
	});

	if (!handling.value) {
		handleNewRequest();
	}

	return promise;
};

function handleNewRequest() {
	let request = requestQueue.value.pop();

	if (isWebsocketRequest(request)) {
		handleWebsocketRequest(request);
	} else {
		handleRegularRequest(request);
	}
}

function isWebsocketRequest(request) {
	return request.WebSocket ?? false;
}

function handleWebsocketRequest(request) {
	//
}

function handleRegularRequest(request) {
	//remeber the scroll position

	let { url, method, data, headers } = splitRequest(request.data);

	handling.value = true;

	const promise = Axios({
		method,
		url,
		data,
		headers,
		onUploadProgress: (progress) => {
			if (data instanceof FormData) {
				// To update the progress bar.
				progress.percentage = Math.round(
					(progress.loaded / progress.total) * 100
				);
				// emit("internal:request-progress", {
				// 	url,
				// 	method,
				// 	data,
				// 	headers,
				// 	replace,
				// 	progress,
				// });
			}
		},
	});

	promise.then((response) => {
		request.resolve(response);

		// TODO defineEmits()
	});

	promise.catch((error) => {
		request.reject(error);

		if (!error.response) {
			return;
		}

		// #TODO redirect away

		// if response status code is 422 return immediately;

		// handle server error pasist to the bridge
	});

	promise.finally(() => {
		handling.value = false;
	});
}

function splitRequest(request) {
	let { url, method, data, headers } = request;

	if (method.toUpperCase() === "GET") {
		const query = new URLSearchParams(data).toString();

		// # find if the url contains a '?' character
		if (query != "") {
			const queryContains = url.indexOf("?") !== -1;
			url = url + (queryContains ? "&" + query : "?" + query);
		}

		data = {};
	}

	headers = {
		"X-Bridge": true,
		"X-Requested-With": "XMLHttpRequest",
		Accept: "text/html, application/xhtml+xml",
		...headers,
	};

	return { url, method, data, headers };
}

export default { request };
