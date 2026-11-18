

namespace OdontoCare.Application.Common.Interfaces;

public interface ITokenService
{
    (string Token, DateTime ExpiraEmUtc) Gerar(int id, string nome, string? email, string role);
}
