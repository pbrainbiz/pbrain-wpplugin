export default class ServerApiError extends Error {
  public readonly statusCode: number;

  public constructor(statusCode: number, message?: string) {
    super(message);
    this.statusCode = statusCode;
  }
}

ServerApiError.prototype.name = "ServerApiError";
