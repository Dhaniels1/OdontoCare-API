using OdontoCare.Application.Features.Auth.DTOs;

namespace OdontoCare.Application.Features.Auth.Interfaces;

public interface IAuthService
{
    Task<LoginResponseDto?> LoginAsync(LoginDto dto);
    Task<UsuarioDto> RegistrarAsync(RegistroUsuarioDto dto);
}
