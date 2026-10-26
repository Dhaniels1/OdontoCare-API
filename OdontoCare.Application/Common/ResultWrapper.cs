using System.Text.Json.Serialization;

namespace OdontoCare.Application.Common;

public sealed class ResultWrapper<T>
{
    [JsonPropertyName("success")] public bool Success { get; init; }
    [JsonPropertyName("message")] public string Message { get; init; } = "";
    [JsonPropertyName("data")] public T? Data { get; init; }
    [JsonPropertyName("errors")] public IReadOnlyList<string> Errors { get; init; } = [];

    public static ResultWrapper<T> Ok(T? data, string message = "Operação realizada com sucesso.") =>
        new() { Success = true, Data = data, Message = message };
    public static ResultWrapper<T> Failure(string message, IEnumerable<string>? errors = null) =>
        new() { Success = false, Message = message, Errors = errors?.ToArray() ?? [] };
    public static ResultWrapper<T> ValidationError(IEnumerable<string> errors) => Failure("Dados inválidos.", errors);
    public static ResultWrapper<T> NotFound(string message = "Registro não encontrado.") => Failure(message);
    public static ResultWrapper<T> Unauthorized(string message = "Autenticação necessária.") => Failure(message);
    public static ResultWrapper<T> ServerError() => Failure("Erro interno. Tente novamente mais tarde.");
}
