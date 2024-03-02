"use strict";
import fs from "fs";
import path from "path";

export default function vitePlugin(options) {
	let configResolved = {};
	let viteAddress = "";

	return {
		name: "vite-plugin-core",
		config: () => {
			return resolveViteConfig(options);
		},
		configResolved(config) {
			configResolved = config;
		},
		transform(code) {
			if (configResolved.command === "serve") {
				return code.replace(/__origing_server__/g, viteAddress);
			}
		},
		configureServer(server) {
			const filePath = path.join("bootstrap/Cache", "server.json");

			server.httpServer != null
				? server.httpServer.once("listening", () => {
						let address = server.httpServer.address();

						if (typeof address === "object") {
							const data = getDevAddress(address, server.config);
							viteAddress = data.full;

							fs.writeFileSync(filePath, JSON.stringify(data));
						}
				  })
				: void 0;

			const clean = () => {
				if (fs.existsSync(filePath)) {
					fs.rmSync(filePath);
					//fs.unlinkSync(filePath);
				}
			};

			process.on("exit", clean);

			return () =>
				server.middlewares.use((req, res, next) => {
					if (req.url === "/index.html") {
						res.statusCode = 404;
						res.end(
							fs
								.readFileSync(path.join(__dirname, "dev-server-index.html"))
								.toString()
						);
					}
					next();
				});
		},
	};
}

function resolveViteConfig(options) {
	let config = {
		build: {
			outDir: "./public/dist",
			assetsDir: "resources/assets",
			manifest: true,
			rollupOptions: {
				input: ["resources/assets/js/main.js"],
			},
		},
		server: {
			origin: "__origing_server__",
		},
		resolve: {
			alias: {
				"@": "resources/assets",
				"@components": "resources/vue",
				"@core": "./core",
			},
		},
	};

	if (options.input) {
		config.build.rollupOptions.input = options.input;
	}

	if (options.alias) {
		for (let i in options.alias) {
			if (!Object.keys(config.resolve.alias).includes(i)) {
				config.resolve.alias[i] = options.alias[i];
			}
		}
	}

	return config;
}

function getDevAddress(address, config) {
	address.protocol = config.server.https ? "https" : "http";

	let host =
		address.family === "IPv6" || address.family === 6
			? `[${address.address}]`
			: address.address;

	address.full = `${address.protocol}://${host}:${address.port}/`;

	return address;
}
