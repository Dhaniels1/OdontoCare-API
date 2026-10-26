using System.Text;
using Microsoft.AspNetCore.Authentication.JwtBearer;
using Microsoft.EntityFrameworkCore;
using Microsoft.IdentityModel.Tokens;
using OdontoCare.API.Security;
using OdontoCare.Application.Common;
using OdontoCare.Application.Interfaces;
using OdontoCare.Application.Services;
using OdontoCare.Infrastructure.Caching;
using OdontoCare.Infrastructure.Data;
using OdontoCare.Infrastructure.Repositories;
using OdontoCare.Infrastructure.Security;
using OdontoCare.Infrastructure.Services;

namespace OdontoCare.API.Extensions;

public static class DependencyInjection
{
    public static IServiceCollection AddOdontoCare(this IServiceCollection services, IConfiguration configuration)
    {
        var connection = configuration.GetConnectionString("OdontoCare");
        if (string.IsNullOrWhiteSpace(connection))
            throw new InvalidOperationException("Configure ConnectionStrings:OdontoCare por Secret Manager ou variável de ambiente.");
        services.AddDbContext<OdontoCareDbContext>(opt => opt.UseMySQL(connection));
        services.AddScoped<IPacienteService, PacienteService>();
        services.AddScoped<IConsultaLookupService, ConsultaLookupService>();
        services.AddScoped<IConsultaService, ConsultaService>();
        services.AddScoped<IPasswordService, Pbkdf2PasswordService>();
        services.AddScoped<IAuthService, AuthService>();
        services.AddScoped<IUsuarioAdminService, UsuarioAdminService>();
        services.AddSingleton<ITokenService, JwtTokenService>();
        services.AddScoped<IMaterialRepository, MaterialRepository>();
        services.AddScoped<IMaterialService, MaterialService>();
        services.AddScoped<IEquipamentoService, EquipamentoService>();
        services.AddMemoryCache(o => o.SizeLimit = 1024);
        services.AddSingleton<IMaterialCache, MaterialMemoryCache>();

        var jwt = configuration.GetSection("Jwt");
        var key = jwt["Key"];
        if (string.IsNullOrWhiteSpace(key) || Encoding.UTF8.GetByteCount(key) < 32)
            throw new InvalidOperationException("Jwt:Key deve ser uma chave secreta de pelo menos 32 bytes, fora do repositório.");
        if (string.IsNullOrWhiteSpace(jwt["Issuer"]) || string.IsNullOrWhiteSpace(jwt["Audience"]))
            throw new InvalidOperationException("Configure Jwt:Issuer e Jwt:Audience.");
        services.AddAuthentication(JwtBearerDefaults.AuthenticationScheme)
            .AddJwtBearer(options =>
            {
                options.MapInboundClaims = false;
                options.TokenValidationParameters = new TokenValidationParameters
                {
                    ValidateIssuer = true, ValidIssuer = jwt["Issuer"],
                    ValidateAudience = true, ValidAudience = jwt["Audience"],
                    ValidateIssuerSigningKey = true,
                    IssuerSigningKey = new SymmetricSecurityKey(Encoding.UTF8.GetBytes(key)),
                    ValidateLifetime = true,
                    ClockSkew = TimeSpan.FromSeconds(15),
                    NameClaimType = "name", RoleClaimType = "role"
                };
                options.Events = new JwtBearerEvents
                {
                    OnChallenge = async context =>
                    {
                        context.HandleResponse();
                        context.Response.StatusCode = StatusCodes.Status401Unauthorized;
                        await context.Response.WriteAsJsonAsync(ResultWrapper<object>.Unauthorized());
                    },
                    OnForbidden = async context =>
                    {
                        context.Response.StatusCode = StatusCodes.Status403Forbidden;
                        await context.Response.WriteAsJsonAsync(ResultWrapper<object>.Failure("Você não tem permissão para esta operação."));
                    }
                };
            });
        services.AddAuthorization();
        return services;
    }
}
