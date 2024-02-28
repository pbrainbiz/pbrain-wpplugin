<script lang="ts">
  import { createEventDispatcher, onMount, tick } from "svelte";
  import Fa from "svelte-fa";
  import { faCircleNotch } from "@fortawesome/free-solid-svg-icons";
  import {
    EventStreamContentType,
    fetchEventSource,
  } from "@microsoft/fetch-event-source";
  import fetchRetry from "./network/fetchRetry";
  import ClientApiError from "./network/ClientApiError";

  const dispatch = createEventDispatcher();

  export let url = "";
  export let email = "";
  export let name = "";

  type CreateResponse =
    | { status: "continue"; message: string }
    | {
        status: "success";
        message: string;
        chatLogicGroupId: string;
        chatbotId: number;
        appId: number;
        channelWebId: number;
        onboardId: string;
      }
    | { status: "error" };

  class FatalError extends Error {}

  let urlSubmitting = false;
  let urlSubmitted = false;
  let urlMessage = "";
  let urlError = "";
  let appId = 0;
  let channelWebId = 0;
  let onboardId = "";

  let emailSubmitting = false;
  let emailSubmitted = false;
  let emailMessage = "";

  function handleCreateSubmit() {
    urlSubmitting = true;
    urlMessage = "";
    urlError = "";
    let retry = 2;
    fetchEventSource("http://localhost:8082/onboard/create", {
      method: "POST",
      openWhenHidden: true,
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        url,
      }),
      async onopen(response) {
        if (
          response.ok &&
          response.headers.get("content-type") === EventStreamContentType
        ) {
          return;
        }

        urlSubmitting = false;
        urlMessage = "";
        urlError = "Something went wrong. Please contact support@pbrain.biz.";
      },
      onerror(err) {
        if (err instanceof FatalError || retry <= 0) {
          urlSubmitting = false;
          urlMessage = "";
          urlError = "Something went wrong. Please try again.";
          throw err; // rethrow to stop the operation
        }

        // Do nothing to automatically retry. You can also
        // return a specific retry interval here.
        retry -= 1;
      },
      async onmessage(msg) {
        const data = JSON.parse(msg.data) as CreateResponse;

        // if the server emits an error message, throw an exception
        // so it gets handled by the onerror callback below:
        if (data.status === "error") {
          urlSubmitting = false;
          urlMessage = "";
          urlError = "Something went wrong. Please try again.";
          retry -= 1;
          throw new FatalError();
        }

        if (data.status === "continue") {
          urlMessage = data.message;
        } else if (data.status === "success") {
          urlMessage = "";
          urlError = "";
          appId = data.appId;
          channelWebId = data.channelWebId;
          onboardId = data.onboardId;
          urlSubmitted = true;
          dispatch("created", { appId, channelWebId, onboardId });
          await submitEmail();
        }
      },
    });
  }

  async function submitEmail() {
    emailSubmitting = true;
    emailMessage = "";
    try {
      await fetchRetry("http://localhost:8082/onboard/email", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          email,
          onboardId,
        }),
      });

      emailSubmitted = true;
    } catch (ex) {
      emailSubmitting = false;
      if (ex instanceof ClientApiError) {
        emailMessage =
          "The email address does not look correct. Email address should look like john@example.com";
      } else {
        emailMessage = "Something went wrong. Please try again.";
      }
    }
  }

  onMount(async () => {
    const urlParams = new URLSearchParams(window.location.search);
    const idFromUrl = urlParams.get("id");
    const onboardIdFromUrl = urlParams.get("onboard-id");
    if (idFromUrl && onboardIdFromUrl) {
      const [appIdFromUrl, channelWebIdFromUrl] = idFromUrl.split("-");
      let appIdFromUrlInt = 0;
      let channelWebIdFromUrlInt = 0;
      if (
        appIdFromUrl &&
        channelWebIdFromUrl &&
        (appIdFromUrlInt = parseInt(appIdFromUrl, 10)) &&
        (channelWebIdFromUrlInt = parseInt(channelWebIdFromUrl, 10))
      ) {
        appId = appIdFromUrlInt;
        channelWebId = channelWebIdFromUrlInt;
        onboardId = onboardIdFromUrl;
        urlSubmitted = true;

        await tick();
        // PBrain.init();
      }
    }
  });
</script>

{#if !urlSubmitted}
  <form
    id="urlForm"
    on:submit|preventDefault|stopPropagation={handleCreateSubmit}
    action="#"
  >
    <p class="submit">
      <button
        type="submit"
        class="button button-primary"
        disabled={urlSubmitting}
        >{#if urlSubmitting}Creating…{:else}Set up lead gen ChatGPT chatbot now{/if}</button
      >
    </p>
    {#if urlMessage}
      <p>
        {#if urlSubmitting}<Fa icon={faCircleNotch} spin class="inline-block" />
        {/if}{urlMessage}
      </p>
    {/if}
  </form>
{/if}
{#if emailMessage}
  <p class="text-red-700">
    {emailMessage}
  </p>
{/if}
