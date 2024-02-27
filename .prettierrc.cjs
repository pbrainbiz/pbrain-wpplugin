module.exports = {
  plugins: [
    require.resolve("prettier-plugin-svelte"),
  ],
  overrides: [
    {
      files: "*.svelte",
      options: {
        parser: "svelte",
      },
    },
  ],
  astroAllowShorthand: true,
  svelteAllowShorthand: true,
};
