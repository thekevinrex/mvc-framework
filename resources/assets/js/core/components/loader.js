import { loadModule } from "vue3-sfc-loader/dist/vue3-sfc-loader.esm.js";
import * as Vue from "vue";

const Loader = {
	application: null,
	setApplication: (app) => (this.application = app),

	registerComponents: () => {
		Object.entries(
			import.meta.globEager("../../../vue/components/*.vue")
		).forEach(([path, definition]) => {
			Loader.registerComponent(
				path
					.split("/")
					.pop()
					.replace(/\.\w+$/, ""),
				definition.default
			);
		});
	},
	compileString: (name, string) => {
		const options = {
			moduleCache: { vue: Vue },
			getFile: () => string,
			addStyle: () => {},
		};

		const component = Vue.defineAsyncComponent(() =>
			loadModule(`${name}.vue`, options)
		);

		return Loader.registerComponent(name, component);
	},

	registerComponent: (name, component) => {
		return Loader.application?.component(name, component);
	},

	registerBridgeComponent: (componentData) => {
		const name = componentData.name;

		const component = Loader.compileString(name, componentData.template);
	},
};

export default Loader;
