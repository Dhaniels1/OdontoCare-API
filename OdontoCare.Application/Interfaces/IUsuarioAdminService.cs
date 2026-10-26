using OdontoCare.Application.DTOs;
namespace OdontoCare.Application.Interfaces;
public interface IUsuarioAdminService
{
    Task<UsuarioDto?> AtualizarAsync(int id, UsuarioUpdateDto dto);
    Task<bool> ExcluirAsync(int id, int executorId);
}
