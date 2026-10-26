namespace OdontoCare.Application.Common;

public sealed class BusinessException : Exception
{
    public int StatusCode { get; }
    public BusinessException(string message, int statusCode = 409) : base(message) => StatusCode = statusCode;
}
