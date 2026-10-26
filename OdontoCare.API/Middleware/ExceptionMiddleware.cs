using Microsoft.EntityFrameworkCore;
using OdontoCare.Application.Common;

namespace OdontoCare.API.Middleware;

public sealed class ExceptionMiddleware(RequestDelegate next, ILogger<ExceptionMiddleware> logger)
{
    public async Task InvokeAsync(HttpContext ctx)
    {
        try { await next(ctx); }
        catch (Exception exception) when (!ctx.Response.HasStarted)
        {
            logger.LogError(exception, "Erro na API. TraceId: {TraceId}", ctx.TraceIdentifier);
            var codigo = exception switch
            {
                BusinessException b => b.StatusCode,
                DbUpdateConcurrencyException => StatusCodes.Status409Conflict,
                DbUpdateException => StatusCodes.Status409Conflict,
                _ => StatusCodes.Status500InternalServerError
            };
            var mensagem = exception switch
            {
                BusinessException b => b.Message,
                DbUpdateException => "Não foi possível salvar: registro duplicado ou relacionado a outros dados.",
                _ => "Ocorreu um erro interno. Contate o suporte informando o horário da falha."
            };
            ctx.Response.Clear();
            ctx.Response.StatusCode = codigo;
            await ctx.Response.WriteAsJsonAsync(ResultWrapper<object>.Failure(mensagem));
        }
    }
}
