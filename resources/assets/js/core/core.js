import { ref } from "@vue/reactivity";
import { isString, isObject } from "@vue/shared";
import { nextTick } from "vue";
import updateHead from "./libs/Seo.js";
import HTTP from "./libs/Http.js";
import loader from "./components/loader.js";

const onHtml = ref();
const currentPage = ref();

const currentData = ref();
const events = ref([]);

const Bridge = {
	version: 1.0,
	defaultElement: "app",
	application: null,
	element: null,
};

Bridge.setElement = (ele) => {
	Bridge.element = isString(ele) ? document.getElementById(ele) : ele;
};

Bridge.setApplication = (app) => {
	Bridge.application = app;
};

Bridge.onHtml = (callback) => {
	onHtml.value = callback;
};

function setCurrentPage(newPage) {
	currentPage.value = newPage;

	return newPage;
}

/**
 * Pushes the given page to the browser's history.
 */
function pushState(page) {
	window.history.pushState(page, "", page.url);
}

/**
 * Replaces the current browser history state with the given page.
 */
function replaceState(page) {
	window.history.replaceState(page, "", page.url);
}

function handlePopState(event) {
	console.log(event);

	let currentPage = setCurrentPage(event.state);

	setHead(currentPage.head);
	setHtml(currentPage.html);
}

Bridge.init = (
	initialHtml,
	initialEvents,
	initialOptions,
	initialComponents
) => {
	window.addEventListener("popstate", handlePopState.bind(this));

	let href = location.href;

	setData(initialOptions);
	setHead(initialOptions.head);
	setHtml(initialHtml);

	setEvents(initialEvents);
	setComponents(initialComponents);

	let newPage = setCurrentPage({
		url: href,
		head: initialOptions.head,
		html: initialHtml,
	});

	replaceState(newPage);
};

function newPageFromRequest(response) {
	if (!isObject(response.data?.bridge)) {
		return;
	}

	let replace = false;
	const url = response.request.responseURL;

	setData(response.data.bridge);
	setHead(response.data.bridge.head);

	if (url === currentPage.value.url) {
		replace = true;
	}

	setHtml(response.data.html);

	let newPage = setCurrentPage({
		url: url,
		head: response.data.bridge.head,
		html: response.data.html,
	});

	replace ? replaceState(newPage) : pushState(newPage);
}

function setData(data) {
	currentData.value = data;
}

function setHtml(html, scrollY) {
	onHtml.value(html);

	nextTick(() => {
		window.scrollTo(0, scrollY);

		[...document.querySelectorAll("a")].forEach((element) => {
			if (element.href == "" || element.href.charAt(0) == "#") {
				return;
			}

			if (element.__vnode.dynamicProps !== null) {
				return;
			}

			if (element.hasAttribute("download")) {
				return;
			}

			element.addEventListener("click", (e) => {
				e.preventDefault();
				e.stopPropagation();

				Bridge.visit(element.href, {}, element.dataset);
			});
		});
	});
}

function setHead(head) {
	updateHead(head);
}

function setComponents(components) {
	components.forEach((component) => {
		loader.registerBridgeComponent(component);
	});
}

function setEvents(events) {}

Bridge.visit = (url, headers = {}, data = {}) => {
	Bridge.request(url, "GET", data, headers);
};

Bridge.request = (url, method, data, headers) => {
	return HTTP.request(url, method, data, headers)
		.then((response) => {
			newPageFromRequest(response);
		})
		.catch((error) => {
			// if bridge data is present updated
		});
};

export default Bridge;
