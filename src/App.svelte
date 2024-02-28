<script>
  import fetchRetry from "./network/fetchRetry";
  import TailwindBase from "./TailwindBase.svelte";
  import ChatbotCreator from "./ChatbotCreator.svelte";
  export let chatbotId = "1-1";
  export let url = "";
  export let email = "";
  export let name = "";

  async function handleSubmit() {
    const body = new URLSearchParams();
    body.append("action", "pbrain_settings_save");
    body.append("_ajax_nonce", pbrain_wpplugin_global.nonce);
    body.append(
      "data",
      JSON.stringify({
        chatbotId,
      }),
    );
    const response = await fetchRetry(ajaxurl, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        Accept: "application/json",
      },
      body,
    });
    console.log(response);
  }

  async function handleCreated(event) {
    const { appId, channelWebId, onboardId } = event.detail;
    console.log("Created", appId, channelWebId, onboardId);
    chatbotId = `${appId}-${channelWebId}`;
    await handleSubmit();
  }
</script>

<h2 id="pbrain_section_general">General</h2>
<p>Generate leads and better engage your customers with your custom ChatGPT</p>
<ChatbotCreator {url} {name} {email} on:created={handleCreated} />

<form on:submit|preventDefault|stopPropagation={handleSubmit} method="post">
  <table class="form-table m-10" role="presentation">
    <tbody
      ><tr
        ><th scope="row">PBrain id</th><td
          ><input
            type="text"
            id="chatbot_id"
            name="pbrain_settings[chatbot_id]"
            bind:value={chatbotId}
          /></td
        ></tr
      ></tbody
    >
  </table>
  <p class="submit">
    <input
      type="submit"
      name="submit"
      id="submit"
      class="button button-primary"
      value="Save Changes"
    />
  </p>
</form>
<TailwindBase />
