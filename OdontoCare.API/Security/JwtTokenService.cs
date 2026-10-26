using System.IdentityModel.Tokens.Jwt;
using System.Security.Claims;
using System.Text;
using Microsoft.IdentityModel.Tokens;
using OdontoCare.Application.Interfaces;

namespace OdontoCare.API.Security;

public sealed class JwtTokenService(IConfiguration configuration) : ITokenService
{
    public (string Token, DateTime ExpiraEmUtc) Gerar(int id, string nome, string? email, string role)
    {
        var jwt = configuration.GetSection("Jwt");
        var key = configuration["Jwt:Key"] ?? throw new InvalidOperationException("Chave JWT não configurada.");
        var minutos = jwt.GetValue<int>("ExpirationMinutes", 60);
        var expira = DateTime.UtcNow.AddMinutes(minutos);
        var claims = new List<Claim>
        {
            new(JwtRegisteredClaimNames.Sub, id.ToString()),
            new(JwtRegisteredClaimNames.Jti, Guid.NewGuid().ToString()),
            new("name", nome), new("role", role)
        };
        if (!string.IsNullOrWhiteSpace(email)) claims.Add(new Claim(JwtRegisteredClaimNames.Email, email));
        var token = new JwtSecurityToken(
            issuer: jwt["Issuer"], audience: jwt["Audience"], claims: claims,
            expires: expira,
            signingCredentials: new SigningCredentials(
                new SymmetricSecurityKey(Encoding.UTF8.GetBytes(key)), SecurityAlgorithms.HmacSha256));
        return (new JwtSecurityTokenHandler().WriteToken(token), expira);
    }
}
