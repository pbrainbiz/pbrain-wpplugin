import AbortError from "./AbortError";
import ClientApiError from "./ClientApiError";
import NetworkError from "./NetworkError";
import ServerApiError from "./ServerApiError";

function contentTypeIsJson(headers: Headers): boolean {
  const contentType = headers.get("Content-Type");
  return (
    !!contentType && /^application\/json\b|^[^;]+\+json\b/.test(contentType)
  );
}

async function fetchOnce(
  input: RequestInfo,
  init?: RequestInit,
): Promise<[undefined, unknown] | [unknown, undefined]> {
  // Returns result when success.
  // Throws error when status code indicates client side error.
  // Returns error when method should be retried.
  let throwError: Error;
  try {
    const response = await fetch(input, init);
    const { status, headers, ok } = response;

    let responseContent: unknown;
    const isJson = contentTypeIsJson(headers);
    if (isJson) {
      responseContent = await response.json();
    } else {
      responseContent = await response.text();
    }

    if (
      (input instanceof Request && input.signal && input.signal.aborted) ||
      (init && init.signal && init.signal.aborted)
    ) {
      // If user aborts during json() or text(), we should throw.
      throwError = new AbortError("Aborted deserialization");
    } else if (ok) {
      return [responseContent, undefined];
    } else if (status >= 400 && status < 500) {
      throwError = new ClientApiError(
        status,
        `Invalid input on: ${input}`,
        responseContent,
      );
    } else {
      return [undefined, new ServerApiError(status, "Server error")];
    }
  } catch (ex) {
    if (ex instanceof Error && ex.name === "AbortError") {
      throwError = ex;
    } else {
      return [undefined, ex];
    }
  }

  throw throwError;
}

async function fetchRetry(
  input: RequestInfo,
  init?: RequestInit,
  retry = 2,
): Promise<unknown> {
  let errorRetry: unknown | undefined;
  for (let i = 0; i < retry; ++i) {
    // Justification: this is a retry loop. It is executed only once if fetch is successful.
    // eslint-disable-next-line no-await-in-loop
    const [result, error] = await fetchOnce(input, init);
    if (!error) {
      return result;
    }

    errorRetry = error;
  }

  if (errorRetry) {
    throw errorRetry;
  }

  throw new NetworkError("Network error");
}

export default fetchRetry;
