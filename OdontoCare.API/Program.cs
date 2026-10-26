using OdontoCare.API.Extensions;
using OdontoCare.API.Middleware;
using OdontoCare.Application.Common;

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
builder.Services.AddOpenApi();
builder.Services.AddOdontoCare(builder.Configuration);

var app = builder.Build();
app.UseMiddleware<ExceptionMiddleware>();
app.UseStatusCodePages(async context =>
{
    var response = context.HttpContext.Response;
    if (!response.HasStarted && context.HttpContext.Request.Path.StartsWithSegments("/api"))
        await response.WriteAsJsonAsync(ResultWrapper<object>.Failure("Recurso não encontrado."));
});
if (app.Environment.IsDevelopment()) app.MapOpenApi(); // /openapi/v1.json
app.UseHttpsRedirection();
app.UseAuthentication();
app.UseAuthorization();
app.MapControllers();
app.Run();
