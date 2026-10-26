using Microsoft.EntityFrameworkCore;
using OdontoCare.Infrastructure.Data;
using OdontoCare.Application.Interfaces;
using OdontoCare.Infrastructure.Services;

var builder = WebApplication.CreateBuilder(args);

// Add services to the container.

builder.Services.AddControllers();

builder.Services.AddScoped<IPacienteService, PacienteService>();
builder.Services.AddScoped<IConsultaService, ConsultaService>();
// Learn more about configuring OpenAPI at https://aka.ms/aspnet/openapi
builder.Services.AddDbContext<OdontoCareDbContext>(options =>
    options.UseMySQL(
        builder.Configuration.GetConnectionString("OdontoCare")!
    )
);
builder.Services.AddOpenApi();

var app = builder.Build();

// Configure the HTTP request pipeline.
if (app.Environment.IsDevelopment())
{
    app.MapOpenApi();
}

app.UseHttpsRedirection();

app.UseAuthorization();

app.MapControllers();

app.MapGet("/api/diagnostico/banco", async (OdontoCareDbContext db) =>
{
    var conectado = await db.Database.CanConnectAsync();

    return conectado
        ? Results.Ok(new { mensagem = "Conexão com o banco realizada com sucesso." })
        : Results.Problem("Não foi possível conectar ao banco de dados.");
});

app.Run();
