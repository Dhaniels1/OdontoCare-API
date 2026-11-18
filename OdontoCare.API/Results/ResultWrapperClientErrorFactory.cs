using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.Infrastructure;
using OdontoCare.Application.Common;

namespace OdontoCare.API.Results;

// Padroniza também erros produzidos pelos filtros do MVC antes do controller,
// como o 415 de um Content-Type ou charset não suportado.
public sealed class ResultWrapperClientErrorFactory : IClientErrorFactory
{
    public IActionResult GetClientError(ActionContext actionContext, IClientErrorActionResult clientError)
    {
        var status = clientError.StatusCode ?? StatusCodes.Status400BadRequest;
        var mensagem = status switch
        {
            StatusCodes.Status415UnsupportedMediaType => "Formato de conteúdo não suportado. Envie JSON com Content-Type: application/json e codificação UTF-8.",
            StatusCodes.Status404NotFound => "Recurso não encontrado.",
            StatusCodes.Status405MethodNotAllowed => "Método HTTP não permitido para este recurso.",
            StatusCodes.Status406NotAcceptable => "Formato de resposta não suportado.",
            _ => "Requisição inválida."
        };
        return new ObjectResult(ResultWrapper<object>.Failure(mensagem))
        {
            StatusCode = status,
            ContentTypes = { "application/json" }
        };
    }
}
