import { createApp } from "vue";
import { isString } from "@vue/shared";
import renderBridgeApp from "./renderBridgeApp.js";
import BridgePlugin from "./BridgePlugin.js";
import Bridge from "./core.js";
import loader from "./components/loader.js";

const createBridgeApp = (el, config) => {
	let baseTemplate = null;
	if (config.baseTemplate) {
		baseTemplate = config.baseTemplate;
		delete config.baseTemplate;
	}

	Bridge.setElement(isString(el) ? document.getElementById(el) : el);

	const app = createApp({
		render: baseTemplate
			? renderBridgeApp(el, baseTemplate)
			: renderBridgeApp(el),
		...config,
	});

	Bridge.setApplication(app);

	loader.application = app;

	app.use(BridgePlugin, {});

	loader.registerComponents();

	app.mount(el);

	return app;
};

export { renderBridgeApp, createBridgeApp };
