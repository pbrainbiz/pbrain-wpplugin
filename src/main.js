import App from "./App.svelte";

const target = document.getElementById("pbrain-options");
const props = target ? target.dataset : {};

const app = new App({
  target,
  props,
});

export default app;
