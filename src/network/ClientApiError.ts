export default class ClientApiError<TErrors> extends Error {
  public readonly statusCode: number;

  public readonly errors?: TErrors;

  public constructor(statusCode: number, message?: string, errors?: TErrors) {
    super(message);
    this.statusCode = statusCode;
    this.errors = errors;
  }
}

ClientApiError.prototype.name = "ClientApiError";
