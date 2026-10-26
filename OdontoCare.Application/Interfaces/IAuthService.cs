using OdontoCare.Application.DTOs;

namespace OdontoCare.Application.Interfaces;

public interface IAuthService
{
    Task<LoginResponseDto?> LoginAsync(LoginDto dto);
    Task<UsuarioDto> RegistrarAsync(RegistroUsuarioDto dto);
}

public interface IPasswordService
{
    string Hash(string senha);
    bool Verify(string senha, string hash);
}

public interface ITokenService
{
    (string Token, DateTime ExpiraEmUtc) Gerar(int id, string nome, string? email, string role);
}
