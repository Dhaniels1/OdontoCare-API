using OdontoCare.Application.Features.Auth.DTOs;
using OdontoCare.Application.Features.Usuarios.DTOs;

namespace OdontoCare.Application.Features.Usuarios.Interfaces;

public interface IUsuarioAdminService
{
    Task<UsuarioDto?> AtualizarAsync(int id, UsuarioUpdateDto dto);
    Task<bool> ExcluirAsync(int id, int executorId);
}
