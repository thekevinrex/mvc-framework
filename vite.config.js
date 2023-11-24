
import {defineConfig, splitVendorChunkPlugin} from 'vite'
import vue from '@vitejs/plugin-vue'
import {liveReload} from "vite-plugin-live-reload";
import vitePlugin from './resources/assets/js/core/vite-plugin-core';

export default defineConfig(() => {
    return {
        plugins : [
            vitePlugin({
                input : ['resources/assets/js/main.js'],
                alias : {
                    'vue' : 'vue/dist/vue.esm-bundler.js',
                }
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
            liveReload([
                'resources/views/**',
                'resources/vue/**',
            ]),
            splitVendorChunkPlugin()
        ],
    };
});
