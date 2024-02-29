<script>
  import Fa from "svelte-fa";
  import {
    faCheckCircle,
    faTimesCircle,
  } from "@fortawesome/free-solid-svg-icons";

  import fetchRetry from "./network/fetchRetry";
  import TailwindBase from "./TailwindBase.svelte";
  import ChatbotCreator from "./ChatbotCreator.svelte";

  export let chatbotId = "";
  export let onboardId = "";
  export let signingKey = "";

  export let url = "";
  export let email = "";
  export let adminName = "";
  export let siteName = "";

  let showEdit = false;
  let successMessage = chatbotId
    ? "PBrain lead gen ChatGPT is already set up"
    : "";
  let errorMessage = "";
  let created = !!chatbotId;
  let creating = false;
  let saving = false;

  async function handleSubmit() {
    successMessage = "";
    errorMessage = "";
    saving = true;
    const body = new URLSearchParams();
    body.append("action", "pbrain_settings_save");
    body.append("_ajax_nonce", pbrain_wpplugin_global.nonce);
    body.append(
      "data",
      JSON.stringify({
        chatbotId,
        onboardId,
        signingKey,
      }),
    );

    try {
      await fetchRetry(ajaxurl, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
          Accept: "application/json",
        },
        body,
      });
      successMessage = "PBrain lead gen ChatGPT has been added.";
      showEdit = false;
      created = true;
    } catch {
      errorMessage = "Something went wrong. Please try again.";
    }

    creating = false;
    saving = false;
  }

  function handleCreating() {
    successMessage = "";
    errorMessage = "";
    creating = true;
  }

  function handleError(event) {
    const { message } = event.detail;
    errorMessage = message;
    creating = false;
  }

  async function handleCreated(event) {
    const {
      appId,
      channelWebId,
      onboardId: newOnboardId,
      signingKey: newSigningKey,
    } = event.detail;

    chatbotId = `${appId}-${channelWebId}`;
    onboardId = newOnboardId;
    signingKey = newSigningKey;
    await handleSubmit();
  }

  function handleEditToggle() {
    showEdit = !showEdit;
  }
</script>

{#if errorMessage}
  <div
    class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mt-4"
    role="alert"
  >
    <Fa icon={faTimesCircle} class="mr-2" />
    {errorMessage}
  </div>
{:else if successMessage}
  <div
    class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded mt-4"
    role="alert"
  >
    <Fa icon={faCheckCircle} class="mr-2" />
    {successMessage}
  </div>
{/if}
<h2 id="pbrain_section_general">General</h2>
<p>Generate leads and better engage your customers with your custom ChatGPT</p>
{#if !created}
  <ChatbotCreator
    {url}
    {email}
    {adminName}
    {siteName}
    on:creating={handleCreating}
    on:created={handleCreated}
    on:error={handleError}
  />
{/if}
{#if !creating && !onboardId}
  {#if !showEdit}
    <button type="button" class="button-link" on:click={handleEditToggle}
      >{#if created}Configure manually{:else}Already have an account on
        PBrain.biz{/if}</button
    >
  {:else}
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
          disabled={saving}
          class="button button-primary"
          value="Save Changes"
        />
      </p>
    </form>
  {/if}
{/if}
<TailwindBase />
