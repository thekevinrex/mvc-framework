import { ref } from "@vue/reactivity";

const currentMeta = ref();

function removeMetaElement(meta) {
	let selector = "meta";

	selector = `${selector}[type="${meta.key}"]`;

	try {
		document.querySelector(selector)?.remove();
	} catch {
		//
	}
}

function insertMetaElement(meta) {
	const $el = document.createElement("meta");

	for (let i in meta.attributes) {
		$el[i] = meta.attributes[i];
	}

	document.getElementsByTagName("head")[0].appendChild($el);
}

export default function updateHead(head) {
	if (currentMeta.value === undefined) {
		currentMeta.value = head.metas;
	}

	for (let i in currentMeta.value) {
		removeMetaElement(currentMeta.value[i]);
	}

	currentMeta.value = head.metas;

	document.title = head.title;

	for (let i in currentMeta.value) {
		insertMetaElement(currentMeta.value[i]);
	}

	document.querySelector('link[rel="canonical"]')?.remove();

	if (head.canonical) {
		const $el = document.createElement("link");
		$el.rel = "canonical";
		$el.href = head.canonical;

		document.getElementsByTagName("head")[0].appendChild($el);
	}
}
