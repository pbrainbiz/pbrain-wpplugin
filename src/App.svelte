<script>
  import fetchRetry from "./network/fetchRetry";
  export let chatbotId = "1-1";

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
</script>

<form on:submit|preventDefault|stopPropagation={handleSubmit} method="post">
  <h2 id="pbrain_section_general">General</h2>
  <p>
    Generate leads and better engage your customers with your custom ChatGPT
  </p>
  <table class="form-table" role="presentation">
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

<style>
  h1 {
    color: #40b3ff;
  }
</style>
