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

// Bridge.registerBridgeComponents = (app, component) => {
// 	Object.keys(components).forEach((componentName) => {
// 		console.log(components[componentName]);

// 		const component = {
// 			template: components[componentName].template,
// 			methods: {},
// 			computed: {},
// 			watch: {},
// 		};

// 		if (components[componentName].props) {
// 			component.props = components[componentName].props;
// 		}

// 		if (components[componentName].data) {
// 			component.data = () => components[componentName].data;
// 		}

// 		if (components[componentName]?.method) {
// 			components[componentName].method.forEach((method) => {
// 				component.methods[method.name] = new Function(
// 					method.params,
// 					method.body
// 				);
// 			});
// 		}

// 		if (components[componentName]?.computed) {
// 			components[componentName].computed.forEach((method) => {
// 				component.computed[method.name] = new Function(
// 					method.params,
// 					method.body
// 				);
// 			});
// 		}

// 		app.component(componentName, component);
// 	});
// };
