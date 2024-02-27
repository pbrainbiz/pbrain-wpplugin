import App from "./App.svelte";

const target = document.getElementById("pbrain-options-fields");
const props = target ? target.dataset : {};

const app = new App({
  target,
  props,
});

export default app;
