import Core from "./Core.vue";
import { h } from "@vue/runtime-core";

export default function renderBridgeApp(el, baseTemplate = null) {
	return function () {
		return h(Core, { el, baseTemplate });
	};
}
