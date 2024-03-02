import Bridge from "./core.js";

export default {
	install: (app, options) => {
		options = options || {};

		Object.defineProperty(app.config.globalProperties, "$bridge", {
			get: () => Bridge,
		});
		Object.defineProperty(app.config.globalProperties, "$bridgeOptions", {
			get: () => Object.assign({}, { ...options }),
		});

		app.provide("$bridge", app.config.globalProperties.$bridge);
		app.provide("$bridgeOptions", app.config.globalProperties.$bridgeOptions);
	},
};
