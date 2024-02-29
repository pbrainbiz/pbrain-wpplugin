<script lang="ts">
  import { createEventDispatcher, onMount, tick } from "svelte";
  import Fa from "svelte-fa";
  import { faCircleNotch } from "@fortawesome/free-solid-svg-icons";
  import {
    EventStreamContentType,
    fetchEventSource,
  } from "@microsoft/fetch-event-source";

  const dispatch = createEventDispatcher();

  export let url = "";
  export let email = "";
  export let adminName = "";
  export let siteName = "";

  const OnboardSource = 1;

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
        signingKey: string;
      }
    | { status: "error" };

  class FatalError extends Error {}

  let urlSubmitting = false;
  let urlSubmitted = false;
  let urlMessage = "";
  let appId = 0;
  let channelWebId = 0;
  let onboardId = "";

  function handleCreateSubmit() {
    urlSubmitting = true;
    urlMessage = "";
    let retry = 2;

    dispatch("creating");

    fetchEventSource("https://www.pbrain.biz/onboard/create", {
      method: "POST",
      openWhenHidden: true,
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        url,
        email,
        adminName,
        siteName,
        onboardSource: OnboardSource,
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
        dispatch("error", {
          message: "Something went wrong. Please contact support@pbrain.biz.",
        });
      },
      onerror(err) {
        if (err instanceof FatalError || retry <= 0) {
          urlSubmitting = false;
          urlMessage = "";
          dispatch("error", {
            message: "Something went wrong. Please try again.",
          });

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
          dispatch("error", {
            message: "Something went wrong. Please try again.",
          });

          retry -= 1;
          throw new FatalError();
        }

        if (data.status === "continue") {
          urlMessage = data.message;
        } else if (data.status === "success") {
          urlMessage = "";
          appId = data.appId;
          channelWebId = data.channelWebId;
          onboardId = data.onboardId;
          urlSubmitted = true;
          dispatch("created", {
            appId,
            channelWebId,
            onboardId,
            signingKey: data.signingKey,
          });
        }
      },
    });
  }
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
        >{#if urlSubmitting}Setting up, please wait…{:else}Set up lead gen
          ChatGPT chatbot now{/if}</button
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
