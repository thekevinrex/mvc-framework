import "vite/modulepreload-polyfill";
import "../css/style.css";

import { createBridgeApp } from "@core";
import App from "../../vue/App.vue";

import.meta.glob(["../images/**"]);

let el = document.getElementById("app");

const app = createBridgeApp(el, {
	baseTemplate: App,
	mounted() {
		console.log("Main.js Application mounted debugging");
	},
});
