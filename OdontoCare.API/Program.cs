using OdontoCare.API.Extensions;
using OdontoCare.API.Middleware;
using OdontoCare.Application.Common;
using Microsoft.AspNetCore.Mvc.Infrastructure;
using OdontoCare.API.Results;

var builder = WebApplication.CreateBuilder(args);
builder.Services.AddControllers().ConfigureApiBehaviorOptions(options =>
{
    options.InvalidModelStateResponseFactory = context =>
    {
        var erros = context.ModelState.Values.SelectMany(v => v.Errors)
            .Select(e => string.IsNullOrWhiteSpace(e.ErrorMessage) ? "Campo inválido." : e.ErrorMessage)
            .Distinct().ToArray();
        return new Microsoft.AspNetCore.Mvc.BadRequestObjectResult(ResultWrapper<object>.ValidationError(erros));
    };
});
builder.Services.AddSingleton<IClientErrorFactory, ResultWrapperClientErrorFactory>();
builder.Services.AddOpenApi();
builder.Services.AddOdontoCare(builder.Configuration);

var app = builder.Build();
app.UseMiddleware<ExceptionMiddleware>();
app.UseStatusCodePages(async context =>
{
    var response = context.HttpContext.Response;
    if (response.HasStarted || !context.HttpContext.Request.Path.StartsWithSegments("/api")) return;
    var mensagem = response.StatusCode switch
    {
        StatusCodes.Status404NotFound => "Recurso não encontrado.",
        StatusCodes.Status403Forbidden => "Você não tem permissão para esta operação.",
        _ => "Requisição inválida."
    };
    await response.WriteAsJsonAsync(ResultWrapper<object>.Failure(mensagem));
});
if (app.Environment.IsDevelopment()) app.MapOpenApi(); // /openapi/v1.json
app.UseHttpsRedirection();
app.UseRateLimiter();
app.UseAuthentication();
app.UseAuthorization();
app.MapControllers();
app.Run();
