import resolve from "@rollup/plugin-node-resolve";
import commonjs from "@rollup/plugin-commonjs";
import terser from "@rollup/plugin-terser";
import typescript from "@rollup/plugin-typescript";
import clean from "@rollup-extras/plugin-clean";
import autoprefixer from "autoprefixer";
import cssnano from "cssnano";
import postcssNested from "postcss-nested";
import css from "rollup-plugin-css-only";
import svelte from "rollup-plugin-svelte";
import sveltePreprocess from "svelte-preprocess";
import tailwind from "tailwindcss";

const production = !process.env.ROLLUP_WATCH;

/** @type {import('rollup').RollupOptions} */
export default {
  input: "src/main.js",
  output: {
    sourcemap: true,
    format: "iife",
    name: "pbrain_wpplugin",
    assetFileNames: "[name]-[hash].[ext]",
    dir: "dist",
    entryFileNames: production ? "bundle-[hash].js" : "bundle.js",
  },
  plugins: [
    clean(["dist"]),
    typescript({
      compilerOptions: {
        target: "es2018",
      },
    }),
    svelte({
      preprocess: sveltePreprocess({
        sourceMap: !production,
        postcss: {
          plugins: [
            postcssNested,
            tailwind,
            autoprefixer,
            ...(production ? [cssnano] : []),
          ],
        },
      }),
    }),

    // If you have external dependencies installed from
    // npm, you'll most likely need these plugins. In
    // some cases you'll need additional configuration —
    // consult the documentation for details:
    // https://github.com/rollup/rollup-plugin-commonjs
    resolve({
      browser: true,
      exportConditions: ["svelte"],
      extensions: [".svelte"],
    }),
    css({
      name: "bundle.css",
    }),
    commonjs(),

    // If we're building for production (npm run build
    // instead of npm run dev), minify
    production && terser(),
  ],
};
